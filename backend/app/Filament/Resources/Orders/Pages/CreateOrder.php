<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        do {
            $data['order_number'] = Order::generateOrderNumber();
        } while (Order::where('order_number', $data['order_number'])->exists());

        unset($data['_customer_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record   = $this->record->fresh('items');
        $subtotal = round($record->items->sum('total_price_omr'), 3);

        $record->update([
            'subtotal_omr' => $subtotal,
            'total_omr'    => $subtotal,
        ]);
    }
}
