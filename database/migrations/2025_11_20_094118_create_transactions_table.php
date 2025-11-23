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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // Mencatat Admin/Kasir yang input (constrained ke tabel users)
            $table->foreignId('user_id')->constrained();
            $table->integer('total_amount')->default(0);
            $table->timestamps(); // Created_at otomatis jadi Tanggal Transaksi
            $table->string('source_type')->nullable(); // Contoh: 'GRAB', 'GOJEK', 'WA', 'DINE_IN'
            $table->string('location_notes')->nullable(); // Lokasi spesifik atau nama kustomer
            $table->text('general_notes')->nullable(); // Catatan tambahan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
