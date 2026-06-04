<?php
namespace App\Filament\Resources\Operations\CashFlow\Pages;
use App\Filament\Resources\Operations\CashFlow\CashFlowResource;
use Filament\Resources\Pages\CreateRecord;
class CreateCashFlow extends CreateRecord { protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
    protected static string $resource = CashFlowResource::class; }
