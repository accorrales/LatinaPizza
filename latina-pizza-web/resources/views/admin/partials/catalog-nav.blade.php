<div class="mb-8 overflow-x-auto rounded-3xl border border-slate-200 bg-white p-2 shadow-sm">
    <nav class="flex min-w-max items-center gap-1 text-sm font-semibold text-slate-600" aria-label="Gestión de catálogo">
        @php
            $catalogLinks = [
                ['route' => 'admin.productos.index', 'pattern' => 'admin.productos.*', 'label' => 'Productos', 'icon' => 'fa-box-open'],
                ['route' => 'admin.categorias.index', 'pattern' => 'admin.categorias.*', 'label' => 'Categorías', 'icon' => 'fa-layer-group'],
                ['route' => 'admin.sabores.index', 'pattern' => 'admin.sabores.*', 'label' => 'Sabores', 'icon' => 'fa-pizza-slice'],
                ['route' => 'admin.tamanos.index', 'pattern' => 'admin.tamanos.*', 'label' => 'Tamaños', 'icon' => 'fa-up-right-and-down-left-from-center'],
                ['route' => 'admin.masas.index', 'pattern' => 'admin.masas.*', 'label' => 'Masas', 'icon' => 'fa-circle-dot'],
                ['route' => 'admin.extras.index', 'pattern' => 'admin.extras.*', 'label' => 'Extras', 'icon' => 'fa-plus'],
                ['route' => 'admin.promociones.index', 'pattern' => 'admin.promociones.*', 'label' => 'Promociones', 'icon' => 'fa-tags'],
            ];
        @endphp

        @foreach($catalogLinks as $link)
            <a href="{{ route($link['route']) }}" data-show-loading
               class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 transition {{ request()->routeIs($link['pattern']) ? 'bg-[#071426] text-white shadow-lg shadow-slate-950/10' : 'hover:bg-slate-50 hover:text-blue-600' }}">
                <i class="fa-solid {{ $link['icon'] }} text-xs"></i>
                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>
