<?php
namespace App\Filament\Resources\Operations\CashFlow\Pages;
use App\Filament\Resources\Operations\CashFlow\CashFlowResource;
use Filament\Resources\Pages\ListRecords;
class ListCashFlow extends ListRecords { protected static string $resource = CashFlowResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()->label("Log Cash Entry")]; } }
