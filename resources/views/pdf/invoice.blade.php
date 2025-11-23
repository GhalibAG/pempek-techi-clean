<!DOCTYPE html>
<html>

<head>
    <title>Nota Transaksi #{{ $record->id }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 2px 0;
        }

        .details {
            margin-bottom: 15px;
        }

        .details p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 5px 0;
        }

        td {
            padding: 5px 0;
        }

        .text-right {
            text-align: right;
        }

        .total-row td {
            border-top: 1px solid #000;
            font-weight: bold;
            padding-top: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #777;
            border-top: 1px dashed #333;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>PEMPEK TE'CHI</h1>
        <p>Jl. Palembang - Layo, Sumatera Selatan</p>
        <p>WA: 0812-3456-7890</p>
    </div>

    <div class="details">
        <p><strong>No. Nota:</strong> #{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}</p>
        <p><strong>Tanggal:</strong> {{ $record->created_at->format('d M Y, H:i') }}</p>
        <p><strong>Kasir:</strong> {{ $record->user->name }}</p>
        <p><strong>Sumber:</strong> {{ $record->source_type }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($record->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->product->price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->quantity * $item->product->price, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="3">TOTAL BAYAR</td>
                <td class="text-right">Rp {{ number_format($record->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Terima kasih sudah berbelanja!</p>
        <p><i>Lemak kasih tau kawan, Dak lemak kasih tau kami!</i></p>
    </div>

</body>

</html>
