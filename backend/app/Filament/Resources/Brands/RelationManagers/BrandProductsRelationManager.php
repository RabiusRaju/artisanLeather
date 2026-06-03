<?php
namespace App\Filament\Resources\Brands\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BrandProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Schema $schema): Schema { return $schema->schema([]); }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->heading('Products in this Collection')
            ->emptyStateHeading('No products assigned yet')
            ->emptyStateDescription('Assign this brand/collection to products in the Products section.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->columns([
                ImageColumn::make('images.url')
                    ->label('')->square()->imageSize(48)->disk('public'),
                TextColumn::make('name')->searchable()->weight('bold')
                    ->description(fn($record) => $record->tagline),
                TextColumn::make('category.name')->badge()->color('warning'),
                TextColumn::make('price')->prefix('OMR '),
                TextColumn::make('badge')->badge()
                    ->color(fn($state) => match($state) {
                        'bestseller' => 'warning', 'new' => 'success', default => 'gray',
                    }),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn($record) => route('filament.admin.resources.products.edit', $record)),
            ]);
    }
}
