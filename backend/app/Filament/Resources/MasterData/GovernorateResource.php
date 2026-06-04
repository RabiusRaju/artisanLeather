<?php
namespace App\Filament\Resources\MasterData;

use App\Enums\NavigationGroupEnum;
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
use Filament\Tables\Table;

class GovernorateResource extends Resource
{
    protected static ?string $model = Governorate::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-map-pin'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Settings->value; }
    public static function getNavigationSort(): int     { return 3; }
    public static function getNavigationLabel(): string { return 'Governorates'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                TextInput::make('name')->label('Name (English)')->required(),
                TextInput::make('name_ar')->label('الاسم (Arabic)'),
                TextInput::make('code')->label('Code')->placeholder('e.g. MC')->maxLength(10),
                Select::make('country_code')->label('Country')->options(['OM'=>'🇴🇲 Oman','AE'=>'🇦🇪 UAE','SA'=>'🇸🇦 Saudi Arabia'])->default('OM')->required(),
                TextInput::make('sort_order')->label('Display Order')->numeric()->default(0),
                Toggle::make('is_active')->label('Active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable()->width(50),
                TextColumn::make('name')->sortable()->searchable()->weight('semibold'),
                TextColumn::make('name_ar')->label('Arabic Name'),
                TextColumn::make('code')->badge()->color('gray'),
                TextColumn::make('country_code')->label('Country')->badge(),
                TextColumn::make('cities_count')->label('Cities')->counts('cities')->badge()->color('info'),
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
            'index'  => MdPages\Governorates\ListGovernorates::route('/'),
            'create' => MdPages\Governorates\CreateGovernorate::route('/create'),
            'edit'   => MdPages\Governorates\EditGovernorate::route('/{record}/edit'),
        ];
    }
}
