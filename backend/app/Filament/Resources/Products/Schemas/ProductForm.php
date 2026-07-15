<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('name_ar')
                    ->default(null),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('tagline')
                    ->default(null),
                TextInput::make('tagline_ar')
                    ->default(null),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('description_ar')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('material')
                    ->default(null),
                TextInput::make('material_ar')
                    ->default(null),
                TextInput::make('origin')
                    ->default(null),
                TextInput::make('origin_ar')
                    ->default(null),
                Textarea::make('care')
                    ->default(null)
                    ->helperText('Enter one care instruction per line. Each line will show as a bullet point on the product page.')
                    ->columnSpanFull(),
                Textarea::make('care_ar')
                    ->default(null)
                    ->helperText('Enter one care instruction per line. Each line will show as a bullet point on the product page.')
                    ->columnSpanFull(),
                Textarea::make('shipping')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('shipping_ar')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Select::make('badge')
                    ->options(['bestseller' => 'Bestseller', 'new' => 'New'])
                    ->default(null),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_featured')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
