<?php

namespace App\Filament\Resources\CustomOrders\Pages;

use App\Filament\Resources\CustomOrders\CustomOrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomOrder extends ViewRecord
{
    protected static string $resource = CustomOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
