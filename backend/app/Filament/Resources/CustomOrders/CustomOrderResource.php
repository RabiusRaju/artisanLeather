<?php
namespace App\Filament\Resources\CustomOrders;

use App\Filament\Resources\CustomOrders\Pages;
use App\Models\Customer;
use App\Models\CustomOrder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use App\Enums\NavigationGroupEnum;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomOrderResource extends Resource
{
    protected static ?string $model = CustomOrder::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-scissors'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Sales->value; }
    public static function getNavigationSort(): int { return 3; }
    public static function getNavigationBadge(): ?string
    {
        return (string) CustomOrder::whereIn('status', ['inquiry','confirmed','in_production'])->count() ?: null;
    }
    public static function getNavigationBadgeColor(): string { return 'info'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make()->tabs([

                // ── Customer & Order Info ─────────────────────────────────
                Tab::make('Order Details')->icon('heroicon-o-document-text')->schema([
                    Section::make('Customer')->schema([
                        Select::make('customer_id')->label('Existing Customer')
                            ->options(Customer::where('status', '!=', 'inactive')->pluck('name','id'))
                            ->searchable()->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $c = Customer::find($state);
                                    $set('customer_name', $c?->name);
                                    $set('customer_phone', $c?->phone);
                                }
                            })
                            ->columnSpanFull(),
                        TextInput::make('customer_name')->label('Customer Name')->required()->columnSpan(2),
                        TextInput::make('customer_phone')->label('Phone / WhatsApp')->required()->columnSpan(1),
                    ])->columns(3),

                    Section::make('Order')->schema([
                        TextInput::make('reference_number')->label('Reference #')
                            ->placeholder('Auto-generated on save')->disabled()->columnSpan(1),
                        Select::make('product_type')->label('Product Type')->options([
                            'wallet'    => 'Wallet',
                            'bag'       => 'Bag',
                            'belt'      => 'Belt',
                            'accessory' => 'Accessory',
                            'other'     => 'Other',
                        ])->required()->columnSpan(1),
                        TextInput::make('product_name')->label('Product Name')
                            ->placeholder('e.g. Bifold Wallet, Executive Tote')->columnSpan(1),
                        Select::make('status')->options([
                            'inquiry'        => '📩 Inquiry',
                            'confirmed'      => '✅ Confirmed',
                            'in_production'  => '🔨 In Production',
                            'quality_check'  => '🔍 Quality Check',
                            'ready'          => '📦 Ready for Collection',
                            'delivered'      => '🚀 Delivered',
                            'cancelled'      => '❌ Cancelled',
                        ])->default('inquiry')->required()->columnSpan(1),
                        DatePicker::make('promised_date')->label('Promised Delivery Date')->columnSpan(1),
                        DatePicker::make('delivered_at')->label('Actual Delivery Date')->columnSpan(1),
                        Textarea::make('description')->label('Order Description')->rows(3)->columnSpanFull(),
                    ])->columns(3),
                ]),

                // ── Leather Specifications ────────────────────────────────
                Tab::make('Specifications')->icon('heroicon-o-swatch')->schema([
                    Section::make('Leather & Hardware')->schema([
                        TextInput::make('leather_color')->label('Leather Colour')
                            ->placeholder('e.g. Cognac, Dark Brown, Black')->columnSpan(1),
                        Select::make('leather_type')->label('Leather Type')->options([
                            'Full Grain Vegetable Tanned' => 'Full Grain Vegetable Tanned',
                            'Full Grain Calfskin'         => 'Full Grain Calfskin',
                            'Pebbled Full Grain'          => 'Pebbled Full Grain',
                            'Suede'                       => 'Suede',
                            'Other'                       => 'Other',
                        ])->columnSpan(1),
                        TextInput::make('stitching_color')->label('Stitching Colour')
                            ->placeholder('e.g. Cream, Dark Brown, Gold')->columnSpan(1),
                        Select::make('hardware_color')->label('Hardware Finish')->options([
                            'gold'         => '🟡 Gold',
                            'silver'       => '⚪ Silver',
                            'antique_brass' => '🟤 Antique Brass',
                            'none'         => '— None',
                        ])->default('gold')->columnSpan(1),
                        TextInput::make('size')->label('Size / Dimensions')
                            ->placeholder('e.g. 11cm × 9cm or Standard')->columnSpan(1),
                    ])->columns(3),

                    Section::make('Personalisation')->schema([
                        TextInput::make('monogram')->label('Monogram / Initials')
                            ->placeholder('e.g. M.A.R or Mohammed')->columnSpan(1),
                        Textarea::make('personalisation_notes')->label('Personalisation Notes')
                            ->rows(3)->placeholder('Font style, placement, any special requests...')->columnSpanFull(),
                        FileUpload::make('reference_images')->label('Reference Images')
                            ->multiple()->image()->directory('custom-orders')
                            ->maxFiles(6)->maxSize(5120)
                            ->helperText('Upload up to 6 reference photos (max 5MB each)')
                            ->columnSpanFull(),
                    ])->columns(2),
                ]),

                // ── Financials ────────────────────────────────────────────
                Tab::make('Financials')->icon('heroicon-o-banknotes')->schema([
                    Section::make('Payment')->schema([
                        TextInput::make('agreed_price_omr')->label('Agreed Price (OMR)')
                            ->numeric()->required()->prefix('OMR')->step(0.001)->columnSpan(1),
                        TextInput::make('deposit_amount_omr')->label('Deposit Amount (OMR)')
                            ->numeric()->prefix('OMR')->step(0.001)->default(0)->columnSpan(1),
                        Grid::make(2)->schema([
                            Toggle::make('deposit_paid')->label('Deposit Received')->default(false)
                                ->live()
                                ->afterStateUpdated(fn($state, $set) => $state ? $set('deposit_paid_at', now()) : null),
                            DateTimePicker::make('deposit_paid_at')->label('Deposit Received At'),
                        ])->columnSpanFull(),
                        TextInput::make('whatsapp_thread')->label('WhatsApp Thread Link')
                            ->url()->placeholder('https://wa.me/...')->columnSpanFull(),
                        Textarea::make('admin_notes')->label('Internal Notes')
                            ->rows(4)->placeholder('Private notes for admin use only...')
                            ->columnSpanFull(),
                    ])->columns(2),
                ]),

            ])->columnSpanFull()->persistTabInQueryString(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')->weight('bold')->searchable()
                    ->copyable()->label('Ref #'),
                TextColumn::make('customer_name')->searchable()->sortable()
                    ->description(fn(CustomOrder $r) => $r->customer_phone),
                TextColumn::make('product_type')->badge()
                    ->color(fn($state) => match($state) {
                        'wallet'    => 'info',
                        'bag'       => 'success',
                        'belt'      => 'warning',
                        'accessory' => 'gray',
                        default     => 'gray',
                    }),
                TextColumn::make('leather_color')->label('Colour')->placeholder('—'),
                TextColumn::make('agreed_price_omr')->label('Price (OMR)')->sortable(),
                IconColumn::make('deposit_paid')->boolean()->label('Deposit'),
                TextColumn::make('status')->badge()
                    ->color(fn($state) => match($state) {
                        'inquiry'       => 'gray',
                        'confirmed'     => 'info',
                        'in_production' => 'warning',
                        'quality_check' => 'primary',
                        'ready'         => 'success',
                        'delivered'     => 'success',
                        'cancelled'     => 'danger',
                        default         => 'gray',
                    }),
                TextColumn::make('promised_date')->date()->sortable()->label('Due'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'inquiry'=>'Inquiry','confirmed'=>'Confirmed',
                    'in_production'=>'In Production','quality_check'=>'QC',
                    'ready'=>'Ready','delivered'=>'Delivered','cancelled'=>'Cancelled',
                ]),
                SelectFilter::make('product_type')->options([
                    'wallet'=>'Wallet','bag'=>'Bag','belt'=>'Belt','accessory'=>'Accessory',
                ]),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomOrders::route('/'),
            'create' => Pages\CreateCustomOrder::route('/create'),
            'view'   => Pages\ViewCustomOrder::route('/{record}'),
            'edit'   => Pages\EditCustomOrder::route('/{record}/edit'),
        ];
    }
}
