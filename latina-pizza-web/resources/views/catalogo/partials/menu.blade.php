<div
    class="mx-auto w-full max-w-[1296px] px-0 sm:px-2"
    data-catalog-root
    data-api-url="{{ config('app.api_url') }}"
    data-login-url="{{ route('login') }}"
    data-i18n='{{ json_encode(trans('catalogo'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}'
>
    <section class="relative overflow-hidden rounded-[28px] bg-[#071426] px-6 py-9 text-white sm:px-10 sm:py-12 lg:px-14 lg:py-14">
        <div class="absolute -right-28 -top-36 h-[360px] w-[360px] rounded-full bg-blue-600/25 blur-3xl"></div>
        <div class="absolute -bottom-36 right-28 h-[300px] w-[300px] rounded-full bg-red-500/20 blur-3xl"></div>
        <div class="relative z-10 grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
            <div class="max-w-2xl">
                <span class="inline-flex rounded-full border border-white/10 bg-white/10 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-white">Menú Latina Pizza</span>
                <h1 class="mt-6 text-4xl font-bold tracking-[-0.04em] sm:text-5xl lg:text-6xl">Elegí, personalizá<br class="hidden sm:block"> y disfrutá.</h1>
                <p class="mt-5 max-w-xl text-sm leading-7 text-slate-300 sm:text-base">Encontrá tu pizza favorita, elegí el tamaño y armala con la masa y extras que querás.</p>
            </div>
            <a href="#pizzas" class="inline-flex w-fit items-center gap-2 rounded-full bg-red-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-red-600">
                Ver pizzas
                <i class="fas fa-arrow-down text-xs"></i>
            </a>
        </div>
    </section>

    <section class="sticky top-[76px] z-30 -mx-4 mt-8 border-y border-slate-100 bg-slate-50/90 px-4 py-4 backdrop-blur-xl sm:mx-0 sm:rounded-2xl sm:border">
        <div class="home-hide-scrollbar flex gap-2.5 overflow-x-auto pb-0.5">
            <a href="{{ route('catalogo.index') }}"
               class="shrink-0 rounded-full px-5 py-2.5 text-sm font-semibold transition {{ is_null($categoriaSeleccionada) ? 'bg-blue-500 text-white shadow-lg shadow-blue-950/10' : 'border border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:text-blue-600' }}">
                {{ __('catalogo.todos') }}
            </a>
            @foreach ($categorias as $cat)
                <a href="{{ route('catalogo.index', ['categoria_id' => $cat['id']]) }}"
                   class="shrink-0 rounded-full px-5 py-2.5 text-sm font-semibold transition {{ $categoriaSeleccionada == $cat['id'] ? 'bg-blue-500 text-white shadow-lg shadow-blue-950/10' : 'border border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:text-blue-600' }}">
                    {{ $cat['nombre'] }}
                </a>
            @endforeach
        </div>
    </section>

    <section id="pizzas" class="scroll-mt-36 py-10 sm:py-14">
        <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-[-0.025em] text-slate-950 sm:text-3xl">
                    {{ is_null($categoriaSeleccionada) ? 'Nuestras pizzas' : 'Resultados' }}
                </h2>
                <p class="mt-2 text-sm text-slate-500">Tocá una pizza para escoger tamaño, masa, sabores y extras.</p>
            </div>
            <span class="w-fit rounded-full bg-[#F6F9FF] px-4 py-2 text-xs font-semibold text-blue-600">{{ count($sabores) }} opciones</span>
        </div>

        @if(count($sabores) > 0)
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($sabores as $sabor)
                    @include('catalogo.partials.card', ['sabor' => $sabor])
                @endforeach
            </div>
        @else
            <div class="rounded-[26px] border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-blue-50 text-2xl">🍕</span>
                <h3 class="mt-5 text-lg font-bold text-slate-900">No encontramos pizzas en esta categoría</h3>
                <p class="mt-2 text-sm text-slate-500">Probá viendo todo el menú.</p>
                <a href="{{ route('catalogo.index') }}" class="mt-5 inline-flex rounded-full bg-blue-500 px-5 py-3 text-sm font-semibold text-white">Ver todo</a>
            </div>
        @endif
    </section>

    @if(count($promociones) > 0)
        <section class="pb-8 pt-4 sm:pb-14" aria-labelledby="catalog-promos-title">
            <div class="mb-7">
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-red-500">Ofertas</span>
                <h2 id="catalog-promos-title" class="mt-2 text-2xl font-bold tracking-[-0.025em] text-slate-950 sm:text-3xl">{{ __('catalogo.promociones_especiales') }}</h2>
                <p class="mt-2 text-sm text-slate-500">Combos y promos listas para personalizar.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach($promociones as $promo)
                    <article class="group relative min-h-[330px] overflow-hidden rounded-[28px] bg-[#071426] shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                        <img
                            src="{{ $promo['imagen'] ?? asset('images/promociones_grade_extragrande.jpg') }}"
                            alt="{{ $promo['nombre'] }}"
                            class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-[#071426]/95 via-[#071426]/55 to-[#071426]/5"></div>

                        @if($promo['incluye_bebida'] ?? false)
                            <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1.5 text-[10px] font-semibold text-blue-600 shadow-sm backdrop-blur">
                                {{ __('catalogo.incluye_bebida') }}
                            </span>
                        @endif

                        <div class="absolute inset-x-0 bottom-0 p-6 text-white">
                            <h3 class="text-2xl font-bold tracking-[-0.02em]">{{ $promo['nombre'] }}</h3>
                            @if (!empty($promo['descripcion']))
                                <p class="mt-2 line-clamp-2 text-sm leading-5 text-slate-300">{{ $promo['descripcion'] }}</p>
                            @endif
                            <div class="mt-5 flex items-center justify-between gap-4">
                                <p class="text-xl font-bold">₡{{ number_format((float) $promo['precio_total'], 0) }}</p>
                                <button
                                    type="button"
                                    data-promotion-id="{{ $promo['id'] }}"
                                    class="rounded-full bg-red-500 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-red-600 active:scale-[0.97]"
                                >
                                    Personalizar
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @include('catalogo.partials.modal')
    @include('catalogo.partials.modal_promocion')
</div>
