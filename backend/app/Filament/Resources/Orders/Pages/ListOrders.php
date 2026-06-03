<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_order')
                ->label('New Order')
                ->icon('heroicon-o-plus')
                ->color('warning')
                ->url(OrderResource::getUrl('create')),
        ];
    }
}
