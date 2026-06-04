<?php
namespace App\Filament\Resources\Finance\PurchaseOrders\Pages;
use App\Filament\Resources\Finance\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\EditRecord;
class EditPurchaseOrder extends EditRecord { protected static string $resource = PurchaseOrderResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("index"); }
    protected function getHeaderActions(): array { return [\Filament\Actions\DeleteAction::make()]; } }
