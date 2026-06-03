<?php
namespace App\Filament\Resources\Operations\CashFlow;

use App\Filament\Resources\Operations\CashFlow\Pages;
use App\Models\CashFlowEntry;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class CashFlowResource extends Resource
{
    protected static ?string $model = CashFlowEntry::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-banknotes'; }
    public static function getNavigationGroup(): string { return 'Operations'; }
    public static function getNavigationSort(): int     { return 2; }
    public static function getNavigationLabel(): string { return 'Cash Flow'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Cash Flow Entry')
                ->description('Record money that actually entered or left your bank account.')
                ->schema([
                    Select::make('type')
                        ->label('Direction')
                        ->options(['in' => '💚 Money IN', 'out' => '🔴 Money OUT'])
                        ->required()->live()
                        ->columnSpanFull(),

                    Select::make('category')
                        ->label('Category')
                        ->options(fn($get) => $get('type') === 'in'
                            ? ['sales'=>'Sales Revenue','custom_order'=>'Custom Order Payment','refund'=>'Refund Received','loan'=>'Loan / Injection','other'=>'Other Income']
                            : ['purchase'=>'Purchase Payment','salary'=>'Salaries & Wages','rent'=>'Rent','utility'=>'Utilities','marketing'=>'Marketing','shipping'=>'Shipping','tax'=>'Tax / VAT','loan'=>'Loan Repayment','other'=>'Other Expense']
                        )
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('description')
                        ->label('What is this for?')
                        ->required()
                        ->placeholder('e.g. Bank transfer from customer Mohammed — Order #AL-2026-001')
                        ->columnSpanFull(),

                    TextInput::make('amount_omr')
                        ->label('Amount (OMR)')
                        ->numeric()->prefix('OMR')->step(0.001)->required()
                        ->placeholder('0.000'),

                    DatePicker::make('entry_date')
                        ->label('Date')
                        ->required()->default(now()),

                    Select::make('payment_method')
                        ->label('Method')
                        ->options(['bank_transfer'=>'🏦 Bank Transfer','cash'=>'💵 Cash','card'=>'💳 Card','cheque'=>'📋 Cheque','other'=>'Other'])
                        ->default('bank_transfer'),

                    TextInput::make('bank_reference')
                        ->label('Bank Reference / Transaction ID')
                        ->placeholder('Optional — from your bank statement')
                        ->columnSpanFull(),

                    Toggle::make('is_reconciled')
                        ->label('Reconciled with bank statement')
                        ->default(false),

                    Textarea::make('notes')->rows(2)->columnSpanFull(),
                ])->columns(['default'=>1,'md'=>2]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_date')->label('Date')->date('d M Y')->sortable(),
                TextColumn::make('type')
                    ->label('')
                    ->formatStateUsing(fn($state) => new HtmlString(
                        $state === 'in'
                            ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">↓ IN</span>'
                            : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400">↑ OUT</span>'
                    )),
                TextColumn::make('description')->searchable()->limit(40),
                TextColumn::make('category')->badge()->color(fn($s) => in_array($s,['sales','custom_order','refund','loan']) ? 'success' : 'warning')
                    ->formatStateUsing(fn($s) => ucfirst(str_replace('_',' ',$s))),
                TextColumn::make('amount_omr')
                    ->label('Amount (OMR)')
                    ->formatStateUsing(fn($state, CashFlowEntry $record) => new HtmlString(
                        '<span class="font-bold tabular-nums '.($record->type==='in'?'text-green-600 dark:text-green-400':'text-red-500').'">'.
                        ($record->type==='in'?'+':'-').' OMR '.number_format($state,3).'</span>'
                    ))
                    ->sortable(),
                TextColumn::make('payment_method')->badge()->color('gray')
                    ->formatStateUsing(fn($s) => match($s){'bank_transfer'=>'Bank','cash'=>'Cash','card'=>'Card','cheque'=>'Cheque',default=>$s}),
                IconColumn::make('is_reconciled')->boolean()->label('Reconciled'),
            ])
            ->defaultSort('entry_date','desc')
            ->filters([
                SelectFilter::make('type')->options(['in'=>'Money In','out'=>'Money Out']),
                SelectFilter::make('is_reconciled')->options([1=>'Reconciled',0=>'Not Reconciled'])->label('Reconciliation'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->poll('10s');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCashFlow::route('/'),
            'create' => Pages\CreateCashFlow::route('/create'),
            'edit'   => Pages\EditCashFlow::route('/{record}/edit'),
        ];
    }
}
