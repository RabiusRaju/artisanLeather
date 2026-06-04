<?php
namespace App\Filament\Resources\HR\Departments\Pages;
use App\Filament\Resources\HR\Departments\DepartmentResource;
use Filament\Resources\Pages\EditRecord;
class EditDepartment extends EditRecord {
    protected static string $resource = DepartmentResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("index"); }
    protected function getHeaderActions(): array { return [\Filament\Actions\DeleteAction::make()]; }
}
