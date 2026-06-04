<?php
namespace App\Filament\Resources\Finance\Suppliers\Pages;
use App\Filament\Resources\Finance\Suppliers\SupplierResource;
use Filament\Resources\Pages\CreateRecord;
class CreateSupplier extends CreateRecord { protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
    protected static string $resource = SupplierResource::class; }
