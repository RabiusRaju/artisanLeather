<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Review')
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->label('Product')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Customer')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('rating')
                            ->label('Rating')
                            ->options([
                                1 => '1 star',
                                2 => '2 stars',
                                3 => '3 stars',
                                4 => '4 stars',
                                5 => '5 stars',
                            ])
                            ->required(),

                        TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255),

                        Textarea::make('comment')
                            ->label('Comment')
                            ->rows(4)
                            ->columnSpanFull(),

                        Toggle::make('is_approved')
                            ->label('Approved (visible on website)')
                            ->inline(false),
                    ]),
            ]);
    }
}
