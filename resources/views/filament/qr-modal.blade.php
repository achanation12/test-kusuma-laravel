<div style="display: flex; justify-content: center; align-items: center;">
    {!! QrCode::size(150)->generate($qrContent) !!}
</div>
<div style="text-align: center;">
    <div style="margin-top: 8px; font-weight: bold;">{{ $qrContent }}</div>
</div>
