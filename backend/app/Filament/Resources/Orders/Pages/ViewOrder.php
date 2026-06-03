<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Mail\OrderInvoice;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Mail;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Print Invoice ──────────────────────────────────────────
            Action::make('printInvoice')
                ->label('Print Invoice')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn(Order $record) => route('invoice.show', $record))
                ->openUrlInNewTab(),

            // ── Email Invoice ──────────────────────────────────────────
            Action::make('emailInvoice')
                ->label('Email Invoice')
                ->icon('heroicon-o-envelope')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Send Invoice by Email')
                ->modalDescription(fn(Order $record) => "Send the invoice for {$record->order_number} to {$record->email}?")
                ->modalSubmitActionLabel('Send Now')
                ->action(function (Order $record) {
                    if (!$record->email) {
                        Notification::make()
                            ->title('No email address on this order.')
                            ->danger()
                            ->send();
                        return;
                    }
                    try {
                        $record->loadMissing('items');
                        Mail::to($record->email)->send(new OrderInvoice($record));
                        Notification::make()
                            ->title('Invoice sent!')
                            ->body("Invoice emailed to {$record->email}")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to send email')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // ── WhatsApp Share ─────────────────────────────────────────
            Action::make('whatsappShare')
                ->label('WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(function (Order $record) {
                    $record->loadMissing('items');

                    $lines = $record->items->map(fn($item) =>
                        "  • {$item->product_name}" .
                        ($item->color_name ? " ({$item->color_name})" : '') .
                        " × {$item->quantity} — OMR " . number_format($item->total_price_omr, 3)
                    )->join("\n");

                    $invoiceUrl = route('invoice.show', $record);

                    $message = "Hello {$record->first_name} 👋\n\n" .
                        "Here is your invoice from *Artisan Leather*:\n\n" .
                        "*Order:* {$record->order_number}\n" .
                        "*Date:* " . $record->created_at->format('d M Y') . "\n\n" .
                        "*Items:*\n" . $lines . "\n\n" .
                        "*Shipping:* FREE\n" .
                        "*TOTAL: OMR " . number_format($record->total_omr, 3) . "*\n\n" .
                        "🔗 View your invoice: {$invoiceUrl}\n\n" .
                        "Thank you for choosing Artisan Leather 🙏\n" .
                        "artisanleatherom.com";

                    $phone = preg_replace('/\D/', '', $record->phone);
                    if (!str_starts_with($phone, '968')) {
                        $phone = '968' . ltrim($phone, '0');
                    }

                    return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
                })
                ->openUrlInNewTab(),

            EditAction::make()->label('Update Status'),
        ];
    }
}
