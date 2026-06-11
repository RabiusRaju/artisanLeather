<?php

namespace App\Filament\Resources\Faqs;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\Faqs\Pages;
use App\Models\Faq;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    public static function getNavigationIcon(): string  { return 'heroicon-o-question-mark-circle'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Content->value; }
    public static function getNavigationSort(): int     { return 4; }
    public static function getNavigationLabel(): string { return 'FAQs'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Question & Answer')
                ->description('Arabic is optional — shown to Arabic-speaking visitors. English is used as the fallback.')
                ->columns(2)
                ->schema([
                    TextInput::make('question')
                        ->label('Question (English)')
                        ->required()
                        ->columnSpan(1),

                    TextInput::make('question_ar')
                        ->label('Question (Arabic, optional)')
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->columnSpan(1),

                    Textarea::make('answer')
                        ->label('Answer (English)')
                        ->required()
                        ->rows(4)
                        ->columnSpan(1),

                    Textarea::make('answer_ar')
                        ->label('Answer (Arabic, optional)')
                        ->rows(4)
                        ->extraInputAttributes(['dir' => 'rtl'])
                        ->columnSpan(1),
                ]),

            Section::make('Visibility')
                ->columns(2)
                ->schema([
                    TextInput::make('sort_order')
                        ->label('Display Order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower = shown first.'),

                    Toggle::make('is_active')
                        ->label('Show on website')
                        ->default(true)
                        ->inline(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label('Question')
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('question_ar')
                    ->label('Arabic')
                    ->limit(40)
                    ->placeholder('—'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No FAQs yet')
            ->emptyStateDescription('Add your first frequently asked question.')
            ->emptyStateIcon('heroicon-o-question-mark-circle');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit'   => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
