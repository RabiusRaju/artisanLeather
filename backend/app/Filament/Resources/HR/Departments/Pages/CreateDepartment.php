<?php
namespace App\Filament\Resources\HR\Departments\Pages;
use App\Filament\Resources\HR\Departments\DepartmentResource;
use Filament\Resources\Pages\CreateRecord;
class CreateDepartment extends CreateRecord {
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
    protected static string $resource = DepartmentResource::class;
}
