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
use Filament\Resources\Resource;
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
    public static function getNavigationGroup(): string { return 'Catalogue'; }
    public static function getNavigationSort(): int { return 2; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
