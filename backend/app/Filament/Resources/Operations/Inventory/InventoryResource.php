<?php
namespace App\Filament\Resources\Operations\Inventory;

use App\Filament\Resources\Operations\Inventory\Pages;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Enums\NavigationGroupEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class InventoryResource extends Resource
{
    protected static ?string $model = ProductStock::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-archive-box'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Operations->value; }
    public static function getNavigationSort(): int     { return 1; }
    public static function getNavigationLabel(): string { return 'Inventory'; }
    public static function getNavigationBadge(): ?string
    {
        $low = ProductStock::where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'minimum_alert')->count();
        $out = ProductStock::where('quantity', '<=', 0)->count();
        $total = $low + $out;
        return $total > 0 ? (string)$total : null;
    }
    public static function getNavigationBadgeColor(): string { return 'danger'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Stock Settings')->schema([
                Select::make('product_id')->label('Product')
                    ->options(function () {
                        $existing = ProductStock::pluck('product_id')->toArray();
                        return Product::where('is_active', true)
                            ->whereNotIn('id', $existing)
                            ->pluck('name', 'id');
                    })
                    ->required()->searchable()->columnSpanFull()
                    ->helperText('Only products without an existing stock record are shown. To update stock, use the + Stock In / - Stock Out buttons on the inventory list.'),
                TextInput::make('quantity')->label('Current Stock Quantity')
                    ->numeric()->required()->default(0)->columnSpan(1),
                TextInput::make('minimum_alert')->label('Low Stock Alert (trigger below)')
                    ->numeric()->default(3)->helperText('You\'ll be alerted when stock drops below this number')->columnSpan(1),
                TextInput::make('reorder_qty')->label('Suggested Reorder Quantity')
                    ->numeric()->default(10)->columnSpan(1),
                TextInput::make('location')->label('Storage Location')
                    ->placeholder('e.g. Shelf A, Back Room, Display Case')->columnSpan(1),
                Textarea::make('notes')->rows(2)->columnSpanFull(),
            ])->columns(['default'=>1,'md'=>2]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                ProductStock::with(['product.category', 'product.images'])
                    ->join('products', 'product_stock.product_id', '=', 'products.id')
                    ->select('product_stock.*')
                    ->orderByRaw('CASE WHEN product_stock.quantity <= 0 THEN 0 WHEN product_stock.quantity <= product_stock.minimum_alert THEN 1 ELSE 2 END')
                    ->orderBy('products.name')
            )
            ->columns([
                // Product info
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn(ProductStock $r) => $r->product?->category?->name),

                // Stock level with visual indicator
                TextColumn::make('quantity')
                    ->label('In Stock')
                    ->alignCenter()
                    ->formatStateUsing(function (ProductStock $record) {
                        $qty = $record->quantity;
                        $min = $record->minimum_alert;
                        if ($qty <= 0) {
                            return new HtmlString('<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">⚠️ OUT OF STOCK</span>');
                        }
                        if ($qty <= $min) {
                            return new HtmlString('<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400">🟡 '.$qty.' (LOW)</span>');
                        }
                        return new HtmlString('<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">✅ '.$qty.'</span>');
                    }),

                TextColumn::make('minimum_alert')->label('Alert At')->alignCenter()->suffix(' units'),
                TextColumn::make('reorder_qty')->label('Reorder')->alignCenter()->suffix(' units'),
                TextColumn::make('location')->placeholder('—'),
                TextColumn::make('updated_at')->label('Last Updated')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stock Status')
                    ->options([
                        'out'  => '⚠️ Out of Stock',
                        'low'  => '🟡 Low Stock',
                        'ok'   => '✅ In Stock',
                    ])
                    ->query(fn($query, $data) => match($data['value'] ?? null) {
                        'out' => $query->where('quantity', '<=', 0),
                        'low' => $query->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'minimum_alert'),
                        'ok'  => $query->whereColumn('quantity', '>', 'minimum_alert'),
                        default => $query,
                    }),
            ])
            ->recordActions([
                // Quick Stock In button
                TableAction::make('stockIn')
                    ->label('+ Stock In')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        TextInput::make('qty')->label('Quantity to add')->numeric()->required()->minValue(1),
                        TextInput::make('reference')->label('Reference (PO No., etc.)')->placeholder('Optional'),
                        Textarea::make('reason')->label('Reason')->rows(2)->default('Stock received'),
                    ])
                    ->action(function (ProductStock $record, array $data) {
                        $before = $record->quantity;
                        $record->increment('quantity', $data['qty']);
                        StockMovement::create([
                            'product_id'     => $record->product_id,
                            'type'           => 'stock_in',
                            'quantity'       => $data['qty'],
                            'quantity_after' => $record->fresh()->quantity,
                            'reference'      => $data['reference'] ?? null,
                            'reason'         => $data['reason'] ?? 'Stock received',
                            'user_id'        => auth()->id(),
                        ]);
                        Notification::make()->title("✅ Stock updated — {$record->product->name}: {$before} → {$record->fresh()->quantity} units")->success()->send();
                    }),

                // Quick Stock Out button
                TableAction::make('stockOut')
                    ->label('- Stock Out')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->form([
                        TextInput::make('qty')->label('Quantity to remove')->numeric()->required()->minValue(1),
                        TextInput::make('reference')->label('Reference (Order No., etc.)'),
                        Select::make('type')->label('Reason')->options([
                            'stock_out'  => 'Sold / Dispatched',
                            'damage'     => 'Damaged / Write-off',
                            'return'     => 'Return to Supplier',
                            'adjustment' => 'Manual Adjustment',
                        ])->default('stock_out')->required(),
                    ])
                    ->action(function (ProductStock $record, array $data) {
                        $qty = min($data['qty'], $record->quantity);
                        $before = $record->quantity;
                        $record->decrement('quantity', $qty);
                        StockMovement::create([
                            'product_id'     => $record->product_id,
                            'type'           => $data['type'],
                            'quantity'       => -$qty,
                            'quantity_after' => $record->fresh()->quantity,
                            'reference'      => $data['reference'] ?? null,
                            'reason'         => $data['type'],
                            'user_id'        => auth()->id(),
                        ]);
                        if ($record->fresh()->quantity <= $record->minimum_alert) {
                            Notification::make()->title("⚠️ Low Stock Warning — {$record->product->name} has only {$record->fresh()->quantity} units left!")->warning()->send();
                        } else {
                            Notification::make()->title("Stock updated — {$record->product->name}: {$before} → {$record->fresh()->quantity} units")->success()->send();
                        }
                    }),

                EditAction::make()->label('Settings'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventory::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit'   => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
