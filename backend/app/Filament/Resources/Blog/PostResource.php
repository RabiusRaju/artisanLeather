<?php
namespace App\Filament\Resources\Blog;

use App\Filament\Resources\Blog\Pages;
use App\Models\Post;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Enums\NavigationGroupEnum;
use App\Services\AiPostService;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;
use App\Support\VideoEmbedder;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $recordTitleAttribute = 'title';
    public static function getNavigationIcon(): string  { return 'heroicon-o-pencil-square'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Content->value; }
    public static function getNavigationSort(): int     { return 1; }
    public static function getNavigationBadge(): ?string
    {
        $count = Post::where('is_published', false)->count();
        return $count > 0 ? (string)$count : null;
    }
    public static function getNavigationBadgeColor(): string { return 'warning'; }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'category'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Category'  => ucfirst(str_replace('-', ' ', $record->category)),
            'Published' => $record->is_published ? 'Yes' : 'Draft',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            // ── AI Auto-Fill ─────────────────────────────────────────────
            Section::make('✨ AI Content Generator')
                ->description('Describe the blog post you want, then choose which AI to generate with. Review all tabs before saving.')
                ->collapsed()
                ->schema([
                    Textarea::make('ai_prompt')
                        ->label('What should the post be about?')
                        ->placeholder('e.g. How to clean and condition a leather wallet to make it last 10 years')
                        ->helperText('Be specific. The more detail you give, the better the result.')
                        ->rows(4)
                        ->columnSpanFull(),

                    TextInput::make('ai_reference_url')
                        ->label('Reference Article URL (optional)')
                        ->placeholder('https://example.com/blogs/how-are-leather-wallets-made')
                        ->helperText('A competitor or inspiration article. The AI studies its depth and structure, then writes a fully original article in our own voice — never copied or paraphrased.')
                        ->url()
                        ->columnSpanFull(),

                    FileUpload::make('ai_attachments')
                        ->label('Reference Images & Documents (optional)')
                        ->helperText('Claude reads images + PDFs. OpenAI reads images only.')
                        ->multiple()
                        ->disk('local')
                        ->directory('ai-uploads')
                        ->visibility('private')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'text/plain'])
                        ->maxSize(10240)
                        ->maxFiles(5)
                        ->columnSpanFull(),

                    \Filament\Schemas\Components\Actions::make([

                        Action::make('generate_claude')
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
                                    \Filament\Notifications\Notification::make()
                                        ->title('Please enter a prompt first.')
                                        ->warning()->send();
                                    return;
                                }
                                $filePaths = self::resolveAiFilePaths($get('ai_attachments') ?? []);
                                try {
                                    $data = app(AiPostService::class)
                                        ->generatePostWithClaude($prompt, $get('category') ?: 'general', $filePaths, $get('ai_reference_url'));
                                    self::fillAiFields($set, $data);
                                    $set('ai_attachments', []);
                                    \Filament\Notifications\Notification::make()
                                        ->title('✅ Claude generated your content!')
                                        ->body('Review all tabs before saving.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Claude generation failed')
                                        ->body($e->getMessage())
                                        ->danger()->send();
                                }
                            }),

                        Action::make('generate_openai')
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
                                    \Filament\Notifications\Notification::make()
                                        ->title('Please enter a prompt first.')
                                        ->warning()->send();
                                    return;
                                }
                                $filePaths = self::resolveAiFilePaths($get('ai_attachments') ?? []);
                                try {
                                    $data = app(AiPostService::class)
                                        ->generatePostWithOpenAI($prompt, $get('category') ?: 'general', $filePaths, $get('ai_reference_url'));
                                    self::fillAiFields($set, $data);
                                    $set('ai_attachments', []);
                                    \Filament\Notifications\Notification::make()
                                        ->title('✅ OpenAI generated your content!')
                                        ->body('Review all tabs before saving.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('OpenAI generation failed')
                                        ->body($e->getMessage())
                                        ->danger()->send();
                                }
                            }),

                    ]),
                ]),

            Tabs::make()->tabs([

                // ── Tab 1: Content ───────────────────────────────────────
                Tab::make('Content')->icon('heroicon-o-document-text')->schema([

                    Section::make('Article Details')
                        ->description('Write the main article content. Use headings, images and formatting to make it scannable.')
                        ->schema([
                            TextInput::make('title')
                                ->label('Title (English)')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state)))
                                ->placeholder('e.g. How to Care for Your Leather Wallet')
                                ->columnSpanFull(),

                            TextInput::make('slug')
                                ->label('URL Slug')
                                ->required()
                                ->unique(Post::class, 'slug', ignoreRecord: true)
                                ->prefix('artisanleatherom.com/blog/')
                                ->helperText('Auto-generated from title. Edit only if needed.')
                                ->columnSpanFull(),

                            Textarea::make('excerpt')
                                ->label('Excerpt (English)')
                                ->rows(2)
                                ->maxLength(300)
                                ->placeholder('A short summary shown in the blog listing. 1–2 sentences.')
                                ->helperText('Max 300 characters. Used in blog listing and social shares.')
                                ->columnSpanFull(),

                            RichEditor::make('content')
                                ->label('Content (English)')
                                ->required()
                                ->live(debounce: 800)
                                ->toolbarButtons([
                                    'h2', 'h3', 'bold', 'italic', 'underline', 'strike',
                                    'link', 'bulletList', 'orderedList', 'blockquote',
                                    'codeBlock', 'attachFiles', 'horizontalRule', 'undo',
                                ])
                                ->helperText('💡 Paste a YouTube or Vimeo link on its own line and it will automatically appear as a playable video — both here in the preview and on the published article.')
                                ->fileAttachmentsDirectory('blog/attachments')
                                ->fileAttachmentsDisk('public')
                                ->columnSpanFull(),

                            Placeholder::make('content_video_preview')
                                ->label('Video Preview')
                                ->content(fn($get) => new HtmlString(VideoEmbedder::extractEmbeds($get('content') ?? '')))
                                ->visible(fn($get) => VideoEmbedder::hasVideoLinks($get('content') ?? ''))
                                ->columnSpanFull(),

                        ]),

                    Section::make('Featured Image')
                        ->schema([
                            FileUpload::make('featured_image')
                                ->label('Cover Image')
                                ->image()
                                ->imageEditor()
                                ->imageEditorAspectRatioOptions(['16:9', '3:2', null])
                                ->directory('blog/covers')
                                ->disk('public')
                                ->maxSize(5120)
                                ->helperText('Recommended: 1200×630px (16:9). Shown in blog listing and social shares. Max 5MB.')
                                ->columnSpanFull(),
                        ]),

                ]),

                // ── Tab 2: Arabic ────────────────────────────────────────
                Tab::make('Arabic / عربي')->icon('heroicon-o-language')->schema([

                    Section::make('Arabic Translation')
                        ->description('Optional but recommended for the Oman market.')
                        ->schema([
                            TextInput::make('title_ar')
                                ->label('العنوان بالعربية')
                                ->placeholder('e.g. كيف تعتني بمحفظتك الجلدية')
                                ->columnSpanFull(),

                            Textarea::make('excerpt_ar')
                                ->label('المقتطف بالعربية')
                                ->rows(2)
                                ->columnSpanFull(),

                            RichEditor::make('content_ar')
                                ->label('المحتوى بالعربية')
                                ->live(debounce: 800)
                                ->toolbarButtons([
                                    'h2', 'h3', 'bold', 'italic', 'underline',
                                    'link', 'bulletList', 'orderedList', 'blockquote', 'undo',
                                ])
                                ->helperText('💡 Paste a YouTube or Vimeo link on its own line and it will automatically appear as a playable video — both here in the preview and on the published article.')
                                ->columnSpanFull(),

                            Placeholder::make('content_ar_video_preview')
                                ->label('Video Preview')
                                ->content(fn($get) => new HtmlString(VideoEmbedder::extractEmbeds($get('content_ar') ?? '')))
                                ->visible(fn($get) => VideoEmbedder::hasVideoLinks($get('content_ar') ?? ''))
                                ->columnSpanFull(),
                        ]),

                ]),

                // ── Tab 2b: Bangla ───────────────────────────────────────
                Tab::make('Bangla / বাংলা')->icon('heroicon-o-language')->schema([

                    Section::make('Bangla Translation')
                        ->description('Optional — for Bangladeshi readers.')
                        ->schema([
                            TextInput::make('title_bn')
                                ->label('শিরোনাম (বাংলা)')
                                ->placeholder('e.g. আপনার চামড়ার ওয়ালেটের যত্ন কীভাবে নেবেন')
                                ->columnSpanFull(),

                            Textarea::make('excerpt_bn')
                                ->label('সংক্ষিপ্তসার (বাংলা)')
                                ->rows(2)
                                ->columnSpanFull(),

                            RichEditor::make('content_bn')
                                ->label('বিষয়বস্তু (বাংলা)')
                                ->live(debounce: 800)
                                ->toolbarButtons([
                                    'h2', 'h3', 'bold', 'italic', 'underline',
                                    'link', 'bulletList', 'orderedList', 'blockquote', 'undo',
                                ])
                                ->helperText('💡 Paste a YouTube or Vimeo link on its own line and it will automatically appear as a playable video — both here in the preview and on the published article.')
                                ->columnSpanFull(),

                            Placeholder::make('content_bn_video_preview')
                                ->label('Video Preview')
                                ->content(fn($get) => new HtmlString(VideoEmbedder::extractEmbeds($get('content_bn') ?? '')))
                                ->visible(fn($get) => VideoEmbedder::hasVideoLinks($get('content_bn') ?? ''))
                                ->columnSpanFull(),
                        ]),

                ]),

                // ── Tab 3: Settings ──────────────────────────────────────
                Tab::make('Settings')->icon('heroicon-o-adjustments-horizontal')->schema([

                    Section::make('Publication')
                        ->columns(2)
                        ->schema([
                            Select::make('category')
                                ->label('Category')
                                ->options([
                                    'care-guide'       => '🧴 Care Guide',
                                    'style-tips'       => '👔 Style Tips',
                                    'leather-knowledge'=> '📖 Leather Knowledge',
                                    'news'             => '📰 News & Updates',
                                    'general'          => '📝 General',
                                ])
                                ->default('general')
                                ->required(),

                            TextInput::make('author')
                                ->label('Author')
                                ->default('Artisan Leather')
                                ->required(),

                            DateTimePicker::make('published_at')
                                ->label('Publish Date & Time')
                                ->default(now())
                                ->helperText('Schedule future posts by setting a future date.'),

                            TextInput::make('read_time')
                                ->label('Read Time (minutes)')
                                ->numeric()
                                ->default(3)
                                ->helperText('Auto-calculated when content is saved.'),

                            TagsInput::make('tags')
                                ->label('Tags')
                                ->placeholder('Add tag and press Enter')
                                ->helperText('e.g. leather care, wallet, Oman, gift ideas')
                                ->columnSpanFull(),

                            Toggle::make('is_published')
                                ->label('Published')
                                ->helperText('Turn on to make this post visible on the website.')
                                ->columnSpanFull(),
                        ]),

                    Section::make('📢 Social Sharing Tracker')
                        ->description('Check off where you\'ve already shared this article, so you know at a glance what\'s left to post.')
                        ->schema([
                            CheckboxList::make('shared_platforms')
                                ->label('')
                                ->options(self::socialPlatformOptions())
                                ->columns(3)
                                ->gridDirection('row')
                                ->columnSpanFull(),
                        ]),

                ]),

                // ── Tab 4: SEO ───────────────────────────────────────────
                Tab::make('SEO')->icon('heroicon-o-magnifying-glass')->schema([

                    Section::make('Search Engine Optimisation')
                        ->description('Leave blank to use smart defaults from the article title and excerpt.')
                        ->schema([
                            TextInput::make('meta_title')
                                ->label('SEO Title')
                                ->maxLength(70)
                                ->placeholder('e.g. How to Care for Leather Wallets — Artisan Leather Oman')
                                ->helperText(fn($state) => sprintf(
                                    '%d chars used %s · Max 60 for best display.',
                                    mb_strlen($state ?? ''),
                                    mb_strlen($state ?? '') > 60 ? '⚠️' : '✅'
                                ))
                                ->live(onBlur: true)
                                ->columnSpanFull(),

                            Textarea::make('meta_description')
                                ->label('SEO Description')
                                ->maxLength(170)
                                ->rows(3)
                                ->placeholder('e.g. Learn how to clean, condition and protect your leather wallet to make it last a lifetime. Expert tips from Artisan Leather, Muscat Oman.')
                                ->helperText(fn($state) => sprintf(
                                    '%d chars used %s · Max 160 chars.',
                                    mb_strlen($state ?? ''),
                                    mb_strlen($state ?? '') > 160 ? '⚠️' : '✅'
                                ))
                                ->live(onBlur: true)
                                ->columnSpanFull(),
                        ]),

                    Section::make('Google Preview')
                        ->schema([
                            Placeholder::make('google_preview')
                                ->label('')
                                ->content(function ($get) {
                                    $title = $get('meta_title') ?: ($get('title') . ' — Artisan Leather Blog');
                                    $desc  = $get('meta_description') ?: ($get('excerpt') ?: 'Read this article on the Artisan Leather blog.');
                                    $slug  = $get('slug') ?: 'article-slug';
                                    return new HtmlString('
                                        <div style="max-width:600px;font-family:arial,sans-serif;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
                                            <div style="font-size:12px;color:#006621;margin-bottom:2px;">artisanleatherom.com › blog › ' . e($slug) . '</div>
                                            <div style="font-size:18px;color:#1a0dab;margin-bottom:4px;">' . e(mb_substr($title, 0, 60)) . (mb_strlen($title) > 60 ? '...' : '') . '</div>
                                            <div style="font-size:13px;color:#545454;">' . e(mb_substr($desc, 0, 160)) . (mb_strlen($desc) > 160 ? '...' : '') . '</div>
                                        </div>
                                    ');
                                })
                                ->columnSpanFull(),
                        ]),

                    Section::make('📊 SEO Ranking Potential')
                        ->description('AI-estimated ranking potential based on your content vs. current competitors. Generate content first to see this score.')
                        ->collapsed()
                        ->schema([
                            TextInput::make('_seo_score')->dehydrated(false)->hidden(),
                            Textarea::make('_seo_notes')->dehydrated(false)->hidden(),

                            Placeholder::make('_seo_score_card')
                                ->label('')
                                ->content(function ($get) {
                                    $score = (int) ($get('_seo_score') ?? 0);
                                    $notes = trim($get('_seo_notes') ?? '');

                                    if ($score === 0 && blank($notes)) {
                                        return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Generate content with AI to see the ranking potential score and improvement tips.</p>');
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

                    Section::make('📱 Social Caption')
                        ->description('Ready-to-paste captions for LinkedIn / Facebook / Instagram. Pair with a tracked link from "Share Links" below.')
                        ->collapsed()
                        ->schema([
                            Textarea::make('_social_caption')->dehydrated(false)->hidden(),
                            Textarea::make('_social_caption_ar')->dehydrated(false)->hidden(),

                            Placeholder::make('_social_caption_card')
                                ->label('')
                                ->content(function ($get) {
                                    $en = trim($get('_social_caption') ?? '');
                                    $ar = trim($get('_social_caption_ar') ?? '');

                                    if (blank($en) && blank($ar)) {
                                        return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Generate content with AI to get a ready-to-paste social caption.</p>');
                                    }

                                    $block = function (string $label, string $text, string $id) {
                                        if (blank($text)) {
                                            return '';
                                        }
                                        return '
                                            <div style="margin-bottom:14px;">
                                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                                    <span style="font-size:12px;font-weight:600;color:#374151;text-transform:uppercase;letter-spacing:.05em;">' . e($label) . '</span>
                                                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById(\'' . $id . '\').value); this.textContent=\'Copied!\'; setTimeout(()=>this.textContent=\'Copy\',1500);" style="font-size:11px;font-weight:600;color:#fff;background:#d97706;border:none;border-radius:6px;padding:4px 10px;cursor:pointer;">Copy</button>
                                                </div>
                                                <textarea id="' . $id . '" readonly style="width:100%;min-height:90px;font-size:13px;color:#374151;border:1px solid #e5e7eb;border-radius:8px;padding:10px;background:#f9fafb;resize:vertical;">' . e($text) . '</textarea>
                                            </div>';
                                    };

                                    return new HtmlString('
                                        <div style="font-family:sans-serif;max-width:640px;">
                                            ' . $block('English Caption', $en, 'social_caption_en') . '
                                            ' . $block('Arabic Caption', $ar, 'social_caption_ar_box') . '
                                        </div>
                                    ');
                                })
                                ->columnSpanFull(),
                        ]),

                    Section::make('🔗 Share Links (UTM-tagged)')
                        ->description('Copy a tracked link per platform — visits and orders from these links will show up in Web Analytics.')
                        ->collapsed()
                        ->schema([
                            Placeholder::make('_share_links_card')
                                ->label('')
                                ->content(function ($get) {
                                    $slug = trim($get('slug') ?? '');

                                    if (blank($slug)) {
                                        return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Enter a title first to generate share links.</p>');
                                    }

                                    $base = "https://artisanleatherom.com/blog/{$slug}";
                                    $platforms = [
                                        'linkedin'  => '💼 LinkedIn',
                                        'facebook'  => '📘 Facebook',
                                        'instagram' => '📷 Instagram',
                                        'whatsapp'  => '💬 WhatsApp',
                                    ];

                                    $rows = '';
                                    foreach ($platforms as $key => $label) {
                                        $url = $base . '?utm_source=' . $key . '&utm_medium=social&utm_campaign=' . $slug;
                                        $id  = 'share_link_' . $key;
                                        $rows .= '
                                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid #f3f4f6;">
                                                <div style="min-width:0;flex:1;">
                                                    <div style="font-size:12px;font-weight:600;color:#374151;">' . $label . '</div>
                                                    <input id="' . $id . '" readonly value="' . e($url) . '" onclick="this.select()" style="width:100%;font-size:11px;color:#6b7280;border:none;background:transparent;padding:0;">
                                                </div>
                                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById(\'' . $id . '\').value); this.textContent=\'Copied!\'; setTimeout(()=>this.textContent=\'Copy\',1500);" style="flex-shrink:0;font-size:11px;font-weight:600;color:#fff;background:#d97706;border:none;border-radius:6px;padding:5px 12px;cursor:pointer;">Copy</button>
                                            </div>';
                                    }

                                    return new HtmlString('<div style="font-family:sans-serif;max-width:640px;">' . $rows . '</div>');
                                })
                                ->columnSpanFull(),
                        ]),

                    Section::make('🔍 Google Competition')
                        ->description('See what currently ranks for your topic — so you can write something more comprehensive and valuable.')
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
                                Action::make('research_competition')
                                    ->label('Research Competition')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->color('gray')
                                    ->action(function ($get, $set) {
                                        $query   = trim($get('meta_title') ?: $get('title') ?: '');
                                        $queryAr = trim($get('title_ar') ?: '') ?: $query;
                                        if (blank($query)) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('Enter a title first.')
                                                ->body('The SEO Title (or article title) is used as the search query.')
                                                ->warning()->send();
                                            return;
                                        }
                                        try {
                                            $results = self::fetchCompetitionData($query, $get('_competition_country') ?? 'all', $get('_competition_lang') ?? 'all', $queryAr);
                                            $set('_competition_json', json_encode($results));
                                            if (empty($results)) {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('No results returned.')
                                                    ->body('Check your Google CSE settings in Business Settings → SEO & Analytics.')
                                                    ->warning()->send();
                                            }
                                        } catch (\Throwable $e) {
                                            \Filament\Notifications\Notification::make()
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
                                        return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Click "Research Competition" to see what currently ranks for your topic.</p>');
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

                ]),

            ])->columnSpanFull()->persistTabInQueryString(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('')
                    ->disk('public')
                    ->width(60)->height(40)
                    ->defaultImageUrl(asset('logo-icon.png')),

                TextColumn::make('title')
                    ->searchable()->sortable()->weight('semibold')
                    ->description(fn($record) => $record->category),

                TextColumn::make('category')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'care-guide'        => 'success',
                        'style-tips'        => 'info',
                        'leather-knowledge' => 'warning',
                        'news'              => 'danger',
                        default             => 'gray',
                    }),

                IconColumn::make('is_published')
                    ->label('Live')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('read_time')
                    ->label('Read')
                    ->suffix(' min')
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y')
                    ->sortable(),

                TextColumn::make('shared_platforms')
                    ->label('Shared On')
                    ->formatStateUsing(function ($state) {
                        $platforms = self::socialPlatformOptions();
                        $selected  = array_intersect_key($platforms, array_flip((array) $state));
                        if (empty($selected)) {
                            return '—';
                        }
                        return implode(' ', array_map(fn ($label) => mb_substr($label, 0, 2), $selected));
                    })
                    ->tooltip(function ($state) {
                        $platforms = self::socialPlatformOptions();
                        $selected  = array_intersect_key($platforms, array_flip((array) $state));
                        return empty($selected) ? 'Not shared anywhere yet' : implode(', ', $selected);
                    }),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                TernaryFilter::make('is_published')->label('Status')
                    ->trueLabel('Published')->falseLabel('Drafts'),
                SelectFilter::make('category')
                    ->options([
                        'care-guide' => 'Care Guide', 'style-tips' => 'Style Tips',
                        'leather-knowledge' => 'Leather Knowledge', 'news' => 'News', 'general' => 'General',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('update_sharing')
                    ->label('Sharing')
                    ->icon('heroicon-o-share')
                    ->color('warning')
                    ->schema([
                        CheckboxList::make('shared_platforms')
                            ->label('Shared On')
                            ->options(self::socialPlatformOptions())
                            ->columns(2),
                    ])
                    ->fillForm(fn ($record) => ['shared_platforms' => $record->shared_platforms ?? []])
                    ->action(function (array $data, $record) {
                        $record->update(['shared_platforms' => $data['shared_platforms'] ?? []]);
                        \Filament\Notifications\Notification::make()
                            ->title('✅ Sharing status updated!')
                            ->success()->send();
                    })
                    ->modalHeading(fn ($record) => 'Update Sharing — ' . $record->title)
                    ->modalSubmitActionLabel('Save'),

                Action::make('share_whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(fn($record) =>
                        'https://wa.me/?text=' . urlencode(
                            "📖 New article from Artisan Leather:\n\n" .
                            "*{$record->title}*\n" .
                            ($record->excerpt ? "_{$record->excerpt}_\n\n" : "\n") .
                            "👉 https://artisanleatherom.com/blog/{$record->slug}\n\n" .
                            "#{$record->category} #ArtisanLeather #Oman"
                        )
                    )
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->is_published),

                Action::make('copy_link')
                    ->label('Copy Link')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->action(function ($record, $livewire) {
                        $url = "https://artisanleatherom.com/blog/{$record->slug}";
                        $livewire->dispatch('copy-to-clipboard', text: $url);
                    })
                    ->extraAttributes(fn($record) => [
                        'x-data' => '{}',
                        'x-on:copy-to-clipboard.window' => "
                            navigator.clipboard.writeText(\$event.detail.text);
                            \$el.textContent = '✓ Copied!';
                            setTimeout(() => \$el.textContent = 'Copy Link', 2000);
                        ",
                    ]),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    private static function resolveAiFilePaths(mixed $files): array
    {
        $paths = [];
        foreach ((array) $files as $relativePath) {
            if (blank($relativePath)) continue;
            $abs = Storage::disk('local')->path($relativePath);
            if (file_exists($abs)) {
                $paths[] = $abs;
            }
        }
        return $paths;
    }

    private static function fillAiFields($set, array $data): void
    {
        $set('title',            $data['title']            ?? '');
        $set('slug',             Str::slug($data['title']  ?? ''));
        $set('excerpt',          $data['excerpt']           ?? '');
        $set('content',          $data['content']           ?? '');
        $set('title_ar',         $data['title_ar']          ?? '');
        $set('excerpt_ar',       $data['excerpt_ar']        ?? '');
        $set('content_ar',       $data['content_ar']        ?? '');
        $set('title_bn',         $data['title_bn']          ?? '');
        $set('excerpt_bn',       $data['excerpt_bn']        ?? '');
        $set('content_bn',       $data['content_bn']        ?? '');
        $set('tags',             $data['tags']              ?? []);
        $set('category',         $data['category']          ?? 'general');
        $set('read_time',        $data['read_time']         ?? 4);
        $set('meta_title',       $data['meta_title']        ?? '');
        $set('meta_description', $data['meta_description']  ?? '');
        $set('_seo_score',       (string) ($data['seo_score'] ?? 0));
        $set('_seo_notes',       $data['seo_notes']         ?? '');
        $set('_social_caption',    $data['social_caption']    ?? '');
        $set('_social_caption_ar', $data['social_caption_ar'] ?? '');
    }

    protected static function socialPlatformOptions(): array
    {
        return [
            'facebook'         => '📘 Facebook',
            'instagram'        => '📷 Instagram',
            'linkedin'         => '💼 LinkedIn',
            'google_business'  => '🔍 Google Business Profile',
            'twitter'          => '🐦 Twitter / X',
            'tiktok'           => '🎵 TikTok',
            'pinterest'        => '📌 Pinterest',
            'whatsapp_status'  => '💬 WhatsApp Status',
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
        return [
            'all' => 'English + Arabic',
            'en'  => 'English only',
            'ar'  => 'Arabic only',
        ];
    }

    private static function fetchCompetitionData(string $query, string $countryFilter = 'all', string $langFilter = 'all', string $queryAr = ''): array
    {
        $flat = Setting::pluck('value', 'key')->toArray();
        $key  = $flat['seo.serper_api_key'] ?? config('services.serper.key');

        if (blank($key)) {
            throw new \RuntimeException('Serper.dev is not configured. Add your API Key in Business Settings → SEO & Analytics.');
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
        $lastError  = null;
        foreach ($markets as $gl => $market) {
            foreach ($languages as $hl => $langLabel) {
                $q = ($hl === 'ar' && $queryAr !== '') ? $queryAr : $query;
                $response = Http::timeout(10)
                    ->withHeaders(['X-API-KEY' => $key, 'Content-Type' => 'application/json'])
                    ->post('https://google.serper.dev/search', [
                        'q' => $q, 'num' => 3, 'gl' => $gl, 'hl' => $hl, 'location' => $market['location'],
                    ]);

                if (!$response->successful()) {
                    $lastError = $response->json('message') ?? $response->status();
                    continue;
                }

                foreach ($response->json('organic', []) as $item) {
                    $url          = $item['link'] ?? '';
                    $candidates[] = [
                        'title'   => $item['title']   ?? '',
                        'url'     => $url,
                        'domain'  => parse_url($url, PHP_URL_HOST) ?: $url,
                        'snippet' => $item['snippet'] ?? '',
                        'market'  => $market['label'] . ' · ' . $langLabel,
                    ];
                }
            }
        }

        // Dedupe by domain so the same site doesn't repeat across markets — surfaces different competitors
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

        if (empty($results) && $lastError) {
            throw new \RuntimeException('Search failed: ' . $lastError);
        }

        return $results;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
