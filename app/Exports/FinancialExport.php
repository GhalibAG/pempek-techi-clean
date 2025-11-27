<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Mengambil data yang akan diexport
     */
    public function collection()
    {
        return collect($this->data);
    }

    /**
     * Judul Kolom (Header) yang Tebal & Rapi
     */
    public function headings(): array
    {
        return [
            'TANGGAL',
            'KETERANGAN',
            'JENIS',
            'NOMINAL (RP)',
            'PIC / KASIR',
        ];
    }

    /**
     * Mengatur isi setiap baris (Mapping Data)
     */
    public function map($row): array
    {
        return [
            \Carbon\Carbon::parse($row['date'])->format('d/m/Y H:i'), // Format Tanggal
            $row['description'],
            strtoupper($row['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran'),
            $row['amount'],
            $row['user'],
        ];
    }

    /**
     * Bikin Header Tebal (Styling)
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Baris 1 (Header) di-bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}
