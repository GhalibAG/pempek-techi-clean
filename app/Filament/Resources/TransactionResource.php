<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Product;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Kasir / Transaksi';

    // protected static ?string $navigationGroup = 'Manajemen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Bagian Kiri: Form Kasir
                Forms\Components\Section::make('Kasir')
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->label('Keranjang Belanja')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->options(Product::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->reactive() // Biar bereaksi pas dipilih
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateTotals($get, $set))
                                    ->columnSpan(3),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->reactive() // Biar bereaksi pas diubah angka
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateTotals($get, $set))
                                    ->columnSpan(1),

                                Forms\Components\Hidden::make('price_at_transaction')
                                    ->default(fn (Get $get) => Product::find($get('product_id'))?->price ?? 0),
                            ])
                            ->columns(4)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                    ]),

                // Bagian Kanan: Ringkasan & Metadata
                Forms\Components\Section::make('Ringkasan & Info')
                    ->columnSpan(1)
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('TOTAL BAYAR')
                            ->prefix('Rp')
                            ->numeric()
                            ->readOnly() // Supaya ga bisa diedit manual
                            ->required()
                            ->default(0)
                            ->dehydrated()
                            ->extraInputAttributes(['style' => 'font-size: 1.5rem; font-weight: bold; color: green;']),

                        Forms\Components\Select::make('source_type')
                            ->options([
                                'OFFLINE' => 'Dine In / Langsung',
                                'WA' => 'WhatsApp',
                                'GOJEK' => 'Gojek',
                                'GRAB' => 'Grabfood',
                            ])
                            ->required()
                            ->default('OFFLINE')
                            ->label('Sumber'),

                        Forms\Components\TextInput::make('location_notes')
                            ->label('Nama Pelanggan / Lokasi'),

                        Forms\Components\Textarea::make('general_notes')
                            ->label('Catatan'),
                    ]),
            ])->columns(3);
    }

    // Fungsi Ajaib: Hitung Total Real-time
    public static function updateTotals(Get $get, Set $set): void
    {
        $items = $get('items'); // Ambil semua item di repeater
        $total = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                $qty = (int) ($item['quantity'] ?? 0);

                if ($productId) {
                    $product = Product::find($productId);
                    if ($product) {
                        $total += $product->price * $qty;
                    }
                }
            }
        }

        $set('total_amount', $total); // Update field Total Bayar
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                // Kolom Baru: Menampilkan Daftar Barang
                Tables\Columns\TextColumn::make('items')
                    ->label('Item Dibeli')
                    ->formatStateUsing(function ($record) {
                        return $record->items->map(function ($item) {
                            // Cek apakah produk masih ada?
                            $productName = $item->product?->name ?? 'Produk Terhapus';

                            return "{$item->quantity}x {$productName}";
                        })->join('<br>');
                    })
                    ->html()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Nota')
                    ->money('IDR', 0)
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('source_type')
                    ->label('Sumber')
                    ->badge(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Nota')
                    ->color('info')
                    ->icon('heroicon-o-eye'),

                // --- TOMBOL CETAK PDF ---
                Tables\Actions\Action::make('cetak_pdf')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('success') // Warna Hijau
                    ->action(function (Transaction $record) {
                        // Load view yang kita buat tadi
                        $pdf = Pdf::loadView('pdf.invoice', ['record' => $record]);

                        // Download file dengan nama "nota-001.pdf"
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'nota-'.$record->id.'.pdf');
                    }),
                // -------------------------

            ]);
    }

    // ... (Sisa fungsi getRelations dan getPages biarkan default/seperti sebelumnya) ...
    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            // 'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
