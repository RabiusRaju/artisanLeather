<?php

namespace App\Filament\Resources\CustomOrders\Pages;

use App\Filament\Resources\CustomOrders\CustomOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomOrder extends CreateRecord
{
    protected static string $resource = CustomOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
