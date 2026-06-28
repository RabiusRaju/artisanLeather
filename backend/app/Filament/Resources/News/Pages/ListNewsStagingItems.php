<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsStagingResource;
use Filament\Resources\Pages\ListRecords;

class ListNewsStagingItems extends ListRecords
{
    protected static string $resource = NewsStagingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
