<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsFeedSourceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsFeedSource extends CreateRecord
{
    protected static string $resource = NewsFeedSourceResource::class;
}
