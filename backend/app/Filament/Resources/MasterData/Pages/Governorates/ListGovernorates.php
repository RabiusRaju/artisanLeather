<?php
namespace App\Filament\Resources\MasterData\Pages\Governorates;
use App\Filament\Resources\MasterData\GovernorateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListGovernorates extends ListRecords {
    protected static string $resource = GovernorateResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
