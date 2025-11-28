<?php

namespace App\Filament\Pages;

use App\Exports\FinancialExport;
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
use Maatwebsite\Excel\Facades\Excel; // <--- INI YANG HILANG!

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
                    ->columns(4) // Jadi 4 kolom biar muat
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Dari Tanggal')
                            ->required(),

                        DatePicker::make('endDate')
                            ->label('Sampai Tanggal')
                            ->required(),

                        // --- FILTER BARU ---
                        \Filament\Forms\Components\Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'all' => 'Semua',
                                'income' => 'Pemasukan',
                                'expense' => 'Pengeluaran',
                            ])
                            ->default('all'),

                        \Filament\Forms\Components\TextInput::make('search')
                            ->label('Cari Keterangan')
                            ->placeholder('Contoh: Gojek, Ikan...'),
                        // -------------------
                    ]),
            ])
            ->statePath('data');
    }

    public function filter(): void
    {
        $state = $this->form->getState();
        $start = $state['startDate'];
        $end = $state['endDate'];
        $type = $state['type'] ?? 'all'; // Ambil jenis
        $search = $state['search'] ?? null; // Ambil kata kunci

        $results = collect();

        // 1. Ambil Pemasukan (Jika filter 'all' atau 'income')
        if ($type === 'all' || $type === 'income') {
            $query = Transaction::whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
                ->with('user');

            // Filter Pencarian (Search)
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('source_type', 'like', "%$search%")
                        ->orWhere('location_notes', 'like', "%$search%")
                        ->orWhere('general_notes', 'like', "%$search%");
                });
            }

            $incomes = $query->get()->map(function ($item) {
                return [
                    'date' => $item->created_at->format('Y-m-d H:i:s'),
                    'description' => 'Penjualan (Nota #'.$item->id.') - '.$item->source_type,
                    'type' => 'income',
                    'amount' => $item->total_amount,
                    'user' => $item->user->name ?? 'System',
                ];
            });
            $results = $results->merge($incomes);
        }

        // 2. Ambil Pengeluaran (Jika filter 'all' atau 'expense')
        if ($type === 'all' || $type === 'expense') {
            $query = Expense::whereBetween('date', [$start, $end]);

            // Filter Pencarian
            if ($search) {
                $query->where('description', 'like', "%$search%");
            }

            $expenses = $query->get()->map(function ($item) {
                return [
                    'date' => $item->date, // Pastikan ini format Y-m-d H:i:s jika perlu sorting
                    'description' => $item->description,
                    'type' => 'expense',
                    'amount' => $item->amount,
                    'user' => 'Admin',
                ];
            });
            $results = $results->merge($expenses);
        }

        // 3. Gabung & Urutkan
        $this->reportData = $results->sortByDesc('date')->values()->all();
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
        // 1. Pastikan data terbaru ter-filter
        $this->filter();

        // 2. Download pakai Library Laravel Excel
        return Excel::download(
            new FinancialExport($this->reportData),
            'Laporan-Keuangan-'.now()->format('d-M-Y').'.xlsx'
        );
    }
}
