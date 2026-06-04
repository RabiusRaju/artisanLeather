<?php
namespace App\Filament\Resources\Operations\CashFlow\Pages;
use App\Filament\Resources\Operations\CashFlow\CashFlowResource;
use Filament\Resources\Pages\EditRecord;
class EditCashFlow extends EditRecord { protected static string $resource = CashFlowResource::class;     protected function getRedirectUrl(): string { return $this->getResource()::getUrl("index"); }
}