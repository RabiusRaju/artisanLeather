<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("index"); }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $subtotal = $this->record->items->sum('total_price_omr');
        $data['subtotal_omr'] = round($subtotal, 3);
        $data['total_omr']    = round($subtotal, 3);
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // total_omr is recalculated in afterSave() after relationship items are written
        return $data;
    }

    protected function afterSave(): void
    {
        $record   = $this->record->fresh('items');
        $subtotal = round($record->items->sum('total_price_omr'), 3);

        $record->update([
            'subtotal_omr' => $subtotal,
            'total_omr'    => $subtotal,
        ]);
    }
}
