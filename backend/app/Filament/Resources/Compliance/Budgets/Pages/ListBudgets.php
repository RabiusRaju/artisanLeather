<?php
namespace App\Filament\Resources\Compliance\Budgets\Pages;
use App\Filament\Resources\Compliance\Budgets\BudgetResource;
use Filament\Resources\Pages\ListRecords;
class ListBudgets extends ListRecords { protected static string $resource = BudgetResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()->label("Set Monthly Budget")]; } }
