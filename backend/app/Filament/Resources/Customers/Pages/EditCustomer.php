<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Customer $record */
        $record = $this->getRecord();

        $phone = $record->whatsapp ?: $record->phone;
        $phone = preg_replace('/\D/', '', $phone ?? '');
        if ($phone && !str_starts_with($phone, '968')) {
            $phone = '968' . ltrim($phone, '0');
        }
        $waMsg = 'Hello ' . $record->name . ' 👋, this is Artisan Leather. How can we help you today?';

        return [
            // ── WhatsApp ──────────────────────────────────────────────
            Action::make('whatsapp')
                ->label('WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url('https://wa.me/' . $phone . '?text=' . urlencode($waMsg))
                ->openUrlInNewTab()
                ->visible((bool) $phone),

            // ── Email ─────────────────────────────────────────────────
            Action::make('email')
                ->label('Email Customer')
                ->icon('heroicon-o-envelope')
                ->color('info')
                ->url('mailto:' . $record->email)
                ->visible(!empty($record->email)),

            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string { return $this->getResource()::getUrl("index"); }
}
