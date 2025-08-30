    <div class="container mx-auto px-4 py-6">
        <!-- ✅ Título -->
        <h2 class="text-3xl font-bold text-center text-red-600 mb-8">{{ __('catalogo.menu_latina') }}</h2>

        <!-- ✅ Filtros por categoría -->
        <div class="flex flex-wrap justify-center gap-3 mb-10">
            <a href="{{ route('catalogo.index') }}"
               class="px-4 py-2 rounded-full border transition
               {{ is_null($categoriaSeleccionada) ? 'bg-red-600 text-white' : 'bg-white text-red-600 border-red-600 hover:bg-red-100' }}">
                {{ __('catalogo.todos') }}
            </a>
            @foreach ($categorias as $cat)
                <a href="{{ route('catalogo.index', ['categoria_id' => $cat['id']]) }}"
                   class="px-4 py-2 rounded-full border transition
                   {{ $categoriaSeleccionada == $cat['id'] ? 'bg-red-600 text-white' : 'bg-white text-red-600 border-red-600 hover:bg-red-100' }}">
                    {{ $cat['nombre'] }}
                </a>
            @endforeach
        </div>

        <!-- ✅ SABORES -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($sabores as $sabor)
                @include('catalogo.partials.card', ['sabor' => $sabor])
            @endforeach
        </div>

        <!-- ✅ PROMOCIONES -->
        @if(count($promociones) > 0)
            <div class="mt-14">
                <h2 class="text-2xl font-bold text-red-600 mb-6 flex items-center gap-2">🎉 {{ __('catalogo.promociones_especiales') }}</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                    @foreach($promociones as $promo)
                        <div class="bg-white rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300">

                            <!-- Imagen protagonista -->
                            <div class="relative">
                                <img src="{{ $promo['imagen'] ?? 'https://via.placeholder.com/600x400?text=Promoción' }}"
                                     alt="{{ $promo['nombre'] }}"
                                     class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-105">

                                <!-- Badge si incluye bebida -->
                                @if($promo['incluye_bebida'] ?? false)
                                    <span class="absolute top-3 left-3 bg-yellow-400 text-black text-xs font-semibold px-3 py-1 rounded-full shadow-md">
                                        🥤 {{ __('catalogo.incluye_bebida') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Contenido -->
                            <div class="p-4 flex flex-col gap-2">
                                <h3 class="text-s font-bold text-gray-800 truncate">{{ $promo['nombre'] }}</h3>

                                <p class="text-red-600 font-bold text-lg">
                                    ₡{{ number_format($promo['precio_total'], 2) }}
                                </p>

                                <button
                                    onclick="abrirModalPromocion({{ $promo['id'] }})"
                                    class="mt-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 px-4 rounded-full w-full shadow transition">
                                    🛒 {{ __('catalogo.personalizar_agregar') }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Modales -->
        @include('catalogo.partials.modal')
        @include('catalogo.partials.modal_promocion')

        {{-- ✅ Pasar traducciones a JS --}}
        <script>
            window.i18n = @json(trans('catalogo'));
        </script>

        <script>
            fetch('http://127.0.0.1:8001/sanctum/csrf-cookie', { credentials: 'include' })
                .then(() => console.log("🟢 " + (window.i18n.csrf_ok || 'CSRF OK')))
                .catch(err => console.error("❌ " + (window.i18n.csrf_error || 'CSRF error'), err));
        </script>
    </div>

<script>
    const API_URL = "{{ config('app.api_url') }}";
</script>

<script>
    function cambiarMetodoEntrega() {
        if (confirm("{{ __('catalogo.confirmar_cambio_metodo') }}")) {
            localStorage.removeItem('tipo_pedido');
            localStorage.removeItem('sucursal_id');
            localStorage.removeItem('direccion_id');

            fetch('http://127.0.0.1:8001/guardar-tipo-pedido', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ tipo: null })
            }).then(() => location.reload());
        }
    }
</script>

