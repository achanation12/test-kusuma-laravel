<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function beforeCreate(): void
    {
        $transaction = $this->data;
        $items = $transaction['items'];
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->stock < $item['quantity']) {
                \Filament\Notifications\Notification::make()
                    ->title("Stok produk {$product->name} tersisa {$product->stock}")
                    ->warning()
                    ->persistent()
                    ->send();
                throw ValidationException::withMessages([
                    'items' => "Stok produk {$product->name} tidak mencukupi.",
                ]);
            }
        }
    }

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $transaction = $this->record;

            // Ambil items dari relationship (misal: cartItems, selectedItems, dll)
            $items = $transaction->items()->get();

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);

                $product->decrement('stock', $item->quantity);
            }
        });
    }
}
