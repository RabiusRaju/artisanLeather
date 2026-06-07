<?php
namespace App\Filament\Resources\Finance\PurchaseOrders\Pages;

use App\Filament\Resources\Finance\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $subtotal = $this->record->items->sum('total_cost_omr');

        $data['subtotal_omr'] = round($subtotal, 3);
        $data['total_omr']    = round(
            $subtotal
            + (float)($data['shipping_cost_omr'] ?? 0)
            + (float)($data['customs_duty_omr']  ?? 0)
            + (float)($data['other_costs_omr']   ?? 0),
            3
        );

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $subtotal = collect($data['items'] ?? [])->sum(fn($i) => (float)($i['total_cost_omr'] ?? 0));

        $data['subtotal_omr'] = round($subtotal, 3);
        $data['total_omr']    = round(
            $subtotal
            + (float)($data['shipping_cost_omr'] ?? 0)
            + (float)($data['customs_duty_omr']  ?? 0)
            + (float)($data['other_costs_omr']   ?? 0),
            3
        );

        return $data;
    }
}
