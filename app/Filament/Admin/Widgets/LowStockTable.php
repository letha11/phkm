<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Medicine;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockTable extends BaseWidget
{
    protected static ?string $heading = 'Low Stock Alert';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Medicine::query()
                    ->where('stock', '<', 20)
                    ->orderBy('stock', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Medicine Name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tablet' => 'success',
                        'capsule' => 'info',
                        'syrup' => 'warning',
                        'injection' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('stock')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 10 => 'warning',
                        $state <= 20 => 'info',
                        default => 'success',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Medicine $record): string => route('filament.admin.resources.medicines.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Great! No low stock medicines')
            ->emptyStateDescription('All medicines have sufficient stock levels.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
} 