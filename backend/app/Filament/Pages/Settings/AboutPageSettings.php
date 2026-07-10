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
                                    $set('about.hero.eyebrow_ar',      $data['hero_eyebrow_ar']      ?? '');
                                    $set('about.hero.headline',        $data['hero_headline']        ?? '');
                                    $set('about.hero.headline_ar',     $data['hero_headline_ar']     ?? '');
                                    $set('about.hero.headline_accent', $data['hero_headline_accent'] ?? '');
                                    $set('about.hero.headline_accent_ar', $data['hero_headline_accent_ar'] ?? '');
                                    $set('about.hero.subtitle',        $data['hero_subtitle']        ?? '');
                                    $set('about.hero.subtitle_ar',     $data['hero_subtitle_ar']     ?? '');

                                    // Story
                                    $set('about.story.headline',        $data['story_headline']        ?? '');
                                    $set('about.story.headline_ar',     $data['story_headline_ar']     ?? '');
                                    $set('about.story.headline_accent', $data['story_headline_accent'] ?? '');
                                    $set('about.story.headline_accent_ar', $data['story_headline_accent_ar'] ?? '');
                                    $set('about.story.years',           $data['story_years']           ?? '');
                                    $set('about.story.p1',              $data['story_p1']              ?? '');
                                    $set('about.story.p1_ar',           $data['story_p1_ar']           ?? '');
                                    $set('about.story.p2',              $data['story_p2']              ?? '');
                                    $set('about.story.p2_ar',           $data['story_p2_ar']           ?? '');
                                    $set('about.story.p3',              $data['story_p3']              ?? '');
                                    $set('about.story.p3_ar',           $data['story_p3_ar']           ?? '');

                                    // Craft Steps
                                    for ($i = 1; $i <= 4; $i++) {
                                        $set("about.craft.{$i}.num",      $data["craft_{$i}_num"]      ?? "0{$i}");
                                        $set("about.craft.{$i}.title",    $data["craft_{$i}_title"]    ?? '');
                                        $set("about.craft.{$i}.title_ar", $data["craft_{$i}_title_ar"] ?? '');
                                        $set("about.craft.{$i}.body",     $data["craft_{$i}_body"]     ?? '');
                                        $set("about.craft.{$i}.body_ar",  $data["craft_{$i}_body_ar"]  ?? '');
                                    }

                                    // Materials
                                    for ($i = 1; $i <= 3; $i++) {
                                        $set("about.material.{$i}.name",        $data["material_{$i}_name"]        ?? '');
                                        $set("about.material.{$i}.name_ar",     $data["material_{$i}_name_ar"]     ?? '');
                                        $set("about.material.{$i}.subtitle",    $data["material_{$i}_subtitle"]    ?? '');
                                        $set("about.material.{$i}.subtitle_ar", $data["material_{$i}_subtitle_ar"] ?? '');
                                        $set("about.material.{$i}.desc",        $data["material_{$i}_desc"]        ?? '');
                                        $set("about.material.{$i}.desc_ar",     $data["material_{$i}_desc_ar"]     ?? '');
                                    }

                                    // Values
                                    $numerals = ['I', 'II', 'III', 'IV'];
                                    for ($i = 1; $i <= 4; $i++) {
                                        $set("about.value.{$i}.number",  $data["value_{$i}_number"] ?? $numerals[$i - 1]);
                                        $set("about.value.{$i}.title",    $data["value_{$i}_title"]    ?? '');
                                        $set("about.value.{$i}.title_ar", $data["value_{$i}_title_ar"] ?? '');
                                        $set("about.value.{$i}.desc",     $data["value_{$i}_desc"]     ?? '');
                                        $set("about.value.{$i}.desc_ar",  $data["value_{$i}_desc_ar"]  ?? '');
                                    }

                                    // Timeline
                                    for ($i = 1; $i <= 5; $i++) {
                                        $set("about.timeline.{$i}.year",     $data["timeline_{$i}_year"]     ?? '');
                                        $set("about.timeline.{$i}.title",    $data["timeline_{$i}_title"]    ?? '');
                                        $set("about.timeline.{$i}.title_ar", $data["timeline_{$i}_title_ar"] ?? '');
                                        $set("about.timeline.{$i}.desc",     $data["timeline_{$i}_desc"]     ?? '');
                                        $set("about.timeline.{$i}.desc_ar",  $data["timeline_{$i}_desc_ar"]  ?? '');
                                    }

                                    // CTA
                                    $set('about.cta.heading',    $data['cta_heading']    ?? '');
                                    $set('about.cta.heading_ar', $data['cta_heading_ar'] ?? '');
                                    $set('about.cta.text',       $data['cta_text']       ?? '');
                                    $set('about.cta.text_ar',    $data['cta_text_ar']    ?? '');

                                    Notification::make()
                                        ->title('✅ Claude generated your About page!')
                                        ->body('Review all sections, then click Save.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title('Generation failed')->body($e->getMessage())->danger()->send();
                                }
                            }),

                        Action::make('generate_about_openai')
                            ->label('Generate with OpenAI')
                            ->icon('heroicon-o-cpu-chip')
                            ->color('info')
                            ->requiresConfirmation()
                            ->modalHeading('Generate About Page Content with OpenAI (GPT-4o)')
                            ->modalDescription('This will overwrite all About page sections (hero, story, craft steps, materials, values, timeline, CTA). You can still adjust before saving. Continue?')
                            ->modalSubmitActionLabel('Yes, generate everything')
                            ->action(function ($get, $set) {
                                $apiKey = config('services.openai.key');
                                if (blank($apiKey)) {
                                    Notification::make()->title('OpenAI API key not set.')->warning()->send();
                                    return;
                                }
                                $theme = trim($get('_ai_about_prompt') ?: 'General About page refresh for a premium leather goods brand in Muscat, Oman — heritage, craftsmanship, authenticity.');
                                try {
                                    $data = app(\App\Services\AiPostService::class)->generateAboutPageWithOpenAI($theme);

                                    // Hero
                                    $set('about.hero.eyebrow',         $data['hero_eyebrow']         ?? '');
                                    $set('about.hero.eyebrow_ar',      $data['hero_eyebrow_ar']      ?? '');
                                    $set('about.hero.headline',        $data['hero_headline']        ?? '');
                                    $set('about.hero.headline_ar',     $data['hero_headline_ar']     ?? '');
                                    $set('about.hero.headline_accent', $data['hero_headline_accent'] ?? '');
                                    $set('about.hero.headline_accent_ar', $data['hero_headline_accent_ar'] ?? '');
                                    $set('about.hero.subtitle',        $data['hero_subtitle']        ?? '');
                                    $set('about.hero.subtitle_ar',     $data['hero_subtitle_ar']     ?? '');

                                    // Story
                                    $set('about.story.headline',        $data['story_headline']        ?? '');
                                    $set('about.story.headline_ar',     $data['story_headline_ar']     ?? '');
                                    $set('about.story.headline_accent', $data['story_headline_accent'] ?? '');
                                    $set('about.story.headline_accent_ar', $data['story_headline_accent_ar'] ?? '');
                                    $set('about.story.years',           $data['story_years']           ?? '');
                                    $set('about.story.p1',              $data['story_p1']              ?? '');
                                    $set('about.story.p1_ar',           $data['story_p1_ar']           ?? '');
                                    $set('about.story.p2',              $data['story_p2']              ?? '');
                                    $set('about.story.p2_ar',           $data['story_p2_ar']           ?? '');
                                    $set('about.story.p3',              $data['story_p3']              ?? '');
                                    $set('about.story.p3_ar',           $data['story_p3_ar']           ?? '');

                                    // Craft Steps
                                    for ($i = 1; $i <= 4; $i++) {
                                        $set("about.craft.{$i}.num",      $data["craft_{$i}_num"]      ?? "0{$i}");
                                        $set("about.craft.{$i}.title",    $data["craft_{$i}_title"]    ?? '');
                                        $set("about.craft.{$i}.title_ar", $data["craft_{$i}_title_ar"] ?? '');
                                        $set("about.craft.{$i}.body",     $data["craft_{$i}_body"]     ?? '');
                                        $set("about.craft.{$i}.body_ar",  $data["craft_{$i}_body_ar"]  ?? '');
                                    }

                                    // Materials
                                    for ($i = 1; $i <= 3; $i++) {
                                        $set("about.material.{$i}.name",        $data["material_{$i}_name"]        ?? '');
                                        $set("about.material.{$i}.name_ar",     $data["material_{$i}_name_ar"]     ?? '');
                                        $set("about.material.{$i}.subtitle",    $data["material_{$i}_subtitle"]    ?? '');
                                        $set("about.material.{$i}.subtitle_ar", $data["material_{$i}_subtitle_ar"] ?? '');
                                        $set("about.material.{$i}.desc",        $data["material_{$i}_desc"]        ?? '');
                                        $set("about.material.{$i}.desc_ar",     $data["material_{$i}_desc_ar"]     ?? '');
                                    }

                                    // Values
                                    $numerals = ['I', 'II', 'III', 'IV'];
                                    for ($i = 1; $i <= 4; $i++) {
                                        $set("about.value.{$i}.number",  $data["value_{$i}_number"] ?? $numerals[$i - 1]);
                                        $set("about.value.{$i}.title",    $data["value_{$i}_title"]    ?? '');
                                        $set("about.value.{$i}.title_ar", $data["value_{$i}_title_ar"] ?? '');
                                        $set("about.value.{$i}.desc",     $data["value_{$i}_desc"]     ?? '');
                                        $set("about.value.{$i}.desc_ar",  $data["value_{$i}_desc_ar"]  ?? '');
                                    }

                                    // Timeline
                                    for ($i = 1; $i <= 5; $i++) {
                                        $set("about.timeline.{$i}.year",     $data["timeline_{$i}_year"]     ?? '');
                                        $set("about.timeline.{$i}.title",    $data["timeline_{$i}_title"]    ?? '');
                                        $set("about.timeline.{$i}.title_ar", $data["timeline_{$i}_title_ar"] ?? '');
                                        $set("about.timeline.{$i}.desc",     $data["timeline_{$i}_desc"]     ?? '');
                                        $set("about.timeline.{$i}.desc_ar",  $data["timeline_{$i}_desc_ar"]  ?? '');
                                    }

                                    // CTA
                                    $set('about.cta.heading',    $data['cta_heading']    ?? '');
                                    $set('about.cta.heading_ar', $data['cta_heading_ar'] ?? '');
                                    $set('about.cta.text',       $data['cta_text']       ?? '');
                                    $set('about.cta.text_ar',    $data['cta_text_ar']    ?? '');

                                    Notification::make()
                                        ->title('✅ OpenAI generated your About page!')
                                        ->body('Review all sections, then click Save.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title('Generation failed')->body($e->getMessage())->danger()->send();
                                }
                            }),
                    ]),
                ]),

            Tabs::make('About Page Content')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('English')
                        ->icon('heroicon-o-language')
                        ->schema([

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
                    FileUpload::make('about.story.image')
                        ->label('Story Image')
                        ->helperText('Shown on the left of the "Our Story" section. Without this, a placeholder "AL" monogram card is shown instead. Recommended: portrait photo, e.g. 1000×1250px.')
                        ->image()
                        ->disk('public')
                        ->directory('about')
                        ->imageEditor()
                        ->maxSize(5120)
                        ->columnSpanFull(),

                    TextInput::make('about.story.eyebrow')
                        ->label('Story Eyebrow')
                        ->placeholder('Our Story'),

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
                        ->columnSpan(1),

                    TextInput::make('about.story.years_label')
                        ->label('Years Badge Label')
                        ->placeholder('Years of Craft')
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
                    TextInput::make('about.craft.section_eyebrow')
                        ->label('Section Eyebrow')
                        ->placeholder('The Process'),
                    TextInput::make('about.craft.section_title')
                        ->label('Section Title')
                        ->placeholder('The Art of Making')
                        ->columnSpan(2),

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
                    TextInput::make('about.material.section_eyebrow')
                        ->label('Section Eyebrow')
                        ->placeholder('What We Use'),
                    TextInput::make('about.material.section_title')
                        ->label('Section Title')
                        ->placeholder('Only the Finest Materials')
                        ->columnSpan(2),

                    TextInput::make('about.material.1.name')
                        ->label('Material 1 — Name')->placeholder('Full Grain'),
                    TextInput::make('about.material.1.subtitle')
                        ->label('Material 1 — Subtitle')->placeholder('The Pinnacle of Leather')->columnSpan(2),
                    FileUpload::make('about.material.1.image')
                        ->label('Material 1 — Image')
                        ->helperText('Optional. Shown above the material text. Recommended: wide leather texture/detail image, e.g. 1200×600px.')
                        ->image()
                        ->disk('public')
                        ->directory('about/materials')
                        ->imageEditor()
                        ->maxSize(5120)
                        ->columnSpanFull(),
                    Textarea::make('about.material.1.desc')
                        ->label('Material 1 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.material.2.name')
                        ->label('Material 2 — Name')->placeholder('Vegetable Tanned'),
                    TextInput::make('about.material.2.subtitle')
                        ->label('Material 2 — Subtitle')->placeholder('Slow-Made & Sustainable')->columnSpan(2),
                    FileUpload::make('about.material.2.image')
                        ->label('Material 2 — Image')
                        ->helperText('Optional. Shown above the material text. Recommended: wide leather texture/detail image, e.g. 1200×600px.')
                        ->image()
                        ->disk('public')
                        ->directory('about/materials')
                        ->imageEditor()
                        ->maxSize(5120)
                        ->columnSpanFull(),
                    Textarea::make('about.material.2.desc')
                        ->label('Material 2 — Description')->rows(2)->columnSpanFull(),

                    TextInput::make('about.material.3.name')
                        ->label('Material 3 — Name')->placeholder('Italian Calfskin'),
                    TextInput::make('about.material.3.subtitle')
                        ->label('Material 3 — Subtitle')->placeholder('Silken & Refined')->columnSpan(2),
                    FileUpload::make('about.material.3.image')
                        ->label('Material 3 — Image')
                        ->helperText('Optional. Shown above the material text. Recommended: wide leather texture/detail image, e.g. 1200×600px.')
                        ->image()
                        ->disk('public')
                        ->directory('about/materials')
                        ->imageEditor()
                        ->maxSize(5120)
                        ->columnSpanFull(),
                    Textarea::make('about.material.3.desc')
                        ->label('Material 3 — Description')->rows(2)->columnSpanFull(),
                ]),

            // ── Values ────────────────────────────────────────────────────
            Section::make('⚖️ Our Values')
                ->description('The 4 value pillars (Heritage, Precision, Longevity, Authenticity).')
                ->columns(2)
                ->schema([
                    TextInput::make('about.value.section_eyebrow')
                        ->label('Section Eyebrow')
                        ->placeholder('What We Stand For'),
                    TextInput::make('about.value.section_title')
                        ->label('Section Title')
                        ->placeholder('Our Four Pillars'),

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
                    TextInput::make('about.timeline.section_eyebrow')
                        ->label('Section Eyebrow')
                        ->placeholder('Our Journey'),
                    TextInput::make('about.timeline.section_title')
                        ->label('Section Title')
                        ->placeholder('Sixteen Years in the Making')
                        ->columnSpan(2),

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
                ->columns(2)
                ->schema([
                    TextInput::make('about.cta.eyebrow')
                        ->label('Eyebrow')
                        ->placeholder('Start Your Journey')
                        ->columnSpanFull(),

                    TextInput::make('about.cta.heading')
                        ->label('Heading')
                        ->placeholder('Own a Piece of the Craft')
                        ->columnSpanFull(),

                    Textarea::make('about.cta.text')
                        ->label('Body Text')
                        ->rows(3)
                        ->placeholder('Every wallet, bag, and belt we make is a promise...')
                        ->columnSpanFull(),

                    TextInput::make('about.cta.shop_label')
                        ->label('Shop Button Label')
                        ->placeholder('Shop Collection'),

                    TextInput::make('about.cta.shop_url')
                        ->label('Shop Button URL')
                        ->placeholder('/collections')
                        ->helperText('Use an internal path like /collections or a full URL like https://example.com.'),

                    TextInput::make('about.cta.contact_label')
                        ->label('Contact Button Label')
                        ->placeholder('Get in Touch'),

                    TextInput::make('about.cta.contact_url')
                        ->label('Contact Button URL')
                        ->placeholder('/contact')
                        ->helperText('Use an internal path like /contact or a full URL like https://example.com.'),
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

                        ]),

                    Tab::make('Arabic / عربي')
                        ->icon('heroicon-o-language')
                        ->schema([

                            Section::make('🎯 Page Hero (Arabic)')
                                ->columns(2)
                                ->schema(self::arabicFields([
                                    ['about.hero.eyebrow_ar', 'Eyebrow Text (Arabic)', 'text', null, true],
                                    ['about.hero.headline_ar', 'Headline — Line 1 (Arabic)', 'text'],
                                    ['about.hero.headline_accent_ar', 'Headline — Line 2 (Arabic)', 'text'],
                                    ['about.hero.subtitle_ar', 'Subtitle (Arabic)', 'text', null, true],
                                ])),

                            Section::make('📖 Our Story (Arabic)')
                                ->columns(2)
                                ->schema(self::arabicFields([
                                    ['about.story.eyebrow_ar', 'Story Eyebrow (Arabic)', 'text'],
                                    ['about.story.headline_ar', 'Story Headline — Line 1 (Arabic)', 'text'],
                                    ['about.story.headline_accent_ar', 'Story Headline — Line 2 (Arabic)', 'text'],
                                    ['about.story.years_ar', 'Years Badge (Arabic)', 'text'],
                                    ['about.story.years_label_ar', 'Years Badge Label (Arabic)', 'text', null, true],
                                    ['about.story.p1_ar', 'Paragraph 1 (Arabic)', 'textarea', 3, true],
                                    ['about.story.p2_ar', 'Paragraph 2 (Arabic)', 'textarea', 3, true],
                                    ['about.story.p3_ar', 'Paragraph 3 (Arabic)', 'textarea', 3, true],
                                ])),

                            Section::make('🔨 Craft Steps (Arabic)')
                                ->columns(2)
                                ->schema(self::arabicFields([
                                    ['about.craft.section_eyebrow_ar', 'Section Eyebrow (Arabic)', 'text'],
                                    ['about.craft.section_title_ar', 'Section Title (Arabic)', 'text'],
                                    ['about.craft.1.num_ar', 'Step 1 — Number (Arabic)', 'text'],
                                    ['about.craft.1.title_ar', 'Step 1 — Title (Arabic)', 'text'],
                                    ['about.craft.1.body_ar', 'Step 1 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.craft.2.num_ar', 'Step 2 — Number (Arabic)', 'text'],
                                    ['about.craft.2.title_ar', 'Step 2 — Title (Arabic)', 'text'],
                                    ['about.craft.2.body_ar', 'Step 2 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.craft.3.num_ar', 'Step 3 — Number (Arabic)', 'text'],
                                    ['about.craft.3.title_ar', 'Step 3 — Title (Arabic)', 'text'],
                                    ['about.craft.3.body_ar', 'Step 3 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.craft.4.num_ar', 'Step 4 — Number (Arabic)', 'text'],
                                    ['about.craft.4.title_ar', 'Step 4 — Title (Arabic)', 'text'],
                                    ['about.craft.4.body_ar', 'Step 4 — Description (Arabic)', 'textarea', 2, true],
                                ])),

                            Section::make('🧵 Materials (Arabic)')
                                ->columns(2)
                                ->schema(self::arabicFields([
                                    ['about.material.section_eyebrow_ar', 'Section Eyebrow (Arabic)', 'text'],
                                    ['about.material.section_title_ar', 'Section Title (Arabic)', 'text'],
                                    ['about.material.1.name_ar', 'Material 1 — Name (Arabic)', 'text'],
                                    ['about.material.1.subtitle_ar', 'Material 1 — Subtitle (Arabic)', 'text'],
                                    ['about.material.1.desc_ar', 'Material 1 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.material.2.name_ar', 'Material 2 — Name (Arabic)', 'text'],
                                    ['about.material.2.subtitle_ar', 'Material 2 — Subtitle (Arabic)', 'text'],
                                    ['about.material.2.desc_ar', 'Material 2 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.material.3.name_ar', 'Material 3 — Name (Arabic)', 'text'],
                                    ['about.material.3.subtitle_ar', 'Material 3 — Subtitle (Arabic)', 'text'],
                                    ['about.material.3.desc_ar', 'Material 3 — Description (Arabic)', 'textarea', 2, true],
                                ])),

                            Section::make('⚖️ Our Values (Arabic)')
                                ->columns(2)
                                ->schema(self::arabicFields([
                                    ['about.value.section_eyebrow_ar', 'Section Eyebrow (Arabic)', 'text'],
                                    ['about.value.section_title_ar', 'Section Title (Arabic)', 'text'],
                                    ['about.value.1.number_ar', 'Value 1 — Number (Arabic)', 'text'],
                                    ['about.value.1.title_ar', 'Value 1 — Title (Arabic)', 'text'],
                                    ['about.value.1.desc_ar', 'Value 1 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.value.2.number_ar', 'Value 2 — Number (Arabic)', 'text'],
                                    ['about.value.2.title_ar', 'Value 2 — Title (Arabic)', 'text'],
                                    ['about.value.2.desc_ar', 'Value 2 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.value.3.number_ar', 'Value 3 — Number (Arabic)', 'text'],
                                    ['about.value.3.title_ar', 'Value 3 — Title (Arabic)', 'text'],
                                    ['about.value.3.desc_ar', 'Value 3 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.value.4.number_ar', 'Value 4 — Number (Arabic)', 'text'],
                                    ['about.value.4.title_ar', 'Value 4 — Title (Arabic)', 'text'],
                                    ['about.value.4.desc_ar', 'Value 4 — Description (Arabic)', 'textarea', 2, true],
                                ])),

                            Section::make('📅 Our Journey (Arabic)')
                                ->columns(2)
                                ->schema(self::arabicFields([
                                    ['about.timeline.section_eyebrow_ar', 'Section Eyebrow (Arabic)', 'text'],
                                    ['about.timeline.section_title_ar', 'Section Title (Arabic)', 'text'],
                                    ['about.timeline.1.year_ar', 'Event 1 — Year (Arabic)', 'text'],
                                    ['about.timeline.1.title_ar', 'Event 1 — Title (Arabic)', 'text'],
                                    ['about.timeline.1.desc_ar', 'Event 1 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.timeline.2.year_ar', 'Event 2 — Year (Arabic)', 'text'],
                                    ['about.timeline.2.title_ar', 'Event 2 — Title (Arabic)', 'text'],
                                    ['about.timeline.2.desc_ar', 'Event 2 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.timeline.3.year_ar', 'Event 3 — Year (Arabic)', 'text'],
                                    ['about.timeline.3.title_ar', 'Event 3 — Title (Arabic)', 'text'],
                                    ['about.timeline.3.desc_ar', 'Event 3 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.timeline.4.year_ar', 'Event 4 — Year (Arabic)', 'text'],
                                    ['about.timeline.4.title_ar', 'Event 4 — Title (Arabic)', 'text'],
                                    ['about.timeline.4.desc_ar', 'Event 4 — Description (Arabic)', 'textarea', 2, true],
                                    ['about.timeline.5.year_ar', 'Event 5 — Year (Arabic)', 'text'],
                                    ['about.timeline.5.title_ar', 'Event 5 — Title (Arabic)', 'text'],
                                    ['about.timeline.5.desc_ar', 'Event 5 — Description (Arabic)', 'textarea', 2, true],
                                ])),

                            Section::make('💬 Call to Action (Arabic)')
                                ->columns(1)
                                ->schema(self::arabicFields([
                                    ['about.cta.eyebrow_ar', 'Eyebrow (Arabic)', 'text'],
                                    ['about.cta.heading_ar', 'Heading (Arabic)', 'text'],
                                    ['about.cta.text_ar', 'Body Text (Arabic)', 'textarea', 3, true],
                                    ['about.cta.shop_label_ar', 'Shop Button Label (Arabic)', 'text'],
                                    ['about.cta.shop_url_ar', 'Shop Button URL (Arabic)', 'text'],
                                    ['about.cta.contact_label_ar', 'Contact Button Label (Arabic)', 'text'],
                                    ['about.cta.contact_url_ar', 'Contact Button URL (Arabic)', 'text'],
                                ])),

                            Section::make('🔍 SEO (Arabic)')
                                ->description('Custom Arabic meta title and description for the About page.')
                                ->schema(self::arabicFields([
                                    ['about.seo.meta_title_ar', 'SEO Title (Arabic)', 'text', null, true],
                                    ['about.seo.meta_description_ar', 'SEO Description (Arabic)', 'textarea', 3, true],
                                ])),

                        ]),

                    Tab::make('Preview')
                        ->icon('heroicon-o-eye')
                        ->schema([
                            Section::make('🌐 Website Preview')
                                ->description('Live preview of the full About page — hero, story, craft steps, materials, values, timeline and CTA — as visitors see them.')
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

                                            $storyImgVal = $get('about.story.image');
                                            $storyImgSrc = null;
                                            if ($storyImgVal instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                                $storyImgSrc = $storyImgVal->temporaryUrl();
                                            } elseif (is_string($storyImgVal) && $storyImgVal !== '') {
                                                $storyImgSrc = \Illuminate\Support\Facades\Storage::disk('public')->url($storyImgVal);
                                            }
                                            $storyImgHtml = $storyImgSrc
                                                ? '<img src="' . e($storyImgSrc) . '" style="width:64px;height:80px;object-fit:cover;border-radius:6px;flex-shrink:0;" />'
                                                : '<div style="width:64px;height:80px;border-radius:6px;flex-shrink:0;background:linear-gradient(135deg,#3A2210,#240F06);display:flex;align-items:center;justify-content:center;font-family:Georgia,serif;font-style:italic;color:rgba(255,255,255,0.15);font-size:18px;">AL</div>';

                                            $craftSteps = '';
                                            for ($i = 1; $i <= 4; $i++) {
                                                $num   = e($get("about.craft.{$i}.num")   ?: sprintf('0%d', $i));
                                                $title = e($get("about.craft.{$i}.title") ?: "Step {$i}");
                                                $body  = e($get("about.craft.{$i}.body")  ?: '');
                                                $craftSteps .= '<div style="display:flex;gap:12px;align-items:flex-start;padding:10px 0;">
                                                    <div style="flex-shrink:0;width:32px;height:32px;border-radius:50%;border:1px solid rgba(212,175,55,0.4);color:#d4af37;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;">' . $num . '</div>
                                                    <div>
                                                        <div style="font-size:13px;font-weight:600;color:#111827;">' . $title . '</div>
                                                        <div style="font-size:12px;color:#6b7280;line-height:1.5;margin-top:2px;">' . $body . '</div>
                                                    </div>
                                                </div>';
                                            }

                                            $materials = '';
                                            for ($i = 1; $i <= 3; $i++) {
                                                $name     = e($get("about.material.{$i}.name")     ?: "Material {$i}");
                                                $subtitle2 = e($get("about.material.{$i}.subtitle") ?: '');
                                                $desc     = e($get("about.material.{$i}.desc")     ?: '');
                                                $materials .= '<div style="background:#f9fafb;border-radius:8px;padding:14px 16px;border:1px solid #e5e7eb;">
                                                    <div style="font-size:13px;font-weight:700;color:#111827;">' . $name . '</div>
                                                    <div style="font-size:11px;color:#d4af37;font-style:italic;margin:2px 0 6px;">' . $subtitle2 . '</div>
                                                    <div style="font-size:11px;color:#6b7280;line-height:1.5;">' . $desc . '</div>
                                                </div>';
                                            }

                                            $values = '';
                                            for ($i = 1; $i <= 4; $i++) {
                                                $num   = e($get("about.value.{$i}.number") ?: ['I','II','III','IV'][$i-1]);
                                                $title = e($get("about.value.{$i}.title")  ?: ['Heritage','Precision','Longevity','Authenticity'][$i-1]);
                                                $values .= '<div style="text-align:center;padding:14px 8px;background:#1a1208;border-radius:6px;">
                                                    <div style="font-size:16px;font-weight:700;color:#d4af37;font-style:italic;margin-bottom:4px;">' . $num . '</div>
                                                    <div style="font-size:11px;color:rgba(255,255,255,0.55);text-transform:uppercase;letter-spacing:.08em;">' . $title . '</div>
                                                </div>';
                                            }

                                            $timeline = '';
                                            for ($i = 1; $i <= 5; $i++) {
                                                $year  = e($get("about.timeline.{$i}.year")  ?: '—');
                                                $title = e($get("about.timeline.{$i}.title") ?: "Event {$i}");
                                                $timeline .= '<div style="text-align:center;padding:0 6px;">
                                                    <div style="font-size:13px;font-weight:700;color:#d4af37;">' . $year . '</div>
                                                    <div style="width:6px;height:6px;border-radius:50%;background:#d4af37;margin:6px auto;"></div>
                                                    <div style="font-size:10px;color:#6b7280;line-height:1.4;">' . $title . '</div>
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
                                                    <div style="background:#f9fafb;border-radius:12px;padding:24px 28px;border:1px solid #e5e7eb;display:flex;gap:18px;">
                                                        ' . $storyImgHtml . '
                                                        <div>
                                                            <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;">Our Story</div>
                                                            <div style="font-size:20px;font-weight:600;color:#111827;">' . $sHeadline . ' <span style="color:#d4af37;font-style:italic;">' . $sAccent . '</span></div>
                                                            <div style="display:inline-block;margin:10px 0;background:#d4af37;color:#0d0a04;font-size:12px;font-weight:700;padding:4px 12px;border-radius:4px;">' . $years . ' Years</div>
                                                            <p style="font-size:13px;color:#374151;line-height:1.7;margin:8px 0 0;">' . $p1 . '</p>
                                                        </div>
                                                    </div>

                                                    <!-- Craft Steps -->
                                                    <div style="background:#f9fafb;border-radius:12px;padding:20px 24px;border:1px solid #e5e7eb;">
                                                        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">The Art of Making</div>
                                                        ' . $craftSteps . '
                                                    </div>

                                                    <!-- Materials -->
                                                    <div style="padding:4px 0;">
                                                        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">Only the Finest Materials</div>
                                                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">' . $materials . '</div>
                                                    </div>

                                                    <!-- Values -->
                                                    <div style="background:#0d0a04;border-radius:12px;padding:20px 24px;">
                                                        <div style="font-size:11px;font-weight:700;color:rgba(212,175,55,0.5);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;">Our Values</div>
                                                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;">' . $values . '</div>
                                                    </div>

                                                    <!-- Timeline -->
                                                    <div style="background:#f9fafb;border-radius:12px;padding:20px 24px;border:1px solid #e5e7eb;">
                                                        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;">Our Journey</div>
                                                        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:4px;border-top:1px solid #e5e7eb;padding-top:14px;">' . $timeline . '</div>
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
                        ]),
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
                                $headline   = trim(($get('about.hero.headline') ?? '') . ' ' . ($get('about.hero.headline_accent') ?? ''));
                                $subtitle   = $get('about.hero.subtitle')  ?? '';
                                $p1         = $get('about.story.p1')       ?? '';
                                $p2         = $get('about.story.p2')       ?? '';
                                $cta        = $get('about.cta.heading')    ?? '';
                                $headlineAr = trim(($get('about.hero.headline_ar') ?? '') . ' ' . ($get('about.hero.headline_accent_ar') ?? ''));
                                $subtitleAr = $get('about.hero.subtitle_ar') ?? '';
                                $p1Ar       = $get('about.story.p1_ar')      ?? '';
                                $p2Ar       = $get('about.story.p2_ar')      ?? '';
                                $ctaAr      = $get('about.cta.heading_ar')   ?? '';
                                $context    = "Page: About Us\n[English]\nHeadline: {$headline}\nSubtitle: {$subtitle}\nBrand story: {$p1} {$p2}\nCTA heading: {$cta}\n\n[Arabic]\nHeadline: {$headlineAr}\nSubtitle: {$subtitleAr}\nBrand story: {$p1Ar} {$p2Ar}\nCTA heading: {$ctaAr}";
                                try {
                                    $client   = new AnthropicClient(apiKey: $apiKey);
                                    $response = $client->messages->create(
                                        model: 'claude-opus-4-8',
                                        maxTokens: 600,
                                        system: 'You are an SEO expert for Artisan Leather, a premium leather goods brand in Muscat, Oman targeting GCC shoppers. Return only valid JSON, no markdown.',
                                        messages: [['role' => 'user', 'content' => "Analyse this About page content (both English and Arabic versions) for SEO and return JSON with exactly two keys:\n\"seo_score\" (integer 0-100) and \"seo_notes\" (string: 3-5 actionable tips covering both languages, each on its own line starting with a dash).\n\n{$context}"]],
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
                        Action::make('research_about_competition')
                            ->label('Research Competition')
                            ->icon('heroicon-o-magnifying-glass')
                            ->color('gray')
                            ->action(function ($get, $set) {
                                $headline   = trim(($get('about.hero.headline') ?? '') . ' ' . ($get('about.hero.headline_accent') ?? ''));
                                $headlineAr = trim(($get('about.hero.headline_ar') ?? '') . ' ' . ($get('about.hero.headline_accent_ar') ?? ''));
                                $query      = $headline ?: 'artisan leather brand about us Oman';
                                $queryAr    = $headlineAr ?: $query;
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
