<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-green { color: green; }
        .text-red { color: red; }
        .header { margin-bottom: 30px; text-align: center; }
        .summary { margin-top: 20px; width: 50%; float: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN KEUANGAN PEMPEK TE'CHI</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($start)->format('d M Y') }} - {{ \Carbon\Carbon::parse($end)->format('d M Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Jenis</th>
                <th class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @php $totalIn = 0; $totalOut = 0; @endphp
            @foreach($data as $row)
                @php 
                    if($row['type'] == 'income') $totalIn += $row['amount'];
                    else $totalOut += $row['amount'];
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                    <td>{{ $row['description'] }}</td>
                    <td>{{ $row['type'] == 'income' ? 'Pemasukan' : 'Pengeluaran' }}</td>
                    <td class="text-right {{ $row['type'] == 'income' ? 'text-green' : 'text-red' }}">
                        {{ number_format($row['amount'], 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table style="border: none;">
            <tr>
                <td style="border: none;">Total Pemasukan</td>
                <td style="border: none;" class="text-right text-green">Rp {{ number_format($totalIn, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: none;">Total Pengeluaran</td>
                <td style="border: none;" class="text-right text-red">Rp {{ number_format($totalOut, 0, ',', '.') }}</td>
            </tr>
            <tr style="font-weight: bold; font-size: 14px;">
                <td style="border-top: 2px solid #000; border-bottom: none; border-left: none; border-right: none;">LABA BERSIH</td>
                <td style="border-top: 2px solid #000; border-bottom: none; border-left: none; border-right: none;" class="text-right">
                    Rp {{ number_format($totalIn - $totalOut, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>