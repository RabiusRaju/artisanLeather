<?php
namespace App\Filament\Resources\MasterData\Pages\Governorates;
use App\Filament\Resources\MasterData\GovernorateResource;
use Filament\Resources\Pages\CreateRecord;
class CreateGovernorate extends CreateRecord {
    protected static string $resource = GovernorateResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
