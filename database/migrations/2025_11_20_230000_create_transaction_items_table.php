<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            // Hubungkan ke kepala nota
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            // Hubungkan ke produk
            $table->foreignId('product_id')->constrained();

            $table->integer('quantity');
            $table->integer('price_at_transaction'); // Harga saat dibeli (penting utk sejarah)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
