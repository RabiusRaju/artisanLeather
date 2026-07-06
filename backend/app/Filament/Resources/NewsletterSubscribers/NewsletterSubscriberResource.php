<?php

namespace App\Filament\Resources\NewsletterSubscribers;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\NewsletterSubscribers\Pages;
use App\Models\NewsletterSubscriber;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-envelope-open'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Sales->value; }
    public static function getNavigationLabel(): string { return 'Newsletter Leads'; }
    public static function getNavigationSort(): int { return 12; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->weight('semibold'),

                TextColumn::make('coupon_code')
                    ->label('Coupon')
                    ->fontFamily('mono')
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('subscribed_at')
                    ->label('Subscribed')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->recordActions([DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterSubscribers::route('/'),
        ];
    }
}
