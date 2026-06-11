<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('governorate')
                    ->required(),
                TextInput::make('city')
                    ->required(),
                Textarea::make('address')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('payment_method')
                    ->options(['cod' => 'Cod', 'bank' => 'Bank', 'whatsapp' => 'Whatsapp'])
                    ->default('cod')
                    ->required(),
                TextInput::make('currency_code')
                    ->required()
                    ->default('OMR'),
                TextInput::make('currency_rate')
                    ->required()
                    ->numeric()
                    ->default(1.0),
                TextInput::make('subtotal_omr')
                    ->required()
                    ->numeric(),
                TextInput::make('total_omr')
                    ->required()
                    ->numeric(),
                TextInput::make('coupon_code')
                    ->label('Coupon Code')
                    ->disabled(),
                TextInput::make('discount_amount')
                    ->label('Discount (OMR)')
                    ->numeric()
                    ->disabled(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                Textarea::make('admin_notes')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
