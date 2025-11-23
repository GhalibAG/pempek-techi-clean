<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockAlert extends BaseWidget
{
    protected static ?string $heading = '🚨 Peringatan Stok Rendah (<= 10)';

    protected static ?int $sort = 1;

    // PENTING: Atur lebar agar dia mengisi ruang kosong di sebelah chart
    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Query: Ambil produk yang stoknya 10 atau kurang
                Product::query()
                    ->whereHas('stock', fn (Builder $query) => $query->where('quantity', '<=', 10))
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produk'),
                Tables\Columns\TextColumn::make('stock.quantity')
                    ->label('Sisa Stok')
                    ->weight('bold')
                    ->color('danger'),
            ])
            ->actions([
                // Kita bisa tambahkan tombol cepat untuk Edit Stok di sini
                Tables\Actions\Action::make('quick_stock')
                    ->label('Isi Stok')
                    ->icon('heroicon-o-arrow-path')
                    ->url(fn (Product $record) => \App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
