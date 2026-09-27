<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use App\Models\DetallePedidoPromocion;
use App\Models\Extra;
use App\Models\Masa;
use App\Models\MesaSesion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Sabor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalonOrderController extends Controller
{
    public function show(Request $request, MesaSesion $session)
    {
        $this->authorizeSession($request, $session);

        $session->load(['mesa:id,numero,nombre,zona,capacidad', 'mesero:id,name', 'sucursal:id,nombre']);
        $orders = $session->pedidos()
            ->where('canal_venta', Pedido::CANAL_SALON)
            ->with('createdBy:id,name')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'data' => $this->sessionPayload($session, $orders),
        ]);
    }

    public function storeRound(Request $request, MesaSesion $session)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.tipo' => ['required', Rule::in(['producto', 'promocion'])],
            'items.*.producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'items.*.cantidad' => ['nullable', 'integer', 'min:1', 'max:50'],
            'items.*.masa_id' => ['nullable', 'integer', 'exists:masas,id'],
            'items.*.nota_cliente' => ['nullable', 'string', 'max:500'],
            'items.*.extras' => ['nullable', 'array', 'max:20'],
            'items.*.extras.*' => ['integer', 'distinct', 'exists:extras,id'],
            'items.*.promocion_id' => ['nullable', 'integer', 'exists:promociones,id'],
            'items.*.productos' => ['nullable', 'array', 'max:20'],
            'items.*.productos.*.tipo' => ['required_with:items.*.productos', Rule::in(['pizza', 'bebida'])],
            'items.*.productos.*.sabor_id' => ['nullable', 'integer', 'exists:sabores,id'],
            'items.*.productos.*.masa_id' => ['nullable', 'integer', 'exists:masas,id'],
            'items.*.productos.*.producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'items.*.productos.*.extras' => ['nullable', 'array', 'max:20'],
            'items.*.productos.*.extras.*' => ['integer', 'distinct', 'exists:extras,id'],
            'items.*.productos.*.nota_cliente' => ['nullable', 'string', 'max:500'],
            'notas_cocina' => ['nullable', 'string', 'max:1000'],
        ]);

        $pedido = DB::transaction(function () use ($request, $session, $validated) {
            $lockedSession = MesaSesion::query()->lockForUpdate()->findOrFail($session->id);
            $this->authorizeSession($request, $lockedSession);

            if ($lockedSession->estado !== MesaSesion::ABIERTA) {
                throw ValidationException::withMessages(['session' => 'La mesa ya no tiene una sesión abierta.']);
            }

            $round = Pedido::query()
                ->where('mesa_sesion_id', $lockedSession->id)
                ->where('canal_venta', Pedido::CANAL_SALON)
                ->count() + 1;

            $snapshotItems = [];
            $detailPlans = [];
            $productQuantities = [];
            $subtotal = 0.0;

            foreach ($validated['items'] as $index => $item) {
                if ($item['tipo'] === 'producto') {
                    if (empty($item['producto_id'])) {
                        throw ValidationException::withMessages([
                            "items.{$index}.producto_id" => 'Seleccione un producto válido.',
                        ]);
                    }

                    $normalized = $this->normalizeProduct($item, $index);
                } else {
                    if (empty($item['promocion_id']) || empty($item['productos'])) {
                        throw ValidationException::withMessages([
                            "items.{$index}.promocion_id" => 'Configure completamente la promoción antes de enviarla.',
                        ]);
                    }

                    $normalized = $this->normalizePromotion($item, $index);
                }

                $snapshotItems[] = $normalized['snapshot'];
                $detailPlans[] = $normalized['plan'];
                $subtotal += $normalized['total'];

                if (! empty($normalized['product_id'])) {
                    $productQuantities[$normalized['product_id']] = ($productQuantities[$normalized['product_id']] ?? 0)
                        + $normalized['quantity'];
                }
            }

            $subtotal = round($subtotal, 2);
            if ($subtotal <= 0) {
                throw ValidationException::withMessages(['items' => 'La ronda debe tener un total mayor a cero.']);
            }

            $sla = (int) config('kitchen.sla_by_tipo.salon', config('kitchen.default_sla', 25));
            $pedido = Pedido::create([
                'user_id' => null,
                'sucursal_id' => $lockedSession->sucursal_id,
                'estado' => 'pendiente',
                'tipo_pedido' => 'salon',
                'tipo_entrega' => 'salon',
                'canal_venta' => Pedido::CANAL_SALON,
                'mesa_sesion_id' => $lockedSession->id,
                'created_by_user_id' => $request->user()->id,
                'subtotal' => $subtotal,
                'delivery_fee' => 0,
                'delivery_currency' => config('delivery.currency', 'CRC'),
                'total' => $subtotal,
                'metodo_pago' => 'pendiente',
                'payment_status' => 'pending',
                'kitchen_status' => 'nuevo',
                'priority' => false,
                'sla_minutes' => $sla,
                'promised_at' => now()->addMinutes($sla),
                'kitchen_notes' => $validated['notas_cocina'] ?? null,
                'detalle_json' => [
                    'canal' => 'salon',
                    'mesa_sesion_id' => $lockedSession->id,
                    'ronda' => $round,
                    'items' => $snapshotItems,
                    'subtotal' => $subtotal,
                    'delivery' => ['fee' => 0, 'currency' => config('delivery.currency', 'CRC'), 'distance' => null],
                    'total' => $subtotal,
                ],
            ]);

            foreach ($detailPlans as $plan) {
                if ($plan['type'] === 'producto') {
                    $detail = DetallePedido::create([
                        'pedido_id' => $pedido->id,
                        'producto_id' => $plan['producto_id'],
                        'sabor_id' => $plan['sabor_id'],
                        'tamano_id' => $plan['tamano_id'],
                        'masa_id' => $plan['masa_id'],
                        'nota_cliente' => $plan['nota_cliente'],
                        'precio_total' => $plan['precio_total'],
                        'cantidad' => $plan['cantidad'],
                    ]);
                    if ($plan['extras']) {
                        $detail->extras()->attach($plan['extras']);
                    }

                    continue;
                }

                $allocated = false;
                foreach ($plan['components'] as $component) {
                    $detail = DetallePedidoPromocion::create([
                        'pedido_id' => $pedido->id,
                        'promocion_id' => $plan['promocion_id'],
                        'producto_id' => $component['producto_id'],
                        'sabor_id' => $component['sabor_id'],
                        'tamano_id' => $component['tamano_id'],
                        'masa_id' => $component['masa_id'],
                        'nota_cliente' => $component['nota_cliente'],
                        'cantidad' => 1,
                        'precio_total' => $allocated ? 0 : $plan['precio_total'],
                    ]);
                    $allocated = true;
                    if ($component['extras']) {
                        $detail->extras()->attach($component['extras']);
                    }
                }
            }

            if ($productQuantities) {
                $pivot = [];
                foreach ($productQuantities as $productId => $quantity) {
                    $pivot[$productId] = ['cantidad' => $quantity];
                }
                $pedido->productos()->attach($pivot);
            }

            $pedido->guardarHistorial('pendiente');

            return $pedido->fresh(['createdBy:id,name']);
        }, 3);

        return response()->json([
            'message' => 'Ronda enviada a cocina correctamente.',
            'data' => $this->orderPayload($pedido),
        ], 201);
    }

    public function serve(Request $request, Pedido $pedido)
    {
        return DB::transaction(function () use ($request, $pedido) {
            $locked = Pedido::query()->lockForUpdate()->findOrFail($pedido->id);
            $session = MesaSesion::query()->lockForUpdate()->findOrFail($locked->mesa_sesion_id);
            $this->authorizeSession($request, $session);
            $this->assertSalonOrder($locked, $session);

            if ($locked->estado === 'cancelado') {
                return response()->json(['message' => 'Una ronda cancelada no se puede servir.'], 409);
            }
            if ($locked->kitchen_status === 'entregado') {
                return response()->json(['message' => 'La ronda ya estaba marcada como servida.', 'data' => $this->orderPayload($locked)]);
            }
            if ($locked->kitchen_status !== 'listo') {
                return response()->json(['message' => 'Cocina debe marcar la ronda como lista antes de servirla.'], 409);
            }

            $locked->forceFill([
                'kitchen_status' => 'entregado',
                'estado' => 'entregado',
            ])->save();
            $locked->guardarHistorial('entregado');

            return response()->json([
                'message' => 'Ronda marcada como servida.',
                'data' => $this->orderPayload($locked->fresh('createdBy:id,name')),
            ]);
        }, 3);
    }

    public function pay(Request $request, MesaSesion $session)
    {
        $validated = $request->validate([
            'metodo_pago' => ['required', Rule::in(['efectivo', 'datafono'])],
            'payment_ref' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($request, $session, $validated) {
            $lockedSession = MesaSesion::query()->lockForUpdate()->findOrFail($session->id);
            $this->authorizeBranch($request, $lockedSession->sucursal_id);

            if ($lockedSession->estado !== MesaSesion::ABIERTA) {
                return response()->json(['message' => 'La sesión ya está cerrada.'], 409);
            }

            $orders = Pedido::query()
                ->where('mesa_sesion_id', $lockedSession->id)
                ->where('canal_venta', Pedido::CANAL_SALON)
                ->where('estado', '!=', 'cancelado')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return response()->json(['message' => 'La mesa no tiene rondas para cobrar.'], 409);
            }

            $unpaid = $orders->where('payment_status', '!=', 'paid');
            if ($unpaid->isEmpty()) {
                return response()->json(['message' => 'La cuenta ya está pagada.'], 409);
            }

            $provider = $validated['metodo_pago'] === 'datafono' ? 'pos' : 'cash';
            $paidAt = now();
            foreach ($unpaid as $order) {
                $order->forceFill([
                    'metodo_pago' => $validated['metodo_pago'],
                    'payment_provider' => $provider,
                    'payment_ref' => $validated['payment_ref'] ?? null,
                    'payment_status' => 'paid',
                    'paid_at' => $paidAt,
                    'estado' => $order->kitchen_status === 'entregado' ? 'entregado' : 'pagado',
                ])->save();
                $order->guardarHistorial('pagado');
            }

            $orders = Pedido::query()
                ->where('mesa_sesion_id', $lockedSession->id)
                ->where('canal_venta', Pedido::CANAL_SALON)
                ->where('estado', '!=', 'cancelado')
                ->get();

            return response()->json([
                'message' => 'Cuenta pagada correctamente.',
                'data' => [
                    'paid_total' => round((float) $unpaid->sum('total'), 2),
                    'session_total' => round((float) $orders->sum('total'), 2),
                    'payment_status' => 'paid',
                    'ready_to_close' => $orders->every(fn (Pedido $order) => $order->payment_status === 'paid' && $order->kitchen_status === 'entregado'),
                ],
            ]);
        }, 3);
    }

    public function cancel(Request $request, Pedido $pedido)
    {
        return DB::transaction(function () use ($request, $pedido) {
            $locked = Pedido::query()->lockForUpdate()->findOrFail($pedido->id);
            $session = MesaSesion::query()->lockForUpdate()->findOrFail($locked->mesa_sesion_id);
            $this->authorizeBranch($request, $session->sucursal_id);
            $this->assertSalonOrder($locked, $session);

            if ($locked->estado === 'cancelado') {
                return response()->json(['message' => 'La ronda ya estaba cancelada.']);
            }
            if ($locked->payment_status === 'paid') {
                return response()->json(['message' => 'No se puede cancelar una ronda ya pagada.'], 409);
            }
            if ($locked->kitchen_status !== 'nuevo') {
                return response()->json(['message' => 'Solo se puede cancelar una ronda antes de que cocina empiece a prepararla.'], 409);
            }

            $locked->forceFill([
                'estado' => 'cancelado',
                'kitchen_status' => 'cancelado',
            ])->save();
            $locked->guardarHistorial('cancelado');

            return response()->json([
                'message' => 'Ronda cancelada correctamente.',
                'data' => $this->orderPayload($locked->fresh('createdBy:id,name')),
            ]);
        }, 3);
    }

    private function normalizeProduct(array $item, int $index): array
    {
        $product = Producto::query()
            ->with(['tamano', 'sabor'])
            ->where('estado', true)
            ->find($item['producto_id']);

        if (! $product) {
            throw ValidationException::withMessages(["items.{$index}.producto_id" => 'El producto ya no está disponible.']);
        }

        $quantity = max(1, (int) ($item['cantidad'] ?? 1));
        $size = $product->tamano;
        $basePrice = $size ? (float) $size->precio_base : (float) $product->precio;
        $masa = null;
        $masaPrice = 0.0;

        if (! empty($item['masa_id'])) {
            if (! $size) {
                throw ValidationException::withMessages(["items.{$index}.masa_id" => 'Este producto no admite selección de masa.']);
            }
            $masa = Masa::find($item['masa_id']);
            $masaPrice = (float) ($masa?->precio_extra ?? 0);
        }

        $extras = Extra::whereIn('id', $item['extras'] ?? [])->get();
        if ($extras->isNotEmpty() && ! $size) {
            throw ValidationException::withMessages(["items.{$index}.extras" => 'Este producto no admite extras por tamaño.']);
        }

        $extraPivot = [];
        $snapshotExtras = [];
        $extrasPrice = 0.0;
        foreach ($extras as $extra) {
            $price = $this->extraPriceForSize($extra, (string) $size->nombre);
            $extrasPrice += $price;
            $extraPivot[$extra->id] = ['precio_extra' => $price];
            $snapshotExtras[] = ['id' => $extra->id, 'nombre' => $extra->nombre, 'precio' => $price];
        }

        $total = round(($basePrice + $masaPrice + $extrasPrice) * $quantity, 2);

        return [
            'total' => $total,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'snapshot' => [
                'tipo' => 'producto',
                'producto_id' => $product->id,
                'nombre' => $product->nombre,
                'sabor' => $product->sabor?->nombre,
                'tamano' => $size?->nombre ?? 'Único',
                'masa' => $masa?->tipo,
                'cantidad' => $quantity,
                'nota_cliente' => $item['nota_cliente'] ?? null,
                'precio_total' => $total,
                'extras' => $snapshotExtras,
            ],
            'plan' => [
                'type' => 'producto',
                'producto_id' => $product->id,
                'sabor_id' => $product->sabor_id,
                'tamano_id' => $product->tamano_id,
                'masa_id' => $masa?->id,
                'cantidad' => $quantity,
                'nota_cliente' => $item['nota_cliente'] ?? null,
                'precio_total' => $total,
                'extras' => $extraPivot,
            ],
        ];
    }

    private function normalizePromotion(array $item, int $index): array
    {
        $promotion = Promocion::with(['componentes.tamano'])->find($item['promocion_id']);
        if (! $promotion) {
            throw ValidationException::withMessages(["items.{$index}.promocion_id" => 'La promoción ya no está disponible.']);
        }

        $pizzaRules = $promotion->componentes
            ->where('tipo', 'pizza')
            ->flatMap(fn ($component) => collect(range(1, max(1, (int) $component->cantidad)))->map(fn () => $component))
            ->values();
        $expectedDrinks = $promotion->componentes
            ->where('tipo', 'bebida')
            ->sum(fn ($component) => max(1, (int) $component->cantidad));

        $selected = collect($item['productos']);
        $pizzas = $selected->where('tipo', 'pizza')->values();
        $drinks = $selected->where('tipo', 'bebida')->values();

        if ($pizzas->count() !== $pizzaRules->count() || $drinks->count() !== $expectedDrinks) {
            throw ValidationException::withMessages(["items.{$index}.productos" => 'Los componentes seleccionados no coinciden con la promoción.']);
        }

        $snapshotComponents = [];
        $planComponents = [];
        $extrasTotal = 0.0;

        foreach ($pizzas as $pizzaIndex => $pizza) {
            $rule = $pizzaRules[$pizzaIndex];
            $size = $rule->tamano;
            if (! $size || empty($pizza['sabor_id']) || empty($pizza['masa_id'])) {
                throw ValidationException::withMessages(["items.{$index}.productos.{$pizzaIndex}" => 'Cada pizza requiere tamaño, sabor y masa.']);
            }

            $flavor = Sabor::find($pizza['sabor_id']);
            $masa = Masa::find($pizza['masa_id']);
            if (! $flavor || ! $masa) {
                throw ValidationException::withMessages(["items.{$index}.productos.{$pizzaIndex}" => 'La configuración de la pizza ya no está disponible.']);
            }

            $extraPivot = [];
            $snapshotExtras = [];
            foreach (Extra::whereIn('id', $pizza['extras'] ?? [])->get() as $extra) {
                $price = $this->extraPriceForSize($extra, (string) $size->nombre);
                $extrasTotal += $price;
                $extraPivot[$extra->id] = ['precio_extra' => $price];
                $snapshotExtras[] = ['id' => $extra->id, 'nombre' => $extra->nombre, 'precio' => $price];
            }

            $snapshotComponents[] = [
                'tipo' => 'pizza',
                'sabor_id' => $flavor->id,
                'sabor' => $flavor->nombre,
                'tamano_id' => $size->id,
                'tamano' => $size->nombre,
                'masa_id' => $masa->id,
                'masa' => $masa->tipo,
                'nota_cliente' => $pizza['nota_cliente'] ?? null,
                'extras' => $snapshotExtras,
            ];
            $planComponents[] = [
                'producto_id' => null,
                'sabor_id' => $flavor->id,
                'tamano_id' => $size->id,
                'masa_id' => $masa->id,
                'nota_cliente' => $pizza['nota_cliente'] ?? null,
                'extras' => $extraPivot,
            ];
        }

        $drinkIds = $drinks->pluck('producto_id')->filter()->unique()->values();
        $availableDrinks = Producto::query()
            ->whereIn('id', $drinkIds)
            ->where('estado', true)
            ->whereHas('categoria', function ($query) {
                $query->where(function ($category) {
                    $category->whereRaw('LOWER(TRIM(nombre)) LIKE ?', ['%bebid%'])
                        ->orWhereRaw('LOWER(TRIM(nombre)) LIKE ?', ['%refresc%']);
                });
            })
            ->get()
            ->keyBy('id');

        foreach ($drinks as $drinkIndex => $drink) {
            $product = $availableDrinks->get((int) ($drink['producto_id'] ?? 0));
            if (! $product) {
                throw ValidationException::withMessages(["items.{$index}.productos.{$drinkIndex}.producto_id" => 'La bebida seleccionada no está disponible.']);
            }
            $snapshotComponents[] = [
                'tipo' => 'bebida',
                'producto_id' => $product->id,
                'producto' => $product->nombre,
                'extras' => [],
            ];
            $planComponents[] = [
                'producto_id' => $product->id,
                'sabor_id' => null,
                'tamano_id' => null,
                'masa_id' => null,
                'nota_cliente' => null,
                'extras' => [],
            ];
        }

        $total = round((float) $promotion->precio_total + $extrasTotal, 2);

        return [
            'total' => $total,
            'product_id' => null,
            'quantity' => 1,
            'snapshot' => [
                'tipo' => 'promocion',
                'promocion_id' => $promotion->id,
                'nombre' => $promotion->nombre,
                'descripcion' => $promotion->descripcion,
                'cantidad' => 1,
                'precio_total' => $total,
                'componentes' => $snapshotComponents,
            ],
            'plan' => [
                'type' => 'promocion',
                'promocion_id' => $promotion->id,
                'precio_total' => $total,
                'components' => $planComponents,
            ],
        ];
    }

    private function authorizeSession(Request $request, MesaSesion $session): void
    {
        $this->authorizeBranch($request, $session->sucursal_id);
        $user = $request->user();

        if ($user->role === User::ROLE_MESERO
            && (int) $session->mesero_user_id !== (int) $user->id) {
            abort(403, 'No puede operar la mesa de otro mesero.');
        }
    }

    private function authorizeBranch(Request $request, int $branchId): void
    {
        $user = $request->user();
        abort_unless($user, 401);

        if ($user->role !== User::ROLE_ADMIN) {
            abort_unless($user->sucursal_id && (int) $user->sucursal_id === $branchId, 403, 'No autorizado para esta sucursal.');
        }
    }

    private function assertSalonOrder(Pedido $pedido, MesaSesion $session): void
    {
        abort_unless(
            $pedido->canal_venta === Pedido::CANAL_SALON
            && (int) $pedido->mesa_sesion_id === (int) $session->id,
            404
        );
    }

    private function extraPriceForSize(Extra $extra, string $sizeName): float
    {
        $normalized = mb_strtolower($sizeName);

        return match (true) {
            str_contains($normalized, 'extra') => (float) ($extra->precio_extragrande ?? 0),
            str_contains($normalized, 'grande') => (float) ($extra->precio_grande ?? 0),
            str_contains($normalized, 'mediana') => (float) ($extra->precio_mediana ?? 0),
            default => (float) ($extra->precio_pequena ?? 0),
        };
    }

    private function orderPayload(Pedido $order): array
    {
        return [
            'id' => $order->id,
            'ronda' => (int) data_get($order->detalle_json, 'ronda', 0),
            'estado' => $order->estado,
            'kitchen_status' => $order->kitchen_status,
            'payment_status' => $order->payment_status,
            'metodo_pago' => $order->metodo_pago,
            'subtotal' => (float) $order->subtotal,
            'total' => (float) $order->total,
            'items' => data_get($order->detalle_json, 'items', []),
            'notas_cocina' => $order->kitchen_notes,
            'created_at' => $order->created_at?->toIso8601String(),
            'created_by' => $order->createdBy ? ['id' => $order->createdBy->id, 'name' => $order->createdBy->name] : null,
        ];
    }

    private function sessionPayload(MesaSesion $session, $orders): array
    {
        $activeOrders = $orders->where('estado', '!=', 'cancelado');

        return [
            'id' => $session->id,
            'estado' => $session->estado,
            'personas' => (int) $session->personas,
            'opened_at' => $session->opened_at?->toIso8601String(),
            'closed_at' => $session->closed_at?->toIso8601String(),
            'notas' => $session->notas,
            'mesa' => $session->mesa ? [
                'id' => $session->mesa->id,
                'numero' => $session->mesa->numero,
                'nombre' => $session->mesa->nombre,
                'zona' => $session->mesa->zona,
                'capacidad' => (int) $session->mesa->capacidad,
            ] : null,
            'sucursal' => $session->sucursal ? ['id' => $session->sucursal->id, 'nombre' => $session->sucursal->nombre] : null,
            'mesero' => $session->mesero ? ['id' => $session->mesero->id, 'name' => $session->mesero->name] : null,
            'orders' => $orders->map(fn (Pedido $order) => $this->orderPayload($order))->values(),
            'summary' => [
                'total' => round((float) $activeOrders->sum('total'), 2),
                'paid_total' => round((float) $activeOrders->where('payment_status', 'paid')->sum('total'), 2),
                'pending_total' => round((float) $activeOrders->where('payment_status', '!=', 'paid')->sum('total'), 2),
                'all_paid' => $activeOrders->isEmpty() || $activeOrders->every(fn (Pedido $order) => $order->payment_status === 'paid'),
                'all_served' => $activeOrders->isEmpty() || $activeOrders->every(fn (Pedido $order) => $order->kitchen_status === 'entregado'),
                'can_close' => $activeOrders->isEmpty() || $activeOrders->every(fn (Pedido $order) => $order->payment_status === 'paid' && $order->kitchen_status === 'entregado'),
            ],
        ];
    }
}
