<?php

namespace App\Filament\Widgets;

// <-- PENTING
use App\Models\Product;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockAlert extends BaseWidget
{
    protected static ?string $heading = '🚨 Peringatan Stok Rendah (<= 10)';

    protected static ?int $sort = 3;

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
                // Action Update Stok (Versi Popup - Aman buat Admin)
                Tables\Actions\Action::make('updateStock')
                    ->label('Isi Stok')
                    ->icon('heroicon-o-circle-stack')
                    ->color('warning')
                    // IZIN KHUSUS: Admin dan Owner BOLEH
                    ->visible(fn (): bool => auth()->user()->role === 'admin' || auth()->user()->role === 'owner')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Stok Baru')
                            ->numeric()
                            ->required(),
                    ])
                    ->action(function (Product $record, array $data): void {
                        // Logic simpan ke database
                        Stock::updateOrCreate(
                            ['product_id' => $record->id],
                            ['quantity' => $data['quantity']]
                        );

                        // Notifikasi
                        \Filament\Notifications\Notification::make()
                            ->title('Stok berhasil diupdate')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('sm'),
            ]);
    }
}
