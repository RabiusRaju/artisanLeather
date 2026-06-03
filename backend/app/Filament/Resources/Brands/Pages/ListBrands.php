<?php
namespace App\Filament\Resources\Brands\Pages;
use App\Filament\Resources\Brands\BrandResource;
use Filament\Resources\Pages\ListRecords;
class ListBrands extends ListRecords {
    protected static string $resource = BrandResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()->label('New Collection')]; }
}
