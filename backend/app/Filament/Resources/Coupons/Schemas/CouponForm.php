<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coupon Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Coupon Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper(trim($state)) : $state)
                            ->helperText('Customers will enter this code at checkout (not case-sensitive).'),

                        TextInput::make('description')
                            ->label('Description (admin only)')
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Discount Type')
                            ->options([
                                'percentage' => 'Percentage off order total',
                                'fixed'      => 'Fixed amount off order total',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('value')
                            ->label('Discount Value')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->suffix(fn (callable $get) => $get('type') === 'percentage' ? '%' : 'OMR'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make('🎯 Featured Popup')
                    ->description('Show this coupon as an automatic popup with a countdown timer the moment anyone visits the site. Only one coupon can be featured at a time — enabling this here will automatically turn it off for any other coupon.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('show_as_popup')
                            ->label('Show as site-wide popup')
                            ->live()
                            ->inline(false)
                            ->columnSpanFull(),

                        TextInput::make('popup_title')
                            ->label('Popup Headline')
                            ->maxLength(255)
                            ->placeholder('e.g. Limited Time Offer!')
                            ->visible(fn (callable $get) => (bool) $get('show_as_popup')),

                        DateTimePicker::make('expires_at')
                            ->label('Countdown Ends At')
                            ->native(false)
                            ->seconds(false)
                            ->required(fn (callable $get) => (bool) $get('show_as_popup'))
                            ->helperText('The popup countdown ticks down to this moment. Once it passes, the coupon stops working and the popup stops showing.')
                            ->visible(fn (callable $get) => (bool) $get('show_as_popup')),

                        Hidden::make('popup_image'),

                        Placeholder::make('popup_image_preview')
                            ->label('Current Banner Image')
                            ->visible(fn (callable $get) => (bool) $get('show_as_popup') && (bool) $get('popup_image'))
                            ->content(fn (callable $get) => $get('popup_image')
                                ? new \Illuminate\Support\HtmlString('<img src="' . Storage::disk('public')->url($get('popup_image')) . '" style="max-height:120px;border-radius:8px" />')
                                : null),

                        FileUpload::make('upload')
                            ->label('Banner Image (optional)')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('coupons')
                            ->visible(fn (callable $get) => (bool) $get('show_as_popup'))
                            ->helperText('Optional. If left empty, the popup falls back to a clean text-only design.')
                            ->columnSpanFull(),

                        TextInput::make('popup_image_alt')
                            ->label('Banner Image ALT Text')
                            ->placeholder('e.g. Artisan Leather newsletter discount leather accessories banner')
                            ->visible(fn (callable $get) => (bool) $get('show_as_popup'))
                            ->maxLength(125)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
