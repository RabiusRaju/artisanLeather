<?php
namespace App\Filament\Resources\Finance\Suppliers;

use App\Filament\Resources\Finance\Suppliers\Pages;
use App\Models\Supplier;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-truck'; }
    public static function getNavigationGroup(): string { return 'Finance'; }
    public static function getNavigationSort(): int     { return 1; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make()->tabs([
                Tab::make('Profile')->icon('heroicon-o-building-storefront')->schema([
                    Section::make('Supplier Details')->schema([
                        TextInput::make('name')->required()->columnSpan(2),
                        TextInput::make('name_ar')->label('Name (Arabic)')->columnSpan(1),
                        Select::make('category')->options([
                            'leather_goods' => '👜 Leather Goods',
                            'hardware'      => '🔩 Hardware & Fittings',
                            'packaging'     => '📦 Packaging',
                            'accessories'   => '✨ Accessories',
                            'services'      => '🛠️ Services',
                            'other'         => 'Other',
                        ])->required()->default('leather_goods')->columnSpan(1),
                        TextInput::make('country')->default('Oman')->columnSpan(1),
                        Select::make('payment_terms')->options([
                            'prepaid' => 'Prepaid','cod' => 'Cash on Delivery',
                            'net_15'  => 'Net 15','net_30' => 'Net 30',
                            'net_60'  => 'Net 60','net_90' => 'Net 90',
                        ])->default('prepaid')->columnSpan(1),
                        Select::make('currency')->options([
                            'OMR'=>'OMR','AED'=>'AED','SAR'=>'SAR','USD'=>'USD','EUR'=>'EUR','GBP'=>'GBP',
                        ])->default('OMR')->columnSpan(1),
                    ])->columns(3),

                    Section::make('Contact')->schema([
                        TextInput::make('contact_person')->columnSpan(1),
                        TextInput::make('phone')->columnSpan(1),
                        TextInput::make('whatsapp')->columnSpan(1),
                        TextInput::make('email')->email()->columnSpan(1),
                        TextInput::make('website')->url()->columnSpan(1),
                        Textarea::make('address')->rows(2)->columnSpanFull(),
                    ])->columns(3),
                ]),

                Tab::make('Terms & Ratings')->icon('heroicon-o-star')->schema([
                    Section::make('Business Terms')->schema([
                        TextInput::make('credit_limit_omr')->label('Credit Limit (OMR)')->numeric()->prefix('OMR')->step(0.001)->columnSpan(1),
                        TextInput::make('lead_time_days')->label('Avg. Lead Time (days)')->numeric()->columnSpan(1),
                        Select::make('rating')->label('Supplier Rating')->options([
                            5=>'⭐⭐⭐⭐⭐ Excellent',4=>'⭐⭐⭐⭐ Good',
                            3=>'⭐⭐⭐ Average',2=>'⭐⭐ Below Average',1=>'⭐ Poor',
                        ])->columnSpan(1),
                        Toggle::make('is_active')->default(true)->columnSpan(1),
                        Textarea::make('notes')->rows(4)->columnSpanFull(),
                    ])->columns(3),
                ]),
            ])->columnSpanFull()->persistTabInQueryString(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('bold')
                    ->description(fn(Supplier $r) => $r->country . ($r->contact_person ? ' · '.$r->contact_person : '')),
                TextColumn::make('category')->badge()->color(fn($s)=>match($s){
                    'leather_goods'=>'warning','hardware'=>'info','packaging'=>'success',default=>'gray'
                }),
                TextColumn::make('payment_terms')->badge()->color('gray')
                    ->formatStateUsing(fn($s)=>strtoupper(str_replace('_',' ',$s))),
                TextColumn::make('currency')->badge()->color('primary'),
                TextColumn::make('purchase_orders_count')->counts('purchaseOrders')->label('POs'),
                TextColumn::make('total_purchases')
                    ->getStateUsing(fn(Supplier $r) => 'OMR '.number_format($r->total_purchases,3))
                    ->label('Total Bought')->color('warning'),
                TextColumn::make('rating')->label('Rating')
                    ->formatStateUsing(fn($s)=>str_repeat('⭐',$s??0))->placeholder('—'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('category')->options([
                    'leather_goods'=>'Leather Goods','hardware'=>'Hardware',
                    'packaging'=>'Packaging','services'=>'Services',
                ]),
                SelectFilter::make('country'),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view'   => Pages\ViewSupplier::route('/{record}'),
            'edit'   => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
