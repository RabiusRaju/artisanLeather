<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'customOrders';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_number')
            ->heading('Custom / Bespoke Orders')
            ->description('Personalised leather orders crafted to specification')
            ->emptyStateHeading('No custom orders yet')
            ->emptyStateDescription('This customer hasn\'t placed any bespoke orders.')
            ->emptyStateIcon('heroicon-o-scissors')
            ->columns([
                TextColumn::make('reference_number')
                    ->weight('bold')
                    ->copyable()
                    ->label('Reference'),

                TextColumn::make('product_type')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'wallet'    => 'info',
                        'bag'       => 'success',
                        'belt'      => 'warning',
                        'accessory' => 'gray',
                        default     => 'gray',
                    }),

                TextColumn::make('product_name')
                    ->label('Product')
                    ->placeholder('—'),

                TextColumn::make('leather_color')
                    ->label('Leather')
                    ->placeholder('—'),

                TextColumn::make('monogram')
                    ->label('Monogram')
                    ->placeholder('—'),

                TextColumn::make('agreed_price_omr')
                    ->label('Price (OMR)')
                    ->prefix('OMR ')
                    ->weight('bold')
                    ->color('warning'),

                IconColumn::make('deposit_paid')
                    ->boolean()
                    ->label('Deposit'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'inquiry'        => 'gray',
                        'confirmed'      => 'info',
                        'in_production'  => 'warning',
                        'quality_check'  => 'primary',
                        'ready'          => 'success',
                        'delivered'      => 'success',
                        'cancelled'      => 'danger',
                        default          => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'inquiry'        => 'Inquiry',
                        'confirmed'      => 'Confirmed',
                        'in_production'  => 'In Production',
                        'quality_check'  => 'QC Check',
                        'ready'          => 'Ready',
                        'delivered'      => 'Delivered',
                        'cancelled'      => 'Cancelled',
                        default          => $state,
                    }),

                TextColumn::make('promised_date')
                    ->label('Due')
                    ->date('d M Y')
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'inquiry'       => 'Inquiry',
                    'confirmed'     => 'Confirmed',
                    'in_production' => 'In Production',
                    'quality_check' => 'QC Check',
                    'ready'         => 'Ready',
                    'delivered'     => 'Delivered',
                    'cancelled'     => 'Cancelled',
                ]),
                SelectFilter::make('product_type')->options([
                    'wallet'    => 'Wallet',
                    'bag'       => 'Bag',
                    'belt'      => 'Belt',
                    'accessory' => 'Accessory',
                ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn($record) => route('filament.admin.resources.custom-orders.view', $record)),
            ]);
    }
}