<script>
    let extrasData = [];
    let precioBase = 0;
    let precioMasa = 0;

    window.abrirModal = async function(element) {
        mostrarLoading();
        const sabor = JSON.parse(element.dataset.sabor);

        precioBase = 0;
        precioMasa = 0;
        extrasData = [];

        document.getElementById('modalImagen').src = sabor.imagen;
        document.getElementById('modalNombre').textContent = sabor.sabor_nombre;
        document.getElementById('modalDescripcion').textContent = sabor.descripcion;

        // Tamaños
        const tamanosHtml = sabor.tamanos.map(t => `
            <label class="flex items-center gap-2 border px-3 py-1 rounded cursor-pointer text-sm text-gray-700">
                <input type="radio" name="producto_id" value="${t.producto_id}" data-precio="${t.precio_base}" onchange="cambiarTamano(${t.precio_base}, '${t.tamano_nombre.toLowerCase()}')" required>
                ${t.tamano_nombre} - ₡${parseFloat(t.precio_base).toFixed(2)}
            </label>
        `).join('');
        document.getElementById('modalTamanos').innerHTML = tamanosHtml;

        // Masas
        try {
            const res = await fetch(`http://127.0.0.1:8001/api/masas`);
            const masas = await res.json();
            const masaSelect = document.getElementById('masa');
            masaSelect.innerHTML = masas.map(m => `<option value="${m.id}" data-precio="${m.precio_extra}">${m.tipo} (+₡${m.precio_extra})</option>`).join('');
            masaSelect.onchange = function () {
                const precio = parseFloat(this.selectedOptions[0].dataset.precio);
                precioMasa = isNaN(precio) ? 0 : precio;
                actualizarTotal();
            };
        } catch {
            document.getElementById('masa').innerHTML = `<option>${window.i18n.error_cargar_masas}</option>`;
        }

        // Extras
        try {
            const res = await fetch(`http://127.0.0.1:8001/api/extras`);
            extrasData = await res.json();
            renderizarExtras('precio_pequena'); // default
        } catch {
            document.getElementById('extrasOpciones').innerHTML = `<p class="text-xs text-red-500">${window.i18n.error_cargar_extras}</p>`;
        }

        document.getElementById('modalSabor').classList.remove('hidden');
        ocultarLoading();
    };

    function cambiarTamano(precio, tamanoNombre) {
        precioBase = parseFloat(precio);
        let key = 'precio_pequena';
        if (tamanoNombre.includes('mediana')) key = 'precio_mediana';
        else if (tamanoNombre.includes('grande') && !tamanoNombre.includes('extra')) key = 'precio_grande';
        else if (tamanoNombre.includes('extra')) key = 'precio_extragrande';

        renderizarExtras(key);
        actualizarTotal();
    }

    function renderizarExtras(clave) {
        const contenedor = document.getElementById('extrasOpciones');
        contenedor.innerHTML = extrasData.map(extra => {
            const precio = parseFloat(extra[clave]) || 0;
            return `
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="extras[]" value="${extra.id}" data-precio="${precio}" onchange="actualizarTotal()">
                    ${extra.nombre} (+₡${precio.toFixed(0)})
                </label>
            `;
        }).join('');
    }

    function actualizarTotal() {
        const extras = document.querySelectorAll('input[name="extras[]"]:checked');
        let totalExtras = 0;
        extras.forEach(e => totalExtras += parseFloat(e.dataset.precio));
        const total = precioBase + precioMasa + totalExtras;
        document.getElementById('precioTotal').textContent = total.toFixed(2);
        document.getElementById('inputPrecioTotal').value = total.toFixed(2);
    }

    function cerrarModal() {
        document.getElementById('modalSabor').classList.add('hidden');
        document.getElementById('modalConfirmacion').classList.add('hidden');
    }
    function cerrarModalConfirmacion() {
        document.getElementById('modalConfirmacion').classList.add('hidden');
    }

    document.getElementById('formAgregarProducto').addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        mostrarLoading();

        try {
            const res = await fetch('/carrito/agregar', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            });
            if (!res.ok) throw new Error("error");
            document.getElementById('modalSabor').classList.add('hidden');
            document.getElementById('modalConfirmacion').classList.remove('hidden');
        } catch (error) {
            console.error('❌', error);
            alert(window.i18n.producto_error);
        } finally {
            ocultarLoading();
        }
    });
</script>

