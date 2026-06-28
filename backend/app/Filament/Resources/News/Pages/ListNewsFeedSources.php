<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsFeedSourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNewsFeedSources extends ListRecords
{
    protected static string $resource = NewsFeedSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
