<?php
namespace App\Filament\Resources\Surveys;

use App\Filament\Resources\Surveys\Pages;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Enums\NavigationGroupEnum;
use App\Services\AiPostService;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class SurveyResource extends Resource
{
    protected static ?string $model = Survey::class;
    public static function getNavigationIcon(): string  { return 'heroicon-o-clipboard-document-check'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Content->value; }
    public static function getNavigationSort(): int     { return 2; }
    public static function getNavigationLabel(): string { return 'Surveys'; }
    public static function getNavigationBadge(): ?string
    {
        $active = Survey::where('status', 'active')->count();
        return $active > 0 ? (string)$active : null;
    }
    public static function getNavigationBadgeColor(): string { return 'success'; }

    public static function form(Schema $schema): Schema
    {
        $questionTypeOptions = [
            'single_choice'   => '🔘 Single Choice (radio)',
            'multiple_choice' => '☑️  Multiple Choice (checkboxes)',
            'rating'          => '⭐ Rating Scale (1–5)',
            'nps'             => '📊 Net Promoter Score (0–10)',
            'text_short'      => '📝 Short Text (one line)',
            'text_long'       => '📄 Long Text (paragraph)',
            'yes_no'          => '✅ Yes / No',
            'dropdown'        => '📋 Dropdown Select',
        ];

        return $schema->schema([

            // ── AI Auto-Fill ─────────────────────────────────────────────
            Section::make('✨ AI Content Generator')
                ->description('Describe the survey you want, then choose which AI to generate it. All questions, options and Arabic translations will be filled automatically.')
                ->collapsed()
                ->schema([
                    Textarea::make('ai_prompt')
                        ->label('What should this survey be about?')
                        ->placeholder('e.g. Post-purchase satisfaction survey to understand how customers feel about product quality and delivery')
                        ->helperText('Be specific about the goal and target audience.')
                        ->rows(4)
                        ->columnSpanFull(),

                    FileUpload::make('ai_attachments')
                        ->label('Reference Images & Documents (optional)')
                        ->helperText('Claude reads images + PDFs. OpenAI reads images only.')
                        ->multiple()
                        ->disk('local')
                        ->directory('ai-uploads')
                        ->visibility('private')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'text/plain'])
                        ->maxSize(10240)
                        ->maxFiles(5)
                        ->columnSpanFull(),

                    \Filament\Schemas\Components\Actions::make([

                        Action::make('generate_claude')
                            ->label('Generate with Claude')
                            ->icon('heroicon-o-sparkles')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Generate Survey with Claude AI')
                            ->modalDescription('This will overwrite the title, descriptions, thank-you messages and all questions. Continue?')
                            ->modalSubmitActionLabel('Yes, generate')
                            ->action(function ($get, $set) {
                                $prompt = $get('ai_prompt');
                                if (blank($prompt)) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Please enter a survey description first.')
                                        ->warning()->send();
                                    return;
                                }
                                $filePaths = self::resolveAiFilePaths($get('ai_attachments') ?? []);
                                try {
                                    $data = app(AiPostService::class)->generateSurveyWithClaude($prompt, $filePaths);
                                    self::fillAiFields($set, $data);
                                    $set('ai_attachments', []);
                                    \Filament\Notifications\Notification::make()
                                        ->title('✅ Claude generated your survey!')
                                        ->body('Review the Questions tab before saving.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Claude generation failed')
                                        ->body($e->getMessage())
                                        ->danger()->send();
                                }
                            }),

                        Action::make('generate_openai')
                            ->label('Generate with OpenAI')
                            ->icon('heroicon-o-cpu-chip')
                            ->color('info')
                            ->requiresConfirmation()
                            ->modalHeading('Generate Survey with OpenAI (GPT-4o)')
                            ->modalDescription('This will overwrite the title, descriptions, thank-you messages and all questions. Continue?')
                            ->modalSubmitActionLabel('Yes, generate')
                            ->action(function ($get, $set) {
                                $prompt = $get('ai_prompt');
                                if (blank($prompt)) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Please enter a survey description first.')
                                        ->warning()->send();
                                    return;
                                }
                                $filePaths = self::resolveAiFilePaths($get('ai_attachments') ?? []);
                                try {
                                    $data = app(AiPostService::class)->generateSurveyWithOpenAI($prompt, $filePaths);
                                    self::fillAiFields($set, $data);
                                    $set('ai_attachments', []);
                                    \Filament\Notifications\Notification::make()
                                        ->title('✅ OpenAI generated your survey!')
                                        ->body('Review the Questions tab before saving.')
                                        ->success()->send();
                                } catch (\Throwable $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('OpenAI generation failed')
                                        ->body($e->getMessage())
                                        ->danger()->send();
                                }
                            }),

                    ]),
                ]),

            Tabs::make()->tabs([

                // ── Tab 1: Survey Details ─────────────────────────────────
                Tab::make('Survey Details')->icon('heroicon-o-document-text')->schema([

                    Section::make('Basic Information')
                        ->description('Set up the survey title, slug and description.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('title')
                                ->label('Survey Title')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state)))
                                ->placeholder('e.g. Product Preference Survey — June 2026')
                                ->columnSpanFull(),

                            TextInput::make('slug')
                                ->label('URL Slug')
                                ->required()
                                ->unique(Survey::class, 'slug', ignoreRecord: true)
                                ->prefix('artisanleatherom.com/survey/')
                                ->helperText('Auto-generated. Share this link with customers.')
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Description (English)')
                                ->rows(2)
                                ->placeholder('Tell respondents what this survey is about and how long it takes.')
                                ->columnSpanFull(),

                            Textarea::make('description_ar')
                                ->label('Description (Arabic)')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),

                    Section::make('Settings & Scheduling')
                        ->columns(3)
                        ->schema([
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'draft'  => '📝 Draft',
                                    'active' => '🟢 Active',
                                    'closed' => '🔴 Closed',
                                ])
                                ->default('draft')
                                ->required(),

                            DateTimePicker::make('starts_at')
                                ->label('Start Date & Time')
                                ->placeholder('Now if empty'),

                            DateTimePicker::make('ends_at')
                                ->label('End Date & Time')
                                ->placeholder('No end date if empty'),

                            TextInput::make('response_limit')
                                ->label('Response Limit')
                                ->numeric()
                                ->placeholder('Unlimited if empty')
                                ->helperText('Max number of responses before auto-close'),

                            Toggle::make('allow_multiple_responses')
                                ->label('Allow same person to respond multiple times')
                                ->default(false),

                            Toggle::make('show_progress')
                                ->label('Show progress bar to respondent')
                                ->default(true),

                            Toggle::make('is_anonymous')
                                ->label('Anonymous responses (no name/email required)')
                                ->default(true),
                        ]),

                    Section::make('Thank You Page')
                        ->columns(2)
                        ->schema([
                            Textarea::make('thank_you_message')
                                ->label('Thank You Message (English)')
                                ->rows(2)
                                ->default('Thank you for your feedback! Your input helps us improve.')
                                ->columnSpanFull(),

                            Textarea::make('thank_you_message_ar')
                                ->label('رسالة الشكر (Arabic)')
                                ->rows(2)
                                ->default('شكراً لك على ملاحظاتك! مساهمتك تساعدنا على التحسين.')
                                ->columnSpanFull(),

                            TextInput::make('redirect_url')
                                ->label('Redirect URL after completion')
                                ->placeholder('e.g. https://artisanleatherom.com/collections (leave blank to show thank you message)')
                                ->url()
                                ->columnSpanFull(),
                        ]),

                ]),

                // ── Tab 2: Questions Builder ───────────────────────────────
                Tab::make('Questions')->icon('heroicon-o-question-mark-circle')->schema([

                    Section::make('Build Your Questions')
                        ->description('Add questions in order. Drag ⠿ to reorder.')
                        ->schema([
                            Repeater::make('questions')
                                ->relationship()
                                ->label('')
                                ->schema([

                                    // Question type selector
                                    Select::make('type')
                                        ->label('Question Type')
                                        ->options($questionTypeOptions)
                                        ->required()
                                        ->live()
                                        ->columnSpanFull(),

                                    // Question text
                                    TextInput::make('question')
                                        ->label('Question (English)')
                                        ->required()
                                        ->placeholder('e.g. Which leather colour do you prefer?')
                                        ->columnSpanFull(),

                                    TextInput::make('question_ar')
                                        ->label('Question (Arabic - optional)')
                                        ->placeholder('e.g. ما هو لون الجلد المفضل لديك؟')
                                        ->columnSpanFull(),

                                    Textarea::make('description')
                                        ->label('Helper Text (shown below question)')
                                        ->rows(1)
                                        ->placeholder('Optional additional context for respondents')
                                        ->columnSpanFull(),

                                    // Options — shown only for choice types
                                    TagsInput::make('options')
                                        ->label('Answer Options (press Enter after each)')
                                        ->placeholder('Type an option and press Enter')
                                        ->helperText('For single/multiple choice and dropdown. e.g. Cognac, Dark Brown, Black')
                                        ->visible(fn($get) => in_array($get('type'), ['single_choice','multiple_choice','dropdown','yes_no']))
                                        ->columnSpanFull(),

                                    TagsInput::make('options_ar')
                                        ->label('Answer Options (Arabic)')
                                        ->placeholder('كونياك، بني غامق، أسود')
                                        ->visible(fn($get) => in_array($get('type'), ['single_choice','multiple_choice','dropdown']))
                                        ->columnSpanFull(),

                                    Grid::make(3)->schema([
                                        Toggle::make('is_required')
                                            ->label('Required')
                                            ->default(true),

                                        TextInput::make('sort_order')
                                            ->label('Order')
                                            ->numeric()
                                            ->default(0),
                                    ]),

                                ])
                                ->columns(1)
                                ->addActionLabel('＋ Add Question')
                                ->reorderable()
                                ->reorderableWithDragAndDrop()
                                ->collapsible()
                                ->cloneable()
                                ->itemLabel(fn($state) =>
                                    ($state['type'] ? ucfirst(str_replace('_', ' ', $state['type'])) . ' — ' : '') .
                                    ($state['question'] ?? 'New Question')
                                ),
                        ]),

                ]),

                // ── Tab 3: Preview ────────────────────────────────────────
                Tab::make('Preview')->icon('heroicon-o-eye')->schema([

                    Section::make('Respondent View')
                        ->description('This is exactly how your survey looks to someone filling it in. Check it before sharing.')
                        ->schema([
                            Placeholder::make('survey_preview')
                                ->label('')
                                ->content(function ($get) {
                                    $title       = $get('title')       ?: 'Survey Title';
                                    $description = $get('description') ?: '';
                                    $questions   = array_values($get('questions') ?? []);

                                    if (empty($questions)) {
                                        return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Add questions in the Questions tab to see the preview.</p>');
                                    }

                                    $typeIcons = [
                                        'single_choice'   => '🔘',
                                        'multiple_choice' => '☑️',
                                        'rating'          => '⭐',
                                        'nps'             => '📊',
                                        'text_short'      => '📝',
                                        'text_long'       => '📄',
                                        'yes_no'          => '✅',
                                        'dropdown'        => '📋',
                                    ];

                                    $html  = '<div style="font-family:sans-serif;max-width:640px;background:#fff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;">';

                                    // Header strip
                                    $html .= '<div style="background:linear-gradient(135deg,#1a1208,#2a1a08);padding:28px 32px;">';
                                    $html .= '<h2 style="margin:0 0 8px;font-size:20px;font-weight:700;color:#fff;">' . e($title) . '</h2>';
                                    if (!blank($description)) {
                                        $html .= '<p style="margin:0;font-size:13px;color:rgba(255,255,255,0.65);line-height:1.5;">' . e($description) . '</p>';
                                    }
                                    $html .= '</div>';

                                    // Questions
                                    $html .= '<div style="padding:24px 32px;display:flex;flex-direction:column;gap:24px;">';

                                    foreach ($questions as $i => $q) {
                                        $type     = $q['type']     ?? 'text_short';
                                        $question = $q['question'] ?? '';
                                        $required = !empty($q['is_required']);
                                        $options  = $q['options']  ?? [];
                                        $icon     = $typeIcons[$type] ?? '❓';

                                        $html .= '<div style="padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">';

                                        // Question label
                                        $html .= '<div style="display:flex;align-items:baseline;gap:6px;margin-bottom:12px;">';
                                        $html .= '<span style="font-size:11px;color:#9ca3af;flex-shrink:0;">Q' . ($i + 1) . '</span>';
                                        $html .= '<span style="font-size:14px;font-weight:600;color:#111827;line-height:1.4;">' . e($question);
                                        if ($required) $html .= ' <span style="color:#ef4444;font-size:12px;">*</span>';
                                        $html .= '</span></div>';

                                        // Input mock by type
                                        if ($type === 'text_short') {
                                            $html .= '<div style="height:38px;background:#fff;border:1px solid #d1d5db;border-radius:6px;padding:0 12px;line-height:38px;font-size:12px;color:#d1d5db;">Your answer…</div>';

                                        } elseif ($type === 'text_long') {
                                            $html .= '<div style="height:80px;background:#fff;border:1px solid #d1d5db;border-radius:6px;padding:10px 12px;font-size:12px;color:#d1d5db;">Your answer…</div>';

                                        } elseif ($type === 'yes_no') {
                                            $html .= '<div style="display:flex;gap:10px;">';
                                            foreach (['Yes', 'No'] as $opt) {
                                                $html .= '<div style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:#fff;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#374151;cursor:pointer;">';
                                                $html .= '<div style="width:14px;height:14px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0;"></div>' . e($opt) . '</div>';
                                            }
                                            $html .= '</div>';

                                        } elseif ($type === 'single_choice') {
                                            foreach ($options as $opt) {
                                                $html .= '<div style="display:flex;align-items:center;gap:8px;padding:6px 0;font-size:13px;color:#374151;">';
                                                $html .= '<div style="width:14px;height:14px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0;"></div>' . e($opt) . '</div>';
                                            }

                                        } elseif ($type === 'multiple_choice') {
                                            foreach ($options as $opt) {
                                                $html .= '<div style="display:flex;align-items:center;gap:8px;padding:6px 0;font-size:13px;color:#374151;">';
                                                $html .= '<div style="width:14px;height:14px;border-radius:3px;border:2px solid #d1d5db;flex-shrink:0;"></div>' . e($opt) . '</div>';
                                            }

                                        } elseif ($type === 'dropdown') {
                                            $html .= '<div style="height:38px;background:#fff;border:1px solid #d1d5db;border-radius:6px;padding:0 12px;line-height:38px;font-size:12px;color:#6b7280;display:flex;justify-content:space-between;align-items:center;">';
                                            $html .= '<span>' . (empty($options) ? 'Select an option…' : e($options[0]) . '…') . '</span><span style="color:#9ca3af;">▾</span></div>';

                                        } elseif ($type === 'rating') {
                                            $html .= '<div style="display:flex;gap:8px;">';
                                            for ($s = 1; $s <= 5; $s++) {
                                                $html .= '<div style="width:36px;height:36px;border-radius:50%;border:2px solid #d1d5db;display:flex;align-items:center;justify-content:center;font-size:13px;color:#9ca3af;cursor:pointer;">' . $s . '</div>';
                                            }
                                            $html .= '</div>';

                                        } elseif ($type === 'nps') {
                                            $html .= '<div style="display:flex;gap:4px;flex-wrap:wrap;">';
                                            for ($s = 0; $s <= 10; $s++) {
                                                $html .= '<div style="width:32px;height:32px;border-radius:4px;border:1px solid #d1d5db;display:flex;align-items:center;justify-content:center;font-size:12px;color:#9ca3af;cursor:pointer;">' . $s . '</div>';
                                            }
                                            $html .= '</div>';
                                            $html .= '<div style="display:flex;justify-content:space-between;margin-top:4px;font-size:10px;color:#9ca3af;"><span>Not at all likely</span><span>Extremely likely</span></div>';
                                        }

                                        $html .= '</div>'; // end question card
                                    }

                                    $html .= '</div>'; // end questions wrapper

                                    // Submit button mock
                                    $html .= '<div style="padding:0 32px 28px;">';
                                    $html .= '<div style="display:inline-block;background:linear-gradient(135deg,#c9a84c,#d4af37);color:#1a1208;font-weight:700;font-size:13px;padding:10px 28px;border-radius:6px;cursor:pointer;letter-spacing:.04em;">Submit →</div>';
                                    $html .= '</div>';

                                    $html .= '</div>'; // end card

                                    return new HtmlString($html);
                                })
                                ->columnSpanFull(),
                        ]),

                    Section::make('📊 SEO Ranking Potential')
                        ->description('AI-estimated engagement and reach potential for this survey. Generate with AI first to see this score.')
                        ->collapsed()
                        ->schema([
                            TextInput::make('_seo_score')->dehydrated(false)->hidden(),
                            Textarea::make('_seo_notes')->dehydrated(false)->hidden(),

                            Placeholder::make('_seo_score_card')
                                ->label('')
                                ->content(function ($get) {
                                    $score = (int) ($get('_seo_score') ?? 0);
                                    $notes = trim($get('_seo_notes') ?? '');

                                    if ($score === 0 && blank($notes)) {
                                        return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Generate with AI to see the engagement potential score and tips to maximise response rates.</p>');
                                    }

                                    $color = $score >= 75 ? '#16a34a' : ($score >= 50 ? '#d97706' : '#dc2626');
                                    $label = $score >= 75 ? 'Strong' : ($score >= 50 ? 'Average' : 'Needs Work');

                                    $notesHtml = '';
                                    if (!blank($notes)) {
                                        $lines = array_filter(array_map('trim', explode("\n", $notes)));
                                        $items = implode('', array_map(fn($l) => '<li style="margin-bottom:6px;">' . e($l) . '</li>', $lines));
                                        $notesHtml = '<div style="margin-top:14px;"><div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;">Tips to Improve Response Rate</div><ul style="margin:0;padding-left:18px;color:#374151;font-size:13px;line-height:1.6;">' . $items . '</ul></div>';
                                    }

                                    return new HtmlString('
                                        <div style="font-family:sans-serif;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;max-width:600px;">
                                            <div style="display:flex;align-items:center;gap:16px;">
                                                <div style="flex-shrink:0;width:64px;height:64px;border-radius:50%;background:' . $color . ';display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;font-weight:700;">' . $score . '</div>
                                                <div>
                                                    <div style="font-size:20px;font-weight:700;color:' . $color . ';">' . $label . '</div>
                                                    <div style="font-size:12px;color:#6b7280;">AI Engagement Potential Score out of 100</div>
                                                </div>
                                            </div>
                                            ' . $notesHtml . '
                                        </div>
                                    ');
                                })
                                ->columnSpanFull(),
                        ]),

                    Section::make('🔍 Google Competition')
                        ->description('Research what content exists on this survey topic — so you can ask better, more relevant questions.')
                        ->collapsed()
                        ->schema([
                            Textarea::make('_competition_json')->dehydrated(false)->hidden(),

                            \Filament\Schemas\Components\Actions::make([
                                Action::make('research_competition_survey')
                                    ->label('Research Topic')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->color('gray')
                                    ->action(function ($get, $set) {
                                        $query = trim($get('title') ?: '');
                                        if (blank($query)) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('Enter a survey title first.')
                                                ->body('The survey title is used as the search query.')
                                                ->warning()->send();
                                            return;
                                        }
                                        try {
                                            $results = self::fetchCompetitionData($query);
                                            $set('_competition_json', json_encode($results));
                                            if (empty($results)) {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('No results returned.')
                                                    ->body('Check your Google CSE settings in Business Settings → SEO & Analytics.')
                                                    ->warning()->send();
                                            }
                                        } catch (\Throwable $e) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('Research failed')
                                                ->body($e->getMessage())
                                                ->danger()->send();
                                        }
                                    }),
                            ]),

                            Placeholder::make('_competition_preview')
                                ->label('')
                                ->content(function ($get) {
                                    $json = $get('_competition_json') ?? '';
                                    if (blank($json)) {
                                        return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">Click "Research Topic" to see what content already exists on this topic.</p>');
                                    }
                                    $items = json_decode($json, true) ?: [];
                                    if (empty($items)) {
                                        return new HtmlString('<p style="color:#9ca3af;font-style:italic;font-size:13px;">No results found for this query.</p>');
                                    }
                                    $cards = '';
                                    foreach ($items as $i => $item) {
                                        $pos     = $i + 1;
                                        $title   = e($item['title']   ?? '');
                                        $url     = e($item['url']     ?? '');
                                        $domain  = e($item['domain']  ?? '');
                                        $snippet = e($item['snippet'] ?? '');
                                        $cards  .= '
                                            <div style="padding:12px 14px;background:#fff;border-radius:8px;border:1px solid #e5e7eb;">
                                                <div style="font-size:11px;color:#6b7280;margin-bottom:2px;">#' . $pos . ' &nbsp;·&nbsp; ' . $domain . '</div>
                                                <a href="' . $url . '" target="_blank" rel="noopener" style="font-size:15px;color:#1a0dab;text-decoration:none;font-weight:500;line-height:1.3;">' . $title . '</a>
                                                <div style="font-size:13px;color:#545454;margin-top:5px;line-height:1.5;">' . $snippet . '</div>
                                            </div>';
                                    }
                                    return new HtmlString('<div style="font-family:arial,sans-serif;display:flex;flex-direction:column;gap:10px;max-width:680px;">' . $cards . '</div>');
                                })
                                ->columnSpanFull(),
                        ]),

                ]), // end Preview tab

                // ── Tab 5: Responses ──────────────────────────────────────
                Tab::make('Responses')->icon('heroicon-o-inbox')->schema([

                    Section::make('Response Summary')
                        ->schema([
                            Placeholder::make('response_summary')
                                ->label('')
                                ->content(function ($record) {
                                    if (!$record?->id) return new HtmlString('<p style="color:#9ca3af;font-size:13px">Save the survey first to see responses.</p>');

                                    $total     = $record->responses()->count();
                                    $completed = $record->responses()->whereNotNull('completed_at')->count();
                                    $rate      = $total > 0 ? round(($completed/$total)*100,1) : 0;

                                    return new HtmlString('
                                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">
                                            <div style="text-align:center;padding:16px;background:#f9fafb;border-radius:10px;border:1px solid #e5e7eb">
                                                <div style="font-size:28px;font-weight:900;color:#2563eb">'.$total.'</div>
                                                <div style="font-size:11px;color:#6b7280;margin-top:4px;text-transform:uppercase;letter-spacing:.05em">Total</div>
                                            </div>
                                            <div style="text-align:center;padding:16px;background:#f9fafb;border-radius:10px;border:1px solid #e5e7eb">
                                                <div style="font-size:28px;font-weight:900;color:#059669">'.$completed.'</div>
                                                <div style="font-size:11px;color:#6b7280;margin-top:4px;text-transform:uppercase;letter-spacing:.05em">Completed</div>
                                            </div>
                                            <div style="text-align:center;padding:16px;background:#f9fafb;border-radius:10px;border:1px solid #e5e7eb">
                                                <div style="font-size:28px;font-weight:900;color:#d97706">'.$rate.'%</div>
                                                <div style="font-size:11px;color:#6b7280;margin-top:4px;text-transform:uppercase;letter-spacing:.05em">Completion</div>
                                            </div>
                                            <div style="text-align:center;padding:16px;background:#f9fafb;border-radius:10px;border:1px solid #e5e7eb">
                                                <div style="font-size:28px;font-weight:900;color:#7c3aed">'.($record->response_limit ? $record->response_limit - $total : '∞').'</div>
                                                <div style="font-size:11px;color:#6b7280;margin-top:4px;text-transform:uppercase;letter-spacing:.05em">Remaining</div>
                                            </div>
                                        </div>
                                    ');
                                })
                                ->columnSpanFull(),
                        ]),

                ]),

                // ── Tab 6: Analytics ──────────────────────────────────────
                Tab::make('Analytics')->icon('heroicon-o-chart-bar')->schema([

                    Section::make('Question-by-Question Results')
                        ->schema([
                            Placeholder::make('analytics')
                                ->label('')
                                ->content(function ($record) {
                                    if (!$record?->id) return new HtmlString('<p style="color:#9ca3af;font-size:13px">Save the survey first to view analytics.</p>');

                                    $record->loadMissing('questions.answers');
                                    $html = '';

                                    foreach ($record->questions as $qi => $q) {
                                        $answers = $q->answers()->with('response')->whereHas('response', fn($r) => $r->whereNotNull('completed_at'))->get();
                                        $count   = $answers->count();

                                        $html .= '<div style="margin-bottom:28px;padding:20px;background:#f9fafb;border-radius:12px;border:1px solid #e5e7eb">';
                                        $html .= '<div style="display:flex;align-items:baseline;gap:8px;margin-bottom:12px">';
                                        $html .= '<span style="font-size:12px;font-weight:700;color:#9ca3af">Q' . ($qi+1) . '</span>';
                                        $html .= '<span style="font-size:15px;font-weight:600;color:#111827">' . e($q->question) . '</span>';
                                        $html .= '<span style="margin-left:auto;font-size:11px;color:#9ca3af;white-space:nowrap">' . $count . ' ' . ($count===1?'response':'responses') . '</span>';
                                        $html .= '</div>';

                                        if ($count === 0) {
                                            $html .= '<p style="color:#9ca3af;font-size:12px;font-style:italic">No responses yet.</p>';
                                        } elseif (in_array($q->type, ['single_choice','multiple_choice','dropdown','yes_no'])) {
                                            // Tally choices
                                            $tally = [];
                                            foreach ($answers as $a) {
                                                foreach ((array)($a->answer) as $val) {
                                                    $tally[$val] = ($tally[$val] ?? 0) + 1;
                                                }
                                            }
                                            arsort($tally);
                                            $maxVotes = max($tally) ?: 1;
                                            foreach ($tally as $option => $votes) {
                                                $pct = round(($votes/$count)*100);
                                                $html .= '<div style="margin-bottom:10px">';
                                                $html .= '<div style="display:flex;justify-content:space-between;font-size:12px;color:#374151;margin-bottom:4px">';
                                                $html .= '<span>' . e($option) . '</span><span style="font-weight:700">' . $votes . ' (' . $pct . '%)</span></div>';
                                                $html .= '<div style="height:8px;background:#e5e7eb;border-radius:99px;overflow:hidden">';
                                                $html .= '<div style="height:100%;width:' . round(($votes/$maxVotes)*100) . '%;background:#f59e0b;border-radius:99px"></div></div></div>';
                                            }
                                        } elseif (in_array($q->type, ['rating','nps'])) {
                                            // Average + distribution
                                            $vals = [];
                                            foreach ($answers as $a) {
                                                $v = (int)(is_array($a->answer) ? ($a->answer[0] ?? 0) : $a->answer);
                                                $vals[] = $v;
                                            }
                                            $avg  = count($vals) ? round(array_sum($vals)/count($vals),1) : 0;
                                            $max  = $q->type === 'nps' ? 10 : 5;
                                            $html .= '<div style="font-size:32px;font-weight:900;color:#f59e0b;margin-bottom:8px">' . $avg . ' <span style="font-size:16px;color:#9ca3af">/ ' . $max . '</span></div>';
                                            $tally = array_count_values($vals);
                                            for ($i = $max; $i >= 1; $i--) {
                                                $v = $tally[$i] ?? 0;
                                                $html .= '<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">';
                                                $html .= '<span style="font-size:11px;color:#6b7280;width:20px;text-align:right">' . $i . '</span>';
                                                $html .= '<div style="flex:1;height:6px;background:#e5e7eb;border-radius:99px;overflow:hidden">';
                                                $html .= '<div style="height:100%;width:' . ($count>0?round(($v/$count)*100):0) . '%;background:#f59e0b;border-radius:99px"></div></div>';
                                                $html .= '<span style="font-size:11px;color:#6b7280;width:28px">' . $v . '</span></div>';
                                            }
                                        } elseif (in_array($q->type, ['text_short','text_long'])) {
                                            // Show last 5 text responses
                                            $html .= '<div style="display:flex;flex-direction:column;gap:6px">';
                                            foreach ($answers->take(8) as $a) {
                                                $text = is_array($a->answer) ? ($a->answer[0] ?? '') : $a->answer;
                                                $html .= '<div style="padding:8px 12px;background:#fff;border-radius:6px;border:1px solid #e5e7eb;font-size:12px;color:#374151">' . e($text) . '</div>';
                                            }
                                            if ($answers->count() > 8) {
                                                $html .= '<p style="font-size:11px;color:#9ca3af">+ ' . ($answers->count()-8) . ' more responses</p>';
                                            }
                                            $html .= '</div>';
                                        }

                                        $html .= '</div>';
                                    }

                                    return new HtmlString($html ?: '<p style="color:#9ca3af;font-size:13px">No questions added yet.</p>');
                                })
                                ->columnSpanFull(),
                        ]),

                ]),

            ])->columnSpanFull()->persistTabInQueryString(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->weight('semibold')
                    ->description(fn($record) => $record?->slug),

                TextColumn::make('status')->badge()
                    ->color(fn($state) => match($state) {
                        'active' => 'success', 'closed' => 'danger', default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'active' => '🟢 Active', 'closed' => '🔴 Closed', default => '📝 Draft',
                    }),

                TextColumn::make('responses_count')
                    ->label('Responses')
                    ->getStateUsing(fn($record) => $record->responses()->count())
                    ->badge()->color('info'),

                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->getStateUsing(fn($record) => $record->questions()->count()),

                TextColumn::make('ends_at')->label('Closes')->dateTime('d M Y')->sortable()->placeholder('No end date'),
                TextColumn::make('created_at')->label('Created')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(['draft'=>'Draft','active'=>'Active','closed'=>'Closed']),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('share_whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(fn($record) =>
                        'https://wa.me/?text=' . urlencode(
                            "Hi! We'd love your feedback 🙏\n\n" .
                            "Please take 2–3 minutes to complete this survey from Artisan Leather:\n\n" .
                            "📋 *{$record->title}*\n" .
                            ($record->description ? "_{$record->description}_\n\n" : "\n") .
                            "👉 https://artisanleatherom.com/survey/{$record->slug}\n\n" .
                            "Your input helps us improve. Thank you! 🌟"
                        )
                    )
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->status === 'active'),

                Action::make('copy_link')
                    ->label('Copy Link')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->action(function ($record, $livewire) {
                        $url = "https://artisanleatherom.com/survey/{$record->slug}";
                        $livewire->dispatch('copy-to-clipboard', text: $url);
                    })
                    ->extraAttributes(fn($record) => [
                        'x-data' => '{}',
                        'x-on:copy-to-clipboard.window' => "
                            navigator.clipboard.writeText(\$event.detail.text);
                            \$el.textContent = '✓ Copied!';
                            setTimeout(() => \$el.textContent = 'Copy Link', 2000);
                        ",
                    ]),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    private static function resolveAiFilePaths(mixed $files): array
    {
        $paths = [];
        foreach ((array) $files as $relativePath) {
            if (blank($relativePath)) continue;
            $abs = Storage::disk('local')->path($relativePath);
            if (file_exists($abs)) {
                $paths[] = $abs;
            }
        }
        return $paths;
    }

    private static function fillAiFields(\Closure $set, array $data): void
    {
        $set('title',                $data['title']                ?? '');
        $set('slug',                 Str::slug($data['title']      ?? ''));
        $set('description',          $data['description']          ?? '');
        $set('description_ar',       $data['description_ar']       ?? '');
        $set('thank_you_message',    $data['thank_you_message']    ?? '');
        $set('thank_you_message_ar', $data['thank_you_message_ar'] ?? '');
        $set('_seo_score',           (string) ($data['seo_score']  ?? 0));
        $set('_seo_notes',           $data['seo_notes']            ?? '');

        $questions = [];
        foreach ($data['questions'] ?? [] as $i => $q) {
            $questions[(string) Str::uuid()] = [
                'type'        => $q['type']        ?? 'text_short',
                'question'    => $q['question']    ?? '',
                'question_ar' => $q['question_ar'] ?? '',
                'description' => $q['description'] ?? '',
                'options'     => $q['options']     ?? [],
                'options_ar'  => $q['options_ar']  ?? [],
                'is_required' => $q['is_required'] ?? true,
                'sort_order'  => $q['sort_order']  ?? $i,
            ];
        }
        $set('questions', $questions);
    }

    private static function fetchCompetitionData(string $query): array
    {
        $flat = Setting::pluck('value', 'key')->toArray();
        $key  = $flat['seo.google_cse_key'] ?? config('services.google_cse.key');
        $cx   = $flat['seo.google_cse_id']  ?? config('services.google_cse.cx');

        if (blank($key) || blank($cx)) {
            throw new \RuntimeException('Google Custom Search is not configured. Add your API Key and Engine ID in Business Settings → SEO & Analytics.');
        }

        $response = Http::timeout(10)->get('https://www.googleapis.com/customsearch/v1', [
            'key' => $key,
            'cx'  => $cx,
            'q'   => $query,
            'num' => 5,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Google search failed: ' . ($response->json('error.message') ?? $response->status()));
        }

        $results = [];
        foreach ($response->json('items', []) as $item) {
            $url       = $item['link'] ?? '';
            $results[] = [
                'title'   => $item['title']   ?? '',
                'url'     => $url,
                'domain'  => parse_url($url, PHP_URL_HOST) ?: $url,
                'snippet' => $item['snippet'] ?? '',
            ];
        }
        return $results;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSurveys::route('/'),
            'create' => Pages\CreateSurvey::route('/create'),
            'edit'   => Pages\EditSurvey::route('/{record}/edit'),
        ];
    }
}
