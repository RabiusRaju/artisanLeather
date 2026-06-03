<?php
namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersTable extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Orders')
            ->query(Order::latest()->limit(10))
            ->columns([
                TextColumn::make('order_number')->weight('bold'),
                TextColumn::make('first_name')->label('Name')
                    ->formatStateUsing(fn($record) => $record->first_name . ' ' . $record->last_name),
                TextColumn::make('total_omr')->label('Total (OMR)'),
                TextColumn::make('payment_method')->badge()
                    ->color(fn($state) => match($state) {
                        'cod' => 'warning', 'bank' => 'info', 'whatsapp' => 'success', default => 'gray'
                    }),
                TextColumn::make('status')->badge()
                    ->color(fn($state) => match($state) {
                        'pending' => 'warning', 'confirmed' => 'info', 'shipped' => 'success',
                        'delivered' => 'success', 'cancelled' => 'danger', default => 'gray'
                    }),
                TextColumn::make('created_at')->dateTime()->label('Date'),
            ])
            ->paginated(false);
    }
}
