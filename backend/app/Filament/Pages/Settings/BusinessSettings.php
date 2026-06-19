<?php
namespace App\Filament\Pages\Settings;

use App\Enums\NavigationGroupEnum;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class BusinessSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.settings.business-settings';
    public static function getNavigationIcon(): string  { return 'heroicon-o-cog-6-tooth'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Settings->value; }
    public static function getNavigationSort(): int     { return 1; }
    public static function getNavigationLabel(): string { return 'Business Settings'; }
    public function getTitle(): string                  { return 'Business Settings'; }

    public ?array $data = [];

    public function mount(): void
    {
        $flat   = Setting::all()->pluck('value', 'key')->toArray();
        $nested = Arr::undot($flat);
        $this->settingsForm->fill($nested);
    }

    public function settingsForm(Schema $schema): Schema
    {
        return $schema->components([

            Tabs::make('Settings')->persistTabInQueryString()->tabs([

                Tab::make('Business Info')->icon('heroicon-o-building-storefront')->schema([
                    Section::make('🏪 Business Information')
                        ->description('Your store name, contact details, and physical address.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('business.name')->label('Business Name')->required(),
                            TextInput::make('business.tagline')->label('Tagline'),
                            TextInput::make('business.email')->label('Business Email')->email(),
                            TextInput::make('business.phone')->label('Business Phone')->tel()->placeholder('+968 XXXX XXXX'),
                            TextInput::make('business.whatsapp')
                                ->label('WhatsApp Number')
                                ->helperText('Format: 96891234567 — no + or spaces')
                                ->placeholder('96891234567'),
                            TextInput::make('business.city')->label('City'),
                            Select::make('business.timezone')
                                ->label('Timezone')
                                ->options([
                                    'Asia/Muscat'    => 'Oman (Asia/Muscat, UTC+4)',
                                    'Asia/Dubai'     => 'UAE (Asia/Dubai, UTC+4)',
                                    'Asia/Riyadh'    => 'Saudi Arabia (Asia/Riyadh, UTC+3)',
                                    'Asia/Kuwait'    => 'Kuwait (Asia/Kuwait, UTC+3)',
                                    'Asia/Qatar'     => 'Qatar (Asia/Qatar, UTC+3)',
                                    'Asia/Bahrain'   => 'Bahrain (Asia/Bahrain, UTC+3)',
                                    'Europe/London'  => 'United Kingdom (Europe/London)',
                                    'UTC'            => 'UTC',
                                ])
                                ->default('Asia/Muscat')
                                ->required()
                                ->helperText('Controls how dates & times are displayed and entered throughout the admin panel.'),
                            Textarea::make('business.address')->label('Full Address (English)')->rows(2)->columnSpanFull(),
                            Textarea::make('business.address_ar')->label('Full Address (Arabic)')->rows(2)->extraAttributes(['dir' => 'rtl'])->columnSpanFull(),
                            TextInput::make('business.address_2')->label('Address Line 2 / District (English)')->placeholder('Al Khuwair District'),
                            TextInput::make('business.address_2_ar')->label('Address Line 2 / District (Arabic)')->extraInputAttributes(['dir' => 'rtl'])->placeholder('حي الخوير'),
                            TextInput::make('business.whatsapp_hours')->label('WhatsApp Availability Note (English)')->placeholder('Available 9am – 9pm GST'),
                            TextInput::make('business.whatsapp_hours_ar')->label('WhatsApp Availability Note (Arabic)')->extraInputAttributes(['dir' => 'rtl'])->placeholder('متاح من 9 صباحاً حتى 9 مساءً'),
                            TextInput::make('business.email_response_time')->label('Email Response Time (English)')->placeholder('We reply within 24 hours'),
                            TextInput::make('business.email_response_time_ar')->label('Email Response Time (Arabic)')->extraInputAttributes(['dir' => 'rtl'])->placeholder('نرد خلال 24 ساعة'),
                        ]),
                ]),

                Tab::make('Appearance')->icon('heroicon-o-swatch')->schema([
                    Section::make('🎨 Appearance')
                        ->description('Control the colour theme visitors see across the website.')
                        ->columns(2)
                        ->schema([
                            Select::make('theme.default')
                                ->label('Default Theme')
                                ->options([
                                    'warm-leather'   => '🟤 Warm Leather',
                                    'classic-black'  => '⬛ Classic Black',
                                    'forest-atelier' => '🌲 Forest Atelier',
                                    'royal-burgundy' => '🍷 Royal Burgundy',
                                    'midnight-navy'  => '🌊 Midnight Navy',
                                    'desert-dusk'    => '🏜️ Desert Dusk',
                                    'daylight-white' => '☀️ Daylight White',
                                ])
                                ->default('warm-leather')
                                ->required()
                                ->live()
                                ->helperText('The theme new visitors see by default.'),
                            Toggle::make('theme.lock_theme')
                                ->label('Lock theme for all visitors')
                                ->helperText('When enabled, visitors cannot change the theme — the theme switcher is hidden and the default above is enforced for everyone.'),
                            View::make('filament.forms.components.theme-preview')
                                ->columnSpanFull(),
                        ]),
                ]),

                Tab::make('Social Media')->icon('heroicon-o-share')->schema([
                    Section::make('📱 Social Media')
                        ->description('Links to your social media profiles. Leave blank if not applicable.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('social.instagram')->label('Instagram URL')->url()->placeholder('https://instagram.com/artisanleather'),
                            TextInput::make('social.facebook')->label('Facebook URL')->url()->placeholder('https://facebook.com/artisanleather'),
                            TextInput::make('social.tiktok')->label('TikTok URL')->url()->placeholder('https://tiktok.com/@artisanleather'),
                            TextInput::make('social.twitter')->label('Twitter / X URL')->url(),
                        ]),
                ]),

                Tab::make('Website')->icon('heroicon-o-globe-alt')->schema([
                    Section::make('🌐 Website')
                        ->columns(2)
                        ->schema([
                            TextInput::make('website.url')->label('Website URL')->url(),
                            TextInput::make('website.support_email')->label('Support Email')->email(),
                        ]),
                ]),

                Tab::make('Orders')->icon('heroicon-o-shopping-bag')->schema([
                    Section::make('🛒 Order Settings')
                        ->columns(2)
                        ->schema([
                            TextInput::make('orders.default_currency')->label('Default Currency')->default('OMR'),
                            TextInput::make('orders.free_delivery_threshold')
                                ->label('Free Delivery Above (OMR)')
                                ->numeric()->suffix('OMR')
                                ->helperText('Set 0 for always free delivery'),
                            Textarea::make('orders.whatsapp_message')
                                ->label('WhatsApp Order Template')
                                ->rows(2)->columnSpanFull()
                                ->helperText('Message sent when customer chooses WhatsApp payment'),
                        ]),
                ]),

                Tab::make('Footer')->icon('heroicon-o-bars-3-bottom-right')->schema([
                    Section::make('🦶 Footer')
                        ->description('Text shown in the footer across every page.')
                        ->columns(1)
                        ->schema([
                            Textarea::make('footer.tagline')
                                ->label('Brand Tagline (English)')
                                ->rows(2)
                                ->placeholder('Premium handcrafted leather goods. Made in Oman. Delivered across the GCC.'),

                            Textarea::make('footer.tagline_ar')
                                ->label('Brand Tagline (Arabic)')
                                ->rows(2)
                                ->extraAttributes(['dir' => 'rtl'])
                                ->placeholder('منتجات جلدية فاخرة مصنوعة يدوياً. صُنعت في عُمان. تُوصَّل إلى دول الخليج.'),

                            TextInput::make('footer.copyright')
                                ->label('Copyright Line (English)')
                                ->placeholder('© 2025 Artisan Leather · artisanleatherom.com · All rights reserved'),

                            TextInput::make('footer.copyright_ar')
                                ->label('Copyright Line (Arabic)')
                                ->extraAttributes(['dir' => 'rtl'])
                                ->placeholder('© 2025 آرتيزان ليذر · artisanleatherom.com · جميع الحقوق محفوظة'),
                        ]),
                ]),

                Tab::make('SEO & Analytics')->icon('heroicon-o-magnifying-glass')->schema([
                    Section::make('🔍 SEO & Analytics')
                        ->columns(2)
                        ->schema([
                            TextInput::make('seo.meta_title')->label('Default Page Title')->columnSpanFull(),
                            Textarea::make('seo.meta_description')->label('Default Meta Description')->rows(2)->columnSpanFull(),

                            TextInput::make('seo.google_analytics')
                                ->label('Google Analytics 4 ID')
                                ->placeholder('G-XXXXXXXXXX')
                                ->helperText('Format: G-XXXXXXXXXX'),

                            TextInput::make('seo.google_tag_manager')
                                ->label('Google Tag Manager ID')
                                ->placeholder('GTM-XXXXXXX')
                                ->helperText('Format: GTM-XXXXXXX'),

                            TextInput::make('seo.search_console')
                                ->label('Google Search Console — Verification Code')
                                ->placeholder('abc123XYZ...')
                                ->helperText('Paste only the content="..." value from the verification meta tag.'),

                            TextInput::make('seo.meta_pixel')
                                ->label('Meta (Facebook) Pixel ID')
                                ->placeholder('1234567890123456')
                                ->helperText('Found in Meta Events Manager → your Pixel → Settings.'),

                            TextInput::make('seo.clarity')
                                ->label('Microsoft Clarity Project ID')
                                ->placeholder('abc1defgh2')
                                ->helperText('Found in Clarity → Settings → Overview → Tracking Code.'),

                            TextInput::make('seo.google_business')
                                ->label('Google Business Profile URL')
                                ->url()
                                ->placeholder('https://maps.app.goo.gl/...')
                                ->helperText('Shown as a "Find us on Google" link in the footer.'),

                            TextInput::make('seo.serper_api_key')
                                ->label('Serper.dev — API Key')
                                ->password()
                                ->revealable()
                                ->placeholder('your serper.dev API key')
                                ->helperText('serper.dev → Dashboard → API Key. Used by AI to research competitor content before generating.'),
                        ]),
                ]),

                Tab::make('Surveys')->icon('heroicon-o-clipboard-document-check')->schema([
                    Section::make('🎯 Field Survey Collection')
                        ->description('Lets staff submit a survey multiple times in a row when collecting feedback in person (e.g. visiting a shop and talking to several customers). Without this, only one response per device/IP is normally allowed.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('survey.collector_pin')
                                ->label('Staff Collector PIN')
                                ->password()
                                ->revealable()
                                ->placeholder('e.g. 2468')
                                ->helperText('Share this PIN with staff who collect feedback in person. On the public survey page they tap "Staff Mode", enter this PIN once per device, and can then submit the survey repeatedly. Leave blank to disable Staff Mode entirely.')
                                ->columnSpanFull(),
                        ]),
                ]),

            ])->columnSpanFull(),

        ])->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save All Settings')
                ->icon('heroicon-o-check')
                ->color('warning')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->settingsForm->getState();

        // Flatten nested array → dot-notation keys
        // e.g. ['business' => ['name' => 'X']] → ['business.name' => 'X']
        $flat = Arr::dot($state);

        foreach ($flat as $key => $value) {
            Setting::set($key, $value);
        }

        Cache::flush();
        Notification::make()->title('✅ Settings saved successfully!')->success()->send();
    }
}
