<?php
namespace App\Filament\Resources\Operations\Inventory\Pages;
use App\Filament\Resources\Operations\Inventory\InventoryResource;
use Filament\Resources\Pages\ListRecords;
class ListInventory extends ListRecords { protected static string $resource = InventoryResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()->label("Add Product to Stock")]; } }
