<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->heading('Regular Orders')
            ->description('Orders placed through the website')
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('This customer hasn\'t placed any regular orders.')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->columns([
                TextColumn::make('order_number')
                    ->weight('bold')
                    ->copyable()
                    ->label('Order #'),

                TextColumn::make('items_summary')
                    ->label('Items')
                    ->getStateUsing(function ($record) {
                        $record->loadMissing('items');
                        $count = $record->items->sum('quantity');
                        $names = $record->items->pluck('product_name')->take(2)->join(', ');
                        $suffix = $record->items->count() > 2 ? ' +more' : '';
                        return new HtmlString(
                            '<span style="font-size:0.8rem;">' . e($names) . $suffix . '</span>'
                            . '<br><span style="font-size:0.75rem;color:#9ca3af;">' . $count . ' item' . ($count !== 1 ? 's' : '') . '</span>'
                        );
                    }),

                TextColumn::make('total_omr')
                    ->label('Total (OMR)')
                    ->prefix('OMR ')
                    ->weight('bold')
                    ->color('warning'),

                TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'cod'      => 'warning',
                        'bank'     => 'info',
                        'whatsapp' => 'success',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'cod'      => 'Cash on Delivery',
                        'bank'     => 'Bank Transfer',
                        'whatsapp' => 'WhatsApp',
                        default    => $state,
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pending'    => 'warning',
                        'confirmed'  => 'info',
                        'processing' => 'primary',
                        'shipped'    => 'success',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d M Y'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'pending'    => 'Pending',
                    'confirmed'  => 'Confirmed',
                    'processing' => 'Processing',
                    'shipped'    => 'Shipped',
                    'delivered'  => 'Delivered',
                    'cancelled'  => 'Cancelled',
                ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn($record) => route('filament.admin.resources.orders.view', $record)),
            ]);
    }
}
