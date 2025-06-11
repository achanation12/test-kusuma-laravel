<?php
namespace App\Traits;

trait HasSku
{
    protected static function bootHasSku()
    {
        static::creating(function ($model) {
            if (empty($model->sku)) {
                $model->sku = $model->generateSku();
            }
        });
    }

    public function generateSku(): string
    {
        $prefix = strtoupper(substr($this->category->name, 0, 3)); // Ambil 3 huruf kategori
        $brandCode = strtoupper(substr($this->name, 0, 2)); // Kode nama
        $uniqueId = str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT); // ID unik

        return "{$prefix}-{$brandCode}-{$uniqueId}";
    }
}
