<?php
namespace App\Filament\Resources\MasterData\Pages\Cities;
use App\Filament\Resources\MasterData\CityResource;
use Filament\Resources\Pages\CreateRecord;
class CreateCity extends CreateRecord {
    protected static string $resource = CityResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
