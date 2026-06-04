<?php
namespace App\Filament\Resources\MasterData\Pages\Cities;
use App\Filament\Resources\MasterData\CityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditCity extends EditRecord {
    protected static string $resource = CityResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("index"); }
}
