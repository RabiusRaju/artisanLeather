<?php
namespace App\Filament\Resources\HR\Employees\Pages;
use App\Filament\Resources\HR\Employees\EmployeeResource;
use Filament\Resources\Pages\ViewRecord;
class ViewEmployee extends ViewRecord {
    protected static string $resource = EmployeeResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\EditAction::make()]; }
}
