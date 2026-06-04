<?php
namespace App\Filament\Resources\Operations\Inventory\Pages;
use App\Filament\Resources\Operations\Inventory\InventoryResource;
use Filament\Resources\Pages\EditRecord;
class EditInventory extends EditRecord { protected static string $resource = InventoryResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("index"); }
    protected function getHeaderActions(): array { return [\Filament\Actions\DeleteAction::make()]; } }
