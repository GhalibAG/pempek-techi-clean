<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTransactions extends BaseWidget
{
    protected static ?string $heading = '5 Transaksi Terakhir';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full'; // Agar tabel ini lebar penuh

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()->latest()->limit(5) // Ambil 5 transaksi terbaru
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->since(), // Menampilkan "berapa waktu yang lalu"

                Tables\Columns\TextColumn::make('source_type')
                    ->label('Sumber')
                    ->badge(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Nota')
                    ->money('IDR', 0)
                    ->weight('bold'),

                // Menampilkan nama kasir yang mencatat
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir'),
            ]);
    }
}
