<?php

namespace App\Filament\Resources\ProductShareLinks;

use App\Enums\NavigationGroupEnum;
use App\Filament\Resources\ProductShareLinks\Pages;
use App\Models\Product;
use App\Models\ProductShareLink;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ProductShareLinkResource extends Resource
{
    protected static ?string $model = ProductShareLink::class;

    public static function getNavigationIcon(): string  { return 'heroicon-o-link'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Sales->value; }
    public static function getNavigationSort(): int     { return 11; }
    public static function getNavigationLabel(): string { return 'Product Share Links'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Share Link')
                ->description('Pick the products to include, then share the generated link with a customer or friend — no login required to view it.')
                ->schema([
                    TextInput::make('name')
                        ->label('Name (for your reference only)')
                        ->placeholder('e.g. Eid Gift Picks for Ahmed')
                        ->columnSpanFull(),

                    Select::make('product_ids')
                        ->label('Products')
                        ->options(fn () => Product::where('is_active', true)
                            ->with('images')
                            ->get()
                            ->mapWithKeys(fn (Product $product) => [$product->id => self::productOptionHtml($product)]))
                        ->allowHtml()
                        ->multiple()
                        ->searchable()
                        ->required()
                        ->columnSpanFull(),

                    DateTimePicker::make('expires_at')
                        ->label('Expires At (optional)')
                        ->native(false)
                        ->seconds(false)
                        ->helperText('Leave blank for a link that never expires.')
                        ->columnSpanFull(),

                    Placeholder::make('share_url')
                        ->label('Shareable Link')
                        ->visible(fn ($record) => $record !== null)
                        ->content(fn ($record) => $record
                            ? new HtmlString('<a href="https://artisanleatherom.com/share/' . $record->token . '" target="_blank" style="color:#C9A84C;">https://artisanleatherom.com/share/' . $record->token . '</a>')
                            : null)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->placeholder('(untitled)')
                    ->searchable(),

                TextColumn::make('product_ids')
                    ->label('Products')
                    ->state(function ($record) {
                        $count = count($record->product_ids ?? []);
                        return $count . ' product' . ($count === 1 ? '' : 's');
                    })
                    ->tooltip(function ($record) {
                        $ids = $record->product_ids ?? [];
                        if (empty($ids)) {
                            return null;
                        }
                        $names = Product::whereIn('id', $ids)->pluck('name', 'id');
                        return collect($ids)->map(fn ($id) => $names[$id] ?? null)->filter()->implode(', ');
                    }),

                TextColumn::make('token')
                    ->label('Link')
                    ->formatStateUsing(fn ($state) => 'artisanleatherom.com/share/' . $state)
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyableState(fn ($state) => 'https://artisanleatherom.com/share/' . $state),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('d M Y, h:i A')
                    ->placeholder('Never')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn ($record) => 'https://artisanleatherom.com/share/' . $record->token)
                    ->openUrlInNewTab(),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No share links yet')
            ->emptyStateDescription('Create a link to share a curated set of products with a customer or friend.');
    }

    protected static function productOptionHtml(Product $product): string
    {
        $imagePath = $product->images->first()?->url;
        $imageUrl  = $imagePath
            ? (str_starts_with($imagePath, 'http') ? $imagePath : asset('storage/' . $imagePath))
            : null;

        $thumb = $imageUrl
            ? '<img src="' . e($imageUrl) . '" style="width:28px;height:28px;object-fit:cover;border-radius:4px;vertical-align:middle;margin-right:8px;" />'
            : '<span style="display:inline-block;width:28px;height:28px;background:#1A1208;border-radius:4px;vertical-align:middle;margin-right:8px;"></span>';

        return $thumb . e($product->name);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProductShareLinks::route('/'),
            'create' => Pages\CreateProductShareLink::route('/create'),
            'edit'   => Pages\EditProductShareLink::route('/{record}/edit'),
        ];
    }
}
