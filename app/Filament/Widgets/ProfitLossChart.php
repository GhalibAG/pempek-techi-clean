<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ProfitLossChart extends ChartWidget
{
    protected static ?string $heading = 'Perbandingan Pemasukan vs Pengeluaran (7 Hari)';

    protected static ?int $sort = 2; // Agar muncul di samping StatsOverview

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $dates = collect(range(0, 6))->map(fn ($i) => Carbon::now()->subDays($i)->format('Y-m-d'));

        $incomeData = $dates->map(fn ($date) => Transaction::whereDate('created_at', $date)->sum('total_amount'));
        $expenseData = $dates->map(fn ($date) => Expense::whereDate('date', $date)->sum('amount'));

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeData->reverse()->values(),
                    'backgroundColor' => '#2563EB', // Biru
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData->reverse()->values(),
                    'backgroundColor' => '#DC2626', // Merah
                ],
            ],
            'labels' => $dates->map(fn ($date) => Carbon::parse($date)->format('D'))->reverse()->values(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Tipe Chart: Bar/Histogram
    }

    public static function canView(): bool
    {
        // Hanya izinkan Owner melihat data keuangan sensitif
        return auth()->user()->role === 'owner';
    }
}
