<?php
namespace App\Filament\Resources\Finance\Suppliers\Pages;
use App\Filament\Resources\Finance\Suppliers\SupplierResource;
use Filament\Resources\Pages\ViewRecord;
class ViewSupplier extends ViewRecord { protected static string $resource = SupplierResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\EditAction::make()]; } }
