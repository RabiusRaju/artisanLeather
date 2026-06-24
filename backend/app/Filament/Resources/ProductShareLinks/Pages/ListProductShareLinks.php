<?php

namespace App\Filament\Resources\ProductShareLinks\Pages;

use App\Filament\Resources\ProductShareLinks\ProductShareLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductShareLinks extends ListRecords
{
    protected static string $resource = ProductShareLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Create Share Link')];
    }
}
