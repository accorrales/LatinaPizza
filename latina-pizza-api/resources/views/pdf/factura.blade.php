@php
  $detalle   = json_decode($pedido->detalle_json ?? '{}', true);
  $items     = $detalle['items'] ?? [];
  $currency  = $pedido->delivery_currency ?? '₡';
  $fecha     = $pedido->created_at?->format('d/m/Y H:i');
  // Si tienes un logo en public/logo.png
  $logoPath  = public_path('logo.png');
  $tieneLogo = file_exists($logoPath);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Factura #{{ $pedido->id }}</title>
  <style>
    /* Estilos compatibles con DomPDF (evitar position:fixed/flex complicados) */
    @page { margin: 24px 28px; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color:#111; }
    h1, h2, h3 { margin:0; padding:0; }
    .muted { color:#666 }
    .mb-1 { margin-bottom:6px } .mb-2 { margin-bottom:12px } .mb-3 { margin-bottom:18px } .mb-4 { margin-bottom:24px }
    .row { width:100% }
    .col-6 { width:48%; display:inline-block; vertical-align:top }
    .right { text-align:right }
    .table { width:100%; border-collapse:collapse }
    .table th, .table td { padding:8px 8px; border-bottom:1px solid #e5e7eb; vertical-align:top }
    .table th { background:#f3f4f6; text-align:left }
    .small { font-size:11px; color:#444 }
    .totals td { padding:6px 8px }
    .badge { display:inline-block; padding:2px 6px; background:#f3f4f6; border-radius:4px; font-size:11px }
    .footer { margin-top:28px; border-top:1px solid #eee; padding-top:10px; font-size:11px; color:#666 }
    .logo { height:38px }
  </style>
</head>
<body>

  <!-- Header -->
  <table class="row mb-3">
    <tr>
      <td class="col-6">
        @if($tieneLogo)
          <img src="{{ $logoPath }}" class="logo" alt="LatinaPizza">
        @else
          <h2>LatinaPizza</h2>
        @endif
        <div class="small muted">Factura #{{ $pedido->id }}</div>
        <div class="small">{{ $fecha }}</div>
      </td>
      <td class="col-6 right">
        <div><strong>Cliente:</strong> {{ $pedido->user->name ?? 'Cliente' }}</div>
        @if($pedido->user?->email)
          <div class="small">{{ $pedido->user->email }}</div>
        @endif
        @if($pedido->tipo_entrega === 'express' && $pedido->direccionUsuario)
          <div class="small">Entrega: {{ $pedido->direccionUsuario->direccion_exacta }}</div>
        @endif
      </td>
    </tr>
  </table>

  <!-- Items -->
  <table class="table mb-2">
    <thead>
      <tr>
        <th style="width:60%">Descripción</th>
        <th class="right" style="width:10%">Cant.</th>
        <th class="right" style="width:30%">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($items as $it)
        <tr>
          <td>
            @if(($it['tipo'] ?? '') === 'producto')
              <strong>{{ $it['nombre'] }}</strong>
              <span class="badge">{{ $it['tamano'] ?? 'N/A' }}</span>
              <div class="small">
                Sabor: {{ $it['sabor'] ?? 'N/A' }} ·
                Masa: {{ $it['masa_nombre'] ?? 'N/A' }}
                @if(!empty($it['extras']))
                  <br>Extras: {{ collect($it['extras'])->pluck('nombre')->implode(', ') }}
                @endif
                @if(!empty($it['nota_cliente']))
                  <br>Nota: {{ $it['nota_cliente'] }}
                @endif
              </div>
            @elseif(($it['tipo'] ?? '') === 'promocion')
              <strong>Promoción: {{ $it['nombre'] }}</strong>
              @if(!empty($it['pizzas']))
                <div class="small">
                  @foreach($it['pizzas'] as $p)
                    • {{ ucfirst($p['tipo']) }}
                    @if(!empty($p['sabor']['nombre'])) — Sabor: {{ $p['sabor']['nombre'] }} @endif
                    @if(!empty($p['tamano']['nombre'])) — Tamaño: {{ $p['tamano']['nombre'] }} @endif
                    @if(!empty($p['extras']))
                      — Extras: {{ collect($p['extras'])->pluck('nombre')->implode(', ') }}
                    @endif
                    <br>
                  @endforeach
                </div>
              @endif
            @endif
          </td>
          <td class="right">{{ $it['cantidad'] ?? 1 }}</td>
          <td class="right">{{ $currency }}{{ number_format($it['precio_total'] ?? 0, 2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Totales -->
  <table class="row totals mb-2">
    <tr>
      <td class="col-6"></td>
      <td class="col-6">
        <table class="row" style="width:100%">
          <tr>
            <td class="right" style="width:70%">Subtotal:</td>
            <td class="right" style="width:30%">{{ $currency }}{{ number_format($pedido->subtotal ?? ($detalle['subtotal'] ?? 0), 2) }}</td>
          </tr>
          <tr>
            <td class="right">
              Delivery ({{ strtoupper($pedido->tipo_entrega ?? 'pickup') }})
              @if($pedido->delivery_distance_km) — {{ $pedido->delivery_distance_km }} km @endif
              :
            </td>
            <td class="right">{{ $currency }}{{ number_format($pedido->delivery_fee ?? 0, 2) }}</td>
          </tr>
          <tr>
            <td class="right"><strong>Total:</strong></td>
            <td class="right"><strong>{{ $currency }}{{ number_format($pedido->total ?? ($detalle['total'] ?? 0), 2) }}</strong></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <div class="footer">
    <div>LatinaPizza — ¡Calientita hasta tu mesa! 🍕</div>
    <div class="muted">
      Si tenés dudas sobre tu pedido, respondé este correo o escribinos por WhatsApp.
    </div>
  </div>

</body>
</html>