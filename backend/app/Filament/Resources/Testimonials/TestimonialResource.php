<?php

namespace App\Filament\Resources\Testimonials;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\Testimonials\Pages;
use App\Models\Testimonial;
use App\Services\AiPostService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    public static function getNavigationIcon(): string  { return 'heroicon-o-chat-bubble-left-right'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Content->value; }
    public static function getNavigationSort(): int     { return 3; }
    public static function getNavigationLabel(): string { return 'Testimonials'; }
    public static function getNavigationBadge(): ?string
    {
        $count = Testimonial::where('is_active', true)->count();
        return $count > 0 ? (string) $count : null;
    }
    public static function getNavigationBadgeColor(): string { return 'success'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            // ── 0. AI Generator ───────────────────────────────────────────
            Section::make('🤖 AI Content Generator')
                ->description('Describe the testimonial you want and AI will generate it instantly.')
                ->schema([
                    Textarea::make('ai_prompt')
                        ->label('Describe the testimonial')
                        ->placeholder('e.g. A 5-star review from a Riyadh businessman about the Heritage Bifold Wallet he has been using for 2 years')
                        ->rows(3)
                        ->dehydrated(false)
                        ->columnSpanFull(),

                    \Filament\Schemas\Components\Actions::make([

                        Action::make('generate_testimonial_claude')
                            ->label('Generate with Claude')
                            ->icon('heroicon-o-sparkles')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Generate with Claude AI')
                            ->modalDescription('This will overwrite any existing content in all fields. Continue?')
                            ->modalSubmitActionLabel('Yes, generate')
                            ->action(function ($get, $set) {
                                $prompt = $get('ai_prompt');
                                if (blank($prompt)) {
                                    Notification::make()->title('Please enter a description first.')->warning()->send();
                                    return;
                                }
                                try {
                                    $data = app(AiPostService::class)->generateTestimonialWithClaude($prompt);
                                    self::fillAiFields($set, $data);
                                    Notification::make()
                                        ->title('✅ Claude generated your testimonial!')
                                        ->body('Review and save.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title('Claude generation failed')->body($e->getMessage())->danger()->send();
                                }
                            }),

                        Action::make('generate_testimonial_openai')
                            ->label('Generate with OpenAI')
                            ->icon('heroicon-o-cpu-chip')
                            ->color('info')
                            ->requiresConfirmation()
                            ->modalHeading('Generate with OpenAI (GPT-4o)')
                            ->modalDescription('This will overwrite any existing content in all fields. Continue?')
                            ->modalSubmitActionLabel('Yes, generate')
                            ->action(function ($get, $set) {
                                $prompt = $get('ai_prompt');
                                if (blank($prompt)) {
                                    Notification::make()->title('Please enter a description first.')->warning()->send();
                                    return;
                                }
                                try {
                                    $data = app(AiPostService::class)->generateTestimonialWithOpenAI($prompt);
                                    self::fillAiFields($set, $data);
                                    Notification::make()
                                        ->title('✅ OpenAI generated your testimonial!')
                                        ->body('Review and save.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title('OpenAI generation failed')->body($e->getMessage())->danger()->send();
                                }
                            }),

                    ]),
                ]),

            // ── 1. Quote ──────────────────────────────────────────────────
            Section::make('Quote')
                ->description('Write exactly what the customer said. Arabic is optional — shown to Arabic-speaking visitors.')
                ->columns(2)
                ->schema([
                    Textarea::make('quote')
                        ->label('English')
                        ->required()
                        ->rows(5)
                        ->placeholder('The most exquisite wallet I have ever owned. The leather is buttery smooth and the craftsmanship is simply unmatched.')
                        ->helperText('Keep it in the customer\'s own words.')
                        ->columnSpan(1),

                    Textarea::make('quote_ar')
                        ->label('Arabic (optional)')
                        ->rows(5)
                        ->placeholder('أروع محفظة امتلكتها على الإطلاق...')
                        ->helperText('Leave blank to show the English quote to all visitors.')
                        ->columnSpan(1),
                ]),

            // ── 2. Customer ───────────────────────────────────────────────
            Section::make('Customer')
                ->description('Who gave this testimonial?')
                ->columns(3)
                ->schema([
                    TextInput::make('author')
                        ->label('Name')
                        ->required()
                        ->placeholder('Mohammed Al Rashidi')
                        ->live(onBlur: true)
                        ->columnSpan(1),

                    TextInput::make('location')
                        ->label('City / Country')
                        ->placeholder('Muscat, Oman')
                        ->live(onBlur: true)
                        ->columnSpan(1),

                    TextInput::make('product')
                        ->label('Product Purchased')
                        ->placeholder('Heritage Bifold Wallet')
                        ->helperText('Optional — for your reference only.')
                        ->columnSpan(1),
                ]),

            // ── 3. Rating + Visibility ────────────────────────────────────
            Section::make('Rating & Visibility')
                ->columns(3)
                ->schema([
                    Select::make('rating')
                        ->label('Star Rating')
                        ->options([
                            5 => '★★★★★  Excellent',
                            4 => '★★★★☆  Very Good',
                            3 => '★★★☆☆  Good',
                            2 => '★★☆☆☆  Fair',
                            1 => '★☆☆☆☆  Poor',
                        ])
                        ->default(5)
                        ->required()
                        ->live()
                        ->columnSpan(1),

                    TextInput::make('sort_order')
                        ->label('Display Order')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lower = shown first.')
                        ->columnSpan(1),

                    Toggle::make('is_active')
                        ->label('Show on website')
                        ->default(true)
                        ->inline(false)
                        ->columnSpan(1),
                ]),

            // ── 4. Preview (collapsed) ────────────────────────────────────
            Section::make('Website Preview')
                ->description('How this testimonial will look on the homepage. Expand to check before saving.')
                ->collapsed()
                ->schema([
                    Placeholder::make('preview_card')
                        ->label('')
                        ->content(function ($get) {
                            $quote    = $get('quote')    ?: 'Your quote will appear here…';
                            $author   = $get('author')   ?: 'Customer Name';
                            $location = $get('location') ?: '';
                            $rating   = max(1, min(5, (int) ($get('rating') ?: 5)));
                            $filled   = str_repeat('★', $rating);
                            $empty    = str_repeat('☆', 5 - $rating);

                            return new HtmlString('
                                <div style="max-width:560px;margin:0 auto;background:linear-gradient(135deg,#1a1208,#120D05);border:1px solid rgba(212,175,55,0.22);border-radius:12px;padding:36px 32px;text-align:center;font-family:Georgia,serif;">
                                    <div style="font-size:52px;color:rgba(212,175,55,0.25);line-height:1;margin-bottom:10px;user-select:none;">"</div>
                                    <p style="color:rgba(255,255,255,0.78);font-style:italic;font-size:15px;line-height:1.8;margin:0 0 24px;">' . e($quote) . '</p>
                                    <div style="width:32px;height:1px;background:rgba(212,175,55,0.4);margin:0 auto 16px;"></div>
                                    <div style="margin-bottom:12px;">
                                        <span style="color:#d4af37;font-size:16px;letter-spacing:3px;">' . $filled . '</span>
                                        <span style="color:rgba(255,255,255,0.12);font-size:16px;letter-spacing:3px;">' . $empty . '</span>
                                    </div>
                                    <p style="color:#fff;font-size:13px;font-weight:600;margin:0 0 5px;letter-spacing:.05em;">' . e($author) . '</p>
                                    ' . ($location ? '<p style="color:rgba(255,255,255,0.32);font-size:11px;margin:0;letter-spacing:.04em;">' . e($location) . '</p>' : '') . '
                                </div>
                            ');
                        })
                        ->columnSpanFull(),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('author')
                    ->label('Customer')
                    ->searchable()
                    ->formatStateUsing(function ($state, Testimonial $record) {
                        $words    = explode(' ', trim($state));
                        $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1));
                        $location = e($record->location ?? '');
                        $name     = e($state);

                        return "
                            <div style='display:flex;align-items:center;gap:10px'>
                                <div style='
                                    width:38px;height:38px;border-radius:50%;
                                    background:rgba(212,175,55,0.12);
                                    border:1px solid rgba(212,175,55,0.35);
                                    display:flex;align-items:center;justify-content:center;
                                    color:#d4af37;font-weight:700;font-size:13px;flex-shrink:0;
                                '>{$initials}</div>
                                <div>
                                    <div style='font-weight:600;font-size:13px'>{$name}</div>
                                    <div style='font-size:11px;color:#9ca3af'>{$location}</div>
                                </div>
                            </div>
                        ";
                    })
                    ->html(),

                TextColumn::make('quote')
                    ->label('Quote')
                    ->limit(75)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn($state) =>
                        '<span style="color:#d4af37;font-size:13px">' . str_repeat('★', (int)$state) . '</span>' .
                        '<span style="color:#4b5563;font-size:13px">' . str_repeat('★', 5 - (int)$state) . '</span>'
                    )
                    ->html()
                    ->sortable(),

                TextColumn::make('product')
                    ->label('Product')
                    ->badge()
                    ->color('warning')
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
            ->filters([])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No testimonials yet')
            ->emptyStateDescription('Add your first customer testimonial to show on the website.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit'   => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }

    private static function fillAiFields($set, array $data): void
    {
        $set('quote',    $data['quote']    ?? '');
        $set('quote_ar', $data['quote_ar'] ?? '');
        $set('author',   $data['author']   ?? '');
        $set('location', $data['location'] ?? '');
        $set('product',  $data['product']  ?? '');
        if (isset($data['rating'])) {
            $set('rating', (int) $data['rating']);
        }
    }
}
