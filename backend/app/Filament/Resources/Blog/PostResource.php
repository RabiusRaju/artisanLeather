<?php
namespace App\Filament\Resources\Blog;

use App\Filament\Resources\Blog\Pages;
use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Actions\DeleteAction;
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

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-pencil-square'; }
    public static function getNavigationGroup(): string { return 'Content'; }
    public static function getNavigationSort(): int     { return 1; }
    public static function getNavigationBadge(): ?string
    {
        $count = Post::where('is_published', false)->count();
        return $count > 0 ? (string)$count : null;
    }
    public static function getNavigationBadgeColor(): string { return 'warning'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
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
                                ->toolbarButtons([
                                    'h2', 'h3', 'bold', 'italic', 'underline', 'strike',
                                    'link', 'bulletList', 'orderedList', 'blockquote',
                                    'codeBlock', 'attachFiles', 'horizontalRule', 'undo',
                                ])
                                ->fileAttachmentsDirectory('blog/attachments')
                                ->fileAttachmentsDisk('public')
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
                                ->toolbarButtons([
                                    'h2', 'h3', 'bold', 'italic', 'underline',
                                    'link', 'bulletList', 'orderedList', 'blockquote', 'undo',
                                ])
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
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
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
