<?php
namespace App\Filament\Resources\HR\Departments\Pages;
use App\Filament\Resources\HR\Departments\DepartmentResource;
use Filament\Resources\Pages\ListRecords;
class ListDepartments extends ListRecords {
    protected static string $resource = DepartmentResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()]; }
}
