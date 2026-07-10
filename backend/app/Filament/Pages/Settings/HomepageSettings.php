<?php
namespace App\Filament\Pages\Settings;

use Anthropic\Client as AnthropicClient;
use App\Enums\NavigationGroupEnum;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class HomepageSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.settings.homepage-settings';

    public static function getNavigationIcon(): string  { return 'heroicon-o-home'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Content->value; }
    public static function getNavigationSort(): int     { return 5; }
    public static function getNavigationLabel(): string { return 'Homepage'; }
    public function getTitle(): string                  { return 'Homepage Content'; }

    public ?array $data = [];

    public function mount(): void
    {
        $flat   = Setting::all()->pluck('value', 'key')->toArray();
        $nested = Arr::undot($flat);
        $this->settingsForm->fill($nested);
    }

    public function settingsForm(Schema $schema): Schema
    {
        return $schema->components([

            // ── AI Generator ──────────────────────────────────────────────
            Section::make('🤖 AI Content Generator')
                ->description('Let Claude refresh your homepage hero and stats. Describe a theme or campaign, or leave blank for a general brand refresh.')
                ->schema([
                    Textarea::make('_ai_hp_prompt')
                        ->label('Theme or campaign (optional)')
                        ->placeholder('e.g. Eid gifting season — emphasise premium gifting, luxury packaging, GCC delivery')
                        ->rows(2)
                        ->dehydrated(false)
                        ->columnSpanFull(),

                    \Filament\Schemas\Components\Actions::make([
                        Action::make('generate_homepage_claude')
                            ->label('Generate with Claude')
                            ->icon('heroicon-o-sparkles')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Generate Homepage Content with Claude AI')
                            ->modalDescription('This will overwrite the hero text and stats bar. You can still adjust before saving. Continue?')
                            ->modalSubmitActionLabel('Yes, generate')
                            ->action(function ($get, $set) {
                                $apiKey = config('services.anthropic.key');
                                if (blank($apiKey)) {
                                    Notification::make()->title('Anthropic API key not set.')->warning()->send();
                                    return;
                                }
                                $theme = trim($get('_ai_hp_prompt') ?: 'General homepage refresh for a premium leather goods brand in Muscat, Oman.');
                                try {
                                    $data = app(\App\Services\AiPostService::class)->generateHomepageWithClaude($theme);
                                    $set('hero.eyebrow',         $data['hero_eyebrow']         ?? '');
                                    $set('hero.eyebrow_ar',      $data['hero_eyebrow_ar']      ?? '');
                                    $set('hero.headline',        $data['hero_headline']        ?? '');
                                    $set('hero.headline_ar',     $data['hero_headline_ar']     ?? '');
                                    $set('hero.headline_accent', $data['hero_headline_accent'] ?? '');
                                    $set('hero.headline_accent_ar', $data['hero_headline_accent_ar'] ?? '');
                                    $set('hero.subtitle',        $data['hero_subtitle']        ?? '');
                                    $set('hero.subtitle_ar',     $data['hero_subtitle_ar']     ?? '');
                                    $set('hero.cta_primary',     $data['hero_cta_primary']     ?? '');
                                    $set('hero.cta_primary_ar',  $data['hero_cta_primary_ar']  ?? '');
                                    $set('hero.cta_secondary',   $data['hero_cta_secondary']   ?? '');
                                    $set('hero.cta_secondary_ar', $data['hero_cta_secondary_ar'] ?? '');
                                    $set('stats.1.value',        $data['stat_1_value']         ?? '');
                                    $set('stats.1.label',        $data['stat_1_label']         ?? '');
                                    $set('stats.1.label_ar',     $data['stat_1_label_ar']      ?? '');
                                    $set('stats.2.value',        $data['stat_2_value']         ?? '');
                                    $set('stats.2.label',        $data['stat_2_label']         ?? '');
                                    $set('stats.2.label_ar',     $data['stat_2_label_ar']      ?? '');
                                    $set('stats.3.value',        $data['stat_3_value']         ?? '');
                                    $set('stats.3.label',        $data['stat_3_label']         ?? '');
                                    $set('stats.3.label_ar',     $data['stat_3_label_ar']      ?? '');
                                    $set('stats.4.value',        $data['stat_4_value']         ?? '');
                                    $set('stats.4.label',        $data['stat_4_label']         ?? '');
                                    $set('stats.4.label_ar',     $data['stat_4_label_ar']      ?? '');
                                    Notification::make()
                                        ->title('✅ Claude generated your homepage content!')
                                        ->body('Review all sections, then click Save.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title('Generation failed')->body($e->getMessage())->danger()->send();
                                }
                            }),

                        Action::make('generate_homepage_openai')
                            ->label('Generate with OpenAI')
                            ->icon('heroicon-o-cpu-chip')
                            ->color('info')
                            ->requiresConfirmation()
                            ->modalHeading('Generate Homepage Content with OpenAI (GPT-4o)')
                            ->modalDescription('This will overwrite the hero text and stats bar. You can still adjust before saving. Continue?')
                            ->modalSubmitActionLabel('Yes, generate')
                            ->action(function ($get, $set) {
                                $apiKey = config('services.openai.key');
                                if (blank($apiKey)) {
                                    Notification::make()->title('OpenAI API key not set.')->warning()->send();
                                    return;
                                }
                                $theme = trim($get('_ai_hp_prompt') ?: 'General homepage refresh for a premium leather goods brand in Muscat, Oman.');
                                try {
                                    $data = app(\App\Services\AiPostService::class)->generateHomepageWithOpenAI($theme);
                                    $set('hero.eyebrow',         $data['hero_eyebrow']         ?? '');
                                    $set('hero.eyebrow_ar',      $data['hero_eyebrow_ar']      ?? '');
                                    $set('hero.headline',        $data['hero_headline']        ?? '');
                                    $set('hero.headline_ar',     $data['hero_headline_ar']     ?? '');
                                    $set('hero.headline_accent', $data['hero_headline_accent'] ?? '');
                                    $set('hero.headline_accent_ar', $data['hero_headline_accent_ar'] ?? '');
                                    $set('hero.subtitle',        $data['hero_subtitle']        ?? '');
                                    $set('hero.subtitle_ar',     $data['hero_subtitle_ar']     ?? '');
                                    $set('hero.cta_primary',     $data['hero_cta_primary']     ?? '');
                                    $set('hero.cta_primary_ar',  $data['hero_cta_primary_ar']  ?? '');
                                    $set('hero.cta_secondary',   $data['hero_cta_secondary']   ?? '');
                                    $set('hero.cta_secondary_ar', $data['hero_cta_secondary_ar'] ?? '');
                                    $set('stats.1.value',        $data['stat_1_value']         ?? '');
                                    $set('stats.1.label',        $data['stat_1_label']         ?? '');
                                    $set('stats.1.label_ar',     $data['stat_1_label_ar']      ?? '');
                                    $set('stats.2.value',        $data['stat_2_value']         ?? '');
                                    $set('stats.2.label',        $data['stat_2_label']         ?? '');
                                    $set('stats.2.label_ar',     $data['stat_2_label_ar']      ?? '');
                                    $set('stats.3.value',        $data['stat_3_value']         ?? '');
                                    $set('stats.3.label',        $data['stat_3_label']         ?? '');
                                    $set('stats.3.label_ar',     $data['stat_3_label_ar']      ?? '');
                                    $set('stats.4.value',        $data['stat_4_value']         ?? '');
                                    $set('stats.4.label',        $data['stat_4_label']         ?? '');
                                    $set('stats.4.label_ar',     $data['stat_4_label_ar']      ?? '');
                                    Notification::make()
                                        ->title('✅ OpenAI generated your homepage content!')
                                        ->body('Review all sections, then click Save.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title('Generation failed')->body($e->getMessage())->danger()->send();
                                }
                            }),
                    ]),
                ]),

            Tabs::make('Homepage Content')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('English')
                        ->icon('heroicon-o-language')
                        ->schema([
                            Section::make('🏠 Hero')
                                ->description('The big headline and text visitors see first when they land on your website.')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('hero.eyebrow')
                                        ->label('Eyebrow Text')
                                        ->placeholder('Muscat · Sultanate of Oman')
                                        ->helperText('Small uppercase text above the headline.')
                                        ->columnSpanFull(),

                                    TextInput::make('hero.headline')
                                        ->label('Headline — Line 1 (white)')
                                        ->placeholder('Where Leather'),

                                    TextInput::make('hero.headline_accent')
                                        ->label('Headline — Line 2 (gold italic)')
                                        ->placeholder('Becomes Legacy'),

                                    Textarea::make('hero.subtitle')
                                        ->label('Subtitle')
                                        ->rows(2)
                                        ->placeholder('Handcrafted premium leather goods for those who appreciate the art of timeless elegance.')
                                        ->columnSpanFull(),

                                    TextInput::make('hero.cta_primary')
                                        ->label('Primary Button Label')
                                        ->placeholder('Explore Collection'),

                                    TextInput::make('hero.cta_secondary')
                                        ->label('Secondary Button Label')
                                        ->placeholder('Our Story'),
                                ]),

                            Section::make('📊 Stats Bar')
                                ->description('The four numbers shown below the hero. Update them as your business grows.')
                                ->columns(4)
                                ->schema([
                                    TextInput::make('stats.1.value')->label('Stat 1 — Value')->placeholder('100%'),
                                    TextInput::make('stats.2.value')->label('Stat 2 — Value')->placeholder('15+'),
                                    TextInput::make('stats.3.value')->label('Stat 3 — Value')->placeholder('50+'),
                                    TextInput::make('stats.4.value')->label('Stat 4 — Value')->placeholder('GCC'),
                                    TextInput::make('stats.1.label')->label('Stat 1 — Label')->placeholder('Handcrafted'),
                                    TextInput::make('stats.2.label')->label('Stat 2 — Label')->placeholder('Years of Excellence'),
                                    TextInput::make('stats.3.label')->label('Stat 3 — Label')->placeholder('Unique Designs'),
                                    TextInput::make('stats.4.label')->label('Stat 4 — Label')->placeholder('Wide Delivery'),
                                ]),

                            Section::make('📖 Story Section')
                                ->description('Homepage story block below collections. Separate from the full About page story.')
                                ->columns(2)
                                ->schema([
                                    FileUpload::make('home.story.image')
                                        ->label('Story Image')
                                        ->helperText('Optional. If empty, the AL monogram card is shown. Recommended: portrait image, e.g. 1000×1250px.')
                                        ->image()
                                        ->disk('public')
                                        ->directory('homepage/story')
                                        ->imageEditor()
                                        ->maxSize(5120)
                                        ->columnSpanFull(),

                                    TextInput::make('home.story.eyebrow')
                                        ->label('Eyebrow')
                                        ->placeholder('Our Story')
                                        ->columnSpanFull(),

                                    TextInput::make('home.story.title1')
                                        ->label('Title — Line 1')
                                        ->placeholder('Crafted with Passion,'),

                                    TextInput::make('home.story.title2')
                                        ->label('Title — Line 2')
                                        ->placeholder('Built to Last'),

                                    Textarea::make('home.story.p1')
                                        ->label('Paragraph 1')
                                        ->rows(3)
                                        ->columnSpanFull(),

                                    Textarea::make('home.story.p2')
                                        ->label('Paragraph 2')
                                        ->rows(3)
                                        ->columnSpanFull(),

                                    TextInput::make('home.story.years')
                                        ->label('Floating Card Title')
                                        ->placeholder('Building for the Future'),

                                    TextInput::make('home.story.years_label')
                                        ->label('Floating Card Subtitle')
                                        ->placeholder('Years of Craft'),

                                    TextInput::make('home.story.button_label')
                                        ->label('Button Label')
                                        ->placeholder('Discover Our Heritage'),

                                    TextInput::make('home.story.button_url')
                                        ->label('Button URL')
                                        ->placeholder('/about')
                                        ->helperText('Use an internal path like /about or a full URL.'),
                                ]),
                        ]),

                    Tab::make('Arabic / عربي')
                        ->icon('heroicon-o-language')
                        ->schema([
                            Section::make('🏠 Hero (Arabic)')
                                ->description('Arabic translations shown to Arabic-speaking visitors.')
                                ->columns(2)
                                ->schema(self::arabicFields([
                                    ['hero.eyebrow_ar', 'Eyebrow Text (Arabic)', 'text', null, true],
                                    ['hero.headline_ar', 'Headline — Line 1 (Arabic)', 'text'],
                                    ['hero.headline_accent_ar', 'Headline — Line 2 (Arabic)', 'text'],
                                    ['hero.subtitle_ar', 'Subtitle (Arabic)', 'textarea', 2, true],
                                    ['hero.cta_primary_ar', 'Primary Button Label (Arabic)', 'text'],
                                    ['hero.cta_secondary_ar', 'Secondary Button Label (Arabic)', 'text'],
                                ])),

                            Section::make('📊 Stats Bar (Arabic)')
                                ->description('Arabic labels for the stats bar. Values (e.g. 100%, 15+) stay the same in both languages.')
                                ->columns(4)
                                ->schema(self::arabicFields([
                                    ['stats.1.label_ar', 'Stat 1 — Label (Arabic)', 'text'],
                                    ['stats.2.label_ar', 'Stat 2 — Label (Arabic)', 'text'],
                                    ['stats.3.label_ar', 'Stat 3 — Label (Arabic)', 'text'],
                                    ['stats.4.label_ar', 'Stat 4 — Label (Arabic)', 'text'],
                                ])),

                            Section::make('📖 Story Section (Arabic)')
                                ->description('Arabic translations for the homepage story block.')
                                ->columns(2)
                                ->schema(self::arabicFields([
                                    ['home.story.eyebrow_ar', 'Eyebrow (Arabic)', 'text', null, true],
                                    ['home.story.title1_ar', 'Title — Line 1 (Arabic)', 'text'],
                                    ['home.story.title2_ar', 'Title — Line 2 (Arabic)', 'text'],
                                    ['home.story.p1_ar', 'Paragraph 1 (Arabic)', 'textarea', 3, true],
                                    ['home.story.p2_ar', 'Paragraph 2 (Arabic)', 'textarea', 3, true],
                                    ['home.story.years_ar', 'Floating Card Title (Arabic)', 'text'],
                                    ['home.story.years_label_ar', 'Floating Card Subtitle (Arabic)', 'text'],
                                    ['home.story.button_label_ar', 'Button Label (Arabic)', 'text'],
                                    ['home.story.button_url_ar', 'Button URL (Arabic)', 'text'],
                                ])),
                        ]),

                    Tab::make('Preview')
                        ->icon('heroicon-o-eye')
                        ->schema([
                            Section::make('🌐 Website Preview')
                                ->description('Live preview of how the homepage hero and stats bar look to visitors.')
                                ->schema([
                                    Placeholder::make('_homepage_preview')
                                        ->label('')
                                        ->content(function ($get) {
                                            $eyebrow  = e($get('hero.eyebrow')          ?: 'Muscat · Sultanate of Oman');
                                            $line1    = e($get('hero.headline')          ?: 'Where Leather');
                                            $line2    = e($get('hero.headline_accent')   ?: 'Becomes Legacy');
                                            $subtitle = e($get('hero.subtitle')          ?: 'Handcrafted premium leather goods for those who appreciate timeless elegance.');
                                            $cta1     = e($get('hero.cta_primary')       ?: 'Explore Collection');
                                            $cta2     = e($get('hero.cta_secondary')     ?: 'Our Story');

                                            $stats = '';
                                            for ($i = 1; $i <= 4; $i++) {
                                                $val   = e($get("stats.{$i}.value") ?: '—');
                                                $label = e($get("stats.{$i}.label") ?: "Stat {$i}");
                                                $stats .= '<div style="text-align:center;padding:16px 8px;background:#1a1208;">
                                                    <div style="font-size:22px;font-weight:700;color:#d4af37;">' . $val . '</div>
                                                    <div style="font-size:10px;color:rgba(255,255,255,0.45);margin-top:4px;text-transform:uppercase;letter-spacing:.06em;">' . $label . '</div>
                                                </div>';
                                            }

                                            return new HtmlString('
                                                <div style="max-width:680px;">
                                                    <div style="background:linear-gradient(135deg,#0d0a04,#1c1408);border-radius:12px 12px 0 0;padding:44px 40px;font-family:Georgia,serif;">
                                                        <div style="font-size:10px;letter-spacing:.55em;text-transform:uppercase;color:rgba(212,175,55,0.6);margin-bottom:18px;">' . $eyebrow . '</div>
                                                        <div style="font-size:34px;font-weight:300;color:#fff;line-height:1.2;margin-bottom:2px;">' . $line1 . '</div>
                                                        <div style="font-size:34px;font-weight:300;color:#d4af37;font-style:italic;line-height:1.2;margin-bottom:20px;">' . $line2 . '</div>
                                                        <p style="color:rgba(255,255,255,0.5);font-size:13px;line-height:1.7;margin:0 0 28px;max-width:460px;">' . $subtitle . '</p>
                                                        <div style="display:flex;gap:12px;">
                                                            <div style="background:#d4af37;color:#0d0a04;font-size:11px;font-weight:700;padding:10px 22px;border-radius:4px;letter-spacing:.06em;">' . $cta1 . '</div>
                                                            <div style="border:1px solid rgba(212,175,55,0.4);color:#d4af37;font-size:11px;padding:10px 22px;border-radius:4px;letter-spacing:.06em;">' . $cta2 . '</div>
                                                        </div>
                                                    </div>
                                                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:#2a1f0e;border-radius:0 0 12px 12px;overflow:hidden;">' . $stats . '</div>
                                                </div>
                                            ');
                                        })
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ]),

            // ── SEO ───────────────────────────────────────────────────────
            Section::make('🔍 SEO')
                ->description('Custom meta title and description for the Homepage. Overrides the site-wide defaults.')
                ->schema([
                    TextInput::make('homepage.seo.meta_title')
                        ->label('SEO Title')
                        ->maxLength(70)
                        ->placeholder('Luxury Handcrafted Leather Goods — Wallets, Bags & Accessories | Artisan Leather Oman')
                        ->helperText(fn($state) => sprintf('%d chars · Max 60 for best display %s', mb_strlen($state ?? ''), mb_strlen($state ?? '') > 60 ? '⚠️' : '✅'))
                        ->live(onBlur: true)
                        ->columnSpanFull(),

                    Textarea::make('homepage.seo.meta_description')
                        ->label('SEO Description')
                        ->maxLength(170)
                        ->rows(3)
                        ->placeholder('Discover premium handcrafted leather wallets, bags, belts and accessories from Artisan Leather, Muscat Oman. Free delivery across Oman and GCC.')
                        ->helperText(fn($state) => sprintf('%d chars · Max 160 chars %s', mb_strlen($state ?? ''), mb_strlen($state ?? '') > 160 ? '⚠️' : '✅'))
                        ->live(onBlur: true)
                        ->columnSpanFull(),

                    Placeholder::make('homepage_google_preview')
                        ->label('Google Preview')
                        ->content(function ($get) {
                            $title = $get('homepage.seo.meta_title') ?: 'Luxury Handcrafted Leather Goods — Wallets, Bags & Accessories | Artisan Leather Oman';
                            $desc  = $get('homepage.seo.meta_description') ?: 'Discover premium handcrafted leather wallets, bags, belts and accessories from Artisan Leather, Muscat Oman. Free delivery across Oman and GCC.';
                            return new HtmlString('
                                <div style="max-width:600px;font-family:arial,sans-serif;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
                                    <div style="font-size:12px;color:#006621;margin-bottom:2px;">artisanleatherom.com</div>
                                    <div style="font-size:18px;color:#1a0dab;margin-bottom:4px;">' . e(mb_substr($title, 0, 60)) . (mb_strlen($title) > 60 ? '...' : '') . '</div>
                                    <div style="font-size:13px;color:#545454;">' . e(mb_substr($desc, 0, 160)) . (mb_strlen($desc) > 160 ? '...' : '') . '</div>
                                </div>
                            ');
                        })
                        ->columnSpanFull(),
                ]),

            // ── SEO Ranking Potential ─────────────────────────────────────
            Section::make('📊 SEO Ranking Potential')
                ->description('Ask AI to score your homepage headline and subtitle for SEO impact and give you improvement tips.')
                ->collapsed()
                ->schema([
                    TextInput::make('_seo_score')->dehydrated(false)->hidden(),
                    Textarea::make('_seo_notes')->dehydrated(false)->hidden(),

                    \Filament\Schemas\Components\Actions::make([
                        Action::make('analyse_homepage_seo')
                            ->label('Analyse with AI')
                            ->icon('heroicon-o-sparkles')
                            ->color('warning')
                            ->action(function ($get, $set) {
                                $apiKey = config('services.anthropic.key');
                                if (blank($apiKey)) {
                                    Notification::make()->title('Anthropic API key not set.')->warning()->send();
                                    return;
                                }
                                $headline   = trim(($get('hero.headline') ?? '') . ' ' . ($get('hero.headline_accent') ?? ''));
                                $subtitle   = $get('hero.subtitle') ?? '';
                                $eyebrow    = $get('hero.eyebrow')  ?? '';
                                $headlineAr = trim(($get('hero.headline_ar') ?? '') . ' ' . ($get('hero.headline_accent_ar') ?? ''));
                                $subtitleAr = $get('hero.subtitle_ar') ?? '';
                                $eyebrowAr  = $get('hero.eyebrow_ar')  ?? '';
                                $context    = "Page: Homepage\n[English]\nEyebrow: {$eyebrow}\nHeadline: {$headline}\nSubtitle: {$subtitle}\n\n[Arabic]\nEyebrow: {$eyebrowAr}\nHeadline: {$headlineAr}\nSubtitle: {$subtitleAr}";
                                try {
                                    $client   = new AnthropicClient(apiKey: $apiKey);
                                    $response = $client->messages->create(
                                        model: 'claude-opus-4-8',
                                        maxTokens: 600,
                                        system: 'You are an SEO expert for Artisan Leather, a premium leather goods brand in Muscat, Oman targeting GCC shoppers. Return only valid JSON, no markdown.',
                                        messages: [['role' => 'user', 'content' => "Analyse this homepage content (both English and Arabic versions) for SEO and return JSON with exactly two keys:\n\"seo_score\" (integer 0-100) and \"seo_notes\" (string: 3-5 actionable tips covering both languages, each on its own line starting with a dash).\n\n{$context}"]],
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
                                return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Click "Analyse with AI" to get your SEO score and improvement tips.</p>');
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
                                            <div style="font-size:12px;color:#6b7280;">AI SEO Score out of 100</div>
                                        </div>
                                    </div>
                                    ' . $notesHtml . '
                                </div>
                            ');
                        })
                        ->columnSpanFull(),
                ]),

            // ── Google Competition ────────────────────────────────────────
            Section::make('🔍 Google Competition')
                ->description('See what currently ranks for your brand keywords — use it to sharpen your homepage messaging.')
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
                        Action::make('research_homepage_competition')
                            ->label('Research Competition')
                            ->icon('heroicon-o-magnifying-glass')
                            ->color('gray')
                            ->action(function ($get, $set) {
                                $query   = trim(($get('hero.headline') ?? '') . ' ' . ($get('hero.headline_accent') ?? ''));
                                $queryAr = trim(($get('hero.headline_ar') ?? '') . ' ' . ($get('hero.headline_accent_ar') ?? ''));
                                if (blank($query)) {
                                    Notification::make()->title('Enter a headline first.')->warning()->send();
                                    return;
                                }
                                if (blank($queryAr)) {
                                    $queryAr = $query;
                                }
                                try {
                                    $flat = Setting::pluck('value', 'key')->toArray();
                                    $key  = $flat['seo.serper_api_key'] ?? config('services.serper.key');
                                    if (blank($key)) {
                                        Notification::make()->title('Serper.dev not configured.')->body('Add API Key in Business Settings → SEO & Analytics.')->warning()->send();
                                        return;
                                    }
                                    $markets = self::competitionMarkets();
                                    $countryFilter = $get('_competition_country') ?? 'all';
                                    if ($countryFilter !== 'all' && isset($markets[$countryFilter])) {
                                        $markets = [$countryFilter => $markets[$countryFilter]];
                                    }
                                    $languages = ['en' => 'EN', 'ar' => 'AR'];
                                    $langFilter = $get('_competition_lang') ?? 'all';
                                    if ($langFilter !== 'all' && isset($languages[$langFilter])) {
                                        $languages = [$langFilter => $languages[$langFilter]];
                                    }
                                    $candidates = [];
                                    foreach ($markets as $gl => $market) {
                                        foreach ($languages as $hl => $langLabel) {
                                            $q = $hl === 'ar' ? $queryAr : $query;
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
                                    // Dedupe by domain so the same site doesn't repeat across markets
                                    $seenDomains = [];
                                    $results = [];
                                    foreach ($candidates as $candidate) {
                                        if (in_array($candidate['domain'], $seenDomains, true)) {
                                            continue;
                                        }
                                        $seenDomains[] = $candidate['domain'];
                                        $results[] = $candidate;
                                        if (count($results) >= 12) {
                                            break;
                                        }
                                    }
                                    $set('_competition_json', json_encode($results));
                                    if (empty($results)) {
                                        Notification::make()->title('No results found.')->warning()->send();
                                    }
                                } catch (\Throwable $e) {
                                    Notification::make()->title('Research failed')->body($e->getMessage())->danger()->send();
                                }
                            }),
                    ]),

                    Placeholder::make('_competition_preview')
                        ->label('')
                        ->content(function ($get) {
                            $json = $get('_competition_json') ?? '';
                            if (blank($json)) {
                                return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Click "Research Competition" to see what currently ranks for your homepage keywords.</p>');
                            }
                            $items = json_decode($json, true) ?: [];
                            if (empty($items)) {
                                return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">No results found.</p>');
                            }
                            $cards = '';
                            foreach ($items as $i => $item) {
                                $pos = $i + 1;
                                $cards .= '<div style="padding:12px 14px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;">
                                    <div style="font-size:11px;color:#6b7280;margin-bottom:2px;">#' . $pos . ' &nbsp;·&nbsp; ' . e($item['market'] ?? '') . ' &nbsp;·&nbsp; ' . e($item['domain'] ?? '') . '</div>
                                    <a href="' . e($item['url'] ?? '') . '" target="_blank" rel="noopener" style="font-size:15px;color:#1a0dab;text-decoration:none;font-weight:500;line-height:1.3;">' . e($item['title'] ?? '') . '</a>
                                    <div style="font-size:13px;color:#545454;margin-top:5px;line-height:1.5;">' . e($item['snippet'] ?? '') . '</div>
                                </div>';
                            }
                            return new HtmlString('<div style="font-family:arial,sans-serif;display:flex;flex-direction:column;gap:10px;max-width:680px;">' . $cards . '</div>');
                        })
                        ->columnSpanFull(),
                ]),

        ])->statePath('data');
    }

    protected static function arabicFields(array $specs): array
    {
        $fields = [];
        foreach ($specs as $spec) {
            [$path, $label, $type] = $spec;
            $rows           = $spec[3] ?? 3;
            $columnSpanFull = $spec[4] ?? false;

            $field = $type === 'textarea'
                ? Textarea::make($path)->rows($rows)
                : TextInput::make($path);

            $field = $field->label($label)->extraInputAttributes(['dir' => 'rtl']);

            if ($columnSpanFull) {
                $field = $field->columnSpanFull();
            }

            $fields[] = $field;
        }
        return $fields;
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
        return [
            'all' => 'English + Arabic',
            'en'  => 'English only',
            'ar'  => 'Arabic only',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->icon('heroicon-o-check')
                ->color('warning')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->settingsForm->getState();
        $flat  = Arr::dot($state);

        foreach ($flat as $key => $value) {
            Setting::set($key, $value);
        }

        Cache::flush();
        Notification::make()->title('✅ Homepage content saved!')->success()->send();
    }
}
