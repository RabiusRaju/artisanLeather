<?php
namespace App\Filament\Resources\MasterData;

use App\Enums\NavigationGroupEnum;
use App\Models\City;
use App\Models\Governorate;
use App\Filament\Resources\MasterData\Pages as MdPages;
use Filament\Forms\Components\Select;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CityResource extends Resource
{
    protected static ?string $model = City::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-building-office-2'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Settings->value; }
    public static function getNavigationSort(): int     { return 4; }
    public static function getNavigationLabel(): string { return 'Cities'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                Select::make('governorate_id')->label('Governorate')
                    ->options(Governorate::where('is_active',true)->orderBy('sort_order')->pluck('name','id'))
                    ->searchable()->required(),
                TextInput::make('sort_order')->label('Display Order')->numeric()->default(0),
                TextInput::make('name')->label('City Name (English)')->required(),
                TextInput::make('name_ar')->label('اسم المدينة (Arabic)'),
                Toggle::make('is_active')->label('Active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('governorate.name')->label('Governorate')->sortable()->searchable(),
                TextColumn::make('name')->sortable()->searchable()->weight('semibold'),
                TextColumn::make('name_ar')->label('Arabic'),
                TextColumn::make('sort_order')->label('#')->sortable(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->defaultSort('governorate_id')
            ->filters([
                SelectFilter::make('governorate_id')->label('Governorate')
                    ->options(Governorate::orderBy('sort_order')->pluck('name','id')),
                SelectFilter::make('is_active')->options([1=>'Active',0=>'Inactive'])->label('Status'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => MdPages\Cities\ListCities::route('/'),
            'create' => MdPages\Cities\CreateCity::route('/create'),
            'edit'   => MdPages\Cities\EditCity::route('/{record}/edit'),
        ];
    }
}
