<?php
namespace App\Filament\Pages\Settings;

use Anthropic\Client as AnthropicClient;
use App\Enums\NavigationGroupEnum;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class AboutPageSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.settings.about-page-settings';

    public static function getNavigationIcon(): string  { return 'heroicon-o-book-open'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Content->value; }
    public static function getNavigationSort(): int     { return 4; }
    public static function getNavigationLabel(): string { return 'About Page'; }
    public function getTitle(): string                  { return 'About Page Content'; }

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
                ->description('Let Claude rewrite the entire About page in one click. Describe a tone or theme, or leave blank for a general brand refresh.')
                ->schema([
                    Textarea::make('_ai_about_prompt')
                        ->label('Theme or tone (optional)')
                        ->placeholder('e.g. Focus on sustainability and ethical sourcing, appeal to eco-conscious GCC shoppers')
                        ->rows(2)
                        ->dehydrated(false)
                        ->columnSpanFull(),

                    \Filament\Schemas\Components\Actions::make([
                        Action::make('generate_about_claude')
                            ->label('Generate with Claude')
                            ->icon('heroicon-o-sparkles')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Generate About Page Content with Claude AI')
                            ->modalDescription('This will overwrite all About page sections (hero, story, craft steps, materials, values, timeline, CTA). You can still adjust before saving. Continue?')
                            ->modalSubmitActionLabel('Yes, generate everything')
                            ->action(function ($get, $set) {
                                $apiKey = config('services.anthropic.key');
                                if (blank($apiKey)) {
                                    Notification::make()->title('Anthropic API key not set.')->warning()->send();
                                    return;
                                }
                                $theme = trim($get('_ai_about_prompt') ?: 'General About page refresh for a premium leather goods brand in Muscat, Oman — heritage, craftsmanship, authenticity.');
                                try {
                                    $data = app(\App\Services\AiPostService::class)->generateAboutPageWithClaude($theme);

                                    // Hero
                                    $set('about.hero.eyebrow',         $data['hero_eyebrow']         ?? '');
                                    $set('about.hero.headline',        $data['hero_headline']        ?? '');
                                    $set('about.hero.headline_accent', $data['hero_headline_accent'] ?? '');
                                    $set('about.hero.subtitle',        $data['hero_subtitle']        ?? '');

                                    // Story
                                    $set('about.story.headline',        $data['story_headline']        ?? '');
                                    $set('about.story.headline_accent', $data['story_headline_accent'] ?? '');
                                    $set('about.story.years',           $data['story_years']           ?? '');
                                    $set('about.story.p1',              $data['story_p1']              ?? '');
                                    $set('about.story.p2',              $data['story_p2']              ?? '');
                                    $set('about.story.p3',              $data['story_p3']              ?? '');

                                    // Craft Steps
                                    for ($i = 1; $i <= 4; $i++) {
                                        $set("about.craft.{$i}.num",   $data["craft_{$i}_num"]   ?? "0{$i}");
                                        $set("about.craft.{$i}.title", $data["craft_{$i}_title"] ?? '');
                                        $set("about.craft.{$i}.body",  $data["craft_{$i}_body"]  ?? '');
                                    }

                                    // Materials
                                    for ($i = 1; $i <= 3; $i++) {
                                        $set("about.material.{$i}.name",     $data["material_{$i}_name"]     ?? '');
                                        $set("about.material.{$i}.subtitle", $data["material_{$i}_subtitle"] ?? '');
                                        $set("about.material.{$i}.desc",     $data["material_{$i}_desc"]     ?? '');
                                    }

                                    // Values
                                    $numerals = ['I', 'II', 'III', 'IV'];
                                    for ($i = 1; $i <= 4; $i++) {
                                        $set("about.value.{$i}.number", $data["value_{$i}_number"] ?? $numerals[$i - 1]);
                                        $set("about.value.{$i}.title",  $data["value_{$i}_title"]  ?? '');
                                        $set("about.value.{$i}.desc",   $data["value_{$i}_desc"]   ?? '');
                                    }

                                    // Timeline
                                    for ($i = 1; $i <= 5; $i++) {
                                        $set("about.timeline.{$i}.year",  $data["timeline_{$i}_year"]  ?? '');
                                        $set("about.timeline.{$i}.title", $data["timeline_{$i}_title"] ?? '');
                                        $set("about.timeline.{$i}.desc",  $data["timeline_{$i}_desc"]  ?? '');
                                    }

                                    // CTA
                                    $set('about.cta.heading', $data['cta_heading'] ?? '');
                                    $set('about.cta.text',    $data['cta_text']    ?? '');

                                    Notification::make()
                                        ->title('✅ Claude generated your About page!')
                                        ->body('Review all sections, then click Save.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title('Generation failed')->body($e->getMessage())->danger()->send();
                                }
                            }),
                    ]),
                ]),

            // ── Hero ─────────────────────────────────────────────────────
            Section::make('🎯 Page Hero')
                ->description('The banner displayed at the top of the About page.')
                ->columns(2)
                ->schema([
                    TextInput::make('about.hero.eyebrow')
                        ->label('Eyebrow Text')
                        ->placeholder('Muscat · Oman · Est. 2009')
                        ->columnSpanFull(),

                    TextInput::make('about.hero.headline')
                        ->label('Headline — Line 1 (white)')
                        ->placeholder('A Story Written'),

                    TextInput::make('about.hero.headline_accent')
                        ->label('Headline — Line 2 (gold italic)')
                        ->placeholder('in Leather'),

                    TextInput::make('about.hero.subtitle')
                        ->label('Subtitle')
                        ->placeholder('Sixteen years of craft. One unwavering standard.')
                        ->columnSpanFull(),
                ]),

            // ── Our Story ─────────────────────────────────────────────────
            Section::make('📖 Our Story')
                ->description('Brand story section shown beside the brand image.')
                ->columns(2)
                ->schema([
                    TextInput::make('about.story.headline')
                        ->label('Story Headline — Line 1 (white)')
                        ->placeholder('Born from a Love'),

                    TextInput::make('about.story.headline_accent')
                        ->label('Story Headline — Line 2 (gold italic)')
                        ->placeholder('of the Craft'),

                    TextInput::make('about.story.years')
                        ->label('Years Badge (shown on the image card)')
                        ->placeholder('16+')
                        ->helperText('e.g. 16+  — updates automatically once a year if you keep it current.')
                        ->columnSpanFull(),

                    Textarea::make('about.story.p1')
                        ->label('Paragraph 1')
                        ->rows(3)
                        ->placeholder('Artisan Leather began not as a business plan, but as an obsession...')
                        ->columnSpanFull(),

                    Textarea::make('about.story.p2')
                        ->label('Paragraph 2')
                        ->rows(3)
                        ->placeholder('The first workshop was a single room in Muscat...')
                        ->columnSpanFull(),

                    Textarea::make('about.story.p3')
                        ->label('Paragraph 3')
                        ->rows(3)
                        ->placeholder('Today, every piece that leaves our workshop is still inspected by hand...')
                        ->columnSpanFull(),
                ]),

            // ── Craft Steps ───────────────────────────────────────────────
            Section::make('🔨 Craft Steps')
                ->description('The 4 steps shown in "The Art of Making" section.')
                ->columns(3)
                ->schema([
                    TextInput::make('about.craft.1.num')
                        ->label('Step 1 — Number')->placeholder('01'),
                    TextInput::make('about.craft.1.title')
                        ->label('Step 1 — Title')->placeholder('Select the Hide')->columnSpan(2),
                    Textarea::make('about.craft.1.body')
                        ->label('Step 1 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.craft.2.num')
                        ->label('Step 2 — Number')->placeholder('02'),
                    TextInput::make('about.craft.2.title')
                        ->label('Step 2 — Title')->placeholder('Cut & Shape')->columnSpan(2),
                    Textarea::make('about.craft.2.body')
                        ->label('Step 2 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.craft.3.num')
                        ->label('Step 3 — Number')->placeholder('03'),
                    TextInput::make('about.craft.3.title')
                        ->label('Step 3 — Title')->placeholder('Hand Stitch')->columnSpan(2),
                    Textarea::make('about.craft.3.body')
                        ->label('Step 3 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.craft.4.num')
                        ->label('Step 4 — Number')->placeholder('04'),
                    TextInput::make('about.craft.4.title')
                        ->label('Step 4 — Title')->placeholder('Finish & Age')->columnSpan(2),
                    Textarea::make('about.craft.4.body')
                        ->label('Step 4 — Description')->rows(2)->columnSpanFull(),
                ]),

            // ── Materials ─────────────────────────────────────────────────
            Section::make('🧵 Materials')
                ->description('The 3 leather material cards shown on the About page.')
                ->columns(3)
                ->schema([
                    TextInput::make('about.material.1.name')
                        ->label('Material 1 — Name')->placeholder('Full Grain'),
                    TextInput::make('about.material.1.subtitle')
                        ->label('Material 1 — Subtitle')->placeholder('The Pinnacle of Leather')->columnSpan(2),
                    Textarea::make('about.material.1.desc')
                        ->label('Material 1 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.material.2.name')
                        ->label('Material 2 — Name')->placeholder('Vegetable Tanned'),
                    TextInput::make('about.material.2.subtitle')
                        ->label('Material 2 — Subtitle')->placeholder('Slow-Made & Sustainable')->columnSpan(2),
                    Textarea::make('about.material.2.desc')
                        ->label('Material 2 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.material.3.name')
                        ->label('Material 3 — Name')->placeholder('Italian Calfskin'),
                    TextInput::make('about.material.3.subtitle')
                        ->label('Material 3 — Subtitle')->placeholder('Silken & Refined')->columnSpan(2),
                    Textarea::make('about.material.3.desc')
                        ->label('Material 3 — Description')->rows(2)->columnSpanFull(),
                ]),

            // ── Values ────────────────────────────────────────────────────
            Section::make('⚖️ Our Values')
                ->description('The 4 value pillars (Heritage, Precision, Longevity, Authenticity).')
                ->columns(2)
                ->schema([
                    TextInput::make('about.value.1.number')
                        ->label('Value 1 — Roman Numeral')->placeholder('I'),
                    TextInput::make('about.value.1.title')
                        ->label('Value 1 — Title')->placeholder('Heritage'),
                    Textarea::make('about.value.1.desc')
                        ->label('Value 1 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.value.2.number')
                        ->label('Value 2 — Roman Numeral')->placeholder('II'),
                    TextInput::make('about.value.2.title')
                        ->label('Value 2 — Title')->placeholder('Precision'),
                    Textarea::make('about.value.2.desc')
                        ->label('Value 2 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.value.3.number')
                        ->label('Value 3 — Roman Numeral')->placeholder('III'),
                    TextInput::make('about.value.3.title')
                        ->label('Value 3 — Title')->placeholder('Longevity'),
                    Textarea::make('about.value.3.desc')
                        ->label('Value 3 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.value.4.number')
                        ->label('Value 4 — Roman Numeral')->placeholder('IV'),
                    TextInput::make('about.value.4.title')
                        ->label('Value 4 — Title')->placeholder('Authenticity'),
                    Textarea::make('about.value.4.desc')
                        ->label('Value 4 — Description')->rows(2)->columnSpanFull(),
                ]),

            // ── Timeline ──────────────────────────────────────────────────
            Section::make('📅 Our Journey')
                ->description('The 5 milestone events shown in the timeline.')
                ->columns(3)
                ->schema([
                    TextInput::make('about.timeline.1.year')
                        ->label('Event 1 — Year')->placeholder('2009'),
                    TextInput::make('about.timeline.1.title')
                        ->label('Event 1 — Title')->placeholder('First Workshop')->columnSpan(2),
                    Textarea::make('about.timeline.1.desc')
                        ->label('Event 1 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.timeline.2.year')
                        ->label('Event 2 — Year')->placeholder('2013'),
                    TextInput::make('about.timeline.2.title')
                        ->label('Event 2 — Title')->placeholder('First Collection')->columnSpan(2),
                    Textarea::make('about.timeline.2.desc')
                        ->label('Event 2 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.timeline.3.year')
                        ->label('Event 3 — Year')->placeholder('2018'),
                    TextInput::make('about.timeline.3.title')
                        ->label('Event 3 — Title')->placeholder('GCC Expansion')->columnSpan(2),
                    Textarea::make('about.timeline.3.desc')
                        ->label('Event 3 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.timeline.4.year')
                        ->label('Event 4 — Year')->placeholder('2023'),
                    TextInput::make('about.timeline.4.title')
                        ->label('Event 4 — Title')->placeholder('Flagship Identity')->columnSpan(2),
                    Textarea::make('about.timeline.4.desc')
                        ->label('Event 4 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.timeline.5.year')
                        ->label('Event 5 — Year')->placeholder('2025'),
                    TextInput::make('about.timeline.5.title')
                        ->label('Event 5 — Title')->placeholder('Online Launch')->columnSpan(2),
                    Textarea::make('about.timeline.5.desc')
                        ->label('Event 5 — Description')->rows(2)->columnSpanFull(),
                ]),

            // ── CTA ───────────────────────────────────────────────────────
            Section::make('💬 Call to Action')
                ->description('The bottom banner encouraging visitors to shop.')
                ->columns(1)
                ->schema([
                    TextInput::make('about.cta.heading')
                        ->label('Heading')
                        ->placeholder('Own a Piece of the Craft'),

                    Textarea::make('about.cta.text')
                        ->label('Body Text')
                        ->rows(3)
                        ->placeholder('Every wallet, bag, and belt we make is a promise...'),
                ]),

            // ── SEO ───────────────────────────────────────────────────────
            Section::make('🔍 SEO')
                ->description('Custom meta title and description for the About page. Overrides the site-wide defaults.')
                ->schema([
                    TextInput::make('about.seo.meta_title')
                        ->label('SEO Title')
                        ->maxLength(70)
                        ->placeholder('Our Story — Handcrafted Leather Artisans in Muscat, Oman')
                        ->helperText(fn($state) => sprintf('%d chars · Max 60 for best display %s', mb_strlen($state ?? ''), mb_strlen($state ?? '') > 60 ? '⚠️' : '✅'))
                        ->live(onBlur: true)
                        ->columnSpanFull(),

                    Textarea::make('about.seo.meta_description')
                        ->label('SEO Description')
                        ->maxLength(170)
                        ->rows(3)
                        ->placeholder('Learn about Artisan Leather\'s heritage, craftsmanship philosophy, and the skilled artisans behind every handcrafted leather piece made in Muscat, Oman.')
                        ->helperText(fn($state) => sprintf('%d chars · Max 160 chars %s', mb_strlen($state ?? ''), mb_strlen($state ?? '') > 160 ? '⚠️' : '✅'))
                        ->live(onBlur: true)
                        ->columnSpanFull(),

                    Placeholder::make('about_google_preview')
                        ->label('Google Preview')
                        ->content(function ($get) {
                            $title = $get('about.seo.meta_title') ?: 'Our Story — Handcrafted Leather Artisans in Muscat, Oman';
                            $desc  = $get('about.seo.meta_description') ?: 'Learn about Artisan Leather\'s heritage, craftsmanship philosophy, and the skilled artisans behind every handcrafted leather piece made in Muscat, Oman.';
                            return new HtmlString('
                                <div style="max-width:600px;font-family:arial,sans-serif;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
                                    <div style="font-size:12px;color:#006621;margin-bottom:2px;">artisanleatherom.com › about</div>
                                    <div style="font-size:18px;color:#1a0dab;margin-bottom:4px;">' . e(mb_substr($title, 0, 60)) . (mb_strlen($title) > 60 ? '...' : '') . '</div>
                                    <div style="font-size:13px;color:#545454;">' . e(mb_substr($desc, 0, 160)) . (mb_strlen($desc) > 160 ? '...' : '') . '</div>
                                </div>
                            ');
                        })
                        ->columnSpanFull(),
                ]),

            // ── Preview ───────────────────────────────────────────────────
            Section::make('🌐 Website Preview')
                ->description('Live preview of the About page hero, story, values and CTA as visitors see them.')
                ->collapsed()
                ->schema([
                    Placeholder::make('_about_preview')
                        ->label('')
                        ->content(function ($get) {
                            $eyebrow  = e($get('about.hero.eyebrow')        ?: 'Muscat · Oman · Est. 2009');
                            $line1    = e($get('about.hero.headline')        ?: 'A Story Written');
                            $line2    = e($get('about.hero.headline_accent') ?: 'in Leather');
                            $subtitle = e($get('about.hero.subtitle')        ?: 'Sixteen years of craft. One unwavering standard.');

                            $sHeadline = e($get('about.story.headline')        ?: 'Born from a Love');
                            $sAccent   = e($get('about.story.headline_accent') ?: 'of the Craft');
                            $years     = e($get('about.story.years')           ?: '16+');
                            $p1        = e($get('about.story.p1')              ?: 'Artisan Leather began not as a business plan, but as an obsession with material and making.');

                            $values = '';
                            for ($i = 1; $i <= 4; $i++) {
                                $num   = e($get("about.value.{$i}.number") ?: ['I','II','III','IV'][$i-1]);
                                $title = e($get("about.value.{$i}.title")  ?: ['Heritage','Precision','Longevity','Authenticity'][$i-1]);
                                $values .= '<div style="text-align:center;padding:14px 8px;background:#1a1208;border-radius:6px;">
                                    <div style="font-size:16px;font-weight:700;color:#d4af37;font-style:italic;margin-bottom:4px;">' . $num . '</div>
                                    <div style="font-size:11px;color:rgba(255,255,255,0.55);text-transform:uppercase;letter-spacing:.08em;">' . $title . '</div>
                                </div>';
                            }

                            $ctaHeading = e($get('about.cta.heading') ?: 'Own a Piece of the Craft');
                            $ctaText    = e($get('about.cta.text')    ?: 'Every wallet, bag, and belt we make is a promise…');

                            return new HtmlString('
                                <div style="max-width:680px;font-family:Georgia,serif;display:flex;flex-direction:column;gap:12px;">

                                    <!-- Hero -->
                                    <div style="background:linear-gradient(135deg,#0d0a04,#1c1408);border-radius:12px;padding:36px 32px;">
                                        <div style="font-size:10px;letter-spacing:.55em;text-transform:uppercase;color:rgba(212,175,55,0.6);margin-bottom:14px;">' . $eyebrow . '</div>
                                        <div style="font-size:30px;font-weight:300;color:#fff;line-height:1.2;">' . $line1 . '</div>
                                        <div style="font-size:30px;font-weight:300;color:#d4af37;font-style:italic;line-height:1.2;margin-bottom:14px;">' . $line2 . '</div>
                                        <p style="color:rgba(255,255,255,0.5);font-size:13px;line-height:1.6;margin:0;">' . $subtitle . '</p>
                                    </div>

                                    <!-- Story -->
                                    <div style="background:#f9fafb;border-radius:12px;padding:24px 28px;border:1px solid #e5e7eb;">
                                        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;">Our Story</div>
                                        <div style="font-size:20px;font-weight:600;color:#111827;">' . $sHeadline . ' <span style="color:#d4af37;font-style:italic;">' . $sAccent . '</span></div>
                                        <div style="display:inline-block;margin:10px 0;background:#d4af37;color:#0d0a04;font-size:12px;font-weight:700;padding:4px 12px;border-radius:4px;">' . $years . ' Years</div>
                                        <p style="font-size:13px;color:#374151;line-height:1.7;margin:8px 0 0;">' . $p1 . '</p>
                                    </div>

                                    <!-- Values -->
                                    <div style="background:#0d0a04;border-radius:12px;padding:20px 24px;">
                                        <div style="font-size:11px;font-weight:700;color:rgba(212,175,55,0.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;">Our Values</div>
                                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;">' . $values . '</div>
                                    </div>

                                    <!-- CTA -->
                                    <div style="background:linear-gradient(135deg,#1a1208,#2a1a08);border-radius:12px;padding:24px 28px;text-align:center;">
                                        <div style="font-size:20px;font-weight:600;color:#fff;margin-bottom:8px;">' . $ctaHeading . '</div>
                                        <p style="font-size:13px;color:rgba(255,255,255,0.5);line-height:1.6;margin:0 0 16px;">' . mb_substr(strip_tags($ctaText), 0, 120) . '…</p>
                                        <div style="display:inline-block;background:#d4af37;color:#0d0a04;font-size:11px;font-weight:700;padding:10px 24px;border-radius:4px;letter-spacing:.06em;">Shop Now →</div>
                                    </div>

                                </div>
                            ');
                        })
                        ->columnSpanFull(),
                ]),

            // ── SEO Ranking Potential ─────────────────────────────────────
            Section::make('📊 SEO Ranking Potential')
                ->description('Ask AI to score your About page content for SEO impact and give you improvement tips.')
                ->collapsed()
                ->schema([
                    TextInput::make('_seo_score')->dehydrated(false)->hidden(),
                    Textarea::make('_seo_notes')->dehydrated(false)->hidden(),

                    \Filament\Schemas\Components\Actions::make([
                        Action::make('analyse_about_seo')
                            ->label('Analyse with AI')
                            ->icon('heroicon-o-sparkles')
                            ->color('warning')
                            ->action(function ($get, $set) {
                                $apiKey = config('services.anthropic.key');
                                if (blank($apiKey)) {
                                    Notification::make()->title('Anthropic API key not set.')->warning()->send();
                                    return;
                                }
                                $headline = trim(($get('about.hero.headline') ?? '') . ' ' . ($get('about.hero.headline_accent') ?? ''));
                                $subtitle = $get('about.hero.subtitle')  ?? '';
                                $p1       = $get('about.story.p1')       ?? '';
                                $p2       = $get('about.story.p2')       ?? '';
                                $cta      = $get('about.cta.heading')    ?? '';
                                $context  = "Page: About Us\nHeadline: {$headline}\nSubtitle: {$subtitle}\nBrand story: {$p1} {$p2}\nCTA heading: {$cta}";
                                try {
                                    $client   = new AnthropicClient(apiKey: $apiKey);
                                    $response = $client->messages->create(
                                        model: 'claude-opus-4-8',
                                        maxTokens: 600,
                                        system: 'You are an SEO expert for Artisan Leather, a premium leather goods brand in Muscat, Oman targeting GCC shoppers. Return only valid JSON, no markdown.',
                                        messages: [['role' => 'user', 'content' => "Analyse this About page content for SEO and return JSON with exactly two keys:\n\"seo_score\" (integer 0-100) and \"seo_notes\" (string: 3-5 actionable tips, each on its own line starting with a dash).\n\n{$context}"]],
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
                ->description('See what About pages currently rank for leather goods brands — use them as benchmarks.')
                ->collapsed()
                ->schema([
                    Textarea::make('_competition_json')->dehydrated(false)->hidden(),

                    \Filament\Schemas\Components\Actions::make([
                        Action::make('research_about_competition')
                            ->label('Research Competition')
                            ->icon('heroicon-o-magnifying-glass')
                            ->color('gray')
                            ->action(function ($get, $set) {
                                $headline = trim(($get('about.hero.headline') ?? '') . ' ' . ($get('about.hero.headline_accent') ?? ''));
                                $query    = $headline ?: 'artisan leather brand about us Oman';
                                try {
                                    $flat = Setting::pluck('value', 'key')->toArray();
                                    $key  = $flat['seo.serper_api_key'] ?? config('services.serper.key');
                                    if (blank($key)) {
                                        Notification::make()->title('Serper.dev not configured.')->body('Add API Key in Business Settings → SEO & Analytics.')->warning()->send();
                                        return;
                                    }
                                    $markets = [
                                        'om' => '🇴🇲 Oman',
                                        'ae' => '🇦🇪 UAE',
                                        'sa' => '🇸🇦 Saudi Arabia',
                                        'qa' => '🇶🇦 Qatar',
                                        'kw' => '🇰🇼 Kuwait',
                                        'bh' => '🇧🇭 Bahrain',
                                    ];
                                    $languages = ['en' => 'EN', 'ar' => 'AR'];
                                    $results = [];
                                    foreach ($markets as $gl => $label) {
                                        foreach ($languages as $hl => $langLabel) {
                                            $response = Http::timeout(10)
                                                ->withHeaders(['X-API-KEY' => $key, 'Content-Type' => 'application/json'])
                                                ->post('https://google.serper.dev/search', ['q' => $query, 'num' => 1, 'gl' => $gl, 'hl' => $hl]);
                                            if (!$response->successful()) {
                                                continue;
                                            }
                                            foreach ($response->json('organic', []) as $item) {
                                                $url = $item['link'] ?? '';
                                                $results[] = ['title' => $item['title'] ?? '', 'url' => $url, 'domain' => parse_url($url, PHP_URL_HOST) ?: $url, 'snippet' => $item['snippet'] ?? '', 'market' => $label . ' · ' . $langLabel];
                                            }
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

                    Placeholder::make('_competition_preview_about')
                        ->label('')
                        ->content(function ($get) {
                            $json = $get('_competition_json') ?? '';
                            if (blank($json)) {
                                return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Click "Research Competition" to see what About pages currently rank for leather goods brands.</p>');
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save All Settings')
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
        Notification::make()->title('✅ About page content saved!')->success()->send();
    }
}
