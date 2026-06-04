<?php
namespace App\Filament\Resources\HR\Departments;

use App\Filament\Resources\HR\Departments\Pages;
use App\Models\Department;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Enums\NavigationGroupEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-building-office'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::HumanResources->value; }
    public static function getNavigationSort(): int     { return 1; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Department Details')->schema([
                TextInput::make('name')->required()->columnSpan(2),
                TextInput::make('name_ar')->label('Name (Arabic)')->columnSpan(1),
                Textarea::make('description')->rows(3)->columnSpanFull(),
                TextInput::make('sort_order')->numeric()->default(0)->columnSpan(1),
                Toggle::make('is_active')->default(true)->columnSpan(1),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('name_ar')->label('Arabic'),
                TextColumn::make('employees_count')->counts('employees')->badge()->color('info')->label('Staff'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit'   => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
