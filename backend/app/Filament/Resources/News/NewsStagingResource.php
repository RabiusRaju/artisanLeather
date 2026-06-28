<?php

namespace App\Filament\Resources\News;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\Blog\PostResource;
use App\Filament\Resources\News\Pages;
use App\Models\NewsStagingItem;
use App\Models\Post;
use App\Services\AiPostService;
use App\Services\NewsFeedScraperService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class NewsStagingResource extends Resource
{
    protected static ?string $model = NewsStagingItem::class;

    public static function getNavigationIcon(): string  { return 'heroicon-o-rss'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Content->value; }
    public static function getNavigationSort(): int     { return 2; }
    public static function getNavigationLabel(): string { return 'News Staging'; }

    public static function getNavigationBadge(): ?string
    {
        $count = NewsStagingItem::where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->limit(60)->searchable()->wrap(),
                TextColumn::make('source_name')->label('Source')->searchable(),
                TextColumn::make('published_at')->label('Published')->dateTime('d M Y')->sortable()->placeholder('—'),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'new' => 'warning',
                    'generated' => 'success',
                    'dismissed' => 'gray',
                    default => 'gray',
                }),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(['new' => 'New', 'generated' => 'Generated', 'dismissed' => 'Dismissed'])
                    ->default('new'),

                SelectFilter::make('source_name')
                    ->label('Source')
                    ->options(fn () => NewsStagingItem::query()
                        ->distinct()
                        ->orderBy('source_name')
                        ->pluck('source_name', 'source_name')
                        ->toArray()),

                Filter::make('published_at')
                    ->label('Published Date')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('from'),
                            DatePicker::make('until'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('published_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('published_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'From ' . \Illuminate\Support\Carbon::parse($data['from'])->format('d M Y');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until ' . \Illuminate\Support\Carbon::parse($data['until'])->format('d M Y');
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                Action::make('view_source')
                    ->label('View Source')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (NewsStagingItem $record) => $record->article_url)
                    ->openUrlInNewTab(),

                Action::make('generate')
                    ->label('Generate Article')
                    ->icon('heroicon-o-sparkles')
                    ->color('warning')
                    ->visible(fn (NewsStagingItem $record) => $record->status === 'new')
                    ->requiresConfirmation()
                    ->modalHeading('Generate curated article')
                    ->modalDescription('AI will fetch the source article and write a short curated summary (with attribution) as a draft post for your review. This takes about 20-30 seconds.')
                    ->action(function (NewsStagingItem $record) {
                        try {
                            $data = app(AiPostService::class)->curateNewsArticle('openai', $record);

                            $post = Post::create([
                                'title'            => $data['title'],
                                'slug'             => Str::slug($data['title']) . '-' . $record->id,
                                'excerpt'          => $data['excerpt'],
                                'content'          => $data['content'],
                                'title_ar'         => $data['title_ar'],
                                'excerpt_ar'       => $data['excerpt_ar'],
                                'content_ar'       => $data['content_ar'],
                                'title_bn'         => $data['title_bn'],
                                'excerpt_bn'       => $data['excerpt_bn'],
                                'content_bn'       => $data['content_bn'],
                                'tags'             => $data['tags'],
                                'category'         => $data['category'],
                                'read_time'        => $data['read_time'],
                                'meta_title'       => $data['meta_title'],
                                'meta_description' => $data['meta_description'],
                                'is_published'     => false,
                            ]);

                            $record->update(['status' => 'generated', 'generated_post_id' => $post->id]);

                            Notification::make()
                                ->title('Article generated as a draft')
                                ->body('Review it before publishing.')
                                ->success()
                                ->actions([
                                    Action::make('view')
                                        ->label('Review Draft')
                                        ->url(PostResource::getUrl('edit', ['record' => $post]))
                                        ->button(),
                                ])
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Generation failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('dismiss')
                    ->label('Dismiss')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->visible(fn (NewsStagingItem $record) => $record->status === 'new')
                    ->requiresConfirmation()
                    ->action(fn (NewsStagingItem $record) => $record->update(['status' => 'dismissed'])),
            ])
            ->headerActions([
                Action::make('sync_now')
                    ->label('Sync Now')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        try {
                            $imported = app(NewsFeedScraperService::class)->syncFeeds();
                            Notification::make()->title("Synced — {$imported} new item(s) found")->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Sync failed')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsStagingItems::route('/'),
        ];
    }
}
