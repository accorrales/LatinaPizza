@extends('layouts.app')

@section('content')
@php
    $user = Auth::user();
    $orders = collect($sessionData['orders'] ?? []);
    $summary = $sessionData['summary'] ?? [];
    $mesa = $sessionData['mesa'] ?? [];
    $mesero = $sessionData['mesero'] ?? [];
    $isOpen = ($sessionData['estado'] ?? null) === 'abierta';
    $canOperate = in_array($user->role, ['admin', 'gerente', 'mesero'], true);
    $canPay = in_array($user->role, ['admin', 'gerente', 'cajero'], true);
    $canCancel = in_array($user->role, ['admin', 'gerente'], true);
@endphp

<div class="mx-auto max-w-[1500px] px-4 py-7 sm:px-6 lg:px-8">
    @if(session('success'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('salon.index', ['sucursal_id' => $sessionData['sucursal']['id'] ?? null]) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <i class="fa-solid fa-arrow-left"></i> Volver al salón
        </a>
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">{{ $sessionData['sucursal']['nombre'] ?? 'Sucursal' }}</span>
            <span class="rounded-full {{ $isOpen ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }} px-3 py-1.5">{{ $isOpen ? 'Mesa abierta' : 'Mesa cerrada' }}</span>
        </div>
    </div>

    <section class="overflow-hidden rounded-[2rem] bg-[#071426] text-white shadow-xl shadow-slate-950/10">
        <div class="grid gap-6 p-6 md:grid-cols-[1fr_auto] md:items-end lg:p-8">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-300">Operación de salón</p>
                <h1 class="mt-2 text-3xl font-black sm:text-4xl">Mesa {{ $mesa['numero'] ?? '-' }} <span class="text-slate-400">·</span> {{ $mesa['nombre'] ?: 'Cuenta abierta' }}</h1>
                <div class="mt-4 flex flex-wrap gap-2 text-xs font-bold text-slate-200">
                    <span class="rounded-full bg-white/10 px-3 py-2"><i class="fa-solid fa-user-tie mr-2 text-blue-300"></i>{{ $mesero['name'] ?? 'Sin mesero' }}</span>
                    <span class="rounded-full bg-white/10 px-3 py-2"><i class="fa-solid fa-user-group mr-2 text-blue-300"></i>{{ $sessionData['personas'] ?? 1 }} personas</span>
                    @if(!empty($mesa['zona']))<span class="rounded-full bg-white/10 px-3 py-2"><i class="fa-solid fa-location-dot mr-2 text-blue-300"></i>{{ $mesa['zona'] }}</span>@endif
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="rounded-2xl bg-white/10 px-4 py-3"><p class="text-[10px] font-bold uppercase text-slate-400">Rondas</p><p class="mt-1 text-xl font-black">{{ $orders->where('estado', '!=', 'cancelado')->count() }}</p></div>
                <div class="rounded-2xl bg-white/10 px-4 py-3"><p class="text-[10px] font-bold uppercase text-slate-400">Cuenta</p><p class="mt-1 text-lg font-black">₡{{ number_format((float)($summary['total'] ?? 0), 0, ',', '.') }}</p></div>
                <div class="rounded-2xl bg-white/10 px-4 py-3"><p class="text-[10px] font-bold uppercase text-slate-400">Pendiente</p><p class="mt-1 text-lg font-black">₡{{ number_format((float)($summary['pending_total'] ?? 0), 0, ',', '.') }}</p></div>
            </div>
        </div>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(360px,.65fr)]">
        <div class="space-y-6">
            @if($isOpen && $canOperate)
                <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-600">Nueva ronda</p>
                            <h2 class="mt-1 text-2xl font-black text-slate-900">Armá la comanda</h2>
                            <p class="mt-1 text-sm text-slate-500">La ronda se puede editar aquí. Al enviarla a cocina queda bloqueada.</p>
                        </div>
                        <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-black">
                            <button type="button" data-tab="products" class="salon-tab rounded-lg bg-white px-4 py-2 text-slate-900 shadow-sm">Productos</button>
                            <button type="button" data-tab="promotions" class="salon-tab rounded-lg px-4 py-2 text-slate-500">Promos</button>
                        </div>
                    </div>

                    <div class="mt-5">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input id="catalog-search" type="search" class="w-full rounded-2xl border-slate-200 py-3 pl-11 pr-4 text-sm" placeholder="Buscar producto o promoción...">
                        </div>
                        <div id="products-panel" class="mt-4"><div id="products-grid" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"></div></div>
                        <div id="promotions-panel" class="mt-4 hidden"><div id="promotions-grid" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"></div></div>
                    </div>
                </section>
            @endif

            <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div><p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Historial de mesa</p><h2 class="mt-1 text-2xl font-black text-slate-900">Rondas enviadas</h2></div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500">{{ $orders->count() }}</span>
                </div>

                @if($orders->isEmpty())
                    <div class="mt-5 rounded-3xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                        <i class="fa-solid fa-receipt mb-3 text-2xl text-slate-300"></i><br>No se ha enviado ninguna ronda todavía.
                    </div>
                @else
                    <div class="mt-5 space-y-4">
                        @foreach($orders->sortByDesc('ronda') as $order)
                            @php
                                $cancelled = ($order['estado'] ?? null) === 'cancelado';
                                $kitchenStatus = $order['kitchen_status'] ?? 'nuevo';
                                $paymentStatus = $order['payment_status'] ?? 'pending';
                            @endphp
                            <article class="rounded-3xl border {{ $cancelled ? 'border-slate-200 bg-slate-50 opacity-70' : 'border-slate-200 bg-white' }} p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-black text-slate-900">Ronda #{{ $order['ronda'] ?: $order['id'] }}</h3>
                                            @if($cancelled)
                                                <span class="rounded-full bg-slate-200 px-2.5 py-1 text-[10px] font-black uppercase text-slate-600">Cancelada</span>
                                            @else
                                                <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $kitchenStatus === 'listo' ? 'bg-emerald-100 text-emerald-700' : ($kitchenStatus === 'entregado' ? 'bg-blue-100 text-blue-700' : ($kitchenStatus === 'preparacion' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600')) }}">
                                                    {{ $kitchenStatus === 'entregado' ? 'Servida' : ucfirst($kitchenStatus) }}
                                                </span>
                                                <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $paymentStatus === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $paymentStatus === 'paid' ? 'Pagada' : 'Pendiente de pago' }}</span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-xs text-slate-400">Pedido #{{ $order['id'] }} · {{ $order['created_by']['name'] ?? 'Colaborador' }}</p>
                                    </div>
                                    <p class="text-lg font-black text-slate-900">₡{{ number_format((float)($order['total'] ?? 0), 0, ',', '.') }}</p>
                                </div>

                                <div class="mt-4 space-y-2">
                                    @foreach(($order['items'] ?? []) as $item)
                                        <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                                            @if(($item['tipo'] ?? null) === 'producto')
                                                <p class="font-bold text-slate-800">{{ $item['cantidad'] ?? 1 }}× {{ $item['nombre'] ?? 'Producto' }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $item['tamano'] ?? '' }} @if(!empty($item['masa'])) · {{ $item['masa'] }} @endif</p>
                                                @if(!empty($item['extras']))<p class="mt-1 text-xs text-slate-500">Extras: {{ collect($item['extras'])->pluck('nombre')->implode(', ') }}</p>@endif
                                                @if(!empty($item['nota_cliente']))<p class="mt-1 text-xs font-semibold text-amber-700">Nota: {{ $item['nota_cliente'] }}</p>@endif
                                            @else
                                                <p class="font-bold text-slate-800">🎁 {{ $item['nombre'] ?? 'Promoción' }}</p>
                                                <div class="mt-1 space-y-1 text-xs text-slate-500">
                                                    @foreach(($item['componentes'] ?? []) as $component)
                                                        @if(($component['tipo'] ?? null) === 'pizza')
                                                            <p>🍕 {{ $component['sabor'] ?? 'Pizza' }} · {{ $component['tamano'] ?? '' }} · {{ $component['masa'] ?? '' }}</p>
                                                        @else
                                                            <p>🥤 {{ $component['producto'] ?? 'Bebida' }}</p>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                @if(!$cancelled && $isOpen)
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @if($canOperate && $kitchenStatus === 'listo')
                                            <form method="POST" action="{{ route('salon.orders.serve', $order['id']) }}" data-show-loading>
                                                @csrf
                                                <button class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black text-white hover:bg-emerald-700"><i class="fa-solid fa-bell-concierge mr-2"></i>Marcar servida</button>
                                            </form>
                                        @endif
                                        @if($canCancel && $kitchenStatus === 'nuevo' && $paymentStatus !== 'paid')
                                            <form method="POST" action="{{ route('salon.orders.cancel', $order['id']) }}" data-show-loading onsubmit="return confirm('¿Cancelar esta ronda antes de que cocina la prepare?');">
                                                @csrf
                                                <button class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-black text-red-700 hover:bg-red-100"><i class="fa-solid fa-ban mr-2"></i>Cancelar ronda</button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-6 xl:sticky xl:top-5 xl:self-start">
            @if($isOpen && $canOperate)
                <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><h2 class="text-lg font-black text-slate-900">Ronda actual</h2><span id="draft-count" class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">0 ítems</span></div>
                    <div id="draft-empty" class="mt-4 rounded-2xl border border-dashed border-slate-300 p-6 text-center text-xs text-slate-500">Elegí productos o promociones para armar la próxima ronda.</div>
                    <div id="draft-list" class="mt-4 hidden space-y-2"></div>
                    <div id="draft-total-row" class="mt-4 hidden border-t border-slate-100 pt-4"><div class="flex items-center justify-between"><span class="text-sm font-bold text-slate-500">Estimado</span><span id="draft-total" class="text-xl font-black text-slate-900">₡0</span></div><p class="mt-1 text-[10px] text-slate-400">El servidor recalcula precios antes de crear la ronda.</p></div>
                    <form method="POST" action="{{ route('salon.sessions.rounds.store', $sessionData['id']) }}" class="mt-4" id="round-form" data-show-loading>
                        @csrf
                        <input type="hidden" name="items_json" id="items-json" value="[]">
                        <label class="mb-1.5 block text-xs font-bold text-slate-600">Nota general para cocina</label>
                        <textarea name="notas_cocina" rows="2" maxlength="1000" class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm" placeholder="Ej: sacar todo junto"></textarea>
                        <button id="send-round" disabled class="mt-3 inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-black text-white transition enabled:hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40"><i class="fa-solid fa-paper-plane"></i>Enviar ronda a cocina</button>
                    </form>
                </section>
            @endif

            <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">Cuenta de la mesa</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Total</span><span class="font-black text-slate-900">₡{{ number_format((float)($summary['total'] ?? 0), 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Pagado</span><span class="font-black text-emerald-700">₡{{ number_format((float)($summary['paid_total'] ?? 0), 0, ',', '.') }}</span></div>
                    <div class="flex justify-between border-t border-slate-100 pt-3"><span class="font-bold text-slate-700">Pendiente</span><span class="text-lg font-black text-red-600">₡{{ number_format((float)($summary['pending_total'] ?? 0), 0, ',', '.') }}</span></div>
                </div>

                @if($isOpen && $canPay && (float)($summary['pending_total'] ?? 0) > 0)
                    <form method="POST" action="{{ route('salon.sessions.pay', $sessionData['id']) }}" class="mt-5 space-y-3" data-show-loading onsubmit="return confirm('¿Confirmar el cobro completo pendiente de esta mesa?');">
                        @csrf
                        <div><label class="mb-1.5 block text-xs font-bold text-slate-600">Método de pago</label><select name="metodo_pago" id="payment-method" required class="w-full rounded-xl border-slate-200 px-3 py-2.5 text-sm"><option value="efectivo">Efectivo</option><option value="datafono">Datáfono</option></select></div>
                        <div id="payment-ref-wrap" class="hidden"><label class="mb-1.5 block text-xs font-bold text-slate-600">Referencia del datáfono</label><input name="payment_ref" maxlength="255" class="w-full rounded-xl border-slate-200 px-3 py-2.5 text-sm" placeholder="Opcional"></div>
                        <button class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 text-sm font-black text-white hover:bg-emerald-700"><i class="fa-solid fa-cash-register"></i>Cobrar cuenta pendiente</button>
                    </form>
                @elseif($isOpen && (float)($summary['total'] ?? 0) > 0 && (bool)($summary['all_paid'] ?? false))
                    <div class="mt-5 rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-700"><i class="fa-solid fa-circle-check mr-2"></i>Cuenta pagada.</div>
                @endif

                @if($isOpen)
                    <form method="POST" action="{{ route('salon.sessions.close', $sessionData['id']) }}" class="mt-4" data-show-loading onsubmit="return confirm('¿Cerrar y liberar esta mesa?');">
                        @csrf
                        <input type="hidden" name="sucursal_id" value="{{ $sessionData['sucursal']['id'] ?? '' }}">
                        <button @disabled(!($summary['can_close'] ?? false)) class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border px-4 text-sm font-black transition {{ ($summary['can_close'] ?? false) ? 'border-slate-300 bg-white text-slate-700 hover:border-red-200 hover:bg-red-50 hover:text-red-700' : 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' }}"><i class="fa-solid fa-door-open"></i>Cerrar y liberar mesa</button>
                    </form>
                    @if(!($summary['can_close'] ?? false))<p class="mt-2 text-center text-[11px] leading-4 text-slate-400">Para cerrar, todas las rondas deben estar servidas y pagadas.</p>@endif
                @endif
            </section>
        </aside>
    </div>
</div>

@if($isOpen && $canOperate)
<div id="config-modal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-[2rem] bg-white p-5 shadow-2xl sm:p-6">
        <div class="flex items-start justify-between gap-4"><div><p id="modal-kicker" class="text-xs font-black uppercase tracking-[0.16em] text-blue-600">Configurar</p><h3 id="modal-title" class="mt-1 text-2xl font-black text-slate-900"></h3></div><button type="button" id="modal-close" class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-500"><i class="fa-solid fa-xmark"></i></button></div>
        <div id="modal-body" class="mt-5 space-y-4"></div>
        <div class="mt-6 flex gap-3"><button type="button" id="modal-cancel" class="h-11 flex-1 rounded-xl border border-slate-200 font-bold text-slate-600">Cancelar</button><button type="button" id="modal-confirm" class="h-11 flex-1 rounded-xl bg-blue-600 font-black text-white">Agregar a la ronda</button></div>
    </div>
</div>

<script>
(() => {
    const catalog = @json($catalog);
    const products = catalog.products || [];
    const promotions = catalog.promotions || [];
    const masses = catalog.masses || [];
    const extras = catalog.extras || [];
    const flavors = catalog.flavors || [];
    const drinks = catalog.drinks || [];
    let draft = [];
    let activeTab = 'products';
    let modalConfirm = null;

    const $ = (id) => document.getElementById(id);
    const money = (value) => new Intl.NumberFormat('es-CR', { style: 'currency', currency: 'CRC', maximumFractionDigits: 0 }).format(Number(value || 0));
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));

    function extraPrice(extra, sizeName) {
        const s = String(sizeName || '').toLowerCase();
        if (s.includes('extra')) return Number(extra.precio_extragrande || 0);
        if (s.includes('grande')) return Number(extra.precio_grande || 0);
        if (s.includes('mediana')) return Number(extra.precio_mediana || 0);
        return Number(extra.precio_pequena || 0);
    }

    function renderCatalog() {
        const q = $('catalog-search').value.trim().toLowerCase();
        const productItems = products.filter(p => `${p.nombre} ${p.categoria || ''}`.toLowerCase().includes(q));
        $('products-grid').innerHTML = productItems.length ? productItems.map(p => `
            <button type="button" data-product="${p.id}" class="group rounded-2xl border border-slate-200 p-4 text-left transition hover:border-blue-300 hover:bg-blue-50/40">
                <div class="flex items-start justify-between gap-2"><div><p class="font-black text-slate-900">${escapeHtml(p.nombre)}</p><p class="mt-1 text-[11px] text-slate-400">${escapeHtml(p.categoria || (p.is_pizza ? 'Pizza' : 'Producto'))}</p></div><span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-500">${escapeHtml(p.tamano || '')}</span></div>
                <div class="mt-3 flex items-center justify-between"><span class="font-black text-slate-900">${money(p.precio)}</span><span class="grid h-8 w-8 place-items-center rounded-full bg-blue-600 text-white"><i class="fa-solid fa-plus"></i></span></div>
            </button>`).join('') : '<p class="col-span-full rounded-2xl bg-slate-50 p-6 text-center text-sm text-slate-500">No hay productos que coincidan.</p>';

        const promoItems = promotions.filter(p => `${p.nombre} ${p.descripcion || ''}`.toLowerCase().includes(q));
        $('promotions-grid').innerHTML = promoItems.length ? promoItems.map(p => `
            <button type="button" data-promotion="${p.id}" class="group rounded-2xl border border-slate-200 p-4 text-left transition hover:border-blue-300 hover:bg-blue-50/40">
                <p class="font-black text-slate-900">${escapeHtml(p.nombre)}</p><p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">${escapeHtml(p.descripcion || 'Promoción')}</p>
                <div class="mt-3 flex items-center justify-between"><span class="font-black text-slate-900">${money(p.precio)}</span><span class="grid h-8 w-8 place-items-center rounded-full bg-blue-600 text-white"><i class="fa-solid fa-plus"></i></span></div>
            </button>`).join('') : '<p class="col-span-full rounded-2xl bg-slate-50 p-6 text-center text-sm text-slate-500">No hay promociones que coincidan.</p>';

        document.querySelectorAll('[data-product]').forEach(el => el.addEventListener('click', () => openProduct(Number(el.dataset.product))));
        document.querySelectorAll('[data-promotion]').forEach(el => el.addEventListener('click', () => openPromotion(Number(el.dataset.promotion))));
    }

    function openModal(kicker, title, body, confirm) {
        $('modal-kicker').textContent = kicker;
        $('modal-title').textContent = title;
        $('modal-body').innerHTML = body;
        modalConfirm = confirm;
        $('config-modal').classList.remove('hidden');
        $('config-modal').classList.add('flex');
    }

    function closeModal() {
        $('config-modal').classList.add('hidden');
        $('config-modal').classList.remove('flex');
        modalConfirm = null;
    }

    function massOptions() {
        return '<option value="">Sin cambio</option>' + masses.map(m => `<option value="${m.id}">${escapeHtml(m.tipo)}${Number(m.precio_extra) ? ` (+${money(m.precio_extra)})` : ''}</option>`).join('');
    }

    function extrasHtml(prefix, size) {
        if (!extras.length) return '<p class="text-xs text-slate-400">No hay extras configurados.</p>';
        return `<div class="grid gap-2 sm:grid-cols-2">${extras.map(e => `<label class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs"><span><input type="checkbox" class="mr-2 rounded border-slate-300" data-extra-group="${prefix}" value="${e.id}">${escapeHtml(e.nombre)}</span><span class="font-bold text-slate-500">+${money(extraPrice(e, size))}</span></label>`).join('')}</div>`;
    }

    function openProduct(id) {
        const product = products.find(p => Number(p.id) === id);
        if (!product) return;
        const pizzaFields = product.is_pizza ? `
            <div><label class="mb-1.5 block text-xs font-bold text-slate-600">Masa</label><select id="cfg-mass" class="w-full rounded-xl border-slate-200 px-3 py-2.5 text-sm">${massOptions()}</select></div>
            <div><p class="mb-2 text-xs font-bold text-slate-600">Extras</p>${extrasHtml('product', product.tamano)}</div>` : '';
        openModal('Producto', product.nombre, `
            <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1.5 block text-xs font-bold text-slate-600">Cantidad</label><input id="cfg-qty" type="number" min="1" max="50" value="1" class="w-full rounded-xl border-slate-200 px-3 py-2.5 text-sm"></div>${product.is_pizza ? `<div><label class="mb-1.5 block text-xs font-bold text-slate-600">Tamaño</label><input disabled value="${escapeHtml(product.tamano || '')}" class="w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-2.5 text-sm"></div>` : ''}</div>
            ${pizzaFields}
            <div><label class="mb-1.5 block text-xs font-bold text-slate-600">Nota para este producto</label><textarea id="cfg-note" maxlength="500" rows="2" class="w-full rounded-xl border-slate-200 px-3 py-2.5 text-sm" placeholder="Sin cebolla, bien tostada..."></textarea></div>`, () => {
                const qty = Math.max(1, Number($('cfg-qty').value || 1));
                const massId = product.is_pizza ? Number($('cfg-mass').value || 0) || null : null;
                const extraIds = product.is_pizza ? [...document.querySelectorAll('[data-extra-group="product"]:checked')].map(e => Number(e.value)) : [];
                const mass = masses.find(m => Number(m.id) === massId);
                const extraTotal = extraIds.reduce((sum, extraId) => sum + extraPrice(extras.find(e => Number(e.id) === extraId) || {}, product.tamano), 0);
                const estimated = (Number(product.precio || 0) + Number(mass?.precio_extra || 0) + extraTotal) * qty;
                draft.push({ tipo: 'producto', producto_id: product.id, cantidad: qty, masa_id: massId, nota_cliente: $('cfg-note').value.trim() || null, extras: extraIds, _label: `${qty}× ${product.nombre}`, _estimated: estimated });
                renderDraft(); closeModal();
            });
    }

    function openPromotion(id) {
        const promo = promotions.find(p => Number(p.id) === id);
        if (!promo) return;
        const slots = [];
        (promo.componentes || []).forEach(component => {
            for (let i = 0; i < Number(component.cantidad || 1); i++) slots.push(component);
        });
        let pizzaNumber = 0, drinkNumber = 0;
        const slotHtml = slots.map((slot, idx) => {
            if (slot.tipo === 'pizza') {
                pizzaNumber++;
                return `<div class="rounded-2xl border border-slate-200 p-4" data-promo-slot="${idx}" data-type="pizza" data-size="${escapeHtml(slot.tamano || '')}">
                    <p class="font-black text-slate-800">Pizza ${pizzaNumber} · ${escapeHtml(slot.tamano || '')}</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2"><div><label class="mb-1 block text-xs font-bold text-slate-600">Sabor</label><select data-field="flavor" class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm"><option value="">Seleccione</option>${flavors.map(f => `<option value="${f.id}">${escapeHtml(f.nombre)}</option>`).join('')}</select></div><div><label class="mb-1 block text-xs font-bold text-slate-600">Masa</label><select data-field="mass" class="w-full rounded-xl border-slate-200 px-3 py-2 text-sm"><option value="">Seleccione</option>${masses.map(m => `<option value="${m.id}">${escapeHtml(m.tipo)}</option>`).join('')}</select></div></div>
                    <div class="mt-3"><p class="mb-2 text-xs font-bold text-slate-600">Extras</p>${extrasHtml(`promo-${idx}`, slot.tamano)}</div>
                    <textarea data-field="note" maxlength="500" rows="1" class="mt-3 w-full rounded-xl border-slate-200 px-3 py-2 text-sm" placeholder="Nota para esta pizza"></textarea>
                </div>`;
            }
            drinkNumber++;
            return `<div class="rounded-2xl border border-slate-200 p-4" data-promo-slot="${idx}" data-type="bebida"><p class="font-black text-slate-800">Bebida ${drinkNumber}</p><select data-field="drink" class="mt-3 w-full rounded-xl border-slate-200 px-3 py-2 text-sm"><option value="">Seleccione</option>${drinks.map(d => `<option value="${d.id}">${escapeHtml(d.nombre)}</option>`).join('')}</select></div>`;
        }).join('');
        openModal('Promoción', promo.nombre, `<div class="rounded-2xl bg-blue-50 p-4 text-sm text-blue-800"><span class="font-black">Base ${money(promo.precio)}</span><br><span class="text-xs">Configurá todos los componentes antes de agregarla.</span></div>${slotHtml}`, () => {
            const configured = [];
            let estimated = Number(promo.precio || 0);
            let valid = true;
            document.querySelectorAll('[data-promo-slot]').forEach(slot => {
                const type = slot.dataset.type;
                if (type === 'pizza') {
                    const flavorId = Number(slot.querySelector('[data-field="flavor"]').value || 0);
                    const massId = Number(slot.querySelector('[data-field="mass"]').value || 0);
                    if (!flavorId || !massId) valid = false;
                    const idx = slot.dataset.promoSlot;
                    const extraIds = [...slot.querySelectorAll(`[data-extra-group="promo-${idx}"]:checked`)].map(e => Number(e.value));
                    extraIds.forEach(extraId => estimated += extraPrice(extras.find(e => Number(e.id) === extraId) || {}, slot.dataset.size));
                    configured.push({ tipo: 'pizza', sabor_id: flavorId || null, masa_id: massId || null, extras: extraIds, nota_cliente: slot.querySelector('[data-field="note"]').value.trim() || null });
                } else {
                    const productId = Number(slot.querySelector('[data-field="drink"]').value || 0);
                    if (!productId) valid = false;
                    configured.push({ tipo: 'bebida', producto_id: productId || null, extras: [] });
                }
            });
            if (!valid) { alert('Completá sabor, masa y bebida de todos los componentes.'); return; }
            draft.push({ tipo: 'promocion', promocion_id: promo.id, productos: configured, _label: `🎁 ${promo.nombre}`, _estimated: estimated });
            renderDraft(); closeModal();
        });
    }

    function cleanItem(item) {
        const copy = { ...item }; delete copy._label; delete copy._estimated; return copy;
    }

    function renderDraft() {
        $('draft-count').textContent = `${draft.length} ${draft.length === 1 ? 'ítem' : 'ítems'}`;
        $('draft-empty').classList.toggle('hidden', draft.length > 0);
        $('draft-list').classList.toggle('hidden', draft.length === 0);
        $('draft-total-row').classList.toggle('hidden', draft.length === 0);
        $('send-round').disabled = draft.length === 0;
        $('draft-list').innerHTML = draft.map((item, index) => `<div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 p-3"><div class="min-w-0"><p class="truncate text-sm font-bold text-slate-800">${escapeHtml(item._label)}</p><p class="mt-0.5 text-xs text-slate-400">${money(item._estimated)}</p></div><button type="button" data-remove-draft="${index}" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white text-red-500 shadow-sm"><i class="fa-solid fa-xmark"></i></button></div>`).join('');
        const total = draft.reduce((sum, item) => sum + Number(item._estimated || 0), 0);
        $('draft-total').textContent = money(total);
        $('items-json').value = JSON.stringify(draft.map(cleanItem));
        document.querySelectorAll('[data-remove-draft]').forEach(btn => btn.addEventListener('click', () => { draft.splice(Number(btn.dataset.removeDraft), 1); renderDraft(); }));
    }

    document.querySelectorAll('.salon-tab').forEach(btn => btn.addEventListener('click', () => {
        activeTab = btn.dataset.tab;
        document.querySelectorAll('.salon-tab').forEach(tab => { tab.classList.remove('bg-white','text-slate-900','shadow-sm'); tab.classList.add('text-slate-500'); });
        btn.classList.add('bg-white','text-slate-900','shadow-sm'); btn.classList.remove('text-slate-500');
        $('products-panel').classList.toggle('hidden', activeTab !== 'products');
        $('promotions-panel').classList.toggle('hidden', activeTab !== 'promotions');
    }));
    $('catalog-search').addEventListener('input', renderCatalog);
    $('modal-close').addEventListener('click', closeModal);
    $('modal-cancel').addEventListener('click', closeModal);
    $('modal-confirm').addEventListener('click', () => modalConfirm && modalConfirm());
    $('config-modal').addEventListener('click', e => { if (e.target === $('config-modal')) closeModal(); });
    renderCatalog(); renderDraft();
})();
</script>
@endif

<script>
(() => {
    const method = document.getElementById('payment-method');
    const refWrap = document.getElementById('payment-ref-wrap');
    if (!method || !refWrap) return;
    const sync = () => refWrap.classList.toggle('hidden', method.value !== 'datafono');
    method.addEventListener('change', sync); sync();
})();
</script>
@endsection
