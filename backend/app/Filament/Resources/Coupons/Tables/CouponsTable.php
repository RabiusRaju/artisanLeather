<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'percentage' ? 'Percentage' : 'Fixed Amount'),

                TextColumn::make('value')
                    ->label('Value')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage' ? rtrim(rtrim((string) $state, '0'), '.') . '%' : 'OMR ' . number_format((float) $state, 3)),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No coupons yet')
            ->emptyStateDescription('Create a coupon code to offer discounts at checkout.');
    }
}
