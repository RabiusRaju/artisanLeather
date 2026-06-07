<?php
namespace App\Filament\Resources\Operations\Inventory\Pages;

use App\Filament\Resources\Operations\Inventory\InventoryResource;
use App\Models\ProductStock;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInventory extends CreateRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return ProductStock::updateOrCreate(
            ['product_id' => $data['product_id']],
            collect($data)->except('product_id')->toArray()
        );
    }
}
