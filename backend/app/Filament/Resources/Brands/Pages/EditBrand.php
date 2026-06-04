<?php
namespace App\Filament\Resources\Brands\Pages;
use App\Filament\Resources\Brands\BrandResource;
use Filament\Resources\Pages\EditRecord;
class EditBrand extends EditRecord {
    protected static string $resource = BrandResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("edit", ["record" => $this->getRecord()]); }
}
