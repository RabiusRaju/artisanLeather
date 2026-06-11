<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coupon Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Coupon Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper(trim($state)) : $state)
                            ->helperText('Customers will enter this code at checkout (not case-sensitive).'),

                        TextInput::make('description')
                            ->label('Description (admin only)')
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Discount Type')
                            ->options([
                                'percentage' => 'Percentage off order total',
                                'fixed'      => 'Fixed amount off order total',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('value')
                            ->label('Discount Value')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->suffix(fn (callable $get) => $get('type') === 'percentage' ? '%' : 'OMR'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
