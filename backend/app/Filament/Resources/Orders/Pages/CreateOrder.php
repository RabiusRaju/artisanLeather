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
        // Auto-generate unique order number
        do {
            $data['order_number'] = Order::generateOrderNumber();
        } while (Order::where('order_number', $data['order_number'])->exists());

        // Compute subtotal and total from repeater items
        $subtotal = 0;
        foreach ($data['items'] ?? [] as $item) {
            $subtotal += (float)($item['total_price_omr'] ?? 0);
        }

        $data['subtotal_omr'] = round($subtotal, 3);
        $data['total_omr']    = round($subtotal, 3); // no shipping cost

        // Remove virtual customer selector field
        unset($data['_customer_id']);

        return $data;
    }
}
