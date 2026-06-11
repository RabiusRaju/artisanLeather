<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use App\Enums\NavigationGroupEnum;
use App\Models\Setting;
use App\Services\AiPostService;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-shopping-bag'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Catalogue->value; }
    public static function getNavigationSort(): int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            // ── AI Auto-Fill ─────────────────────────────────────────────
            Section::make('✨ AI Content Generator')
                ->description('Describe the product, then choose which AI to generate all copy. Review every tab before saving.')
                ->collapsed()
                ->schema([
                    Textarea::make('ai_prompt')
                        ->label('What is this product?')
                        ->placeholder('e.g. Black premium calf leather bifold wallet with 8 card slots, handmade in Oman')
                        ->helperText('Be specific about colour, material, style and use case.')
                        ->rows(4)
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
                            ->modalDescription('This will overwrite all text fields (name, description, care, shipping, SEO…). Continue?')
                            ->modalSubmitActionLabel('Yes, generate')
                            ->action(function ($get, $set) {
                                $prompt = $get('ai_prompt');
                                if (blank($prompt)) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Please enter a product description first.')
                                        ->warning()->send();
                                    return;
                                }
                                $filePaths = self::resolveAiFilePaths($get('ai_attachments') ?? []);
                                try {
                                    $data = app(AiPostService::class)->generateProductWithClaude($prompt, $filePaths);
                                    self::fillAiFields($set, $data);
                                    $set('ai_attachments', []);
                                    \Filament\Notifications\Notification::make()
                                        ->title('✅ Claude generated your product copy!')
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
                            ->modalDescription('This will overwrite all text fields (name, description, care, shipping, SEO…). Continue?')
                            ->modalSubmitActionLabel('Yes, generate')
                            ->action(function ($get, $set) {
                                $prompt = $get('ai_prompt');
                                if (blank($prompt)) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Please enter a product description first.')
                                        ->warning()->send();
                                    return;
                                }
                                $filePaths = self::resolveAiFilePaths($get('ai_attachments') ?? []);
                                try {
                                    $data = app(AiPostService::class)->generateProductWithOpenAI($prompt, $filePaths);
                                    self::fillAiFields($set, $data);
                                    $set('ai_attachments', []);
                                    \Filament\Notifications\Notification::make()
                                        ->title('✅ OpenAI generated your product copy!')
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

            Tabs::make('Product')
                ->tabs([

                    // ── Tab 1: Basic Info ────────────────────────────────
                    Tab::make('Basic Info')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make('Classification')->schema([
                                Select::make('category_id')
                                    ->label('Category')
                                    ->options(Category::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(1),

                                Select::make('brand_id')
                                    ->label('Collection / Brand')
                                    ->options(Brand::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable()
                                    ->placeholder('No collection')
                                    ->helperText('Optional — assign to a product collection')
                                    ->columnSpan(1),

                                Select::make('badge')
                                    ->label('Badge')
                                    ->options(['bestseller' => '⭐ Bestseller', 'new' => '🆕 New'])
                                    ->placeholder('None')
                                    ->nullable()
                                    ->columnSpan(1),

                                TextInput::make('sort_order')
                                    ->label('Display Order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Lower number = appears first')
                                    ->columnSpan(1),

                                Grid::make(2)->schema([
                                    Toggle::make('is_active')
                                        ->label('Active (visible on website)')
                                        ->default(true),
                                    Toggle::make('is_featured')
                                        ->label('Featured (show in Best Sellers)')
                                        ->default(false),
                                ])->columnSpanFull(),
                            ])->columns(3),

                            Section::make('Identity (English)')->schema([
                                TextInput::make('name')
                                    ->label('Product Name')
                                    ->required()
                                    ->placeholder('e.g. The Heritage Bifold')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state)))
                                    ->columnSpan(2),

                                TextInput::make('slug')
                                    ->label('URL Slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Auto-generated from name. Used in the URL.')
                                    ->columnSpan(1),

                                TextInput::make('tagline')
                                    ->label('Tagline')
                                    ->placeholder('e.g. Classic meets refined minimalism.')
                                    ->helperText('Short one-liner shown under the product name.')
                                    ->columnSpanFull(),

                                Textarea::make('description')
                                    ->label('Full Description')
                                    ->rows(5)
                                    ->placeholder('Detailed product description...')
                                    ->columnSpanFull(),
                            ])->columns(3),

                            Section::make('Pricing')->schema([
                                TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('OMR')
                                    ->step(0.001)
                                    ->placeholder('0.000')
                                    ->helperText('Enter price in Omani Rial (OMR). Other currencies convert automatically.')
                                    ->columnSpan(1),

                                TextInput::make('material')
                                    ->label('Material')
                                    ->placeholder('e.g. Full Grain Vegetable Tanned')
                                    ->columnSpan(1),

                                TextInput::make('origin')
                                    ->label('Origin')
                                    ->placeholder('e.g. Hand-stitched in Muscat, Oman')
                                    ->columnSpan(1),
                            ])->columns(3),
                        ]),

                    // ── Tab 2: Arabic Content ────────────────────────────
                    Tab::make('Arabic / عربي')
                        ->icon('heroicon-o-language')
                        ->schema([
                            Section::make('Arabic Translation')->description('All Arabic fields are optional but recommended for the Oman market.')->schema([
                                TextInput::make('name_ar')
                                    ->label('Product Name (Arabic)')
                                    ->placeholder('e.g. المحفظة التراثية')
                                    ->columnSpan(2),

                                TextInput::make('tagline_ar')
                                    ->label('Tagline (Arabic)')
                                    ->placeholder('e.g. الكلاسيكية تلتقي بالأناقة')
                                    ->columnSpanFull(),

                                Textarea::make('description_ar')
                                    ->label('Description (Arabic)')
                                    ->rows(5)
                                    ->columnSpanFull(),

                                TextInput::make('material_ar')
                                    ->label('Material (Arabic)')
                                    ->placeholder('e.g. جلد كامل الحبوب مدبوغ نباتيًا')
                                    ->columnSpan(1),

                                TextInput::make('origin_ar')
                                    ->label('Origin (Arabic)')
                                    ->placeholder('e.g. مخيط يدويًا في مسقط، عُمان')
                                    ->columnSpan(1),
                            ])->columns(2),

                            Section::make('Care & Shipping (Arabic)')->schema([
                                Textarea::make('care_ar')
                                    ->label('Care Instructions (Arabic)')
                                    ->rows(3)
                                    ->columnSpan(1),

                                Textarea::make('shipping_ar')
                                    ->label('Shipping Info (Arabic)')
                                    ->rows(3)
                                    ->columnSpan(1),
                            ])->columns(2),
                        ]),

                    // ── Tab 3: Care & Shipping ────────────────────────────
                    Tab::make('Care & Shipping')
                        ->icon('heroicon-o-truck')
                        ->schema([
                            Section::make('Care Instructions & Shipping')->schema([
                                Textarea::make('care')
                                    ->label('Care Instructions (English)')
                                    ->rows(4)
                                    ->placeholder('e.g. Condition with leather balm every 6 months...')
                                    ->columnSpan(1),

                                Textarea::make('shipping')
                                    ->label('Shipping Information (English)')
                                    ->rows(4)
                                    ->placeholder('e.g. Complimentary delivery across Oman & GCC...')
                                    ->columnSpan(1),
                            ])->columns(2),
                        ]),

                    // ── Tab 4: Images ────────────────────────────────────
                    Tab::make('Images')
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Section::make('Product Images')
                                ->description('Upload up to 6 images (max 5 MB each · JPG / PNG / WebP). After uploading, click ✏️ Edit to crop, rotate, adjust brightness, contrast and saturation. Images are auto-converted to WebP.')
                                ->schema([
                                    Repeater::make('images')
                                        ->relationship()
                                        ->label('')
                                        ->schema([
                                            // ── Current image preview ───────────────────────
                                            // Works for both local paths AND external URLs (Unsplash, etc.)
                                            Placeholder::make('image_preview')
                                                ->label('Current Image')
                                                ->content(function ($record): HtmlString {
                                                    $url = $record?->url;

                                                    if (!$url) {
                                                        return new HtmlString(
                                                            '<p style="color:#9ca3af;font-size:0.8rem;padding:8px 0">
                                                                No image yet — upload one below.
                                                             </p>'
                                                        );
                                                    }

                                                    // External URL → use as-is; local path → resolve via disk
                                                    $src = str_starts_with($url, 'http')
                                                        ? $url
                                                        : Storage::disk('public')->url($url);

                                                    return new HtmlString(
                                                        '<div style="display:flex;align-items:center;gap:16px;margin-bottom:4px;">
                                                            <img src="' . e($src) . '"
                                                                 style="height:160px;width:160px;object-fit:cover;
                                                                        border-radius:4px;border:1px solid rgba(201,168,76,0.25);"
                                                                 alt="Product image preview" />
                                                            <div style="font-size:0.75rem;color:#9ca3af;line-height:1.6;">
                                                                <strong style="color:#d4af37;display:block;margin-bottom:4px;">
                                                                    ✓ Image saved
                                                                </strong>
                                                                Upload a new file below to replace this image.<br>
                                                                The editor will open after upload so you can<br>
                                                                crop, rotate and fine-tune before saving.
                                                            </div>
                                                         </div>'
                                                    );
                                                })
                                                ->columnSpanFull(),

                                            FileUpload::make('url')
                                                ->label('Upload / Replace Image')
                                                ->image()

                                                // ── Built-in image editor ──────────────────
                                                ->imageEditor()

                                                // Mode 3 = Crop + Fine-tune (brightness, contrast, saturation, warmth)
                                                ->imageEditorMode(3)

                                                // Aspect ratio presets the editor offers
                                                ->imageEditorAspectRatioOptions([
                                                    null,    // Free / no fixed ratio
                                                    '1:1',   // Square — catalogue card
                                                    '4:5',   // Portrait — product detail
                                                    '3:4',   // Portrait — standard product
                                                    '16:9',  // Landscape — banner / hero
                                                ])

                                                // Editor popup size (larger = more comfortable editing)
                                                ->imageEditorViewportWidth(1100)
                                                ->imageEditorViewportHeight(700)

                                                // Dark fill for any transparent PNG areas
                                                ->imageEditorEmptyFillColor('#120D05')

                                                // ── Auto-resize output ─────────────────────
                                                // Resize to max 1200px (observer converts to WebP after save)
                                                ->automaticallyResizeImagesMode('cover')
                                                ->automaticallyResizeImagesToWidth(1200)
                                                ->automaticallyResizeImagesToHeight(1200)
                                                ->automaticallyUpscaleImagesWhenResizing(false)

                                                // ── Upload settings ────────────────────────
                                                ->directory('products')
                                                ->disk('public')
                                                ->visibility('public')
                                                ->maxSize(5120)
                                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                                                ->rules(['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'])
                                                ->validationMessages([
                                                    'max'   => 'Image must be smaller than 5 MB.',
                                                    'mimes' => 'Only JPG, PNG or WebP images are accepted.',
                                                ])
                                                ->helperText('💡 Tip: After uploading, click the ✏️ pencil icon to open the editor — crop, rotate, adjust brightness/contrast/saturation.')
                                                ->required()
                                                ->columnSpanFull(),

                                            Select::make('label')
                                                ->label('View Label')
                                                ->options([
                                                    'Front'  => 'Front',
                                                    'Side'   => 'Side',
                                                    'Open'   => 'Open / Interior',
                                                    'Detail' => 'Detail / Closeup',
                                                    'Back'   => 'Back',
                                                ])
                                                ->placeholder('Select view…')
                                                ->columnSpan(1),

                                            TextInput::make('sort_order')
                                                ->label('Order')
                                                ->numeric()
                                                ->default(0)
                                                ->helperText('1 = main image')
                                                ->columnSpan(1),

                                            TextInput::make('alt_text')
                                                ->label('Alt Text (SEO)')
                                                ->placeholder('e.g. Heritage Bifold Wallet open view showing 8 card slots — Artisan Leather Oman')
                                                ->helperText('Describe the image for Google Image Search and accessibility. Auto-generated if left blank.')
                                                ->maxLength(125)
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(2)
                                        ->maxItems(6)
                                        ->reorderable()
                                        ->reorderableWithDragAndDrop()
                                        ->collapsible()
                                        ->itemLabel(fn(array $state): ?string => $state['label'] ?? 'Image'),
                                ]),
                        ]),

                    // ── Tab 5: Colors ────────────────────────────────────
                    Tab::make('Colors')
                        ->icon('heroicon-o-swatch')
                        ->schema([
                            Section::make('Available Colors')
                                ->description('Add the leather colors available for this product. At least one color is required.')
                                ->schema([
                                    Repeater::make('colors')
                                        ->relationship()
                                        ->label('')
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Color Name (EN)')
                                                ->required()
                                                ->placeholder('e.g. Cognac')
                                                ->columnSpan(1),

                                            TextInput::make('name_ar')
                                                ->label('Color Name (Arabic)')
                                                ->placeholder('e.g. كونياك')
                                                ->columnSpan(1),

                                            ColorPicker::make('hex')
                                                ->label('Color Swatch')
                                                ->required()
                                                ->columnSpan(1),

                                            TextInput::make('sort_order')
                                                ->label('Order')
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),
                                        ])
                                        ->columns(4)
                                        ->reorderable()
                                        ->reorderableWithDragAndDrop()
                                        ->itemLabel(fn(array $state): ?string => $state['name'] ?? 'Color'),
                                ]),
                        ]),

                    // ── Tab 6: Bullet Points ─────────────────────────────
                    Tab::make('Bullet Points')
                        ->icon('heroicon-o-list-bullet')
                        ->schema([
                            Section::make('Product Details')
                                ->description('Add 4–6 bullet points that appear in the product details accordion. Be specific — dimensions, slots, material thickness, etc.')
                                ->schema([
                                    Repeater::make('details')
                                        ->relationship()
                                        ->label('')
                                        ->schema([
                                            TextInput::make('detail')
                                                ->label('Detail (English)')
                                                ->required()
                                                ->placeholder('e.g. 8 card slots + 2 bill compartments')
                                                ->columnSpan(2),

                                            TextInput::make('detail_ar')
                                                ->label('Detail (Arabic)')
                                                ->placeholder('e.g. 8 فتحات بطاقات + خانتان')
                                                ->columnSpan(2),

                                            TextInput::make('sort_order')
                                                ->label('Order')
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),
                                        ])
                                        ->columns(5)
                                        ->reorderable()
                                        ->reorderableWithDragAndDrop()
                                        ->maxItems(10),
                                ]),
                        ]),

                    // ── Tab 7: SEO ───────────────────────────────────────
                    Tab::make('SEO')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([

                            Section::make('Search Engine Optimisation')
                                ->description('Custom SEO fields for this product. Leave blank to use smart defaults (product name + tagline).')
                                ->schema([

                                    TextInput::make('meta_title')
                                        ->label('SEO Title')
                                        ->placeholder('e.g. Heritage Bifold Wallet — Handcrafted Leather | Artisan Leather Oman')
                                        ->maxLength(70)
                                        ->helperText(fn ($state) => sprintf(
                                            'Max 60 chars · %d chars used %s · Leave blank to auto-generate from product name.',
                                            mb_strlen($state ?? ''),
                                            mb_strlen($state ?? '') > 60 ? '⚠️ TOO LONG' : '✅'
                                        ))
                                        ->columnSpanFull()
                                        ->live(onBlur: true),

                                    Textarea::make('meta_description')
                                        ->label('SEO Description')
                                        ->placeholder('e.g. Handcrafted Heritage Bifold Wallet in full-grain leather. 8 card slots, 2 bill compartments. Free delivery across Oman. Shop now at Artisan Leather Muscat.')
                                        ->maxLength(170)
                                        ->rows(3)
                                        ->helperText('Max 160 characters. Describe the product with keywords. Leave blank to use the tagline.')
                                        ->columnSpanFull(),

                                ])->columns(1),

                            Section::make('Preview — How Google Sees It')
                                ->description('Live preview of how this product will appear in Google search results.')
                                ->schema([
                                    Placeholder::make('google_preview')
                                        ->label('')
                                        ->content(function ($get, $record) {
                                            $name  = $get('name') ?: ($record?->name ?? 'Product Name');
                                            $title = $get('meta_title') ?: ($name . ' — Handcrafted Leather | Artisan Leather Oman');
                                            $desc  = $get('meta_description') ?: ($get('tagline') ?: 'Premium handcrafted leather goods from Artisan Leather, Muscat Oman.');
                                            $slug  = $get('slug') ?: 'product-slug';

                                            return new HtmlString('
                                                <div style="max-width:600px;font-family:arial,sans-serif;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
                                                    <div style="font-size:12px;color:#006621;margin-bottom:2px;">artisanleatherom.com › product › ' . e($slug) . '</div>
                                                    <div style="font-size:18px;color:#1a0dab;margin-bottom:4px;font-weight:normal;">' . e(mb_substr($title, 0, 60)) . (mb_strlen($title) > 60 ? '...' : '') . '</div>
                                                    <div style="font-size:13px;color:#545454;line-height:1.5;">' . e(mb_substr($desc, 0, 160)) . (mb_strlen($desc) > 160 ? '...' : '') . '</div>
                                                    <div style="margin-top:8px;font-size:11px;color:' . (mb_strlen($title) > 60 ? '#dc2626' : '#059669') . ';">Title: ' . mb_strlen($title) . ' chars ' . (mb_strlen($title) > 60 ? '⚠️ too long' : '✅') . ' &nbsp;|&nbsp; Description: ' . mb_strlen($desc) . ' chars ' . (mb_strlen($desc) > 160 ? '⚠️ too long' : '✅') . '</div>
                                                </div>
                                            ');
                                        })
                                        ->columnSpanFull(),
                                ]),

                            Section::make('📊 SEO Ranking Potential')
                                ->description('AI-estimated ranking potential based on your product vs. current competitors. Generate content first to see this score.')
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

                            Section::make('🔍 Google Competition')
                                ->description('See what currently ranks for your product — so you can write more comprehensive and valuable copy.')
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
                                        Action::make('research_competition_product')
                                            ->label('Research Competition')
                                            ->icon('heroicon-o-magnifying-glass')
                                            ->color('gray')
                                            ->action(function ($get, $set) {
                                                $query   = trim($get('meta_title') ?: $get('name') ?: '');
                                                $queryAr = trim($get('name_ar') ?: '') ?: $query;
                                                if (blank($query)) {
                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Enter a product name first.')
                                                        ->body('The SEO Title (or product name) is used as the search query.')
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
                                                return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Click "Research Competition" to see what currently ranks for your product.</p>');
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

                ])
                ->columnSpanFull()
                ->persistTabInQueryString(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images.url')
                    ->label('')
                    ->square()
                    ->imageSize(52)
                    ->disk('public')
                    ->defaultImageUrl(fn() => null),
                TextColumn::make('name')->searchable()->sortable()->weight('bold')
                    ->description(fn(Product $record) => $record->tagline),
                TextColumn::make('category.name')->badge()->color('warning')->sortable(),
                TextColumn::make('brand.name')->badge()->color('info')->label('Collection')->placeholder('—'),
                TextColumn::make('price')->prefix('OMR ')->sortable(),
                TextColumn::make('badge')->badge()
                    ->color(fn($state) => match($state) {
                        'bestseller' => 'warning',
                        'new'        => 'success',
                        default      => 'gray',
                    }),
                IconColumn::make('is_active')->boolean()->label('Active'),
                IconColumn::make('is_featured')->boolean()->label('Featured'),
                TextColumn::make('updated_at')->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
                SelectFilter::make('badge')->options(['bestseller' => 'Bestseller', 'new' => 'New']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('sort_order');
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
        $set('name',             $data['name']             ?? '');
        $set('name_ar',          $data['name_ar']          ?? '');
        $set('slug',             Str::slug($data['name']   ?? ''));
        $set('tagline',          $data['tagline']          ?? '');
        $set('tagline_ar',       $data['tagline_ar']       ?? '');
        $set('description',      $data['description']      ?? '');
        $set('description_ar',   $data['description_ar']   ?? '');
        $set('material',         $data['material']         ?? '');
        $set('material_ar',      $data['material_ar']      ?? '');
        $set('origin',           $data['origin']           ?? '');
        $set('origin_ar',        $data['origin_ar']        ?? '');
        $set('care',             $data['care']             ?? '');
        $set('care_ar',          $data['care_ar']          ?? '');
        $set('shipping',         $data['shipping']         ?? '');
        $set('shipping_ar',      $data['shipping_ar']      ?? '');
        $set('meta_title',       $data['meta_title']       ?? '');
        $set('meta_description', $data['meta_description'] ?? '');
        $set('_seo_score',       (string) ($data['seo_score'] ?? 0));
        $set('_seo_notes',       $data['seo_notes']        ?? '');
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
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
