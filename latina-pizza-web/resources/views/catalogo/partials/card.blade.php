@php
    $promedio = round((float) ($sabor['promedio'] ?? 0), 1);
    $totalResenas = (int) ($sabor['total_resenas'] ?? 0);
    $precios = collect($sabor['tamanos'] ?? [])->pluck('precio_base')->filter(fn ($precio) => is_numeric($precio));
    $precioDesde = $precios->isNotEmpty() ? (float) $precios->min() : null;
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-[26px] border border-slate-200 bg-white p-[18px] shadow-sm transition duration-300 hover:-translate-y-1.5 hover:border-blue-100 hover:shadow-xl">
    <button
        type="button"
        data-catalog-product
        data-sabor='@json($sabor)'
        class="block w-full text-left"
        aria-label="Personalizar {{ $sabor['sabor_nombre'] }}"
    >
        <div class="relative h-52 overflow-hidden rounded-[20px] bg-gradient-to-br from-blue-50 via-white to-red-50 sm:h-56">
            <img
                src="{{ $sabor['imagen'] }}"
                alt="{{ $sabor['sabor_nombre'] }}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            >
            <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-slate-950/20 to-transparent"></div>
            <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1.5 text-[11px] font-semibold text-slate-700 shadow-sm backdrop-blur">
                <i class="fas fa-star text-amber-400"></i>
                {{ number_format($promedio, 1) }}
            </span>
        </div>
    </button>

    <div class="flex flex-1 flex-col pt-5">
        <button
            type="button"
            data-catalog-product
            data-sabor='@json($sabor)'
            class="text-left"
        >
            <h3 class="text-xl font-bold tracking-[-0.02em] text-slate-950 transition group-hover:text-blue-600">{{ $sabor['sabor_nombre'] }}</h3>
            <p class="mt-2 line-clamp-2 min-h-[40px] text-sm leading-5 text-slate-500">{{ $sabor['descripcion'] }}</p>
        </button>

        <div class="mt-4 flex items-center justify-between gap-3 text-xs">
            <a href="{{ route('sabor.resenas', $sabor['sabor_id']) }}" class="font-semibold text-slate-500 transition hover:text-blue-600">
                {{ $totalResenas > 0 ? $totalResenas.' reseñas' : __('catalogo.ver_resenas') }}
            </a>
            <span class="text-slate-400">{{ count($sabor['tamanos'] ?? []) }} tamaños</span>
        </div>

        <div class="mt-auto flex items-end justify-between gap-4 pt-6">
            <div>
                @if ($precioDesde !== null)
                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400">Desde</p>
                    <p class="mt-1 text-xl font-bold text-red-500">₡{{ number_format($precioDesde, 0) }}</p>
                @else
                    <p class="text-sm font-semibold text-red-500">Ver tamaños</p>
                @endif
            </div>

            <button
                type="button"
                data-catalog-product
                data-sabor='@json($sabor)'
                class="inline-flex h-11 items-center gap-2 rounded-full bg-blue-500 px-4 text-sm font-semibold text-white shadow-lg shadow-blue-950/10 transition hover:bg-blue-600 active:scale-[0.97]"
            >
                Personalizar
                <span class="text-lg leading-none">+</span>
            </button>
        </div>
    </div>
</article>
