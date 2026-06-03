<?php
namespace App\Filament\Resources\Finance\PurchaseOrders\Pages;
use App\Filament\Resources\Finance\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\ListRecords;
class ListPurchaseOrders extends ListRecords { protected static string $resource = PurchaseOrderResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()->label("New Purchase Order")]; } }
