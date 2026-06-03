<?php
namespace App\Filament\Resources\Finance\Expenses\Pages;
use App\Filament\Resources\Finance\Expenses\ExpenseResource;
use Filament\Resources\Pages\ListRecords;
class ListExpenses extends ListRecords { protected static string $resource = ExpenseResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()->label("Log Expense")]; } }
