<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class FinancialReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text'; // Ikon yang aman

    protected static ?string $navigationLabel = 'Laporan Keuangan';

    protected static ?string $navigationGroup = 'Laporan';

    protected static string $view = 'filament.pages.financial-report';

    public static function canAccess(): bool
    {
        // HANYA Owner yang boleh melihat laporan ini
        return auth()->user()->role === 'owner';
    }

    // --- PERBAIKAN DISINI: Gunakan array $data untuk menampung form ---
    public ?array $data = [];

    // Variabel untuk menampung hasil query (untuk tabel view)
    public $reportData = [];

    public function mount(): void
    {
        // Isi default form (bulan ini)
        $this->form->fill([
            'startDate' => now()->startOfMonth()->format('Y-m-d'),
            'endDate' => now()->endOfMonth()->format('Y-m-d'),
        ]);

        // Jalankan filter otomatis saat halaman dibuka
        $this->filter();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Dari Tanggal')
                            ->required(),
                        DatePicker::make('endDate')
                            ->label('Sampai Tanggal')
                            ->required(),
                    ]),
            ])
            ->statePath('data'); // Form akan disimpan ke variabel $data
    }

    public function filter(): void
    {
        // Ambil data dari form state ($this->data)
        $state = $this->form->getState();
        $start = $state['startDate'];
        $end = $state['endDate'];

        // 1. Ambil Pemasukan (Transaksi)
        $incomes = Transaction::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->created_at->format('Y-m-d H:i:s'),
                    'description' => 'Penjualan (Nota #'.$item->id.') - '.$item->source_type,
                    'type' => 'income',
                    'amount' => $item->total_amount,
                    'user' => $item->user->name ?? 'System',
                ];
            });

        // 2. Ambil Pengeluaran
        $expenses = Expense::whereBetween('date', [$start, $end])
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'description' => $item->description,
                    'type' => 'expense',
                    'amount' => $item->amount,
                    'user' => 'Admin',
                ];
            });

        // 3. Gabung dan Urutkan
        $this->reportData = $incomes->merge($expenses)
            ->sortByDesc('date')
            ->values()
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Tombol Filter di Header (Opsional, karena sudah ada di form)
            Action::make('refresh')
                ->label('Refresh Data')
                ->action(fn () => $this->filter()),

            // Tombol Export PDF
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->action(function () {
                    // Ambil data tanggal dari form untuk judul PDF
                    $state = $this->form->getState();

                    $pdf = Pdf::loadView('pdf.financial-report', [
                        'data' => $this->reportData,
                        'start' => $state['startDate'],
                        'end' => $state['endDate'],
                    ]);

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'Laporan-Keuangan-'.now()->format('Ymd-His').'.pdf');
                }),
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells') // Ikon Excel/Tabel
                ->color('success') // Warna Hijau khas Excel
                ->action(fn () => $this->exportExcel()), // Panggil fungsi yang kita buat di atas
            // --------------------------
        ];
    }

    public function exportExcel()
    {
        // 1. Pastikan data terbaru sudah ter-load
        $this->filter();
        $data = $this->reportData;

        // 2. Nama File Keren
        $fileName = 'Laporan-Keuangan-'.now()->format('d-M-Y_H-i').'.csv';

        // 3. Header Browser (biar otomatis download)
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // 4. Proses Penulisan Data
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Trik Ajaib: Tambahkan BOM agar Excel bisa baca simbol Rupiah/UTF-8 dengan benar
            fwrite($file, "\xEF\xBB\xBF");

            // Tulis Judul Kolom (Header)
            fputcsv($file, ['TANGGAL', 'KETERANGAN', 'JENIS', 'NOMINAL (Rp)', 'PIC']);

            // Tulis Isi Data
            foreach ($data as $row) {
                fputcsv($file, [
                    \Carbon\Carbon::parse($row['date'])->format('d/m/Y H:i'), // Format Tanggal
                    $row['description'],
                    $row['type'] === 'income' ? 'PEMASUKAN' : 'PENGELUARAN', // Huruf Besar biar tegas
                    $row['amount'], // Angka murni biar bisa di-sum di Excel
                    $row['user'],
                ]);
            }

            fclose($file);
        };

        // 5. Download!
        return response()->stream($callback, 200, $headers);
    }
}
