<?php
namespace App\Filament\Resources\Finance\OtherIncome\Pages;
use App\Filament\Resources\Finance\OtherIncome\OtherIncomeResource;
use Filament\Resources\Pages\CreateRecord;
class CreateOtherIncome extends CreateRecord { protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
    protected static string $resource = OtherIncomeResource::class; }
