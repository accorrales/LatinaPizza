@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h2 class="text-3xl font-bold text-center text-red-600 mb-6">Menú Latina</h2>

        <!-- Filtros de categorías -->
        <div class="flex flex-wrap justify-center gap-3 mb-8">
            <a href="{{ route('catalogo.index') }}"
            class="px-4 py-2 rounded-full border transition
                    {{ is_null($categoriaSeleccionada) ? 'bg-red-600 text-white' : 'bg-white text-red-600 border-red-600 hover:bg-red-100' }}">
                Todos
            </a>
            @foreach ($categorias as $cat)
                <a href="{{ route('catalogo.index', ['categoria_id' => $cat['id']]) }}"
                class="px-4 py-2 rounded-full border transition
                    {{ $categoriaSeleccionada == $cat['id'] ? 'bg-red-600 text-white' : 'bg-white text-red-600 border-red-600 hover:bg-red-100' }}">
                    {{ $cat['nombre'] }}
                </a>
            @endforeach
        </div>

        <!-- Vista agrupada por tamaño con carrusel -->
        @if(isset($agrupadosPorTamanio))
            @foreach($agrupadosPorTamanio as $tamanio => $grupo)
            <h3 class="text-xl font-bold text-gray-800 mb-2 mt-6">{{ $tamanio }}</h3>

                @if(count($grupo) > 0)
                    <div class="relative group">
                        <!-- Flecha izquierda -->
                        <button onclick="scrollLeft('{{ Str::slug($tamanio) }}')"
                                class="hidden md:flex items-center justify-center absolute left-0 top-1/2 transform -translate-y-1/2 z-20 w-10 h-10 bg-white border rounded-full shadow hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- Contenedor deslizable -->
                        <div id="scroll-{{ Str::slug($tamanio) }}"
                            class="flex overflow-x-auto gap-4 pb-2 scrollbar-hide scroll-smooth px-8 md:px-12">
                            @foreach ($grupo as $producto)
                                <div class="flex-shrink-0 w-72">
                                    @include('catalogo.partials.card', ['producto' => $producto])
                                </div>
                            @endforeach
                        </div>

                        <!-- Flecha derecha -->
                        <button onclick="scrollRight('{{ Str::slug($tamanio) }}')"
                                class="hidden md:flex items-center justify-center absolute right-0 top-1/2 transform -translate-y-1/2 z-20 w-10 h-10 bg-white border rounded-full shadow hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                @else
                    <div class="text-center text-gray-500 italic mb-8">
                        No hay productos disponibles para este tamaño por ahora.
                    </div>
                @endif

        @endforeach

        @else
            <!-- Vista sin agrupación -->
            <div class="flex overflow-x-auto gap-4 pb-2 scrollbar-hide">
                @foreach ($productos as $producto)
                    <div class="flex-shrink-0 w-72">
                        @include('catalogo.partials.card', ['producto' => $producto])
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Modal -->
        <div id="modalDetalle" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm hidden transition-opacity duration-300">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6 relative animate-fade-in-down">
                <button onclick="cerrarModal()" class="absolute top-3 right-3 text-gray-500 hover:text-black text-2xl font-bold">&times;</button>
                <img id="modalImagen" class="w-full h-48 object-cover rounded-xl mb-4 shadow-sm" src="" alt="Imagen del producto">
                <h2 id="modalNombre" class="text-2xl font-bold text-red-600 mb-2"></h2>
                <p id="modalDescripcion" class="text-gray-700 mb-4 text-sm leading-relaxed"></p>
                <p id="modalPrecio" class="text-lg font-bold text-green-600"></p>
            </div>
        </div>
    </div>
    <style>
        @keyframes fade-in-down {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-down {
            animation: fade-in-down 0.3s ease-out;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        .relative.group {
            padding-left: 2rem;
            padding-right: 2rem;
        }
    </style>
@endsection
@section('scripts')
    <script>
        window.scrollLeft = function(id) {
            const el = document.getElementById('scroll-' + id);
            if (el) el.scrollBy({ left: -500, behavior: 'smooth' });
        }

        window.scrollRight = function(id) {
            const el = document.getElementById('scroll-' + id);
            if (el) el.scrollBy({ left: 500, behavior: 'smooth' });
        }

        window.mostrarDetalle = function(producto) {
            document.getElementById('modalImagen').src = producto.imagen;
            document.getElementById('modalNombre').textContent = producto.nombre;
            document.getElementById('modalDescripcion').textContent = producto.descripcion;
            document.getElementById('modalPrecio').textContent = '₡' + parseFloat(producto.precio).toFixed(2);
            document.getElementById('modalDetalle').classList.remove('hidden');
        }

        window.cerrarModal = function() {
            document.getElementById('modalDetalle').classList.add('hidden');
        }
    </script>
@endsection









