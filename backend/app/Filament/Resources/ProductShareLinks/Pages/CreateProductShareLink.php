<?php

namespace App\Filament\Resources\ProductShareLinks\Pages;

use App\Filament\Resources\ProductShareLinks\ProductShareLinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductShareLink extends CreateRecord
{
    protected static string $resource = ProductShareLinkResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
