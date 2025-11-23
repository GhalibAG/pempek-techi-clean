<x-filament-panels::page>
    {{-- 1. Form Filter Tanggal --}}
    <div class="bg-white p-4 rounded-lg shadow mb-4">
        {{ $this->form }}

        <div class="mt-4 flex justify-end">
            <x-filament::button wire:click="filter">
                Terapkan Filter
            </x-filament::button>
        </div>
    </div>

    {{-- 2. Ringkasan Angka (Saldo) --}}
    @php
        $totalIncome = collect($reportData)->where('type', 'income')->sum('amount');
        $totalExpense = collect($reportData)->where('type', 'expense')->sum('amount');
        $netProfit = $totalIncome - $totalExpense;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
            <h3 class="text-green-800 text-sm font-medium">Total Pemasukan</h3>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <h3 class="text-red-800 text-sm font-medium">Total Pengeluaran</h3>
            <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h3 class="text-blue-800 text-sm font-medium">Laba Bersih (Net Profit)</h3>
            <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- 3. Tabel Rincian Data --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Tanggal</th>
                    <th class="px-6 py-3">Keterangan</th>
                    <th class="px-6 py-3">PIC</th>
                    <th class="px-6 py-3">Jenis</th>
                    <th class="px-6 py-3 text-right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $row)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $row['description'] }}</td>
                        <td class="px-6 py-4">{{ $row['user'] }}</td>
                        <td class="px-6 py-4">
                            @if ($row['type'] === 'income')
                                <span
                                    class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Pemasukan</span>
                            @else
                                <span
                                    class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Pengeluaran</span>
                            @endif
                        </td>
                        <td
                            class="px-6 py-4 text-right font-bold {{ $row['type'] === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $row['type'] === 'income' ? '+' : '-' }} Rp
                            {{ number_format($row['amount'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center">Tidak ada data pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
