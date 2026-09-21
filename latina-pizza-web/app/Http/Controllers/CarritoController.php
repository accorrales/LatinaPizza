<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class CarritoController extends Controller
{
    /**
     * NUEVO: Punto de retorno tras login/register.
     * Consume el item pendiente y luego decide a dónde redirigir.
     */
    public function consumePending(Request $request)
    {
        $token = Session::get('token');
        if (! $token) {
            Redirect::setIntendedUrl(route('carrito.consume_pending'));

            return redirect()->route('login')->with('error', 'Debe iniciar sesión para continuar.');
        }

        $pending = Session::pull('cart.pending_item');
        if ($pending) {
            try {
                $resp = Http::withToken($token)->post(config('services.latina_api.base_url').'/carrito/add', [
                    'producto_id' => $pending['producto_id'],
                    'cantidad' => $pending['cantidad'] ?? 1,
                    'masa_id' => $pending['masa_id'] ?? null,
                    'extras' => $pending['extras'] ?? [],
                    'nota_cliente' => $pending['nota_cliente'] ?? null,
                ]);
                if (! $resp->successful()) {
                    return redirect('/catalogo')->with('error', 'No se pudo agregar el producto pendiente: '.$resp->body());
                }
            } catch (\Throwable $e) {
                Log::error('No se pudo agregar el producto pendiente.', ['exception' => $e]);

                return redirect('/catalogo')->with('error', 'No se pudo conectar con el servicio de pedidos.');
            }
        }

        return session()->has('delivery.type')
            ? redirect()->route('carrito.ver')->with('success', 'Producto agregado a tu carrito.')
            : redirect('/catalogo?cambiar_entrega=1')->with('success', 'Producto agregado a tu carrito.');
    }

    /**
     * MODIFICADO: Si no hay token (invitado), guardamos intento y mandamos a login.
     * Tras login, Laravel nos regresará a carrito.consume_pending.
     */
    public function agregar(Request $request)
    {
        $payload = $request->validate([
            'producto_id' => 'required|integer',
            'cantidad' => 'nullable|integer|min:1',
            'masa_id' => 'nullable|integer',
            'extras' => 'array',
            'extras.*' => 'integer',
            'nota_cliente' => 'nullable|string|max:500',
        ]);
        $payload['cantidad'] = $payload['cantidad'] ?? 1;

        $token = Session::get('token');

        // ⛔ Invitado
        if (! $token) {
            Session::put('cart.pending_item', $payload);
            Redirect::setIntendedUrl(route('carrito.consume_pending'));

            // 👈 CLAVE: si es AJAX/JSON devolvemos 401 con destino, NO redirect
            if ($request->expectsJson() || $request->ajax() ||
                str_contains($request->header('accept', ''), 'application/json')) {
                return response()->json([
                    'message' => 'UNAUTHENTICATED',
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
                'producto_id' => $payload['producto_id'],
                'cantidad' => $payload['cantidad'],
                'masa_id' => $payload['masa_id'] ?? null,
                'extras' => $payload['extras'] ?? [],
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
                    ? redirect()->route('carrito.ver')->with('success', 'Producto agregado al carrito correctamente')
                    : redirect('/catalogo?cambiar_entrega=1')->with('success', 'Producto agregado al carrito correctamente');
            }

            // error de API
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'error' => $resp->body()], $resp->status());
            }

            return redirect('/catalogo')->with('error', 'Error al agregar producto: '.$resp->body());
        } catch (\Throwable $e) {
            Log::error('No se pudo agregar el producto al carrito.', ['exception' => $e]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'error' => 'No se pudo conectar con el servicio de pedidos.'], 503);
            }

            return redirect('/catalogo')->with('error', 'No se pudo conectar con el servicio de pedidos.');
        }
    }

    public function checkout(Request $request)
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para confirmar el pedido');
        }

        $data = $request->validate([
            'metodo_pago' => 'required|in:efectivo,datafono,stripe',
            'payment_intent_id' => 'nullable|string',
        ]);

        if ($data['metodo_pago'] === 'stripe' && empty($data['payment_intent_id'])) {
            return back()->with('error', 'Falta el identificador de pago de Stripe. Intenta nuevamente.');
        }

        $payload = [
            'metodo_pago' => $data['metodo_pago'],
            'payment_intent_id' => $data['payment_intent_id'] ?? null,
        ];

        try {
            $resp = Http::withToken($token)
                ->post("{$this->apiBase}/checkout", $payload);

            if ($resp->successful()) {
                Session::forget('stripe_pi_id');

                $pedidoId = data_get($resp->json(), 'pedido_id');

                return redirect()->route('usuario.pedidos')
                    ->with('pedido_confirmado_id', $pedidoId)
                    ->with('success', "Pedido #{$pedidoId} creado correctamente. Te enviamos la factura por correo.");
            }

            $msg = $resp->json('message') ?? $resp->body();

            return redirect()->route('carrito.ver')
                ->with('error', 'No se pudo confirmar el pedido: '.$msg);

        } catch (\Throwable $e) {
            Log::error('No se pudo confirmar el pedido.', ['exception' => $e]);

            return redirect()->route('carrito.ver')
                ->with('error', 'No se pudo conectar con el servicio de pedidos.');
        }
    }

    public function ver()
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para ver el carrito');
        }

        try {
            $resp = Http::withToken($token)->get("{$this->apiBase}/carrito");
            if (! $resp->successful()) {
                return redirect('/catalogo')->with('error', 'Error al cargar el carrito: '.$resp->body());
            }

            $payload = $resp->json();

            $carrito = [
                'items' => $payload['data']['items'] ?? [],
                'subtotal' => $payload['subtotal'] ?? 0,
                'delivery' => $payload['delivery'] ?? ['fee' => 0, 'currency' => '₡', 'distance' => 0],
                'total' => $payload['total'] ?? 0,
                'data' => $payload['data'] ?? [],
            ];

            return view('carrito.index', compact('carrito'));
        } catch (\Throwable $e) {
            Log::error('No se pudo consultar el carrito.', ['exception' => $e]);

            return redirect('/catalogo')->with('error', 'No se pudo conectar con el servicio de pedidos.');
        }
    }

    public function eliminar($id)
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $resp = Http::withToken($token)->delete("{$this->apiBase}/carrito/remove/{$id}");
            if ($resp->successful()) {
                return redirect()->route('carrito.ver')->with('success', 'Producto eliminado del carrito');
            }

            return redirect()->route('carrito.ver')->with('error', 'Error al eliminar producto: '.$resp->body());
        } catch (\Throwable $e) {
            Log::error('No se pudo eliminar el producto del carrito.', ['exception' => $e]);

            return redirect()->route('carrito.ver')->with('error', 'No se pudo conectar con el servicio de pedidos.');
        }
    }

    public function actualizarCantidad(Request $request, $id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para modificar el carrito');
        }

        $accion = $request->validate(['accion' => ['required', 'in:sumar,restar']])['accion'];
        $carritoResponse = Http::withToken($token)->get("{$this->apiBase}/carrito");

        if (! $carritoResponse->successful()) {
            return back()->with('error', 'No se pudo obtener el carrito');
        }

        $productoEnCarrito = collect($carritoResponse->json('data.items', []))->firstWhere('id', (int) $id);

        if (! $productoEnCarrito) {
            return back()->with('error', 'Producto no encontrado en el carrito');
        }

        $cantidadActual = (int) ($productoEnCarrito['cantidad'] ?? 1);
        $nuevaCantidad = $accion === 'sumar' ? $cantidadActual + 1 : max(1, $cantidadActual - 1);

        $response = Http::withToken($token)->put("{$this->apiBase}/carrito/items/{$id}", [
            'cantidad' => $nuevaCantidad,
        ]);

        if ($response->successful()) {
            return back()->with('success', 'Cantidad actualizada correctamente');
        }

        return back()->with('error', 'Error al actualizar cantidad: '.$response->body());
    }

    public function agregarPromocion(Request $request)
    {
        $token = Session::get('token');

        if (! $token) {
            return response()->json(['error' => 'Debe iniciar sesión'], 401);
        }

        $payload = $request->validate([
            'promocion_id' => ['required', 'integer'],
            'productos' => ['required', 'array', 'min:1', 'max:20'],
            'productos.*.tipo' => ['required', 'in:pizza,bebida'],
            'productos.*.sabor_id' => ['nullable', 'integer'],
            'productos.*.masa_id' => ['nullable', 'integer'],
            'productos.*.producto_id' => ['nullable', 'integer'],
            'productos.*.extras' => ['nullable', 'array', 'max:20'],
            'productos.*.extras.*' => ['integer', 'distinct'],
            'productos.*.nota_cliente' => ['nullable', 'string', 'max:500'],
        ]);

        $response = Http::withToken($token)->post("{$this->apiBase}/carrito/agregar-promocion", $payload);

        if ($response->successful()) {
            return response()->json([
                'message' => '🎉 Promoción agregada correctamente al carrito',
                'data' => $response->json(),
            ]);
        }

        return response()->json([
            'error' => $response->json('message') ?? 'No se pudo agregar la promoción.',
        ], $response->status());
    }

    public function createStripeIntent()
    {
        $token = Session::get('token');
        if (! $token) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(15)
                ->post("{$this->apiBase}/pagos/stripe/intent");

            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'No se pudo iniciar el pago.'], 503);
        }
    }
}
