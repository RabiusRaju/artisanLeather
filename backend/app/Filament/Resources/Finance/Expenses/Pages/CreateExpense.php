<?php
namespace App\Filament\Resources\Finance\Expenses\Pages;
use App\Filament\Resources\Finance\Expenses\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;
class CreateExpense extends CreateRecord { protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
    protected static string $resource = ExpenseResource::class; }
