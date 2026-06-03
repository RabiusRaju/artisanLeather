<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-clipboard-document-list'; }
    public static function getNavigationGroup(): string { return 'Sales'; }
    public static function getNavigationSort(): int { return 1; }
    public static function getNavigationBadge(): ?string
    {
        $count = Order::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }
    public static function getNavigationBadgeColor(): string { return 'warning'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('status')
                ->options([
                    'pending'    => 'Pending',
                    'confirmed'  => 'Confirmed',
                    'processing' => 'Processing',
                    'shipped'    => 'Shipped',
                    'delivered'  => 'Delivered',
                    'cancelled'  => 'Cancelled',
                ])
                ->required(),
            Textarea::make('admin_notes')->rows(3)->label('Internal Notes')->columnSpanFull(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([

            // ── Order header ──────────────────────────────────────────
            Section::make()->schema([
                Grid::make(4)->schema([
                    TextEntry::make('order_number')
                        ->label('Order Number')
                        ->badge()->color('warning')->size('lg'),
                    TextEntry::make('status')
                        ->badge()->size('lg')
                        ->color(fn($state) => match($state) {
                            'pending'    => 'warning',
                            'confirmed'  => 'info',
                            'processing' => 'primary',
                            'shipped'    => 'success',
                            'delivered'  => 'success',
                            'cancelled'  => 'danger',
                            default      => 'gray',
                        }),
                    TextEntry::make('payment_method')
                        ->label('Payment Method')
                        ->badge()
                        ->color(fn($state) => match($state) {
                            'cod'      => 'warning',
                            'bank'     => 'info',
                            'whatsapp' => 'success',
                            default    => 'gray',
                        })
                        ->formatStateUsing(fn($state) => match($state) {
                            'cod'      => '💵 Cash on Delivery',
                            'bank'     => '🏦 Bank Transfer',
                            'whatsapp' => '📱 WhatsApp Order',
                            default    => $state,
                        }),
                    TextEntry::make('created_at')
                        ->label('Order Date')
                        ->dateTime('d M Y · H:i'),
                ]),
            ])->compact(),

            // ── Customer details ──────────────────────────────────────
            Section::make('Customer Details')
                ->icon('heroicon-o-user')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('full_name')
                            ->label('Name')
                            ->getStateUsing(fn(Order $r) => $r->first_name . ' ' . $r->last_name)
                            ->weight('bold'),
                        TextEntry::make('email'),
                        TextEntry::make('phone'),
                        TextEntry::make('governorate'),
                        TextEntry::make('city'),
                        TextEntry::make('address')->columnSpan(1),
                    ]),
                    TextEntry::make('notes')
                        ->label('Customer Notes')
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),

            // ── Items ordered ─────────────────────────────────────────
            Section::make('Items Ordered')
                ->icon('heroicon-o-shopping-bag')
                ->schema([
                    // Custom HTML table for rich item display
                    TextEntry::make('items_table')
                        ->label('')
                        ->getStateUsing(function (Order $record) {
                            $record->loadMissing('items.product.images');
                            $rows = '';
                            foreach ($record->items as $item) {
                                $imgUrl = $item->product?->images?->first()?->url;
                                $imgHtml = $imgUrl
                                    ? '<img src="' . e($imgUrl) . '" style="width:52px;height:52px;object-fit:cover;border-radius:4px;border:1px solid rgba(0,0,0,0.1);">'
                                    : '<div style="width:52px;height:52px;background:#f3f4f6;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:18px;">🧳</div>';

                                $rows .= '<tr style="border-bottom:1px solid #f3f4f6;">
                                    <td style="padding:12px 8px;vertical-align:middle;">' . $imgHtml . '</td>
                                    <td style="padding:12px 8px;vertical-align:middle;">
                                        <div style="font-weight:600;color:#111827;">' . e($item->product_name) . '</div>
                                        <div style="font-size:0.8rem;color:#6b7280;">' . e($item->product_name_ar ?? '') . '</div>
                                    </td>
                                    <td style="padding:12px 8px;vertical-align:middle;">
                                        <span style="display:inline-flex;align-items:center;gap:6px;">
                                            ' . ($item->color_hex ? '<span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:' . e($item->color_hex) . ';border:1px solid rgba(0,0,0,0.2);"></span>' : '') . '
                                            ' . e($item->color_name ?? '—') . '
                                        </span>
                                    </td>
                                    <td style="padding:12px 8px;text-align:center;vertical-align:middle;">' . $item->quantity . '</td>
                                    <td style="padding:12px 8px;text-align:right;vertical-align:middle;color:#6b7280;">OMR ' . number_format($item->unit_price_omr, 3) . '</td>
                                    <td style="padding:12px 8px;text-align:right;vertical-align:middle;font-weight:600;">OMR ' . number_format($item->total_price_omr, 3) . '</td>
                                </tr>';
                            }

                            return new HtmlString('
                                <div style="overflow:auto;">
                                <table style="width:100%;border-collapse:collapse;font-size:0.9rem;">
                                    <thead>
                                        <tr style="border-bottom:2px solid #e5e7eb;color:#6b7280;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">
                                            <th style="padding:8px;text-align:left;width:60px;">Photo</th>
                                            <th style="padding:8px;text-align:left;">Product</th>
                                            <th style="padding:8px;text-align:left;">Colour</th>
                                            <th style="padding:8px;text-align:center;">Qty</th>
                                            <th style="padding:8px;text-align:right;">Unit Price</th>
                                            <th style="padding:8px;text-align:right;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>' . $rows . '</tbody>
                                </table>
                                </div>
                            ');
                        })
                        ->columnSpanFull(),
                ]),

            // ── Financial summary ─────────────────────────────────────
            Section::make('Financial Summary')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    TextEntry::make('financials')
                        ->label('')
                        ->getStateUsing(function (Order $record) {
                            $subtotal = number_format($record->subtotal_omr, 3);
                            $total    = number_format($record->total_omr, 3);
                            return new HtmlString('
                                <div style="display:flex;flex-direction:column;gap:8px;max-width:320px;margin-left:auto;">
                                    <div style="display:flex;justify-content:space-between;color:#6b7280;">
                                        <span>Subtotal</span>
                                        <span>OMR ' . $subtotal . '</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;color:#6b7280;">
                                        <span>Shipping</span>
                                        <span style="color:#059669;font-weight:500;">FREE</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;border-top:2px solid #e5e7eb;padding-top:8px;font-weight:700;font-size:1.1rem;">
                                        <span>Total</span>
                                        <span style="color:#d97706;">OMR ' . $total . '</span>
                                    </div>
                                    <div style="font-size:0.75rem;color:#9ca3af;text-align:right;">
                                        Currency: ' . e($record->currency_code) . ' (rate: ' . $record->currency_rate . ')
                                    </div>
                                </div>
                            ');
                        })
                        ->columnSpanFull(),
                ]),

            // ── Admin notes ───────────────────────────────────────────
            Section::make('Internal Notes')
                ->icon('heroicon-o-lock-closed')
                ->schema([
                    TextEntry::make('admin_notes')->label('')->placeholder('No internal notes.')->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(fn(Order $r) => blank($r->admin_notes)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->sortable()->weight('bold'),
                TextColumn::make('first_name')->label('First Name')->searchable(),
                TextColumn::make('last_name')->label('Last Name')->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('governorate'),
                TextColumn::make('payment_method')->badge()
                    ->color(fn($state) => match($state) {
                        'cod' => 'warning', 'bank' => 'info', 'whatsapp' => 'success', default => 'gray',
                    }),
                TextColumn::make('total_omr')->label('Total (OMR)')->sortable(),
                TextColumn::make('status')->badge()
                    ->color(fn($state) => match($state) {
                        'pending' => 'warning', 'confirmed' => 'info', 'processing' => 'primary',
                        'shipped' => 'success', 'delivered' => 'success', 'cancelled' => 'danger', default => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'confirmed' => 'Confirmed', 'processing' => 'Processing',
                    'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled',
                ]),
                SelectFilter::make('payment_method')->options([
                    'cod' => 'Cash on Delivery', 'bank' => 'Bank Transfer', 'whatsapp' => 'WhatsApp',
                ]),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
