<?php
namespace App\Filament\Resources\Brands;

use App\Filament\Resources\Brands\Pages;
use App\Filament\Resources\Brands\RelationManagers;
use App\Models\Brand;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Enums\NavigationGroupEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    public static function getNavigationIcon(): string  { return 'heroicon-o-bookmark'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Catalogue->value; }
    public static function getNavigationSort(): int     { return 3; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make()->tabs([

                // ── Basic Info ───────────────────────────────────────
                Tab::make('Basic Info')->icon('heroicon-o-information-circle')->schema([
                    Section::make('Identity (English)')->schema([
                        TextInput::make('name')
                            ->label('Collection Name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state)))
                            ->placeholder('e.g. Heritage Collection')
                            ->columnSpan(2),
                        TextInput::make('slug')
                            ->required()->unique(ignoreRecord: true)
                            ->helperText('URL: /collections?brand=heritage-collection')
                            ->columnSpan(1),
                        TextInput::make('tagline')
                            ->placeholder('e.g. Timeless craftsmanship, handmade in Oman.')
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->rows(4)
                            ->placeholder('Describe this collection...')
                            ->columnSpanFull(),
                        TextInput::make('website')
                            ->label('External Website (optional)')
                            ->url()->placeholder('https://...')
                            ->columnSpanFull(),
                    ])->columns(3),

                    Section::make('Status')->schema([
                        Grid::make(3)->schema([
                            Toggle::make('is_active')
                                ->label('Active (visible on website)')
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label('Featured (show on homepage)')
                                ->default(false),
                            TextInput::make('sort_order')
                                ->label('Display Order')
                                ->numeric()->default(0)
                                ->helperText('Lower = appears first'),
                        ]),
                    ]),
                ]),

                // ── Arabic ───────────────────────────────────────────
                Tab::make('Arabic / عربي')->icon('heroicon-o-language')->schema([
                    Section::make('Arabic Translation')->schema([
                        TextInput::make('name_ar')
                            ->label('Collection Name (Arabic)')
                            ->placeholder('e.g. مجموعة التراث')
                            ->columnSpan(2),
                        TextInput::make('tagline_ar')
                            ->label('Tagline (Arabic)')
                            ->placeholder('e.g. حرفية خالدة، مصنوعة يدوياً في عُمان.')
                            ->columnSpanFull(),
                        Textarea::make('description_ar')
                            ->label('Description (Arabic)')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2),
                ]),

                // ── Images ───────────────────────────────────────────
                Tab::make('Images')->icon('heroicon-o-photo')->schema([
                    Section::make('Brand Visuals')->schema([
                        FileUpload::make('logo')
                            ->label('Logo / Icon')
                            ->image()
                            ->imageEditor()
                            ->imageEditorMode(3)
                            ->imageEditorAspectRatioOptions(['1:1', null])
                            ->directory('brands/logos')
                            ->disk('public')
                            ->maxSize(2048)
                            ->helperText('Square logo — min 400×400px, max 2MB')
                            ->columnSpan(1),
                        FileUpload::make('banner')
                            ->label('Banner Image')
                            ->image()
                            ->imageEditor()
                            ->imageEditorMode(3)
                            ->imageEditorAspectRatioOptions(['16:9', '3:1', null])
                            ->directory('brands/banners')
                            ->disk('public')
                            ->maxSize(5120)
                            ->helperText('Wide banner for collection page — 16:9 or 3:1 ratio, max 5MB')
                            ->columnSpan(1),
                    ])->columns(2),
                ]),

                // ── SEO Tab ──────────────────────────────────────────────
                Tab::make('SEO')->icon('heroicon-o-magnifying-glass')->schema([
                    Section::make('Collection Page SEO')
                        ->description('Optimise how this collection appears in Google search. Leave blank to use smart defaults.')
                        ->schema([
                            TextInput::make('meta_title')
                                ->label('SEO Title')
                                ->placeholder('e.g. Heritage Collection — Handcrafted Leather Wallets & Bags | Artisan Leather Oman')
                                ->maxLength(70)
                                ->helperText('Max 60 characters. Leave blank to auto-generate from collection name.')
                                ->columnSpanFull(),
                            Textarea::make('meta_description')
                                ->label('SEO Description')
                                ->placeholder('e.g. Explore the Heritage Collection by Artisan Leather — full-grain wallets, bags and accessories handcrafted in Muscat, Oman. Free delivery across the GCC.')
                                ->maxLength(170)
                                ->rows(3)
                                ->helperText('Max 160 characters. Describe the collection with keywords.')
                                ->columnSpanFull(),
                        ]),
                ]),

            ])->columnSpanFull()->persistTabInQueryString(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()->schema([
                \Filament\Schemas\Components\Grid::make(4)->schema([
                    \Filament\Infolists\Components\TextEntry::make('name')->weight('bold'),
                    \Filament\Infolists\Components\TextEntry::make('slug')->badge()->color('gray'),
                    \Filament\Infolists\Components\TextEntry::make('status')
                        ->getStateUsing(fn(Brand $r) => $r->is_active ? 'Active' : 'Inactive')
                        ->badge()->color(fn($state) => $state === 'Active' ? 'success' : 'danger'),
                    \Filament\Infolists\Components\TextEntry::make('featured')
                        ->getStateUsing(fn(Brand $r) => $r->is_featured ? '⭐ Featured' : 'Not Featured')
                        ->badge()->color(fn($state) => str_starts_with($state, '⭐') ? 'warning' : 'gray'),
                ]),
                \Filament\Infolists\Components\TextEntry::make('tagline')->italic()->placeholder('—'),
                \Filament\Infolists\Components\TextEntry::make('description')->placeholder('—'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')->square()->imageSize(44)->disk('public'),
                TextColumn::make('name')->searchable()->sortable()->weight('bold')
                    ->description(fn(Brand $r) => $r->tagline),
                TextColumn::make('name_ar')->label('Arabic'),
                TextColumn::make('products_count')->counts('products')->label('Products')->badge()->color('info'),
                IconColumn::make('is_active')->boolean()->label('Active'),
                IconColumn::make('is_featured')->boolean()->label('Featured'),
                TextColumn::make('sort_order')->sortable()->label('Order'),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [RelationManagers\BrandProductsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'view'   => Pages\ViewBrand::route('/{record}'),
            'edit'   => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
