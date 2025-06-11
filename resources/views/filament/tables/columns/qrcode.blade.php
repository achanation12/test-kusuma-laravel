@php
    $product = $getRecord();
    $qrContent = "{$product->sku}|{$product->name}|{$product->price}|{$product->stock}";
@endphp

<div style="display: flex; justify-content: center; align-items: center;">
    {!! QrCode::size(40)->generate($qrContent) !!}
</div>
