<div class="mb-8 overflow-x-auto rounded-3xl border border-slate-200 bg-white p-2 shadow-sm">
    <nav class="flex min-w-max items-center gap-1 text-sm font-semibold text-slate-600" aria-label="Operación administrativa">
        @php
            $operationLinks = [
                ['route' => 'admin.pedidos.index', 'pattern' => 'admin.pedidos.*', 'label' => 'Pedidos', 'icon' => 'fa-receipt'],
                ['route' => 'admin.usuarios.index', 'pattern' => 'admin.usuarios.*', 'label' => 'Usuarios', 'icon' => 'fa-users'],
                ['route' => 'admin.resenas.index', 'pattern' => 'admin.resenas.*', 'label' => 'Reseñas', 'icon' => 'fa-star'],
                ['route' => 'kitchen.index', 'pattern' => 'kitchen.*', 'label' => 'Cocina', 'icon' => 'fa-fire-burner'],
                ['route' => 'admin.ventas', 'pattern' => 'admin.ventas*', 'label' => 'Ventas', 'icon' => 'fa-chart-line'],
            ];
        @endphp

        @foreach($operationLinks as $link)
            <a href="{{ route($link['route']) }}" data-show-loading
               class="inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 transition {{ request()->routeIs($link['pattern']) ? 'bg-[#071426] text-white shadow-lg shadow-slate-950/10' : 'hover:bg-slate-50 hover:text-blue-600' }}">
                <i class="fa-solid {{ $link['icon'] }} text-xs"></i>
                <span>{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>
