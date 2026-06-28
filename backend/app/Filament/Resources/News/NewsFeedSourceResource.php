<?php

namespace App\Filament\Resources\News;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\News\Pages;
use App\Models\NewsFeedSource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsFeedSourceResource extends Resource
{
    protected static ?string $model = NewsFeedSource::class;

    public static function getNavigationIcon(): string  { return 'heroicon-o-globe-alt'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Content->value; }
    public static function getNavigationSort(): int     { return 3; }
    public static function getNavigationLabel(): string { return 'News Sources'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Feed Source')->schema([
                TextInput::make('name')
                    ->label('Site Name')
                    ->required()
                    ->placeholder('e.g. Leather News'),
                TextInput::make('feed_url')
                    ->label('RSS/Atom Feed URL')
                    ->required()
                    ->url()
                    ->unique(ignoreRecord: true)
                    ->placeholder('https://example.com/feed')
                    ->helperText('Must be a direct RSS or Atom feed URL, not just the blog page. For Shopify blogs this is usually /blogs/{handle}.atom'),
                Toggle::make('is_active')
                    ->label('Active (included in sync)')
                    ->default(true),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('feed_url')->label('Feed URL')->limit(50)->copyable(),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('last_synced_at')->label('Last Synced')->dateTime('d M Y, H:i')->placeholder('Never')->sortable(),
                TextColumn::make('last_error')->label('Last Error')->limit(40)->color('danger')->placeholder('—'),
            ])
            ->defaultSort('name')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNewsFeedSources::route('/'),
            'create' => Pages\CreateNewsFeedSource::route('/create'),
            'edit'   => Pages\EditNewsFeedSource::route('/{record}/edit'),
        ];
    }
}
