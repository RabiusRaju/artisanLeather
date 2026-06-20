<?php

namespace App\Filament\Resources\LegalPages\Pages;

use App\Filament\Resources\LegalPages\LegalPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalPage extends CreateRecord
{
    protected static string $resource = LegalPageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
