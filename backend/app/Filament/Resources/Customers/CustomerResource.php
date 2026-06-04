<?php
namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages;
use App\Filament\Resources\Customers\RelationManagers;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Governorate;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Enums\NavigationGroupEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-users'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Customers->value; }
    public static function getNavigationSort(): int { return 1; }
    public static function getNavigationBadge(): ?string
    {
        return (string) Customer::where('status', 'vip')->count() ?: null;
    }
    public static function getNavigationBadgeColor(): string { return 'warning'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('Customer')->tabs([

                Tab::make('Profile')->icon('heroicon-o-user')->schema([
                    Section::make('Personal Details')->schema([
                        TextInput::make('name')->label('Full Name')->required()->columnSpan(2),
                        TextInput::make('name_ar')->label('Name (Arabic)')->columnSpan(1),
                        Select::make('status')->options([
                            'active'   => 'Active',
                            'inactive' => 'Inactive',
                            'vip'      => '⭐ VIP',
                        ])->default('active')->columnSpan(1),
                        TextInput::make('phone')->label('Phone')->required()->columnSpan(1),
                        TextInput::make('whatsapp')->label('WhatsApp')->placeholder('+968 ···· ····')->columnSpan(1),
                        TextInput::make('email')->label('Email')->email()->columnSpan(2),
                        DatePicker::make('date_of_birth')->label('Date of Birth')->columnSpan(1),
                    ])->columns(3),

                    Section::make('Location')->schema([
                        Select::make('country')->label('Country')->options(\App\Models\Country::where('is_active',true)->orderBy('sort_order')->pluck('name','name'))->searchable()->default('Oman')->columnSpan(1),
                        Select::make('governorate')->label('Governorate')->options(\App\Models\Governorate::where('is_active',true)->orderBy('sort_order')->pluck('name','name'))->searchable()->columnSpan(1),
                        TextInput::make('city')->columnSpan(1),
                        Textarea::make('address')->rows(2)->columnSpanFull(),
                    ])->columns(3),
                ]),

                Tab::make('Preferences')->icon('heroicon-o-heart')->schema([
                    Section::make('Leather Preferences')->schema([
                        Select::make('preferred_category')->label('Favourite Category')->options([
                            'wallets'     => 'Wallets',
                            'bags'        => 'Bags',
                            'belts'       => 'Belts',
                            'accessories' => 'Accessories',
                        ])->columnSpan(1),
                        TextInput::make('preferred_color')->label('Favourite Leather Colour')
                            ->placeholder('e.g. Cognac, Dark Brown')->columnSpan(1),
                        TagsInput::make('tags')->label('Customer Tags')
                            ->suggestions(['vip','wholesale','gift_buyer','regular','corporate'])
                            ->helperText('Press Enter to add a tag')
                            ->columnSpanFull(),
                        Textarea::make('notes')->label('Private Notes (Admin only)')
                            ->rows(4)->placeholder('Customer preferences, special requests, occasion dates...')
                            ->columnSpanFull(),
                    ])->columns(2),
                ]),

            ])->columnSpanFull()->persistTabInQueryString(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('bold')
                    ->description(fn(Customer $r) => $r->phone),
                TextColumn::make('governorate')->label('Location')
                    ->formatStateUsing(fn($state, Customer $r) => trim(($state ?? '') . ($r->city ? ', '.$r->city : ''))),
                TextColumn::make('preferred_category')->badge()->label('Category')
                    ->color(fn($state) => match($state) {
                        'wallets'     => 'info',
                        'bags'        => 'success',
                        'belts'       => 'warning',
                        'accessories' => 'gray',
                        default       => 'gray',
                    }),
                TextColumn::make('status')->badge()
                    ->color(fn($state) => match($state) {
                        'vip'      => 'warning',
                        'active'   => 'success',
                        'inactive' => 'gray',
                        default    => 'gray',
                    }),
                TextColumn::make('custom_orders_count')->counts('customOrders')->label('Custom Orders'),
                TextColumn::make('created_at')->label('Since')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(['active'=>'Active','inactive'=>'Inactive','vip'=>'VIP']),
                SelectFilter::make('preferred_category')->options([
                    'wallets'=>'Wallets','bags'=>'Bags','belts'=>'Belts','accessories'=>'Accessories',
                ])->label('Category'),
                SelectFilter::make('country'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                // ── WhatsApp ──────────────────────────────────────────
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(function (Customer $record) {
                        $phone = $record->whatsapp ?: $record->phone;
                        $phone = preg_replace('/\D/', '', $phone);
                        if (!str_starts_with($phone, '968')) {
                            $phone = '968' . ltrim($phone, '0');
                        }
                        $msg = "Hello {$record->name} 👋, this is Artisan Leather. How can we help you today?";
                        return 'https://wa.me/' . $phone . '?text=' . urlencode($msg);
                    })
                    ->openUrlInNewTab()
                    ->tooltip('Open WhatsApp chat'),

                // ── Email ─────────────────────────────────────────────
                Action::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->url(fn(Customer $r) => $r->email ? 'mailto:' . $r->email : null)
                    ->visible(fn(Customer $r) => !empty($r->email))
                    ->tooltip('Send email'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    // ── Customer view (infolist) with lifetime stats ──────────────────────
    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Schemas\Components\Section::make('Customer Profile')
                ->schema([
                    \Filament\Schemas\Components\Grid::make(4)->schema([
                        \Filament\Infolists\Components\TextEntry::make('name')
                            ->label('Name')->weight('bold'),
                        \Filament\Infolists\Components\TextEntry::make('phone')
                            ->label('Phone')
                            ->url(fn(Customer $r) => 'tel:' . $r->phone),
                        \Filament\Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->url(fn(Customer $r) => $r->email ? 'mailto:' . $r->email : null)
                            ->placeholder('—'),
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn($state) => match($state) {
                                'vip'      => 'warning',
                                'active'   => 'success',
                                'inactive' => 'gray',
                                default    => 'gray',
                            }),
                    ]),
                    \Filament\Schemas\Components\Grid::make(4)->schema([
                        \Filament\Infolists\Components\TextEntry::make('city')->placeholder('—'),
                        \Filament\Infolists\Components\TextEntry::make('governorate')->placeholder('—'),
                        \Filament\Infolists\Components\TextEntry::make('country')->placeholder('Oman'),
                        \Filament\Infolists\Components\TextEntry::make('preferred_category')
                            ->label('Prefers')
                            ->badge()
                            ->placeholder('—'),
                    ]),
                ]),

            // ── Lifetime stats ─────────────────────────────────────────
            \Filament\Schemas\Components\Section::make('Lifetime Summary')
                ->schema([
                    \Filament\Schemas\Components\Grid::make(4)->schema([
                        \Filament\Infolists\Components\TextEntry::make('orders_count')
                            ->label('Total Orders')
                            ->getStateUsing(fn(Customer $r) => $r->orders_count)
                            ->badge()->color('info'),
                        \Filament\Infolists\Components\TextEntry::make('custom_orders_count')
                            ->label('Custom Orders')
                            ->getStateUsing(fn(Customer $r) => $r->customOrders()->count())
                            ->badge()->color('warning'),
                        \Filament\Infolists\Components\TextEntry::make('total_spend')
                            ->label('Lifetime Spend')
                            ->getStateUsing(fn(Customer $r) => 'OMR ' . number_format($r->total_spend, 3))
                            ->weight('bold')->color('success'),
                        \Filament\Infolists\Components\TextEntry::make('member_since')
                            ->label('Member Since')
                            ->getStateUsing(fn(Customer $r) => $r->created_at->format('d M Y')),
                    ]),
                ])->compact(),

            \Filament\Infolists\Components\TextEntry::make('notes')
                ->label('Private Notes')
                ->placeholder('No notes.')
                ->columnSpanFull(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\CustomOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view'   => Pages\ViewCustomer::route('/{record}'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
