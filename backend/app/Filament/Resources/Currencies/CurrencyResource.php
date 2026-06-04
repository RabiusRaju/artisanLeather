<?php
namespace App\Filament\Resources\Currencies;

use App\Filament\Resources\Currencies\Pages;
use App\Models\Currency;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Enums\NavigationGroupEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-currency-dollar'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Settings->value; }
    public static function getNavigationSort(): int { return 1; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('code')->required()->maxLength(3)->disabled(),
            TextInput::make('symbol')->required()->maxLength(5),
            TextInput::make('name')->required(),
            TextInput::make('name_ar')->label('Name (Arabic)'),
            TextInput::make('rate')->numeric()->required()->step(0.000001)
                ->helperText('Rate relative to OMR (base currency). 1 OMR = X of this currency.'),
            TextInput::make('decimals')->numeric()->required()->minValue(0)->maxValue(4),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->weight('bold')->sortable(),
                TextColumn::make('symbol'),
                TextColumn::make('name')->sortable(),
                TextColumn::make('name_ar')->label('Arabic'),
                TextColumn::make('rate')->sortable()
                    ->formatStateUsing(fn($state) => number_format($state, 4)),
                TextColumn::make('decimals'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrencies::route('/'),
            'edit'  => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
