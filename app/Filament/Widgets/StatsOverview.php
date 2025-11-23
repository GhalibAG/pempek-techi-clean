<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use NumberFormatter; // Tambahkan untuk format Rupiah

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // PENTING: Untuk Laba Bersih, kita hitung Pemasukan dan Pengeluaran
        $incomeMonth = Transaction::whereMonth('created_at', now()->month)->sum('total_amount');
        $expenseMonth = Expense::whereMonth('date', now()->month)->sum('amount');
        $profitMonth = $incomeMonth - $expenseMonth;

        // Format Rupiah
        $formatter = new NumberFormatter('id_ID', NumberFormatter::CURRENCY);

        return [
            Stat::make('Total Produk', Product::count())
                ->description('Menu terdaftar')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            Stat::make('Transaksi Hari Ini', Transaction::whereDate('created_at', today())->count())
                ->description('Nota berhasil dicetak')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('Laba Bersih Bulan Ini', $formatter->formatCurrency($profitMonth, 'IDR'))
                ->description('Pemasukan - Pengeluaran')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color($profitMonth >= 0 ? 'success' : 'danger'),
        ];
    }
}
