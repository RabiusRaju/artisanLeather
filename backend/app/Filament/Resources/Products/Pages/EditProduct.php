<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("index"); }
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        Log::info('EditProduct::save', [
            'product_id' => $this->getRecord()?->id,
            'data_keys'  => array_keys($data),
        ]);
        return $data;
    }

    protected function afterSave(): void
    {
        Log::info('EditProduct::afterSave', [
            'product_id'   => $this->getRecord()?->id,
            'images_count' => $this->getRecord()?->images()->count(),
        ]);
    }
}
