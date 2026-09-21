<div class="container mx-auto px-4 py-6"
     data-catalog-root
     data-api-url="{{ config('app.api_url') }}"
     data-login-url="{{ route('login') }}"
     data-i18n='{{ json_encode(trans('catalogo'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}'>
    <h2 class="text-3xl font-bold text-center text-red-600 mb-8">{{ __('catalogo.menu_latina') }}</h2>

    <div class="flex flex-wrap justify-center gap-3 mb-10">
        <a href="{{ route('catalogo.index') }}"
           class="px-4 py-2 rounded-full border transition {{ is_null($categoriaSeleccionada) ? 'bg-red-600 text-white' : 'bg-white text-red-600 border-red-600 hover:bg-red-100' }}">
            {{ __('catalogo.todos') }}
        </a>
        @foreach ($categorias as $cat)
            <a href="{{ route('catalogo.index', ['categoria_id' => $cat['id']]) }}"
               class="px-4 py-2 rounded-full border transition {{ $categoriaSeleccionada == $cat['id'] ? 'bg-red-600 text-white' : 'bg-white text-red-600 border-red-600 hover:bg-red-100' }}">
                {{ $cat['nombre'] }}
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach ($sabores as $sabor)
            @include('catalogo.partials.card', ['sabor' => $sabor])
        @endforeach
    </div>

    @if(count($promociones) > 0)
        <div class="mt-14">
            <h2 class="text-2xl font-bold text-red-600 mb-6 flex items-center gap-2">🎉 {{ __('catalogo.promociones_especiales') }}</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                @foreach($promociones as $promo)
                    <div class="bg-white rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300">
                        <div class="relative">
                            <img src="{{ $promo['imagen'] ?? asset('images/promociones_grade_extragrande.jpg') }}"
                                 alt="{{ $promo['nombre'] }}"
                                 class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-105">

                            @if($promo['incluye_bebida'] ?? false)
                                <span class="absolute top-3 left-3 bg-yellow-400 text-black text-xs font-semibold px-3 py-1 rounded-full shadow-md">
                                    🥤 {{ __('catalogo.incluye_bebida') }}
                                </span>
                            @endif
                        </div>

                        <div class="p-4 flex flex-col gap-2">
                            <h3 class="text-sm font-bold text-gray-800 truncate">{{ $promo['nombre'] }}</h3>
                            <p class="text-red-600 font-bold text-lg">₡{{ number_format($promo['precio_total'], 2) }}</p>
                            <button type="button"
                                    data-promotion-id="{{ $promo['id'] }}"
                                    class="mt-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-4 rounded-full w-full shadow transition">
                                🛒 {{ __('catalogo.personalizar_agregar') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @include('catalogo.partials.modal')
    @include('catalogo.partials.modal_promocion')
</div>

<style>
@keyframes fade-in-down { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in-down { animation: fade-in-down 0.3s ease-out; }
</style>
