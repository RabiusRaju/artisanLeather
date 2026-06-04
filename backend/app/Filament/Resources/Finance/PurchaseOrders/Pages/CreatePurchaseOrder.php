<?php
namespace App\Filament\Resources\Finance\PurchaseOrders\Pages;
use App\Filament\Resources\Finance\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;
class CreatePurchaseOrder extends CreateRecord { protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
    protected static string $resource = PurchaseOrderResource::class; }
