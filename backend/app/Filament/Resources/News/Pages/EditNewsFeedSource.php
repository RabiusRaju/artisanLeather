<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsFeedSourceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsFeedSource extends EditRecord
{
    protected static string $resource = NewsFeedSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
