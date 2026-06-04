<?php
namespace App\Filament\Resources\MasterData\Pages\Countries;
use App\Filament\Resources\MasterData\CountryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListCountries extends ListRecords {
    protected static string $resource = CountryResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
