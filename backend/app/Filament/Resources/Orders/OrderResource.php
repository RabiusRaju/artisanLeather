<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Governorate;
use App\Models\Product;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use App\Enums\NavigationGroupEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Sales->value; }
    public static function getNavigationSort(): int { return 1; }
    public static function getNavigationBadge(): ?string
    {
        $count = Order::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }
    public static function getNavigationBadgeColor(): string { return 'warning'; }

    public static function form(Schema $schema): Schema
    {
        // Governorates from DB — managed in Settings > Governorates
        $omaniGovernorates = Governorate::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name', 'name')
            ->toArray();

        return $schema->schema([

            Hidden::make('order_number'),
            Hidden::make('total_omr')->default(0),
            Hidden::make('currency_rate')->default(1.0),

            Tabs::make()->tabs([

                // ── TAB 1: Order Info (Customer) ──────────────────────
                Tab::make('Order Info')
                    ->icon('heroicon-o-user')
                    ->schema([

                        Section::make('Customer Details')
                            ->description('Search an existing customer to auto-fill, or enter manually.')
                            ->columns(2)
                            ->schema([

                                Select::make('_customer_id')
                                    ->label('Auto-fill from existing customer')
                                    ->placeholder('Search by name or email…')
                                    ->options(
                                        Customer::orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn($c) => [$c->id => $c->name . ' — ' . $c->email])
                                    )
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $c = Customer::find($state);
                                        if (!$c) return;
                                        [$first, $last] = array_pad(explode(' ', $c->name, 2), 2, '');
                                        $set('first_name',  $first);
                                        $set('last_name',   $last);
                                        $set('email',       $c->email);
                                        $set('phone',       $c->phone);
                                        $set('governorate', $c->governorate);
                                        $set('city',        $c->city);
                                        $set('address',     $c->address);
                                    })
                                    ->columnSpanFull()
                                    ->dehydrated(false),

                                TextInput::make('first_name')
                                    ->label('First Name')
                                    ->required()
                                    ->placeholder('Ahmad'),

                                TextInput::make('last_name')
                                    ->label('Last Name')
                                    ->required()
                                    ->placeholder('Al Rashdi'),

                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required()
                                    ->placeholder('ahmad@example.com'),

                                TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->required()
                                    ->placeholder('+968 9X XXX XXX'),

                                Select::make('governorate')
                                    ->label('Governorate')
                                    ->options($omaniGovernorates)
                                    ->searchable()
                                    ->required(),

                                TextInput::make('city')
                                    ->label('City / Area')
                                    ->required()
                                    ->placeholder('e.g. Al Khuwair'),

                                Textarea::make('address')
                                    ->label('Delivery Address')
                                    ->required()
                                    ->rows(3)
                                    ->placeholder('Building, Street, Wilayat…')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Customer Notes')
                            ->description('Special delivery instructions or customer requests')
                            ->schema([
                                Textarea::make('notes')
                                    ->label('')
                                    ->rows(3)
                                    ->placeholder('e.g. Please call before delivery, leave at reception…'),
                            ])
                            ->collapsible()
                            ->collapsed(),

                    ]),

                // ── TAB 2: Items ──────────────────────────────────────
                Tab::make('Items')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([

                        Section::make('Order Products')
                            ->description('Add products. Select a product and the price fills automatically.')
                            ->schema([

                                Repeater::make('items')
                                    ->relationship()
                                    ->label('')
                                    ->schema([

                                        Select::make('product_id')
                                            ->label('Product')
                                            ->options(function ($get) {
                                                $allItems   = $get('../../items') ?? [];
                                                $usedIds    = collect($allItems)
                                                    ->pluck('product_id')
                                                    ->filter()
                                                    ->values()
                                                    ->toArray();
                                                $current    = $get('product_id');
                                                $excludeIds = collect($usedIds)
                                                    ->reject(fn($id) => $id == $current)
                                                    ->values()
                                                    ->toArray();

                                                return Product::orderBy('name')
                                                    ->whereNotIn('id', $excludeIds)
                                                    ->get()
                                                    ->mapWithKeys(fn($p) => [
                                                        $p->id => $p->name . '  —  OMR ' . number_format((float)$p->price, 3),
                                                    ]);
                                            })
                                            ->searchable()
                                            ->required()
                                            ->placeholder('Search and select a product…')
                                            ->live()
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                $product = Product::find($state);
                                                if (!$product) return;
                                                $qty     = (int)($get('quantity') ?: 1);
                                                $newLine = round((float)$product->price * $qty, 3);
                                                $set('product_name',    $product->name);
                                                $set('product_name_ar', $product->name_ar ?? '');
                                                $set('unit_price_omr',  round((float)$product->price, 3));
                                                $set('color_name', null);
                                                $set('color_hex',  null);
                                                $set('total_price_omr', $newLine);
                                                $subtotal = collect($get('../../items') ?? [])->sum(fn($i) => (float)($i['total_price_omr'] ?? 0));
                                                $set('../../subtotal_omr', round($subtotal, 3));
                                            })
                                            ->columnSpanFull(),

                                        Select::make('color_name')
                                            ->label('Colour / Variant')
                                            ->options(fn($get) =>
                                                Product::find($get('product_id'))
                                                    ?->colors->pluck('name', 'name')->toArray() ?? []
                                            )
                                            ->searchable()
                                            ->placeholder('Select colour…')
                                            ->live()
                                            ->columnSpan(['default' => 'full', 'md' => 2]),

                                        TextInput::make('quantity')
                                            ->label('Quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, $get, $set) {
                                                $newLine = round((float)($get('unit_price_omr') ?: 0) * (int)($state ?: 1), 3);
                                                $set('total_price_omr', $newLine);
                                                $subtotal = collect($get('../../items') ?? [])->sum(fn($i) => (float)($i['total_price_omr'] ?? 0));
                                                $set('../../subtotal_omr', round($subtotal, 3));
                                            })
                                            ->columnSpan(['default' => 1, 'md' => 1]),

                                        TextInput::make('unit_price_omr')
                                            ->label('Unit Price (OMR)')
                                            ->numeric()
                                            ->prefix('OMR')
                                            ->step(0.001)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, $get, $set) {
                                                $newLine = round((float)($state ?: 0) * (int)($get('quantity') ?: 1), 3);
                                                $set('total_price_omr', $newLine);
                                                $subtotal = collect($get('../../items') ?? [])->sum(fn($i) => (float)($i['total_price_omr'] ?? 0));
                                                $set('../../subtotal_omr', round($subtotal, 3));
                                            })
                                            ->columnSpan(['default' => 1, 'md' => 1]),

                                        TextInput::make('total_price_omr')
                                            ->label('Line Total (OMR)')
                                            ->numeric()
                                            ->prefix('OMR')
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'font-weight:700;color:#d97706'])
                                            ->columnSpan(['default' => 1, 'md' => 1]),

                                        Hidden::make('product_name'),
                                        Hidden::make('product_name_ar'),
                                        Hidden::make('color_hex'),
                                    ])
                                    ->columns(['default' => 2, 'md' => 5])
                                    ->addActionLabel('＋  Add Another Product')
                                    ->reorderable()
                                    ->reorderableWithDragAndDrop()
                                    ->collapsible()
                                    ->cloneable()
                                    ->itemLabel(fn($state) =>
                                        ($state['product_name'] ?? 'New item') .
                                        (isset($state['quantity'])        ? '  ×' . $state['quantity']                                          : '') .
                                        (isset($state['total_price_omr']) ? '  =  OMR ' . number_format((float)$state['total_price_omr'], 3) : '')
                                    ),

                            ]),

                    ]),

                // ── TAB 3: Payment ────────────────────────────────────
                Tab::make('Payment')
                    ->icon('heroicon-o-banknotes')
                    ->schema([

                        Section::make('Payment Method')
                            ->description('How is the customer paying?')
                            ->columns(2)
                            ->schema([
                                Select::make('payment_method')
                                    ->label('Payment Method')
                                    ->options([
                                        'cod'      => '💵 Cash on Delivery',
                                        'bank'     => '🏦 Bank Transfer',
                                        'whatsapp' => '📱 WhatsApp Order',
                                    ])
                                    ->default('cod')
                                    ->required(),

                                TextInput::make('currency_code')
                                    ->label('Currency')
                                    ->default('OMR')
                                    ->required(),

                                Select::make('status')
                                    ->label('Order Status')
                                    ->options([
                                        'pending'    => '⏳ Pending',
                                        'confirmed'  => '✅ Confirmed',
                                        'processing' => '🔨 Processing',
                                        'shipped'    => '🚚 Shipped',
                                        'delivered'  => '🎉 Delivered',
                                        'cancelled'  => '❌ Cancelled',
                                    ])
                                    ->default('pending')
                                    ->required(),

                                TextInput::make('subtotal_omr')
                                    ->label('Order Total (OMR)')
                                    ->numeric()
                                    ->prefix('OMR')
                                    ->readOnly()
                                    ->default(0)
                                    ->helperText('Updates live as you add items')
                                    ->extraInputAttributes(['style' => 'font-size:1.2rem;font-weight:700;color:#d97706']),
                            ]),

                        Section::make('Internal Notes')
                            ->description('Notes visible only to admin staff')
                            ->schema([
                                Textarea::make('admin_notes')
                                    ->label('')
                                    ->placeholder('e.g. Called customer — will pay via bank transfer Thursday')
                                    ->rows(4),
                            ])
                            ->collapsible()
                            ->collapsed(),

                    ]),

            ])->columnSpanFull(),

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
                            $calculated = $record->items->sum('total_price_omr');
                            $subtotal   = $calculated ?: $record->subtotal_omr;
                            $total      = $calculated ?: $record->total_omr;
                            return new HtmlString('
                                <div style="display:flex;flex-direction:column;gap:8px;max-width:320px;margin-left:auto;">
                                    <div style="display:flex;justify-content:space-between;color:#6b7280;">
                                        <span>Subtotal</span>
                                        <span>OMR ' . number_format($subtotal, 3) . '</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;color:#6b7280;">
                                        <span>Shipping</span>
                                        <span style="color:#059669;font-weight:500;">FREE</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;border-top:2px solid #e5e7eb;padding-top:8px;font-weight:700;font-size:1.1rem;">
                                        <span>Total</span>
                                        <span style="color:#d97706;">OMR ' . number_format($total, 3) . '</span>
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
            'create' => Pages\CreateOrder::route('/create'),
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
