<?php
namespace App\Filament\Resources\Operations\Inventory\Pages;
use App\Filament\Resources\Operations\Inventory\InventoryResource;
use Filament\Resources\Pages\CreateRecord;
class CreateInventory extends CreateRecord { protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
    protected static string $resource = InventoryResource::class; }
