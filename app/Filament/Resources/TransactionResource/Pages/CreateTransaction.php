<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Product;
use App\Models\Stock; // Tambahkan ini
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth; // <-- PENTING

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        // --- VALIDASI STOK KRITIS ---
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                $requestedQuantity = (int) ($item['quantity'] ?? 0);

                // Ambil stok saat ini (pastikan relasi stock ada)
                $currentStock = $product->stock->quantity ?? 0;

                // Cek: Jika stok saat ini KURANG dari jumlah yang diminta
                if ($currentStock < $requestedQuantity) {

                    // Batalkan transaksi dan kirim notifikasi error
                    Notification::make()
                        ->title('Stok Tidak Mencukupi!')
                        ->body("Stok untuk {$product->name} tersisa $currentStock pcs. Transaksi dibatalkan.")
                        ->danger()
                        ->persistent()
                        ->send();

                    // Menghentikan proses penyimpanan
                    throw new \Exception("Stok tidak cukup untuk {$product->name}.");
                }
            }
        }
        // --- AKHIR VALIDASI ---

        return $data;
    }

    // Logic kurangi stok tetap kita pertahankan
    protected function afterCreate(): void
    {
        $transaction = $this->record;

        foreach ($transaction->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)->first();
            if ($stock) {
                $stock->decrement('quantity', $item->quantity);
            } else {
                Stock::create([
                    'product_id' => $item->product_id,
                    'quantity' => -$item->quantity,
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
