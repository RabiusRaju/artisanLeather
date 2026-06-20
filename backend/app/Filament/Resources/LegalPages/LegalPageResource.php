<?php

namespace App\Filament\Resources\LegalPages;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\LegalPages\Pages;
use App\Models\LegalPage;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class LegalPageResource extends Resource
{
    protected static ?string $model = LegalPage::class;

    public static function getNavigationIcon(): string  { return 'heroicon-o-scale'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Content->value; }
    public static function getNavigationSort(): int     { return 5; }
    public static function getNavigationLabel(): string { return 'Legal Pages'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Tabs::make()->tabs([

                Tab::make('Content')->icon('heroicon-o-document-text')->schema([

                    Section::make('Page')
                        ->columns(2)
                        ->schema([
                            Select::make('slug')
                                ->label('Page')
                                ->options([
                                    'privacy' => 'Privacy Policy (/privacy)',
                                    'terms'   => 'Terms of Service (/terms)',
                                ])
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->helperText('Which frontend page this content appears on.'),

                            TextInput::make('last_updated')
                                ->label('Last Updated Label')
                                ->placeholder('e.g. June 2026'),

                            TextInput::make('title')
                                ->label('Page Title (English)')
                                ->required(),

                            TextInput::make('title_ar')
                                ->label('Page Title (Arabic)')
                                ->extraInputAttributes(['dir' => 'rtl']),
                        ]),

                    Section::make('Sections')
                        ->description('Add, edit, reorder, or remove sections freely. Each section gets a heading and a body paragraph.')
                        ->schema([
                            Repeater::make('sections')
                                ->label('')
                                ->reorderable()
                                ->collapsible()
                                ->addActionLabel('Add Section')
                                ->itemLabel(fn (array $state) => $state['heading'] ?? 'New Section')
                                ->schema([
                                    TextInput::make('heading')
                                        ->label('Heading (English)')
                                        ->required(),
                                    TextInput::make('heading_ar')
                                        ->label('Heading (Arabic, optional)')
                                        ->extraInputAttributes(['dir' => 'rtl']),
                                    Textarea::make('body')
                                        ->label('Body (English)')
                                        ->rows(4)
                                        ->required()
                                        ->columnSpanFull(),
                                    Textarea::make('body_ar')
                                        ->label('Body (Arabic, optional)')
                                        ->rows(4)
                                        ->extraAttributes(['dir' => 'rtl'])
                                        ->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ]),
                ]),

                Tab::make('Preview')->icon('heroicon-o-eye')->schema([
                    Section::make('Website Preview')
                        ->description('Exactly how this page will look to visitors. Note: the final "Contact Us" / return-policy details shown live also pull from Business Settings, not shown here.')
                        ->schema([
                            Placeholder::make('legal_preview')
                                ->label('')
                                ->content(function ($get) {
                                    $title    = $get('title') ?: 'Page Title';
                                    $updated  = $get('last_updated') ?: '';
                                    $sections = array_values($get('sections') ?? []);

                                    $html  = '<div style="font-family:Georgia,serif;max-width:680px;background:#120D05;border:1px solid rgba(212,175,55,0.18);border-radius:8px;overflow:hidden;">';
                                    $html .= '<div style="padding:28px 32px;border-bottom:1px solid rgba(212,175,55,0.12);background:#1E1508;">';
                                    $html .= '<p style="margin:0 0 6px;font-size:10px;letter-spacing:3px;text-transform:uppercase;color:rgba(212,175,55,0.6);">Legal</p>';
                                    $html .= '<h1 style="margin:0;font-size:26px;font-weight:300;color:#F5EDD8;">' . e($title) . '</h1>';
                                    if ($updated) {
                                        $html .= '<p style="margin:10px 0 0;font-size:11px;color:rgba(255,255,255,0.3);">Last updated: ' . e($updated) . '</p>';
                                    }
                                    $html .= '</div>';

                                    $html .= '<div style="padding:28px 32px;">';
                                    if (empty($sections)) {
                                        $html .= '<p style="color:#9ca3af;font-style:italic;font-size:13px;">Add sections in the Content tab to see the preview.</p>';
                                    }
                                    foreach ($sections as $i => $s) {
                                        $heading = e($s['heading'] ?? '');
                                        $body    = nl2br(e($s['body'] ?? ''));
                                        $border  = $i === 0 ? '' : 'border-top:1px solid rgba(255,255,255,0.08);';
                                        $html .= '<div style="' . $border . 'padding:20px 0;">';
                                        $html .= '<h2 style="margin:0 0 10px;font-size:17px;font-weight:400;color:#fff;">' . $heading . '</h2>';
                                        $html .= '<div style="font-size:13px;line-height:1.7;color:rgba(255,255,255,0.5);">' . $body . '</div>';
                                        $html .= '</div>';
                                    }
                                    $html .= '</div></div>';

                                    return new HtmlString($html);
                                })
                                ->columnSpanFull(),
                        ]),
                ]),

            ])->columnSpanFull(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Page')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('URL')
                    ->formatStateUsing(fn (string $state) => '/' . $state)
                    ->fontFamily('mono')
                    ->copyable(),

                TextColumn::make('sections')
                    ->label('Sections')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' sections' : '0 sections'),

                TextColumn::make('last_updated')
                    ->label('Last Updated')
                    ->placeholder('—'),

                TextColumn::make('updated_at')
                    ->label('Saved')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('No legal pages yet')
            ->emptyStateDescription('Create Privacy Policy and Terms of Service content here.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLegalPages::route('/'),
            'create' => Pages\CreateLegalPage::route('/create'),
            'edit'   => Pages\EditLegalPage::route('/{record}/edit'),
        ];
    }
}
