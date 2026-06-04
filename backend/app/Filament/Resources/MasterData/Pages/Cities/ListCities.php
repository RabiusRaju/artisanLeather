<?php
namespace App\Filament\Resources\MasterData\Pages\Cities;
use App\Filament\Resources\MasterData\CityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListCities extends ListRecords {
    protected static string $resource = CityResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
