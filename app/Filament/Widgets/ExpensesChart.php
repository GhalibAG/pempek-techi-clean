<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Widgets\ChartWidget;

// Library untuk analisis tren (Filament dependency)

class ExpensesChart extends ChartWidget
{
    protected static ?string $heading = 'Komposisi Pengeluaran (30 Hari)';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $data = Expense::selectRaw('description, sum(amount) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('description')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pengeluaran',
                    'data' => $data->pluck('total'),
                    // === PERBAIKAN WARNA (Palette Tetap) ===
                    'backgroundColor' => [
                        '#ef4444', // Red
                        '#3b82f6', // Blue
                        '#10b981', // Green
                        '#f59e0b', // Yellow
                        '#8b5cf6', // Purple
                        '#ec4899', // Pink
                        '#06b6d4', // Cyan
                    ],
                    'borderColor' => '#1f2937', // Border gelap biar rapi di dark mode
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data->pluck('description'),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // Tipe Chart: Donut/Pie
    }

    // Tambahkan fungsi ini di dalam class ExpensesChart
    protected function getOptions(): array
    {
        return [
            'scales' => [
                // Matikan sumbu X dan Y karena tidak relevan untuk Pie Chart
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
            // Pastikan chart responsive
            'responsive' => true,
        ];
    }
}