<script>
    let promocionSeleccionadaId = null;
    let precioBasePromocion = 0;
    let datosExtras = [];

    const API_BASE = 'http://127.0.0.1:8001/api';

    // GET genérico: agrega Authorization solo si hay token
    async function apiGet(path) {
        const headers = { 'Accept': 'application/json' };
        const token = (localStorage.getItem('token') || "{{ session('token') }}")?.trim();
        if (token) headers['Authorization'] = `Bearer ${token}`;
        const res = await fetch(`${API_BASE}${path}`, { headers, credentials: 'include' });
        if (!res.ok) throw new Error(`${path} -> ${res.status}`);
        const json = await res.json();
        return json?.data ?? json; // por si el API viene envuelto en {data: ...}
    }

    async function abrirModalPromocion(id) {
        mostrarLoading();
        promocionSeleccionadaId = id;
        document.getElementById('modalPromocion').classList.remove('hidden');

        try {
        // ✅ Endpoints públicos (sin /admin)
        const [promo, masas, sabores, refrescos, extras] = await Promise.all([
            apiGet(`/promociones/${id}`),
            apiGet(`/masas`),
            apiGet(`/sabores`),
            apiGet(`/bebidas`),     // ← antes usabas /admin/productos?categoria_id=2
            apiGet(`/extras`),
        ]);

        // guarda extras global si lo necesitás en otros handlers
        datosExtras = extras;

        // Precio base de la promo
        precioBasePromocion = parseFloat(promo.precio_base ?? promo.precio_total ?? 0);

        const contieneBebida   = promo.incluye_bebida === true;
        const componentesPizza = (promo.componentes || []).filter(c => c.tipo === 'pizza');

        let bloquesPizza = '';
        let contadorGlobal = 1;

        componentesPizza.forEach((c, i) => {
            const tamanoTexto = (c.tamano?.nombre || 'grande').toLowerCase();
            let clavePrecio = 'precio_grande';
            if (tamanoTexto.includes('peque'))      clavePrecio = 'precio_pequena';
            else if (tamanoTexto.includes('mediana')) clavePrecio = 'precio_mediana';
            else if (tamanoTexto.includes('extra'))   clavePrecio = 'precio_extragrande';

            for (let j = 0; j < (c.cantidad || 1); j++) {
            const index = `${i}_${j}`;
            const pizzaLabel = (window.i18n.pizza_label || 'Pizza :num - Size :tamano')
                .replace(':num', contadorGlobal++)
                .replace(':tamano', c.tamano?.nombre || '');

            const saborSelect = (sabores || []).map(s => `<option value="${s.id}">${s.nombre}</option>`).join('');
            const masaSelect  = (masas || []).map(m => `<option value="${m.id}">${m.tipo}</option>`).join('');

            const extrasHTML = (datosExtras || []).map(e => {
                const precio = parseFloat(e[clavePrecio]) || 0;
                return `
                <label class="block text-sm">
                    <input type="checkbox" name="extrasPizza${index}[]" value="${e.id}" data-precio="${precio}">
                    ${e.nombre} (+₡${precio})
                </label>`;
            }).join('');

            bloquesPizza += `
                <div class="mb-6 border-b pb-4">
                <h3 class="text-sm font-bold text-gray-800 mb-2">🍕 ${pizzaLabel}</h3>

                <label class="text-sm">${window.i18n.label_sabor}</label>
                <select id="promoSabor${index}" class="w-full border rounded px-2 py-1 mb-2">${saborSelect}</select>

                <label class="text-sm">${window.i18n.label_masa}</label>
                <select id="promoMasa${index}" class="w-full border rounded px-2 py-1 mb-2">${masaSelect}</select>

                <label class="text-sm">${window.i18n.label_extras}</label>
                <div class="mb-2 text-sm" id="extrasPromo${index}">${extrasHTML}</div>

                <label class="text-sm">${window.i18n.label_nota}</label>
                <textarea id="notaPizza${index}" class="w-full border rounded px-2 py-1 text-sm mb-2"></textarea>
                </div>`;
            }
        });

        const refrescoSelect = (refrescos || []).map(r => `<option value="${r.id}">${r.nombre}</option>`).join('');
        const bebidaHTML = contieneBebida ? `
            <div class="mt-4">
            <label class="text-sm font-bold text-gray-700">🥤 ${window.i18n.refresco_incluido}</label>
            <select id="selectBebida" class="w-full border px-3 py-2 rounded text-sm mt-1">
                <option value="">${window.i18n.seleccione_refresco}</option>
                ${refrescoSelect}
            </select>
            </div>` : '';

        document.getElementById('contenedorPizzaPersonalizada').innerHTML = bloquesPizza + bebidaHTML;

        // Listeners para actualizar total con extras
        componentesPizza.forEach((c, i) => {
            for (let j = 0; j < (c.cantidad || 1); j++) {
            const index = `${i}_${j}`;
            document.querySelectorAll(`#extrasPromo${index} input[type="checkbox"]`)
                .forEach(input => input.addEventListener('change', calcularTotalPromo));
            }
        });

        calcularTotalPromo();

        } catch (error) {
        console.error('❌ abrirModalPromocion:', error);
        document.getElementById('contenedorPizzaPersonalizada').innerHTML =
            `<p class="text-red-500">${window.i18n.error_cargar_promocion || 'Error al cargar datos de la promoción.'}</p>`;
        } finally {
        ocultarLoading();
        }
    }

    function cerrarModalPromocion() {
        document.getElementById('modalPromocion').classList.add('hidden');
        document.getElementById('modalConfirmacion').classList.add('hidden');
    }
    function cerrarModalConfirmacion() {
        document.getElementById('modalConfirmacion').classList.add('hidden');
    }

    function calcularTotalPromo() {
        let totalExtras = 0;
        document.querySelectorAll(`input[name^="extrasPizza"]`).forEach(input => {
            if (input.checked) totalExtras += parseFloat(input.dataset.precio || 0);
        });
        const total = precioBasePromocion + totalExtras;
        document.getElementById('totalPromo').textContent = `${window.i18n.total} ₡${total.toFixed(2)}`;
    }

    function agregarPromocionAlCarrito() {
        const pizzas = [];
        let i = 0;

        // 🔁 Recorrer dinámicamente cada grupo de pizzas (índices i_j)
        while (document.getElementById(`promoSabor${i}_0`)) {
            let j = 0;
            while (document.getElementById(`promoSabor${i}_${j}`)) {
                const saborId = document.getElementById(`promoSabor${i}_${j}`).value;
                const masaId = document.getElementById(`promoMasa${i}_${j}`).value;
                const extras = Array.from(
                    document.querySelectorAll(`#extrasPromo${i}_${j} input[type="checkbox"]:checked`)
                ).map(e => parseInt(e.value));
                const nota = document.getElementById(`notaPizza${i}_${j}`).value;

                if (!saborId || !masaId) {
                    alert((window.i18n.validar_pizza_incompleta || 'Pizza incomplete').replace(':num', (i + 1)));
                    return;
                }

                pizzas.push({
                    tipo: 'pizza',
                    sabor_id: parseInt(saborId),
                    masa_id: parseInt(masaId),
                    extras: extras,
                    nota_cliente: nota
                });
                j++;
            }
            i++;
        }

        // ✅ Si hay bebida seleccionada
        const bebidaSelect = document.getElementById('selectBebida');
        if (bebidaSelect && bebidaSelect.value) {
            pizzas.push({ tipo: 'bebida', producto_id: parseInt(bebidaSelect.value) });
        }

        const payload = { promocion_id: promocionSeleccionadaId, productos: pizzas };

        mostrarLoading();

        fetch('/carrito/agregar-promocion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        })
        .then(res => { if (!res.ok) throw new Error('error'); return res.json(); })
        .then(data => {
            alert(window.i18n.promo_agregada);
            const precioFinal = data.data.precio_total;
            const totalPromo = document.getElementById('totalPromo');
            if (totalPromo) {
                totalPromo.textContent = `${window.i18n.total} ₡${precioFinal.toLocaleString('es-CR', {
                    minimumFractionDigits: 2, maximumFractionDigits: 2
                })}`;
            }
            cerrarModalPromocion();
            document.getElementById('modalConfirmacion').classList.remove('hidden');
        })
        .catch(err => {
            console.error('❌', err);
            alert(window.i18n.promo_error);
        })
        .finally(() => ocultarLoading());
    }
</script>

<style>
@keyframes fade-in-down { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in-down { animation: fade-in-down 0.3s ease-out; }
</style>