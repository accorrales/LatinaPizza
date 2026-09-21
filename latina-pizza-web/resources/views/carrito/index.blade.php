@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
  <h2 class="text-3xl font-extrabold text-red-600 mb-6 text-center">{{ __('carrito.mi_carrito') }}</h2>

  @if(session('success'))<div class="bg-green-100 border border-green-300 text-green-800 p-3 mb-5 rounded-lg">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="bg-red-100 border border-red-300 text-red-800 p-3 mb-5 rounded-lg">{{ session('error') }}</div>@endif

  @php
    $data = $carrito['data'] ?? [];
    $items = $data['items'] ?? [];
    $subtotal = $carrito['subtotal'] ?? 0;
    $deliveryF = $carrito['delivery']['fee'] ?? 0;
    $deliveryC = $carrito['delivery']['currency'] ?? '₡';
    $distance = $carrito['delivery']['distance'] ?? null;
    $total = $carrito['total'] ?? 0;
    $stripeEnabled = filled(config('services.stripe.key'));
  @endphp

  @if(!empty($items))
    <div class="hidden md:block overflow-x-auto rounded-xl shadow-lg mt-4">
      <table class="min-w-full divide-y divide-gray-200 bg-white border border-gray-200">
        <thead class="bg-red-100 text-red-700">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-bold">{{ __('carrito.detalle') }}</th>
            <th class="px-6 py-3 text-center text-sm font-bold">{{ __('carrito.cantidad') }}</th>
            <th class="px-6 py-3 text-center text-sm font-bold">{{ __('carrito.precio') }}</th>
            <th class="px-6 py-3 text-center text-sm font-bold">{{ __('carrito.acciones') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-sm text-gray-800">
          @foreach($items as $it)
            <tr class="hover:bg-gray-50 transition">
              <td class="px-6 py-4 align-top">
                <div class="font-semibold text-red-700 text-base">{{ $it['tipo']==='producto' ? '🍕' : '🎁' }} {{ $it['nombre'] }}</div>
                <div class="text-xs text-gray-600 mt-2 space-y-1">
                  @if($it['tipo']==='producto')
                    <p><strong>{{ __('carrito.tamano') }}:</strong> {{ $it['tamano'] ?? '-' }}</p>
                    <p><strong>{{ __('carrito.sabor') }}:</strong> {{ $it['sabor'] ?? '-' }}</p>
                    <p><strong>{{ __('carrito.masa') }}:</strong> {{ $it['masa_nombre'] ?? '-' }}</p>
                    @if(!empty($it['nota_cliente']))<p><strong>{{ __('carrito.nota_cliente') }}:</strong> <em>"{{ $it['nota_cliente'] }}"</em></p>@endif
                    @if(!empty($it['extras']))
                      <p><strong>{{ __('carrito.extras') }}:</strong></p>
                      <ul class="list-disc ml-6">@foreach($it['extras'] as $ex)<li>{{ $ex['nombre'] }}</li>@endforeach</ul>
                    @endif
                  @else
                    <p class="text-sm text-gray-700 mb-1">{{ $it['descripcion'] }}</p>
                    @foreach($it['pizzas'] as $pz)
                      @if($pz['tipo']==='pizza')
                        <div class="border p-2 rounded bg-gray-50 mb-1">🍕 <strong>{{ $pz['sabor']['nombre'] }}</strong> ({{ $pz['masa']['nombre'] }}) @if(!empty($pz['nota_cliente']))<br><em>"{{ $pz['nota_cliente'] }}"</em>@endif</div>
                      @elseif($pz['tipo']==='bebida')
                        <p class="text-blue-600">{{ __('carrito.bebida') }}: {{ $pz['producto']['nombre'] }}</p>
                      @endif
                    @endforeach
                    <p class="text-sm mt-2">{{ __('carrito.base') }}: {{ $deliveryC }}{{ number_format($it['desglose']['base'] ?? 0, 2) }}</p>
                    <p class="text-sm">{{ __('carrito.extras') }}: {{ $deliveryC }}{{ number_format($it['desglose']['extras'] ?? 0, 2) }}</p>
                  @endif
                </div>
              </td>
              <td class="px-6 py-4 text-center">{{ $it['cantidad'] ?? 1 }}</td>
              <td class="px-6 py-4 text-center text-green-700 font-bold">{{ $deliveryC }}{{ number_format($it['precio_total'], 2) }}</td>
              <td class="px-6 py-4 text-center">
                <form method="POST" action="{{ route('carrito.eliminar', ['id'=>$it['id']]) }}">
                  @csrf @method('DELETE')
                  <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded shadow text-sm">{{ __('carrito.eliminar') }}</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="md:hidden space-y-4 mt-6">
      @foreach($items as $it)
        <div class="bg-white rounded-xl shadow-md border p-4 space-y-2">
          <div class="flex justify-between gap-3">
            <div class="min-w-0">
              <h3 class="text-red-700 font-bold text-lg truncate">{{ $it['tipo']==='producto' ? '🍕' : '🎁' }} {{ $it['nombre'] }}</h3>
              <div class="text-sm text-gray-600 mt-1 space-y-0.5">
                @if($it['tipo']==='producto')
                  <p>{{ __('carrito.tamano') }}: {{ $it['tamano'] ?? '-' }}</p>
                  <p>{{ __('carrito.sabor') }}: {{ $it['sabor'] ?? '-' }}</p>
                  <p>{{ __('carrito.masa') }}: {{ $it['masa_nombre'] ?? '-' }}</p>
                  @if(!empty($it['nota_cliente']))<p><em>"{{ $it['nota_cliente'] }}"</em></p>@endif
                  @if(!empty($it['extras']))
                    <p class="mt-1">{{ __('carrito.extras') }}:</p>
                    <ul class="list-disc ml-5 text-sm">@foreach($it['extras'] as $ex)<li>{{ $ex['nombre'] }}</li>@endforeach</ul>
                  @endif
                @else
                  <p class="mb-1">{{ $it['descripcion'] }}</p>
                  @foreach($it['pizzas'] as $pz)
                    @if($pz['tipo']==='pizza')
                      <div class="border p-2 rounded bg-gray-50 mb-1">🍕 <strong>{{ $pz['sabor']['nombre'] }}</strong> ({{ $pz['masa']['nombre'] }}) @if(!empty($pz['nota_cliente']))<br><em>"{{ $pz['nota_cliente'] }}"</em>@endif</div>
                    @elseif($pz['tipo']==='bebida')
                      <p class="text-blue-600">{{ __('carrito.bebida') }}: {{ $pz['producto']['nombre'] }}</p>
                    @endif
                  @endforeach
                  <div class="text-sm mt-1 text-gray-700">{{ __('carrito.base') }}: {{ $deliveryC }}{{ number_format($it['desglose']['base'] ?? 0, 2) }}<br>{{ __('carrito.extras') }}: {{ $deliveryC }}{{ number_format($it['desglose']['extras'] ?? 0, 2) }}</div>
                @endif
              </div>
            </div>
            <div class="text-right">
              <div class="text-green-700 font-bold">{{ $deliveryC }}{{ number_format($it['precio_total'], 2) }}</div>
              <div class="text-xs text-gray-500">{{ __('carrito.cantidad') }}: {{ $it['cantidad'] ?? 1 }}</div>
            </div>
          </div>
          <div class="flex justify-end">
            <form method="POST" action="{{ route('carrito.eliminar', ['id'=>$it['id']]) }}">
              @csrf @method('DELETE')
              <button class="text-red-600 hover:underline text-sm">{{ __('carrito.eliminar') }}</button>
            </form>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-8 grid md:grid-cols-2 gap-6 items-start">
      <div class="bg-white rounded-xl border p-4 shadow-sm">
        <h4 class="font-semibold text-gray-800 mb-3">{{ __('carrito.resumen') }}</h4>
        <div class="space-y-1 text-sm">
          <div class="flex justify-between"><span>Subtotal</span><span class="font-semibold">{{ $deliveryC }}{{ number_format($subtotal,2) }}</span></div>
          <div class="flex justify-between"><span>Delivery @if($distance)<span class="text-gray-500">({{ number_format($distance,1) }} km)</span>@endif</span><span class="font-semibold">{{ $deliveryC }}{{ number_format($deliveryF,2) }}</span></div>
          <hr class="my-2">
          <div class="flex justify-between text-lg"><span>Total</span><span class="font-bold text-green-700">{{ $deliveryC }}{{ number_format($total,2) }}</span></div>
        </div>
      </div>

      <div class="bg-white rounded-xl border p-4 shadow-sm">
        <form id="checkout-form"
              method="POST"
              action="{{ route('carrito.checkout') }}"
              data-stripe-enabled="{{ $stripeEnabled ? '1' : '0' }}"
              data-stripe-key="{{ config('services.stripe.key') }}"
              data-intent-url="{{ route('carrito.stripe.intent') }}"
              data-submit-label="{{ __('carrito.confirmar_pedido') }}">
          @csrf

          <h4 class="font-semibold text-gray-800 mb-2">Método de pago</h4>
          <div class="space-y-2 mb-4">
            <label class="flex items-center gap-2"><input type="radio" name="metodo_pago" value="efectivo" checked><span>Efectivo (pagás al recibir o retirar)</span></label>
            <label class="flex items-center gap-2"><input type="radio" name="metodo_pago" value="datafono"><span>Datáfono (tarjeta en el local o con el repartidor)</span></label>
            @if ($stripeEnabled)
              <label class="flex items-center gap-2"><input type="radio" name="metodo_pago" value="stripe"><span>Tarjeta en línea (Stripe)</span></label>
            @endif
          </div>

          <div id="stripe-box" class="hidden border rounded p-4 mb-4">
            <div id="payment-element"></div>
            <div id="payment-error" class="text-red-600 text-sm mt-2 hidden"></div>
          </div>

          <input type="hidden" name="payment_intent_id" id="payment_intent_id">

          <div class="flex flex-wrap gap-3 justify-end">
            <a href="{{ url('/catalogo') }}" class="px-5 py-2 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold shadow">{{ __('carrito.seguir_comprando') }}</a>
            <button id="btn-submit" type="submit" class="relative px-6 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white font-semibold shadow disabled:opacity-60 disabled:cursor-not-allowed">
              <svg id="btn-spinner" class="hidden animate-spin h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
              </svg>
              <span id="btn-text">{{ __('carrito.confirmar_pedido') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  @else
    <div class="text-center text-gray-600 text-lg mt-10">{{ __('carrito.carrito_vacio') }}</div>
    <div class="text-center mt-4"><a href="{{ url('/catalogo') }}" class="px-5 py-2 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold shadow">{{ __('carrito.seguir_comprando') }}</a></div>
  @endif
</div>
@endsection
