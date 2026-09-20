<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Carrito;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Redirect;
class CarritoController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        // Ej: http://127.0.0.1:8001/api
        $this->apiBase = rtrim(config('services.latina_api.base_url'), '/');
    }

    /**
     * Decide a dónde ir tras agregar:
     * - Si ya tenemos un tipo de entrega fijado en sesión → carrito
     * - Si no → abrir tu modal selector con ?cambiar_entrega=1 en /catalogo
     */
    private function redirigirSegunEntrega(string $okMsg = 'Producto agregado a tu carrito.')
    {
        if (session()->has('delivery.type')) {
            return redirect()->route('carrito.ver')->with('success', $okMsg);
        }

        // Usa tu modal (entregaModal) con el query param que ya manejas en JS
        return redirect('/catalogo?cambiar_entrega=1')
            ->with('success', $okMsg);
    }
    
     /**
     * NUEVO: Punto de retorno tras login/register.
     * Consume el item pendiente y luego decide a dónde redirigir.
     */
    public function consumePending(Request $request)
    {
        $token = Session::get('token');
        if (!$token) {
            Redirect::setIntendedUrl(route('carrito.consume_pending'));
            return redirect()->route('login')->with('error','Debe iniciar sesión para continuar.');
        }

        $pending = Session::pull('cart.pending_item');
        if ($pending) {
            try {
                $resp = Http::withToken($token)->post(config('services.latina_api.base_url').'/carrito/add', [
                    'producto_id'  => $pending['producto_id'],
                    'cantidad'     => $pending['cantidad'] ?? 1,
                    'masa_id'      => $pending['masa_id'] ?? null,
                    'extras'       => $pending['extras'] ?? [],
                    'nota_cliente' => $pending['nota_cliente'] ?? null,
                ]);
                if (!$resp->successful()) {
                    return redirect('/catalogo')->with('error','No se pudo agregar el producto pendiente: '.$resp->body());
                }
            } catch (\Throwable $e) {
                return redirect('/catalogo')->with('error','Error al agregar el producto pendiente: '.$e->getMessage());
            }
        }

        return session()->has('delivery.type')
            ? redirect()->route('carrito.ver')->with('success','Producto agregado a tu carrito.')
            : redirect('/catalogo?cambiar_entrega=1')->with('success','Producto agregado a tu carrito.');
    }

    /**
     * MODIFICADO: Si no hay token (invitado), guardamos intento y mandamos a login.
     * Tras login, Laravel nos regresará a carrito.consume_pending.
     */
    public function agregar(Request $request)
    {
        $payload = $request->validate([
            'producto_id'  => 'required|integer',
            'cantidad'     => 'nullable|integer|min:1',
            'masa_id'      => 'nullable|integer',
            'extras'       => 'array',
            'extras.*'     => 'integer',
            'nota_cliente' => 'nullable|string|max:500',
        ]);
        $payload['cantidad'] = $payload['cantidad'] ?? 1;

        $token = Session::get('token');

        // ⛔ Invitado
        if (!$token) {
            Session::put('cart.pending_item', $payload);
            Redirect::setIntendedUrl(route('carrito.consume_pending'));

            // 👈 CLAVE: si es AJAX/JSON devolvemos 401 con destino, NO redirect
            if ($request->expectsJson() || $request->ajax() ||
                str_contains($request->header('accept', ''), 'application/json')) {
                return response()->json([
                    'message'  => 'UNAUTHENTICATED',
                    'redirect' => route('login'),
                ], 401);
            }

            // Form tradicional
            return redirect()->route('login')
                ->with('error', 'Inicia sesión o regístrate para continuar con tu compra.');
        }

        // ✅ Autenticado → llama a tu API
        try {
            $resp = Http::withToken($token)->post(config('services.latina_api.base_url').'/carrito/add', [
                'producto_id'  => $payload['producto_id'],
                'cantidad'     => $payload['cantidad'],
                'masa_id'      => $payload['masa_id'] ?? null,
                'extras'       => $payload['extras'] ?? [],
                'nota_cliente' => $payload['nota_cliente'] ?? null,
            ]);

            if ($resp->successful()) {
                // si es AJAX, devolvemos next como JSON para redirigir desde el JS
                if ($request->expectsJson() || $request->ajax()) {
                    $next = session()->has('delivery.type')
                        ? route('carrito.ver')
                        : url('/catalogo?cambiar_entrega=1');
                    return response()->json(['ok' => true, 'next' => $next]);
                }
                // navegación normal
                return session()->has('delivery.type')
                    ? redirect()->route('carrito.ver')->with('success','Producto agregado al carrito correctamente')
                    : redirect('/catalogo?cambiar_entrega=1')->with('success','Producto agregado al carrito correctamente');
            }

            // error de API
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok'=>false,'error'=>$resp->body()], $resp->status());
            }
            return redirect('/catalogo')->with('error', 'Error al agregar producto: '.$resp->body());
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok'=>false,'error'=>$e->getMessage()], 500);
            }
            return redirect('/catalogo')->with('error', 'Error de conexión con el API: '.$e->getMessage());
        }
    }
    public function checkout(Request $request)
    {
        $token = Session::get('token');
        if (!$token) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para confirmar el pedido');
        }

        // 1) Validación básica del formulario
        $data = $request->validate([
            'metodo_pago'        => 'required|in:efectivo,datafono,stripe',
            'payment_intent_id'  => 'nullable|string'
        ]);

        // 2) Si es Stripe, exigir el payment_intent_id (del paso de confirmación en JS)
        if ($data['metodo_pago'] === 'stripe') {
            if (empty($data['payment_intent_id'])) {
                return back()->with('error', 'Falta el identificador de pago de Stripe. Intenta nuevamente.');
            }

            // (Opcional) Pre-validación con Stripe para evitar enviar al API algo inválido
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $pi = PaymentIntent::retrieve($data['payment_intent_id']);

                // Estados aceptables antes de crear el pedido:
                // - succeeded: pago ya capturado
                // - processing: Stripe aún procesa (ok)
                // - requires_capture: si usas captura manual (opcional)
                if (!in_array($pi->status, ['succeeded', 'processing', 'requires_capture'])) {
                    return back()->with('error', 'El pago con tarjeta no fue autorizado por Stripe.');
                }
            } catch (\Throwable $e) {
                return back()->with('error', 'No se pudo validar el pago con Stripe: ' . $e->getMessage());
            }
        }

        // 3) Construir el payload para el API
        $payload = [
            'metodo_pago'       => $data['metodo_pago'],
            'payment_intent_id' => $data['payment_intent_id'] ?? null,
        ];

        // 4) Llamar al API para cerrar el pedido
        try {
            $resp = Http::withToken($token)
                ->post("{$this->apiBase}/checkout", $payload);

            if ($resp->successful()) {
                // si usaste PaymentIntent de Stripe, limpia el que guardaste en sesión
                Session::forget('stripe_pi_id');

                $pedidoId = data_get($resp->json(), 'pedido_id');
                return redirect()->route('carrito.ver')
                    ->with('success', "✅ Pedido #{$pedidoId} creado correctamente. Te enviamos la factura por correo.");
            }

            // Mostrar un mensaje más legible si el API devolvió 4xx/5xx con JSON
            $msg = $resp->json('message') ?? $resp->body();
            return redirect()->route('carrito.ver')
                ->with('error', 'No se pudo confirmar el pedido: ' . $msg);

        } catch (\Throwable $e) {
            return redirect()->route('carrito.ver')
                ->with('error', 'Error de conexión con el API: ' . $e->getMessage());
        }
    }
    public function ver()
    {
        $token = Session::get('token');
        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para ver el carrito');
        }

        try {
            $resp = Http::withToken($token)->get("{$this->apiBase}/carrito");
            if (!$resp->successful()) {
                return redirect('/catalogo')->with('error', 'Error al cargar el carrito: ' . $resp->body());
            }

            $payload = $resp->json();

            // Aplanamos para que tu Blade funcione sin tocar mucho:
            // - tu Blade usa $carrito['items'] y también mostrará subtotal/delivery/total
            $carrito = [
                'items'    => $payload['data']['items'] ?? [],
                'subtotal' => $payload['subtotal'] ?? 0,
                'delivery' => $payload['delivery'] ?? ['fee' => 0, 'currency' => '₡', 'distance' => 0],
                'total'    => $payload['total'] ?? 0,
                'data'     => $payload['data'] ?? [], // aquí vienen tipo_entrega, sucursal_id, etc.
            ];

            return view('carrito.index', compact('carrito'));
        } catch (\Throwable $e) {
            return redirect('/catalogo')->with('error', 'Error de conexión con el API: ' . $e->getMessage());
        }
    }
    public function eliminar($id)
    {
        $token = Session::get('token');
        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $resp = Http::withToken($token)->delete("{$this->apiBase}/carrito/remove/{$id}");
            if ($resp->successful()) {
                return redirect()->route('carrito.ver')->with('success', 'Producto eliminado del carrito');
            }
            return redirect()->route('carrito.ver')->with('error', 'Error al eliminar producto: ' . $resp->body());
        } catch (\Throwable $e) {
            return redirect()->route('carrito.ver')->with('error', 'Error de conexión con el API: ' . $e->getMessage());
        }
    }

    public function actualizarCantidad(Request $request, $id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para modificar el carrito');
        }

        // Lógica de tipo de acción
        $accion = $request->input('accion');

        // Primero traemos el carrito para saber la cantidad actual
        $carritoResponse = Http::withToken($token)->get('http://127.0.0.1:8001/api/carrito');

        if (!$carritoResponse->successful()) {
            return back()->with('error', 'No se pudo obtener el carrito');
        }

        $carrito = $carritoResponse->json();
        $productoEnCarrito = collect($carrito['productos'])->firstWhere('id', $id);

        if (!$productoEnCarrito) {
            return back()->with('error', 'Producto no encontrado en el carrito');
        }

        $cantidadActual = $productoEnCarrito['pivot']['cantidad'];
        $nuevaCantidad = $accion === 'sumar' ? $cantidadActual + 1 : max(1, $cantidadActual - 1);

        // Actualizar cantidad (volvemos a usar el mismo endpoint del API de agregar)
        $response = Http::withToken($token)->post('http://127.0.0.1:8001/api/carrito/add', [
            'producto_id' => $id,
            'cantidad' => $nuevaCantidad
        ]);

        if ($response->successful()) {
            return back()->with('success', 'Cantidad actualizada correctamente');
        } else {
            return back()->with('error', 'Error al actualizar cantidad: ' . $response->body());
        }
    }
    public function agregarPromocion(Request $request)
    {
        $token = Session::get('token');

        if (!$token) {
            return response()->json(['error' => 'Debe iniciar sesión'], 401);
        }

        $response = Http::withToken($token)->post('http://127.0.0.1:8001/api/carrito/agregar-promocion', $request->all());

        if ($response->successful()) {
            return response()->json([
                'message' => '🎉 Promoción agregada correctamente al carrito',
                'data' => $response->json()
            ]);
        } else {
            return response()->json([
                'error' => '❌ Error al agregar la promoción',
                'debug' => $response->body()
            ], $response->status());
        }
    }
    public function createStripeIntent()
    {
        $token = Session::get('token');
        if (!$token) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // 1) Traer total real desde tu API (evitamos que el cliente fije montos)
        $apiBase = rtrim(config('services.latina_api.base_url'), '/'); // p.ej. http://127.0.0.1:8001/api
        $resp = Http::withToken($token)->get("{$apiBase}/carrito");

        if (!$resp->successful()) {
            return response()->json([
                'message' => 'No se pudo obtener el carrito para calcular el total.',
                'debug'   => $resp->body(),
            ], 422);
        }

        $payload = $resp->json();
        $total   = (float) ($payload['total'] ?? 0);

        if ($total <= 0) {
            return response()->json(['message' => 'El total debe ser mayor a 0.'], 422);
        }

        // Stripe usa montos en la menor unidad (centavos)
        $amountMinor = (int) round($total * 100);
        $currency    = strtolower(config('services.stripe.currency', 'crc'));

        Stripe::setApiKey(config('services.stripe.secret'));

        // 2) Reusar PaymentIntent si existe (para evitar crear uno por cada cambio de radio)
        $existingId = Session::get('stripe_pi_id');
        try {
            if ($existingId) {
                // Intentamos actualizar el monto (por si cambió el delivery, etc.)
                $pi = PaymentIntent::retrieve($existingId);
                // Si el intent no está en estado "terminal", lo actualizamos
                if (!in_array($pi->status, ['succeeded', 'canceled'])) {
                    $pi->amount = $amountMinor;
                    $pi->currency = $currency;
                    $pi->save();
                } else {
                    // Si ya está "succeeded/canceled", creamos uno nuevo
                    $pi = PaymentIntent::create([
                        'amount'   => $amountMinor,
                        'currency' => $currency,
                        'automatic_payment_methods' => ['enabled' => true],
                        'metadata' => [
                            'app'      => 'LatinaPizza',
                            'from'     => 'web',
                            'user_id'  => auth()->id(),
                        ],
                    ]);
                    Session::put('stripe_pi_id', $pi->id);
                }
            } else {
                $pi = PaymentIntent::create([
                    'amount'   => $amountMinor,
                    'currency' => $currency,
                    'automatic_payment_methods' => ['enabled' => true],
                    'metadata' => [
                        'app'      => 'LatinaPizza',
                        'from'     => 'web',
                        'user_id'  => auth()->id(),
                    ],
                ]);
                Session::put('stripe_pi_id', $pi->id);
            }

            return response()->json([
                'id'            => $pi->id,
                'client_secret' => $pi->client_secret,
                'amount'        => $pi->amount,
                'currency'      => $pi->currency,
                'status'        => $pi->status,
            ]);
        } catch (\Throwable $e) {
            // Si falló por un intent corrupto, limpia sesión y fuerza crear uno nuevo en el prox. intento
            Session::forget('stripe_pi_id');
            return response()->json([
                'message' => 'No se pudo crear/actualizar el intento de pago.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}