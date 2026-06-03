<?php
namespace App\Filament\Resources\Finance\PurchaseOrders\Pages;
use App\Filament\Resources\Finance\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\ViewRecord;
class ViewPurchaseOrder extends ViewRecord { protected static string $resource = PurchaseOrderResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\EditAction::make()]; } }
