<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Details')
                    ->columns(2)
                    ->components([
                        TextEntry::make('name'),
                        TextEntry::make('email')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->placeholder('—'),
                        IconEntry::make('is_admin')
                            ->label('Admin access')
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label('Member since')
                            ->dateTime(),
                    ]),
                Section::make('Activity')
                    ->columns(3)
                    ->components([
                        TextEntry::make('orders_count')
                            ->label('Orders')
                            ->state(fn ($record) => $record->orders()->count()),
                        TextEntry::make('reviews_count')
                            ->label('Reviews')
                            ->state(fn ($record) => $record->reviews()->count()),
                        TextEntry::make('wishlists_count')
                            ->label('Wishlist items')
                            ->state(fn ($record) => $record->wishlists()->count()),
                    ]),
            ]);
    }
}
