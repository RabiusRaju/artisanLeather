<?php
namespace App\Filament\Resources\Finance\Expenses;

use App\Filament\Resources\Finance\Expenses\Pages;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Enums\NavigationGroupEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-arrow-trending-down'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Finance->value; }
    public static function getNavigationSort(): int     { return 4; }
    public static function getNavigationBadge(): ?string
    {
        $total = Expense::whereMonth('expense_date', now()->month)->sum('amount_omr');
        return $total > 0 ? 'OMR '.number_format($total,0) : null;
    }
    public static function getNavigationBadgeColor(): string { return 'danger'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            // ── Core info — what every expense needs ───────────────────────
            Section::make('Expense Details')
                ->description('Record what you spent, how much, and when.')
                ->schema([
                    // Category + Title — the two most important fields
                    Select::make('expense_category_id')
                        ->label('Category')
                        ->options(ExpenseCategory::where('is_active',true)->orderBy('sort_order')->pluck('name','id'))
                        ->required()->searchable()->preload()
                        ->placeholder('Select expense category...')
                        ->columnSpanFull(),

                    TextInput::make('title')
                        ->label('What was this expense for?')
                        ->required()
                        ->placeholder('e.g. Workshop rent — June, Instagram ads, Electricity bill')
                        ->columnSpanFull(),

                    // Amount + Date — side by side always
                    TextInput::make('amount_omr')
                        ->label('Amount (OMR)')
                        ->numeric()->prefix('OMR')->step(0.001)->required()
                        ->placeholder('0.000'),

                    DatePicker::make('expense_date')
                        ->label('Date Paid')
                        ->required()->default(now()),

                    // Payment method — full row
                    Select::make('payment_method')
                        ->label('How was it paid?')
                        ->options([
                            'cash'          => '💵 Cash',
                            'bank_transfer' => '🏦 Bank Transfer',
                            'card'          => '💳 Card',
                            'cheque'        => '📋 Cheque',
                            'other'         => '— Other',
                        ])->default('bank_transfer')
                        ->columnSpanFull(),
                ])->columns(['default'=>1,'md'=>2]),

            // ── Optional details — collapsible ─────────────────────────────
            Section::make('Receipt & Details')
                ->description('Attach a receipt, link a supplier, or add notes.')
                ->schema([
                    Select::make('supplier_id')
                        ->label('Supplier (optional)')
                        ->options(Supplier::where('is_active',true)->pluck('name','id'))
                        ->searchable()->nullable()
                        ->placeholder('Link to supplier...'),

                    TextInput::make('reference')
                        ->label('Receipt / Reference No.')
                        ->placeholder('e.g. INV-2026-001'),

                    FileUpload::make('receipt_image')
                        ->label('Receipt Photo')
                        ->image()
                        ->imageEditor()->imageEditorMode(1)
                        ->directory('receipts')->disk('public')
                        ->maxSize(5120)
                        ->helperText('Take a photo of the receipt — max 5MB')
                        ->columnSpanFull(),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->placeholder('Any additional context...')
                        ->columnSpanFull(),
                ])->columns(['default'=>1,'md'=>2])
                ->collapsible()->collapsed(),

            // ── Recurring flag — separate, clean ───────────────────────────
            Section::make('Recurring Expense?')
                ->description('Mark if this expense repeats automatically each period.')
                ->schema([
                    Toggle::make('is_recurring')
                        ->label('This is a recurring expense')
                        ->live()->default(false)
                        ->columnSpanFull(),

                    Select::make('recurring_period')
                        ->label('Repeats every')
                        ->options([
                            'weekly'    => '📆 Every Week',
                            'monthly'   => '📅 Every Month',
                            'quarterly' => '🗓️ Every Quarter',
                            'yearly'    => '📆 Every Year',
                        ])
                        ->visible(fn($get) => $get('is_recurring'))
                        ->columnSpanFull(),
                ])->columns(1)
                ->collapsible()->collapsed(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_date')->date('d M Y')->sortable()->label('Date'),
                TextColumn::make('category.name')->badge()->searchable()->label('Category'),
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('amount_omr')->prefix('OMR ')->sortable()->weight('bold')->color('danger'),
                TextColumn::make('payment_method')->badge()->color('gray')
                    ->formatStateUsing(fn($s)=>match($s){'cash'=>'Cash','bank_transfer'=>'Bank','cheque'=>'Cheque','card'=>'Card',default=>$s}),
                TextColumn::make('supplier.name')->label('Supplier')->placeholder('—'),
                TextColumn::make('reference')->label('Ref.')->placeholder('—'),
            ])
            ->defaultSort('expense_date','desc')
            ->filters([
                SelectFilter::make('expense_category_id')->label('Category')->options(ExpenseCategory::pluck('name','id')),
                SelectFilter::make('payment_method')->options(['cash'=>'Cash','bank_transfer'=>'Bank Transfer','cheque'=>'Cheque','card'=>'Card']),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit'   => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
