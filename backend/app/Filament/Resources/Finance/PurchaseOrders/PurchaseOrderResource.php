<?php
namespace App\Filament\Resources\Finance\PurchaseOrders;

use App\Filament\Resources\Finance\PurchaseOrders\Pages;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-shopping-cart'; }
    public static function getNavigationGroup(): string { return 'Finance'; }
    public static function getNavigationSort(): int     { return 2; }
    public static function getNavigationBadge(): ?string
    {
        $count = PurchaseOrder::whereIn('status',['ordered','partial'])->count();
        return $count > 0 ? (string)$count : null;
    }
    public static function getNavigationBadgeColor(): string { return 'warning'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make()->tabs([

                // ── Tab 1: Order Info ─────────────────────────────────────────
                Tab::make('Order Info')->icon('heroicon-o-document-text')->schema([

                    Section::make('Supplier & Dates')
                        ->description('Who are you buying from, and when?')
                        ->schema([
                            Select::make('supplier_id')
                                ->label('Supplier')
                                ->options(Supplier::where('is_active',true)->pluck('name','id'))
                                ->required()->searchable()->preload()
                                ->placeholder('Search supplier...')
                                ->columnSpanFull(),

                            DatePicker::make('order_date')
                                ->label('Order Date')
                                ->required()->default(now())
                                ->columnSpan(['default'=>1,'md'=>1]),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'draft'     => '📝 Draft',
                                    'ordered'   => '📦 Ordered',
                                    'partial'   => '🔄 Partially Received',
                                    'received'  => '✅ Fully Received',
                                    'cancelled' => '❌ Cancelled',
                                ])->default('draft')->required()
                                ->columnSpan(['default'=>1,'md'=>1]),
                        ])->columns(['default'=>1,'md'=>2]),

                    Section::make('Additional Details')
                        ->description('Optional delivery dates and currency')
                        ->schema([
                            DatePicker::make('expected_delivery')->label('Expected Delivery'),
                            DatePicker::make('actual_delivery')->label('Actual Delivery'),
                            Select::make('currency')
                                ->options(['OMR'=>'OMR','AED'=>'AED','SAR'=>'SAR','USD'=>'USD','EUR'=>'EUR','GBP'=>'GBP'])
                                ->default('OMR'),
                            TextInput::make('exchange_rate')->label('Rate to OMR')
                                ->numeric()->step(0.000001)->default(1),
                        ])->columns(['default'=>1,'sm'=>2])
                        ->collapsible()->collapsed(),

                ]),

                // ── Tab 2: Items ──────────────────────────────────────────────
                Tab::make('Items')->icon('heroicon-o-list-bullet')->schema([

                    Section::make('What are you buying?')
                        ->description('Add each product or item. Line total is calculated automatically.')
                        ->schema([
                            Repeater::make('items')
                                ->relationship()
                                ->label('')
                                ->schema([
                                    // Item description — full width always
                                    TextInput::make('description')
                                        ->label('Item / Product Name')
                                        ->required()
                                        ->placeholder('e.g. Full Grain Leather Wallets x 20 pcs')
                                        ->columnSpanFull(),

                                    // Numbers — 2 per row on mobile, 4 on desktop
                                    TextInput::make('quantity')
                                        ->label('Qty')
                                        ->numeric()->default(1)->required()
                                        ->live()
                                        ->afterStateUpdated(fn($state,$get,$set) =>
                                            $set('total_cost_omr', round(($state??0)*($get('unit_cost_omr')??0),3)))
                                        ->columnSpan(['default'=>1,'md'=>1]),

                                    Select::make('unit')
                                        ->label('Unit')
                                        ->options(['pcs'=>'Pieces','sq_ft'=>'Sq Ft','meters'=>'Meters','kg'=>'Kg','pairs'=>'Pairs','sets'=>'Sets','boxes'=>'Boxes'])
                                        ->default('pcs')
                                        ->columnSpan(['default'=>1,'md'=>1]),

                                    TextInput::make('unit_cost_omr')
                                        ->label('Unit Cost (OMR)')
                                        ->numeric()->prefix('OMR')->step(0.001)->required()
                                        ->live()
                                        ->afterStateUpdated(fn($state,$get,$set) =>
                                            $set('total_cost_omr', round(($state??0)*($get('quantity')??1),3)))
                                        ->columnSpan(['default'=>1,'md'=>1]),

                                    TextInput::make('total_cost_omr')
                                        ->label('Line Total (OMR)')
                                        ->numeric()->prefix('OMR')->step(0.001)
                                        ->disabled()->dehydrated()
                                        ->extraInputAttributes(['class'=>'font-bold text-amber-600 dark:text-amber-400'])
                                        ->columnSpan(['default'=>1,'md'=>1]),

                                    // Optional: product link + SKU (collapsible per item won't work, use hidden by default)
                                    Select::make('product_id')
                                        ->label('Link to catalogue product (optional)')
                                        ->options(Product::pluck('name','id'))
                                        ->searchable()->nullable()
                                        ->live()
                                        ->afterStateUpdated(fn($state,$set) =>
                                            $set('description', Product::find($state)?->name ?? ''))
                                        ->columnSpan(['default'=>'full','md'=>2]),

                                    TextInput::make('sku')
                                        ->label('SKU / Code (optional)')
                                        ->placeholder('Internal reference')
                                        ->columnSpan(['default'=>'full','md'=>2]),
                                ])
                                ->columns(['default'=>2,'md'=>4])
                                ->reorderable()
                                ->reorderableWithDragAndDrop()
                                ->addActionLabel('＋ Add Item')
                                ->itemLabel(fn(array $state): ?string =>
                                    ($state['description'] ?? null)
                                        ? ($state['description'] . ' — OMR ' . number_format((float)($state['total_cost_omr']??0),3))
                                        : null
                                )
                                ->cloneable(),
                        ]),

                ]),

                // ── Tab 3: Payment ────────────────────────────────────────────
                Tab::make('Payment')->icon('heroicon-o-banknotes')->schema([

                    Section::make('Extra Costs')
                        ->description('Add shipping, customs or other charges on top of item costs.')
                        ->schema([
                            TextInput::make('shipping_cost_omr')->label('Shipping & Freight (OMR)')
                                ->numeric()->prefix('OMR')->step(0.001)->default(0),
                            TextInput::make('customs_duty_omr')->label('Customs Duty (OMR)')
                                ->numeric()->prefix('OMR')->step(0.001)->default(0),
                            TextInput::make('other_costs_omr')->label('Other Charges (OMR)')
                                ->numeric()->prefix('OMR')->step(0.001)->default(0),
                        ])->columns(['default'=>1,'md'=>3]),

                    Section::make('Order Total')
                        ->schema([
                            TextInput::make('subtotal_omr')->label('Items Subtotal (OMR)')
                                ->numeric()->prefix('OMR')->step(0.001)->default(0)
                                ->helperText('Sum of all line totals'),
                            TextInput::make('total_omr')->label('Grand Total (OMR)')
                                ->numeric()->prefix('OMR')->step(0.001)->default(0)
                                ->extraInputAttributes(['class'=>'font-bold text-lg']),
                        ])->columns(['default'=>1,'md'=>2]),

                    Section::make('Payment Status')
                        ->schema([
                            Select::make('payment_status')
                                ->label('Payment Status')
                                ->options([
                                    'unpaid'  => '⏳ Not yet paid',
                                    'partial' => '🔄 Partially paid',
                                    'paid'    => '✅ Fully paid',
                                ])->default('unpaid')->required()
                                ->live()
                                ->columnSpanFull(),

                            TextInput::make('paid_amount_omr')->label('Amount Paid (OMR)')
                                ->numeric()->prefix('OMR')->step(0.001)->default(0)
                                ->visible(fn($get) => in_array($get('payment_status'), ['partial','paid'])),

                            DatePicker::make('payment_date')->label('Payment Date')
                                ->visible(fn($get) => in_array($get('payment_status'), ['partial','paid'])),

                            Textarea::make('notes')->label('Internal Notes')
                                ->rows(3)->placeholder('Any notes for this purchase...')
                                ->columnSpanFull(),
                        ])->columns(['default'=>1,'md'=>2]),

                ]),

            ])->columnSpanFull()->persistTabInQueryString(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make()->schema([
                \Filament\Schemas\Components\Grid::make(['default'=>2,'md'=>4])->schema([
                    \Filament\Infolists\Components\TextEntry::make('reference_number')->badge()->color('warning'),
                    \Filament\Infolists\Components\TextEntry::make('supplier.name')->label('Supplier')->weight('bold'),
                    \Filament\Infolists\Components\TextEntry::make('status')->badge()->color(fn($s)=>match($s){
                        'draft'=>'gray','ordered'=>'warning','partial'=>'info','received'=>'success','cancelled'=>'danger',default=>'gray'
                    }),
                    \Filament\Infolists\Components\TextEntry::make('payment_status')->label('Payment')->badge()->color(fn($s)=>match($s){
                        'unpaid'=>'danger','partial'=>'warning','paid'=>'success',default=>'gray'
                    }),
                ]),
            ])->compact(),

            Section::make('Items')->schema([
                \Filament\Infolists\Components\RepeatableEntry::make('items')->schema([
                    \Filament\Infolists\Components\TextEntry::make('description'),
                    \Filament\Infolists\Components\TextEntry::make('quantity')->suffix(fn($record)=>' '.$record->unit),
                    \Filament\Infolists\Components\TextEntry::make('unit_cost_omr')->prefix('OMR '),
                    \Filament\Infolists\Components\TextEntry::make('total_cost_omr')->prefix('OMR ')->weight('bold'),
                ])->columns(['default'=>1,'md'=>4]),
            ]),

            Section::make('Financial Summary')->schema([
                \Filament\Infolists\Components\TextEntry::make('subtotal_omr')->prefix('OMR '),
                \Filament\Infolists\Components\TextEntry::make('shipping_cost_omr')->label('Shipping')->prefix('OMR '),
                \Filament\Infolists\Components\TextEntry::make('customs_duty_omr')->label('Customs')->prefix('OMR '),
                \Filament\Infolists\Components\TextEntry::make('total_omr')->label('TOTAL')->prefix('OMR ')->weight('bold')->color('warning'),
                \Filament\Infolists\Components\TextEntry::make('paid_amount_omr')->label('Paid')->prefix('OMR ')->color('success'),
                \Filament\Infolists\Components\TextEntry::make('balance_due')->label('Balance Due')
                    ->getStateUsing(fn(PurchaseOrder $r)=>'OMR '.number_format($r->balance_due,3))
                    ->color(fn(PurchaseOrder $r)=>$r->balance_due>0?'danger':'success'),
            ])->columns(['default'=>1,'sm'=>2,'md'=>3]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')->weight('bold')->searchable()->copyable(),
                TextColumn::make('supplier.name')->searchable()->sortable(),
                TextColumn::make('order_date')->date('d M Y')->sortable(),
                TextColumn::make('status')->badge()->color(fn($s)=>match($s){
                    'draft'=>'gray','ordered'=>'warning','partial'=>'info','received'=>'success','cancelled'=>'danger',default=>'gray'
                }),
                TextColumn::make('total_omr')->label('Total (OMR)')->prefix('OMR ')->sortable()->weight('bold'),
                TextColumn::make('payment_status')->label('Payment')->badge()->color(fn($s)=>match($s){
                    'unpaid'=>'danger','partial'=>'warning','paid'=>'success',default=>'gray'
                }),
                TextColumn::make('expected_delivery')->label('Expected')->date('d M Y'),
            ])
            ->defaultSort('order_date','desc')
            ->filters([
                SelectFilter::make('status')->options(['draft'=>'Draft','ordered'=>'Ordered','partial'=>'Partial','received'=>'Received','cancelled'=>'Cancelled']),
                SelectFilter::make('payment_status')->options(['unpaid'=>'Unpaid','partial'=>'Partial','paid'=>'Paid']),
                SelectFilter::make('supplier_id')->label('Supplier')->options(Supplier::pluck('name','id')),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view'   => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit'   => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
