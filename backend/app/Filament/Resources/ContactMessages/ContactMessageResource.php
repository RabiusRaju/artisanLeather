<?php
namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages;
use App\Models\ContactMessage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use App\Enums\NavigationGroupEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-envelope'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Sales->value; }
    public static function getNavigationSort(): int { return 2; }
    public static function getNavigationBadge(): ?string {
        return (string) ContactMessage::where('status', 'unread')->count() ?: null;
    }
    public static function getNavigationBadgeColor(): string { return 'danger'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('status')->options([
                'unread'  => 'Unread',
                'read'    => 'Read',
                'replied' => 'Replied',
            ])->required(),
            Textarea::make('admin_notes')->rows(3)->label('Internal Notes')->columnSpanFull(),
        ]);
    }

    public static function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            \Filament\Schemas\Components\Section::make('From')->schema([
                \Filament\Infolists\Components\TextEntry::make('name'),
                \Filament\Infolists\Components\TextEntry::make('email'),
                \Filament\Infolists\Components\TextEntry::make('phone')->placeholder('—'),
                \Filament\Infolists\Components\TextEntry::make('subject')->placeholder('—'),
                \Filament\Infolists\Components\TextEntry::make('status')->badge()
                    ->color(fn($state) => match($state) {
                        'unread'  => 'danger',
                        'read'    => 'warning',
                        'replied' => 'success',
                        default   => 'gray',
                    }),
                \Filament\Infolists\Components\TextEntry::make('created_at')->dateTime(),
            ])->columns(2),
            \Filament\Schemas\Components\Section::make('Message')->schema([
                \Filament\Infolists\Components\TextEntry::make('message')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('subject')->limit(30)->placeholder('—'),
                TextColumn::make('message')->limit(50)->wrap(),
                TextColumn::make('status')->badge()
                    ->color(fn($state) => match($state) {
                        'unread'  => 'danger',
                        'read'    => 'warning',
                        'replied' => 'success',
                        default   => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'unread'  => 'Unread',
                    'read'    => 'Read',
                    'replied' => 'Replied',
                ]),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContactMessages::route('/'),
            'view'   => Pages\ViewContactMessage::route('/{record}'),
            'edit'   => Pages\EditContactMessage::route('/{record}/edit'),
        ];
    }
}
