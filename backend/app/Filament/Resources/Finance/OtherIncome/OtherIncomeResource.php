<?php
namespace App\Filament\Resources\Finance\OtherIncome;

use App\Filament\Resources\Finance\OtherIncome\Pages;
use App\Models\OtherIncome;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OtherIncomeResource extends Resource
{
    protected static ?string $model = OtherIncome::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-arrow-trending-up'; }
    public static function getNavigationGroup(): string { return 'Finance'; }
    public static function getNavigationSort(): int     { return 5; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            // ── Core income info ───────────────────────────────────────────
            Section::make('Income Details')
                ->description('Log income that did not come through the online store.')
                ->schema([
                    Select::make('category')
                        ->label('Income Type')
                        ->options([
                            'wholesale' => '🏭 Wholesale / Bulk Order',
                            'gifting'   => '🎁 Corporate Gifting',
                            'rental'    => '🏠 Rental / Space',
                            'refund'    => '↩️ Refund Received',
                            'other'     => '— Other',
                        ])->required()
                        ->placeholder('Select type of income...')
                        ->columnSpanFull(),

                    TextInput::make('title')
                        ->label('Description / Source')
                        ->required()
                        ->placeholder('e.g. Wholesale order — Al Noor Trading, Refund from supplier')
                        ->columnSpanFull(),

                    TextInput::make('amount_omr')
                        ->label('Amount Received (OMR)')
                        ->numeric()->prefix('OMR')->step(0.001)->required()
                        ->placeholder('0.000'),

                    DatePicker::make('income_date')
                        ->label('Date Received')
                        ->required()->default(now()),

                    Select::make('payment_method')
                        ->label('Received via')
                        ->options([
                            'bank_transfer' => '🏦 Bank Transfer',
                            'cash'          => '💵 Cash',
                            'card'          => '💳 Card',
                            'cheque'        => '📋 Cheque',
                            'other'         => '— Other',
                        ])->default('bank_transfer')
                        ->columnSpanFull(),
                ])->columns(['default'=>1,'md'=>2]),

            // ── Optional details ───────────────────────────────────────────
            Section::make('Additional Details')
                ->description('Reference number and notes are optional.')
                ->schema([
                    TextInput::make('reference')
                        ->label('Invoice / Reference No.')
                        ->placeholder('e.g. WS-2026-001'),

                    Textarea::make('description')
                        ->label('More details')
                        ->rows(3)
                        ->placeholder('Customer name, order items, any context...')
                        ->columnSpanFull(),

                    Textarea::make('notes')
                        ->label('Private Notes')
                        ->rows(2)
                        ->placeholder('Internal notes only...')
                        ->columnSpanFull(),
                ])->columns(['default'=>1,'md'=>2])
                ->collapsible()->collapsed(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('income_date')->date('d M Y')->sortable(),
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('category')->badge()->color('success'),
                TextColumn::make('amount_omr')->prefix('OMR ')->weight('bold')->color('success')->sortable(),
                TextColumn::make('payment_method')->badge()->color('gray'),
            ])
            ->defaultSort('income_date','desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOtherIncome::route('/'),
            'create' => Pages\CreateOtherIncome::route('/create'),
            'edit'   => Pages\EditOtherIncome::route('/{record}/edit'),
        ];
    }
}
