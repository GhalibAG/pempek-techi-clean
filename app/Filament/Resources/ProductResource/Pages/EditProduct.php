<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Stock;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol Edit default (yang ada di kanan atas)
            Actions\DeleteAction::make(),

            // --- TOMBOL BARU: TAMBAH STOK ---
            Action::make('add_stock')
                ->label('➕ Tambah Stok')
                ->color('success')
                ->icon('heroicon-o-plus')
                ->form([
                    TextInput::make('amount_added')
                        ->label('Jumlah Stok yang Ditambahkan')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $product = $this->getRecord(); // Ambil data produk yang sedang diedit

                    // Logic: Jika stok sudah ada, tambahkan. Jika belum, buat record baru.
                    Stock::updateOrCreate(
                        ['product_id' => $product->id],
                        [
                            'quantity' => \DB::raw('quantity + '.$data['amount_added']),
                        ]
                    );

                    // Kirim notifikasi sukses
                    \Filament\Notifications\Notification::make()
                        ->title('Stok berhasil diperbarui!')
                        ->success()
                        ->send();
                }),
        ];
    }
}
