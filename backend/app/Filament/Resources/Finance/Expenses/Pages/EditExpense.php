<?php
namespace App\Filament\Resources\Finance\Expenses\Pages;
use App\Filament\Resources\Finance\Expenses\ExpenseResource;
use Filament\Resources\Pages\EditRecord;
class EditExpense extends EditRecord { protected static string $resource = ExpenseResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\DeleteAction::make()]; } }
