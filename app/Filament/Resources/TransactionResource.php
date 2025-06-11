<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Product;
use App\Models\Transaction;
use Filament\Actions\RestoreAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\ValidationException;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)->schema([
                    DatePicker::make('date')
                        ->default(now())
                        ->required(),

                    Select::make('discount_type')
                        ->options([
                            'fix' => 'Fix (Rp)',
                            'percent' => 'Percent (%)',
                        ])
                        ->default('fix')
                        ->reactive()
                        ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                            static::recalculateTotal($set, $get)
                        ),

                    TextInput::make('discount')
                        ->numeric()
                        ->default(0)
                        ->debounce(1500)
                        ->reactive()
                        ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                            static::recalculateTotal($set, $get)
                        ),

                    Placeholder::make('total_display')
                        ->label('Total Bayar')
                        ->hidden()
                        ->content(fn($get) => 'Rp ' . number_format($get('total') ?? 0, 0, ',', '.')),

                    TextInput::make('total')
                        ->label('Total Bayar')
                        ->prefix('Rp')
                        ->numeric()
                        ->readOnly()
                        ->required(),
                ]),

                Repeater::make('items')
                    ->label('Produk')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(
                                Product::where('stock', '>', 0)->pluck('name', 'id')
                            )
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $product = Product::find($state);
                                $set('price', $product?->price ?? 0);

                                // Pastikan qty default 1 kalau belum ada
                                $quantity = $get('quantity') ?? 0;
                                $set('quantity', $quantity);

                                static::recalculateTotal($set, $get);
                            }),

                        TextInput::make('price')
                            ->label('Harga')
                            ->prefix('Rp')
                            ->disabled()
                            ->numeric()
                            ->dehydrated()
                            ->afterStateHydrated(function (callable $set, $state, $record) {
                                if ($record?->product) {
                                    $set('price', $record->product->price);
                                }
                            }),

                        TextInput::make('quantity')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, Forms\Set $formSet) {
                                static::recalculateTotal($set, $get);
                            }),
                    ])
                    ->columns(3)
                    ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                        static::recalculateTotal($set, $get)
                    )
                    ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR', true)
                    ->sortable(),

                TextColumn::make('discount')
                    ->label('Diskon')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->discount_type === 'percent'
                            ? $state . '%'
                            : 'Rp ' . number_format($state, 0, ',', '.');
                    }),

                TextColumn::make('items_count')
                    ->label('Jumlah Produk')
                    ->counts('items')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since(), // "2 jam yang lalu"

                TextColumn::make('items_list')
                    ->label('Produk')
                    ->getStateUsing(function ($record) {
                        return $record->items
                            ->map(fn($item) => $item->product->name . ' x' . $item->quantity)
                            ->join(', ');
                    })
                    ->wrap()
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    protected static function recalculateTotal(callable $set, callable $get): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;

        foreach ($items as $item) {
            $qty = intval($item['quantity'] ?? 0);
            $price = floatval($item['price'] ?? 0);
            $subtotal += $qty * $price;
        }

        $discount = floatval($get('discount') ?? 0);
        $discountType = $get('discount_type') ?? 'fix';

        $total = $discountType === 'percent'
            ? $subtotal - ($subtotal * $discount / 100)
            : $subtotal - $discount;

        $set('total', max($total, 0));
    }

}
