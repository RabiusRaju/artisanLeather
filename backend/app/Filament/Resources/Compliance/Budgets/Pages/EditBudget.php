<?php
namespace App\Filament\Resources\Compliance\Budgets\Pages;
use App\Filament\Resources\Compliance\Budgets\BudgetResource;
use Filament\Resources\Pages\EditRecord;
class EditBudget extends EditRecord { protected static string $resource = BudgetResource::class;     protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
}