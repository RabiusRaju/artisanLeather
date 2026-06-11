<?php

namespace App\Filament\Resources\Testimonials;

use Anthropic\Client as AnthropicClient;
use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\Testimonials\Pages;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Services\AiPostService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    public static function getNavigationIcon(): string  { return 'heroicon-o-chat-bubble-left-right'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Content->value; }
    public static function getNavigationSort(): int     { return 3; }
    public static function getNavigationLabel(): string { return 'Testimonials'; }
    public static function getNavigationBadge(): ?string
    {
        $count = Testimonial::where('is_active', true)->count();
        return $count > 0 ? (string) $count : null;
    }
    public static function getNavigationBadgeColor(): string { return 'success'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            // ── 0. AI Generator ───────────────────────────────────────────
            Section::make('🤖 AI Content Generator')
                ->description('Describe the testimonial you want and AI will generate it instantly.')
                ->schema([
                    Textarea::make('ai_prompt')
                        ->label('Describe the testimonial')
                        ->placeholder('e.g. A 5-star review from a Riyadh businessman about the Heritage Bifold Wallet he has been using for 2 years')
                        ->rows(3)
                        ->dehydrated(false)
                        ->columnSpanFull(),

                    \Filament\Schemas\Components\Actions::make([

                        Action::make('generate_testimonial_claude')
                            ->label('Generate with Claude')
                            ->icon('heroicon-o-sparkles')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Generate with Claude AI')
                            ->modalDescription('This will overwrite any existing content in all fields. Continue?')
                            ->modalSubmitActionLabel('Yes, generate')
                            ->action(function ($get, $set) {
                                $prompt = $get('ai_prompt');
                                if (blank($prompt)) {
                                    Notification::make()->title('Please enter a description first.')->warning()->send();
                                    return;
                                }
                                try {
                                    $data = app(AiPostService::class)->generateTestimonialWithClaude($prompt);
                                    self::fillAiFields($set, $data);
                                    Notification::make()
                                        ->title('✅ Claude generated your testimonial!')
                                        ->body('Review and save.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title('Claude generation failed')->body($e->getMessage())->danger()->send();
                                }
                            }),

                        Action::make('generate_testimonial_openai')
                            ->label('Generate with OpenAI')
                            ->icon('heroicon-o-cpu-chip')
                            ->color('info')
                            ->requiresConfirmation()
                            ->modalHeading('Generate with OpenAI (GPT-4o)')
                            ->modalDescription('This will overwrite any existing content in all fields. Continue?')
                            ->modalSubmitActionLabel('Yes, generate')
                            ->action(function ($get, $set) {
                                $prompt = $get('ai_prompt');
                                if (blank($prompt)) {
                                    Notification::make()->title('Please enter a description first.')->warning()->send();
                                    return;
                                }
                                try {
                                    $data = app(AiPostService::class)->generateTestimonialWithOpenAI($prompt);
                                    self::fillAiFields($set, $data);
                                    Notification::make()
                                        ->title('✅ OpenAI generated your testimonial!')
                                        ->body('Review and save.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title('OpenAI generation failed')->body($e->getMessage())->danger()->send();
                                }
                            }),

                    ]),
                ]),

            // ── 1. Quote ──────────────────────────────────────────────────
            Section::make('Quote')
                ->description('Write exactly what the customer said. Arabic is optional — shown to Arabic-speaking visitors.')
                ->columns(2)
                ->schema([
                    Textarea::make('quote')
                        ->label('English')
                        ->required()
                        ->rows(5)
                        ->placeholder('The most exquisite wallet I have ever owned. The leather is buttery smooth and the craftsmanship is simply unmatched.')
                        ->helperText('Keep it in the customer\'s own words.')
                        ->columnSpan(1),

                    Textarea::make('quote_ar')
                        ->label('Arabic (optional)')
                        ->rows(5)
                        ->placeholder('أروع محفظة امتلكتها على الإطلاق...')
                        ->helperText('Leave blank to show the English quote to all visitors.')
                        ->columnSpan(1),
                ]),

            // ── 2. Customer ───────────────────────────────────────────────
            Section::make('Customer')
                ->description('Who gave this testimonial?')
                ->columns(3)
                ->schema([
                    TextInput::make('author')
                        ->label('Name')
                        ->required()
                        ->placeholder('Mohammed Al Rashidi')
                        ->live(onBlur: true)
                        ->columnSpan(1),

                    TextInput::make('location')
                        ->label('City / Country')
                        ->placeholder('Muscat, Oman')
                        ->live(onBlur: true)
                        ->columnSpan(1),

                    TextInput::make('product')
                        ->label('Product Purchased')
                        ->placeholder('Heritage Bifold Wallet')
                        ->helperText('Optional — for your reference only.')
                        ->columnSpan(1),

                    TextInput::make('author_ar')
                        ->label('Name (Arabic, optional)')
                        ->placeholder('محمد الراشدي')
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->columnSpan(1),

                    TextInput::make('location_ar')
                        ->label('City / Country (Arabic, optional)')
                        ->placeholder('مسقط، عُمان')
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->columnSpan(1),

                    TextInput::make('product_ar')
                        ->label('Product Purchased (Arabic, optional)')
                        ->placeholder('محفظة هيريتدج القابلة للطي')
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->helperText('Optional — for your reference only.')
                        ->columnSpan(1),
                ]),

            // ── 3. Rating + Visibility ────────────────────────────────────
            Section::make('Rating & Visibility')
                ->columns(3)
                ->schema([
                    Select::make('rating')
                        ->label('Star Rating')
                        ->options([
                            5 => '★★★★★  Excellent',
                            4 => '★★★★☆  Very Good',
                            3 => '★★★☆☆  Good',
                            2 => '★★☆☆☆  Fair',
                            1 => '★☆☆☆☆  Poor',
                        ])
                        ->default(5)
                        ->required()
                        ->live()
                        ->columnSpan(1),

                    TextInput::make('sort_order')
                        ->label('Display Order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower = shown first.')
                        ->columnSpan(1),

                    Toggle::make('is_active')
                        ->label('Show on website')
                        ->default(true)
                        ->inline(false)
                        ->columnSpan(1),
                ]),

            // ── 4. Preview (collapsed) ────────────────────────────────────
            Section::make('Website Preview')
                ->description('How this testimonial will look on the homepage. Expand to check before saving.')
                ->collapsed()
                ->schema([
                    Placeholder::make('preview_card')
                        ->label('')
                        ->content(function ($get) {
                            $quote    = $get('quote')    ?: 'Your quote will appear here…';
                            $author   = $get('author')   ?: 'Customer Name';
                            $location = $get('location') ?: '';
                            $rating   = max(1, min(5, (int) ($get('rating') ?: 5)));
                            $filled   = str_repeat('★', $rating);
                            $empty    = str_repeat('☆', 5 - $rating);

                            return new HtmlString('
                                <div style="max-width:560px;margin:0 auto;background:linear-gradient(135deg,#1a1208,#120D05);border:1px solid rgba(212,175,55,0.22);border-radius:12px;padding:36px 32px;text-align:center;font-family:Georgia,serif;">
                                    <div style="font-size:52px;color:rgba(212,175,55,0.25);line-height:1;margin-bottom:10px;user-select:none;">"</div>
                                    <p style="color:rgba(255,255,255,0.78);font-style:italic;font-size:15px;line-height:1.8;margin:0 0 24px;">' . e($quote) . '</p>
                                    <div style="width:32px;height:1px;background:rgba(212,175,55,0.4);margin:0 auto 16px;"></div>
                                    <div style="margin-bottom:12px;">
                                        <span style="color:#d4af37;font-size:16px;letter-spacing:3px;">' . $filled . '</span>
                                        <span style="color:rgba(255,255,255,0.12);font-size:16px;letter-spacing:3px;">' . $empty . '</span>
                                    </div>
                                    <p style="color:#fff;font-size:13px;font-weight:600;margin:0 0 5px;letter-spacing:.05em;">' . e($author) . '</p>
                                    ' . ($location ? '<p style="color:rgba(255,255,255,0.32);font-size:11px;margin:0;letter-spacing:.04em;">' . e($location) . '</p>' : '') . '
                                </div>
                            ');
                        })
                        ->columnSpanFull(),
                ]),

            // ── 5. SEO Ranking Potential ──────────────────────────────────
            Section::make('📊 SEO Ranking Potential')
                ->description('Ask AI to score this testimonial for on-page SEO value (keywords, product/location mentions) and give improvement tips.')
                ->collapsed()
                ->schema([
                    TextInput::make('_seo_score')->dehydrated(false)->hidden(),
                    Textarea::make('_seo_notes')->dehydrated(false)->hidden(),

                    \Filament\Schemas\Components\Actions::make([
                        Action::make('analyse_testimonial_seo')
                            ->label('Analyse with AI')
                            ->icon('heroicon-o-sparkles')
                            ->color('warning')
                            ->action(function ($get, $set) {
                                $apiKey = config('services.anthropic.key');
                                if (blank($apiKey)) {
                                    Notification::make()->title('Anthropic API key not set.')->warning()->send();
                                    return;
                                }
                                $quote    = $get('quote')    ?? '';
                                $quoteAr  = $get('quote_ar') ?? '';
                                $author   = $get('author')   ?? '';
                                $location = $get('location') ?? '';
                                $product  = $get('product')  ?? '';
                                if (blank($quote)) {
                                    Notification::make()->title('Write the quote first.')->warning()->send();
                                    return;
                                }
                                $context = "[English]\nQuote: {$quote}\nCustomer: {$author}\nLocation: {$location}\nProduct: {$product}";
                                if (!blank($quoteAr)) {
                                    $context .= "\n\n[Arabic]\nQuote: {$quoteAr}";
                                }
                                try {
                                    $client   = new AnthropicClient(apiKey: $apiKey);
                                    $response = $client->messages->create(
                                        model: 'claude-opus-4-8',
                                        maxTokens: 600,
                                        system: 'You are an SEO expert for Artisan Leather, a premium leather goods brand in Muscat, Oman targeting GCC shoppers. Return only valid JSON, no markdown.',
                                        messages: [['role' => 'user', 'content' => "Analyse this customer testimonial (English and, if provided, Arabic) for on-page SEO value (does it mention useful keywords, the product, location, or brand benefits that help search ranking?) and return JSON with exactly two keys:\n\"seo_score\" (integer 0-100) and \"seo_notes\" (string: 3-5 actionable tips to make this testimonial more SEO-valuable in both languages, each on its own line starting with a dash).\n\n{$context}"]],
                                    );
                                    $text = '';
                                    foreach ($response->content as $block) {
                                        if ($block->type === 'text') { $text = $block->text; break; }
                                    }
                                    $data = json_decode(trim($text), true) ?? [];
                                    $set('_seo_score', (string) ($data['seo_score'] ?? 0));
                                    $set('_seo_notes', $data['seo_notes'] ?? '');
                                    Notification::make()->title('✅ SEO analysis complete!')->success()->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title('Analysis failed')->body($e->getMessage())->danger()->send();
                                }
                            }),
                    ]),

                    Placeholder::make('_seo_score_card')
                        ->label('')
                        ->content(function ($get) {
                            $score = (int) ($get('_seo_score') ?? 0);
                            $notes = trim($get('_seo_notes') ?? '');

                            if ($score === 0 && blank($notes)) {
                                return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Click "Analyse with AI" to get the SEO ranking potential score and improvement tips.</p>');
                            }

                            $color = $score >= 75 ? '#16a34a' : ($score >= 50 ? '#d97706' : '#dc2626');
                            $label = $score >= 75 ? 'Strong' : ($score >= 50 ? 'Average' : 'Needs Work');

                            $notesHtml = '';
                            if (!blank($notes)) {
                                $lines = array_filter(array_map('trim', explode("\n", $notes)));
                                $items = implode('', array_map(fn($l) => '<li style="margin-bottom:6px;">' . e($l) . '</li>', $lines));
                                $notesHtml = '<div style="margin-top:14px;"><div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;">Improvement Tips</div><ul style="margin:0;padding-left:18px;color:#374151;font-size:13px;line-height:1.6;">' . $items . '</ul></div>';
                            }

                            return new HtmlString('
                                <div style="font-family:sans-serif;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;max-width:600px;">
                                    <div style="display:flex;align-items:center;gap:16px;">
                                        <div style="flex-shrink:0;width:64px;height:64px;border-radius:50%;background:' . $color . ';display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:700;">' . $score . '</div>
                                        <div>
                                            <div style="font-size:20px;font-weight:700;color:' . $color . ';">' . $label . '</div>
                                            <div style="font-size:12px;color:#6b7280;">AI Ranking Potential Score out of 100</div>
                                        </div>
                                    </div>
                                    ' . $notesHtml . '
                                </div>
                            ');
                        })
                        ->columnSpanFull(),
                ]),

            // ── 6. Google Competition ─────────────────────────────────────
            Section::make('🔍 Google Competition')
                ->description('See what currently ranks for this product/testimonial topic — so you can highlight points your competitors miss.')
                ->collapsed()
                ->schema([
                    Textarea::make('_competition_json')->dehydrated(false)->hidden(),

                    Select::make('_competition_country')
                        ->label('Country')
                        ->dehydrated(false)
                        ->default('all')
                        ->options(self::competitionCountryOptions()),

                    Select::make('_competition_lang')
                        ->label('Language')
                        ->dehydrated(false)
                        ->default('all')
                        ->options(self::competitionLanguageOptions()),

                    \Filament\Schemas\Components\Actions::make([
                        Action::make('research_testimonial_competition')
                            ->label('Research Competition')
                            ->icon('heroicon-o-magnifying-glass')
                            ->color('gray')
                            ->action(function ($get, $set) {
                                $product = trim($get('product') ?? '');
                                $query   = $product ? ($product . ' leather Oman') : 'handcrafted leather goods Oman';
                                $queryAr = $product ? ($product . ' جلد عمان') : 'منتجات جلدية يدوية الصنع عمان';
                                try {
                                    $results = self::fetchCompetitionData($query, $get('_competition_country') ?? 'all', $get('_competition_lang') ?? 'all', $queryAr);
                                    $set('_competition_json', json_encode($results));
                                    if (empty($results)) {
                                        Notification::make()
                                            ->title('No results returned.')
                                            ->body('Check your Serper.dev settings in Business Settings → SEO & Analytics.')
                                            ->warning()->send();
                                    }
                                } catch (\Throwable $e) {
                                    Notification::make()
                                        ->title('Research failed')
                                        ->body($e->getMessage())
                                        ->danger()->send();
                                }
                            }),
                    ]),

                    Placeholder::make('_competition_preview')
                        ->label('')
                        ->content(function ($get) {
                            $json = $get('_competition_json') ?? '';
                            if (blank($json)) {
                                return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Click "Research Competition" to see what currently ranks for this product.</p>');
                            }
                            $items = json_decode($json, true) ?: [];
                            if (empty($items)) {
                                return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">No results found for this query.</p>');
                            }
                            $cards = '';
                            foreach ($items as $i => $item) {
                                $pos     = $i + 1;
                                $title   = e($item['title']   ?? '');
                                $url     = e($item['url']     ?? '');
                                $domain  = e($item['domain']  ?? '');
                                $snippet = e($item['snippet'] ?? '');
                                $cards  .= '
                                    <div style="padding:12px 14px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;">
                                        <div style="font-size:11px;color:#6b7280;margin-bottom:2px;">#' . $pos . ' &nbsp;·&nbsp; ' . e($item['market'] ?? '') . ' &nbsp;·&nbsp; ' . $domain . '</div>
                                        <a href="' . $url . '" target="_blank" rel="noopener" style="font-size:15px;color:#1a0dab;text-decoration:none;font-weight:500;line-height:1.3;">' . $title . '</a>
                                        <div style="font-size:13px;color:#545454;margin-top:5px;line-height:1.5;">' . $snippet . '</div>
                                    </div>';
                            }
                            return new HtmlString('<div style="font-family:arial,sans-serif;display:flex;flex-direction:column;gap:10px;max-width:680px;">' . $cards . '</div>');
                        })
                        ->columnSpanFull(),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('author')
                    ->label('Customer')
                    ->searchable()
                    ->formatStateUsing(function ($state, Testimonial $record) {
                        $words    = explode(' ', trim($state));
                        $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1));
                        $location = e($record->location ?? '');
                        $name     = e($state);

                        return "
                            <div style='display:flex;align-items:center;gap:10px'>
                                <div style='
                                    width:38px;height:38px;border-radius:50%;
                                    background:rgba(212,175,55,0.12);
                                    border:1px solid rgba(212,175,55,0.35);
                                    display:flex;align-items:center;justify-content:center;
                                    color:#d4af37;font-weight:700;font-size:13px;flex-shrink:0;
                                '>{$initials}</div>
                                <div>
                                    <div style='font-weight:600;font-size:13px'>{$name}</div>
                                    <div style='font-size:11px;color:#9ca3af'>{$location}</div>
                                </div>
                            </div>
                        ";
                    })
                    ->html(),

                TextColumn::make('quote')
                    ->label('Quote')
                    ->limit(75)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn($state) =>
                        '<span style="color:#d4af37;font-size:13px">' . str_repeat('★', (int)$state) . '</span>' .
                        '<span style="color:#4b5563;font-size:13px">' . str_repeat('★', 5 - (int)$state) . '</span>'
                    )
                    ->html()
                    ->sortable(),

                TextColumn::make('product')
                    ->label('Product')
                    ->badge()
                    ->color('warning')
                    ->placeholder('—'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No testimonials yet')
            ->emptyStateDescription('Add your first customer testimonial to show on the website.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit'   => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }

    protected static function competitionMarkets(): array
    {
        return [
            'om' => ['label' => '🇴🇲 Oman',         'location' => 'Muscat, Oman'],
            'ae' => ['label' => '🇦🇪 UAE',          'location' => 'Dubai, United Arab Emirates'],
            'sa' => ['label' => '🇸🇦 Saudi Arabia', 'location' => 'Riyadh, Saudi Arabia'],
            'qa' => ['label' => '🇶🇦 Qatar',        'location' => 'Doha, Qatar'],
            'kw' => ['label' => '🇰🇼 Kuwait',       'location' => 'Kuwait City, Kuwait'],
            'bh' => ['label' => '🇧🇭 Bahrain',      'location' => 'Manama, Bahrain'],
        ];
    }

    protected static function competitionCountryOptions(): array
    {
        return ['all' => '🌍 All GCC Countries'] + array_map(fn($m) => $m['label'], self::competitionMarkets());
    }

    protected static function competitionLanguageOptions(): array
    {
        return ['all' => 'English + Arabic', 'en' => 'English only', 'ar' => 'Arabic only'];
    }

    private static function fetchCompetitionData(string $query, string $countryFilter = 'all', string $langFilter = 'all', string $queryAr = ''): array
    {
        $flat = Setting::pluck('value', 'key')->toArray();
        $key  = $flat['seo.serper_api_key'] ?? config('services.serper.key');
        if (blank($key)) {
            throw new \RuntimeException('Serper.dev not configured. Add API Key in Business Settings → SEO & Analytics.');
        }

        $markets = self::competitionMarkets();
        if ($countryFilter !== 'all' && isset($markets[$countryFilter])) {
            $markets = [$countryFilter => $markets[$countryFilter]];
        }

        $languages = ['en' => 'EN', 'ar' => 'AR'];
        if ($langFilter !== 'all' && isset($languages[$langFilter])) {
            $languages = [$langFilter => $languages[$langFilter]];
        }

        $candidates = [];
        foreach ($markets as $gl => $market) {
            foreach ($languages as $hl => $langLabel) {
                $q = ($hl === 'ar' && $queryAr !== '') ? $queryAr : $query;
                $response = Http::timeout(10)
                    ->withHeaders(['X-API-KEY' => $key, 'Content-Type' => 'application/json'])
                    ->post('https://google.serper.dev/search', [
                        'q' => $q, 'num' => 3, 'gl' => $gl, 'hl' => $hl, 'location' => $market['location'],
                    ]);
                if (!$response->successful()) {
                    continue;
                }
                foreach ($response->json('organic', []) as $item) {
                    $url = $item['link'] ?? '';
                    $candidates[] = ['title' => $item['title'] ?? '', 'url' => $url, 'domain' => parse_url($url, PHP_URL_HOST) ?: $url, 'snippet' => $item['snippet'] ?? '', 'market' => $market['label'] . ' · ' . $langLabel];
                }
            }
        }

        $seenDomains = [];
        $results     = [];
        foreach ($candidates as $candidate) {
            if (in_array($candidate['domain'], $seenDomains, true)) {
                continue;
            }
            $seenDomains[] = $candidate['domain'];
            $results[]     = $candidate;
            if (count($results) >= 12) {
                break;
            }
        }

        return $results;
    }

    private static function fillAiFields($set, array $data): void
    {
        $set('quote',    $data['quote']    ?? '');
        $set('quote_ar', $data['quote_ar'] ?? '');
        $set('author',   $data['author']   ?? '');
        $set('location', $data['location'] ?? '');
        $set('product',  $data['product']  ?? '');
        if (isset($data['rating'])) {
            $set('rating', (int) $data['rating']);
        }
    }
}
