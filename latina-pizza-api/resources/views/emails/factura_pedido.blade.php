@php
  $nombre = $pedido->user->name ?? 'cliente';
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-family:Arial, Helvetica, sans-serif; color:#111; line-height:1.45">
  <tr>
    <td style="padding:20px 0">
      <h2 style="margin:0 0 6px 0; font-size:20px; color:#111">¡Gracias por tu orden, {{ $nombre }}! 🎉</h2>
      <p style="margin:0 0 14px 0; color:#444">
        Adjuntamos la <strong>factura PDF</strong> de tu pedido <strong>#{{ $pedido->id }}</strong>.
      </p>

      <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:10px 0 16px 0">
        <tr>
          <td style="padding:8px; background:#f3f4f6; width:40%">Fecha</td>
          <td style="padding:8px; background:#f9fafb">{{ $pedido->created_at?->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
          <td style="padding:8px; background:#f3f4f6">Método de pago</td>
          <td style="padding:8px; background:#f9fafb">{{ ucfirst($pedido->metodo_pago ?? 'efectivo') }}</td>
        </tr>
        <tr>
          <td style="padding:8px; background:#f3f4f6">Entrega</td>
          <td style="padding:8px; background:#f9fafb">
            {{ strtoupper($pedido->tipo_entrega ?? 'pickup') }}
            @if($pedido->tipo_entrega === 'express' && $pedido->direccionUsuario)
              — {{ $pedido->direccionUsuario->direccion_exacta }}
            @endif
          </td>
        </tr>
        <tr>
          <td style="padding:8px; background:#f3f4f6">Total</td>
          <td style="padding:8px; background:#f9fafb">
            {{ $pedido->delivery_currency ?? '₡' }}{{ number_format($pedido->total ?? 0, 2) }}
          </td>
        </tr>
      </table>

      <p style="margin:0 0 8px 0; color:#444">
        Cualquier consulta, respondé este correo o escribinos por WhatsApp 📲.
      </p>
      <p style="margin:0; color:#999; font-size:12px">
        LatinaPizza — ¡Calientita hasta tu mesa!
      </p>
    </td>
  </tr>
</table>