<?php
namespace App\Filament\Pages\Settings;

use App\Enums\NavigationGroupEnum;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
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
                    Textarea::make('business.address')->label('Full Address')->rows(2)->columnSpanFull(),
                ]),

            Section::make('📱 Social Media')
                ->description('Links to your social media profiles. Leave blank if not applicable.')
                ->columns(2)
                ->schema([
                    TextInput::make('social.instagram')->label('Instagram URL')->url()->placeholder('https://instagram.com/artisanleather'),
                    TextInput::make('social.facebook')->label('Facebook URL')->url()->placeholder('https://facebook.com/artisanleather'),
                    TextInput::make('social.tiktok')->label('TikTok URL')->url()->placeholder('https://tiktok.com/@artisanleather'),
                    TextInput::make('social.twitter')->label('Twitter / X URL')->url(),
                ]),

            Section::make('🌐 Website')
                ->columns(2)
                ->schema([
                    TextInput::make('website.url')->label('Website URL')->url(),
                    TextInput::make('website.support_email')->label('Support Email')->email(),
                ]),

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

            Section::make('🦶 Footer')
                ->description('Text shown in the footer across every page.')
                ->columns(1)
                ->schema([
                    Textarea::make('footer.tagline')
                        ->label('Brand Tagline')
                        ->rows(2)
                        ->placeholder('Premium handcrafted leather goods. Made in Oman. Delivered across the GCC.'),

                    TextInput::make('footer.copyright')
                        ->label('Copyright Line')
                        ->placeholder('© 2025 Artisan Leather · artisanleatherom.com · All rights reserved'),
                ]),

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
