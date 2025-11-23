<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Stock;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    // Kita HAPUS mutateFormDataBeforeCreate karena total_amount
    // sudah dikirim otomatis dari Form yang kita buat di atas.

    // Tapi kita tetap butuh mengisi User ID kasir
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

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
