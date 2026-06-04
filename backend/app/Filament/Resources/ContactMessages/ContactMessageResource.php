<?php
namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages;
use App\Models\ContactMessage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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

            Section::make('Customer Details')
                ->description('Fill in when logging a phone or WhatsApp inquiry manually.')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Full Name')
                        ->required()
                        ->placeholder('e.g. Ahmad Al Rashdi'),

                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->placeholder('e.g. ahmad@example.com'),

                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->placeholder('+968 9X XXX XXX'),

                    TextInput::make('subject')
                        ->label('Subject')
                        ->placeholder('e.g. Custom wallet enquiry'),
                ]),

            Section::make('Message')
                ->schema([
                    Textarea::make('message')
                        ->label('Message / Enquiry')
                        ->required()
                        ->rows(4)
                        ->placeholder('Write the customer\'s enquiry here…')
                        ->columnSpanFull(),
                ]),

            Section::make('Status & Notes')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options([
                            'unread'  => '🔴 Unread',
                            'read'    => '🟡 Read',
                            'replied' => '🟢 Replied',
                        ])
                        ->default('unread')
                        ->required(),

                    Textarea::make('admin_notes')
                        ->label('Internal Notes')
                        ->rows(3)
                        ->placeholder('e.g. Called back, will visit showroom Thursday')
                        ->columnSpanFull(),
                ]),

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
