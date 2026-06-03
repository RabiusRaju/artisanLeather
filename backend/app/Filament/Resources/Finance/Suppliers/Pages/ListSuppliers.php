<?php
namespace App\Filament\Resources\Finance\Suppliers\Pages;
use App\Filament\Resources\Finance\Suppliers\SupplierResource;
use Filament\Resources\Pages\ListRecords;
class ListSuppliers extends ListRecords { protected static string $resource = SupplierResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()->label("New Supplier")]; } }
