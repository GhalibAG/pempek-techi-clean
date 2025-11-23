<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Widgets\ChartWidget;

// Library untuk analisis tren (Filament dependency)

class ExpensesChart extends ChartWidget
{
    protected static ?string $heading = 'Komposisi Pengeluaran (30 Hari)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Ambil data pengeluaran dan kelompokkan
        $data = Expense::selectRaw('description, sum(amount) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('description')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Pengeluaran',
                    'data' => $data->pluck('total'), // Nilai nominal
                    // Kita berikan warna default (Filament akan mencocokkan)
                ],
            ],
            'labels' => $data->pluck('description'), // Label di chart (misal: Beli Ikan)
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // Tipe Chart: Donut/Pie
    }
}
