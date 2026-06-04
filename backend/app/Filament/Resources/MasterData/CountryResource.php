<?php
namespace App\Filament\Resources\MasterData;

use App\Enums\NavigationGroupEnum;
use App\Models\Country;
use App\Filament\Resources\MasterData\Pages as MdPages;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-globe-alt'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Settings->value; }
    public static function getNavigationSort(): int     { return 5; }
    public static function getNavigationLabel(): string { return 'Countries'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(3)->schema([
                TextInput::make('name')->label('Country (English)')->required(),
                TextInput::make('name_ar')->label('الدولة (Arabic)'),
                TextInput::make('code')->label('Code (3-letter)')->maxLength(3)->required()->placeholder('OMR'),
                TextInput::make('dial_code')->label('Dial Code')->placeholder('+968'),
                TextInput::make('flag_emoji')->label('Flag Emoji')->placeholder('🇴🇲'),
                TextInput::make('sort_order')->label('Order')->numeric()->default(0),
                Toggle::make('is_active')->label('Active')->default(true),
                Toggle::make('is_gcc')->label('GCC Country')->default(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable()->width(50),
                TextColumn::make('flag_emoji')->label('')->width(40),
                TextColumn::make('name')->sortable()->searchable()->weight('semibold'),
                TextColumn::make('name_ar')->label('Arabic'),
                TextColumn::make('code')->badge()->color('gray'),
                TextColumn::make('dial_code')->label('Dial'),
                IconColumn::make('is_gcc')->label('GCC')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => MdPages\Countries\ListCountries::route('/'),
            'create' => MdPages\Countries\CreateCountry::route('/create'),
            'edit'   => MdPages\Countries\EditCountry::route('/{record}/edit'),
        ];
    }
}
