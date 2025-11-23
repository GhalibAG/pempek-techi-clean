<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- KRITIS (1)
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory; // <-- KRITIS (2)

    protected $fillable = [
        'name',
        'description',
        'price',
        'cost_price', // HPP
    ];

    /**
     * Relasi ke Stok (dipakai di ProductResource untuk lihat stok)
     */
    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    /**
     * Relasi ke detail item transaksi
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
