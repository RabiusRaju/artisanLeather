<?php
namespace App\Filament\Resources\Finance\OtherIncome\Pages;
use App\Filament\Resources\Finance\OtherIncome\OtherIncomeResource;
use Filament\Resources\Pages\ListRecords;
class ListOtherIncome extends ListRecords { protected static string $resource = OtherIncomeResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()->label("Log Income")]; } }
