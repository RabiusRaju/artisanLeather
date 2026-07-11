<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages;
use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Enums\NavigationGroupEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-tag'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Catalogue->value; }
    public static function getNavigationSort(): int { return 1; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Category Details')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),
                    TextInput::make('name_ar')->label('Name (Arabic)'),
                    TextInput::make('slug')->required()->unique(ignoreRecord: true),
                    TextInput::make('sort_order')->numeric()->default(0),
                    Toggle::make('is_active')->default(true),
                ]),

            Section::make('Homepage Collection Card Image')
                ->description('This image appears on the homepage Collections cards, like Wallets, Bags, Belts, and Accessories.')
                ->schema([
                    FileUpload::make('image')
                        ->label('Upload Image')
                        ->image()
                        ->imageEditor()
                        ->directory('categories')
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(4096)
                        ->helperText('Recommended: portrait image, around 900×1200px. This fills the homepage collection card.'),
                    TextInput::make('image_alt')
                        ->label('Image ALT Text')
                        ->placeholder('e.g. Handcrafted leather wallets collection by Artisan Leather Oman')
                        ->helperText('Describe the category image for SEO and screen readers.')
                        ->maxLength(125),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->square()->size(48),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('name_ar')->label('Arabic'),
                TextColumn::make('products_count')->counts('products')->label('Products'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
