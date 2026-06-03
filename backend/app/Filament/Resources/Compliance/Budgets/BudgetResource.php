<?php
namespace App\Filament\Resources\Compliance\Budgets;

use App\Filament\Resources\Compliance\Budgets\Pages;
use App\Models\Budget;
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

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-calculator'; }
    public static function getNavigationGroup(): string { return 'Compliance'; }
    public static function getNavigationSort(): int     { return 4; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Monthly Budget')->schema([
                Select::make('year')->options(array_combine(range(now()->year+1,now()->year-2),range(now()->year+1,now()->year-2)))->default(now()->year)->required()->columnSpan(1),
                Select::make('month')->options([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'])->default(now()->month)->required()->columnSpan(1),
                TextInput::make('revenue_target')->label('Revenue Target (OMR)')->numeric()->prefix('OMR')->step(0.001)->required()->columnSpan(1),
                TextInput::make('expense_budget')->label('Expense Budget (OMR)')->numeric()->prefix('OMR')->step(0.001)->default(0)->columnSpan(1),
                TextInput::make('purchase_budget')->label('Purchase Budget (OMR)')->numeric()->prefix('OMR')->step(0.001)->default(0)->columnSpan(1),
                Textarea::make('notes')->rows(2)->columnSpanFull(),
            ])->columns(['default'=>1,'md'=>3]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year')->sortable(),
                TextColumn::make('month')->formatStateUsing(fn($s)=>date('F',mktime(0,0,0,$s,1)))->sortable(),
                TextColumn::make('revenue_target')->prefix('OMR ')->sortable()->weight('bold'),
                TextColumn::make('expense_budget')->prefix('OMR ')->label('Expense Budget'),
                TextColumn::make('purchase_budget')->prefix('OMR ')->label('Purchase Budget'),
            ])
            ->defaultSort('year', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit'   => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
