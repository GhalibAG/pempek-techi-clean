<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Kelola Produk';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nama Produk')
                    ->maxLength(255),

                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Harga Jual'),

                // Kolom HPP (Modal)
                Forms\Components\TextInput::make('cost_price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Harga Modal (HPP)'),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Nama'),

                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable()
                    ->label('Harga Jual'),

                Tables\Columns\TextColumn::make('cost_price')
                    ->money('IDR')
                    ->label('Modal'),

                // Hitung Laba Kotor Otomatis
                Tables\Columns\TextColumn::make('profit')
                    ->label('Laba Kotor')
                    ->state(function (Product $record): string {
                        $profit = $record->price - $record->cost_price;

                        return 'Rp '.number_format($profit, 0, ',', '.');
                    })
                    ->color('success')
                    ->weight('bold'),

                // Tampilkan Stok (Ambil dari relasi stock)
                Tables\Columns\TextColumn::make('stock.quantity')
                    ->label('Stok')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    })
                    ->default(0),

            ])
            ->filters([
                //
            ])
            ->actions([
                // --- TOMBOL UPDATE STOK ---
                Tables\Actions\Action::make('updateStock')
                    ->label('Stok')
                    ->icon('heroicon-o-circle-stack')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Stok Baru')
                            ->numeric()
                            ->required(),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $record->stock()->updateOrCreate(
                            ['product_id' => $record->id],
                            ['quantity' => $data['quantity']]
                        );
                    })
                    ->modalWidth('sm'),
                // ---------------------------

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
