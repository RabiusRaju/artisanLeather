<?php
namespace App\Filament\Resources\MasterData\Pages\Governorates;
use App\Filament\Resources\MasterData\GovernorateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditGovernorate extends EditRecord {
    protected static string $resource = GovernorateResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("index"); }
}
