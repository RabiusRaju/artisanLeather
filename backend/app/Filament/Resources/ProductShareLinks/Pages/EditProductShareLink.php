<?php

namespace App\Filament\Resources\ProductShareLinks\Pages;

use App\Filament\Resources\ProductShareLinks\ProductShareLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductShareLink extends EditRecord
{
    protected static string $resource = ProductShareLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
