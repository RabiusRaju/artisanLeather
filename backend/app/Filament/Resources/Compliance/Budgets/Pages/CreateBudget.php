<?php
namespace App\Filament\Resources\Compliance\Budgets\Pages;
use App\Filament\Resources\Compliance\Budgets\BudgetResource;
use Filament\Resources\Pages\CreateRecord;
class CreateBudget extends CreateRecord { protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
    protected static string $resource = BudgetResource::class; }
