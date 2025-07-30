<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PedidoConfirmadoMail;
use App\Models\User;
use App\Models\Masa;
use App\Models\Extra;
use Illuminate\Support\Facades\Log;
use App\Models\Tamano;
use App\Models\CarritoItem;
use App\Models\CarritoItemExtra;
use App\Models\Promocion;
use App\Models\CarritoItemPromocionDetalle;
use App\Models\CarritoItemsPromocionExtra;
class CarritoController extends Controller
{
    // Ver el carrito del usuario
    public function index()
    {
        $user = Auth::user();
        $carrito = $user->carrito()->with([
            'items.producto.tamano',
            'items.producto.sabor',
            'items.masa',
            'items.extras',
            'items.promocion',
            'items.detallesPromocion.sabor',
            'items.detallesPromocion.masa',
            'items.detallesPromocion.extras.extra',
        ])->first();

        if (!$carrito || $carrito->items->isEmpty()) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        $items = [];
        $total = 0;

        foreach ($carrito->items as $item) {
            // Producto normal
            if ($item->producto_id && $item->producto) {
                $subtotal = $item->precio_total;
                $total += $subtotal;

                $items[] = [
                    'id' => $item->id,
                    'tipo' => 'producto',
                    'nombre' => $item->producto->nombre,
                    'tamano' => $item->producto->tamano->nombre ?? 'N/A',
                    'sabor' => $item->producto->sabor->nombre ?? 'N/A',
                    'masa_nombre' => $item->masa->tipo ?? 'N/A',
                    'cantidad' => $item->cantidad,
                    'nota_cliente' => $item->nota_cliente,
                    'precio_total' => $item->precio_total,
                    'extras' => $item->extras->map(fn($extra) => [
                        'id' => $extra->id,
                        'nombre' => $extra->nombre,
                    ])->toArray(),
                ];
            }

            // Promoción personalizada
            elseif ($item->promocion_id && $item->promocion) {
                $precioBD = floatval($item->precio_total);
                $extrasTotal = 0;

                $componentes = $item->detallesPromocion->map(function ($detalle) use (&$extrasTotal) {
                    if ($detalle->tipo === 'pizza') {
                        $tamanoNombre = strtolower($detalle->tamano->nombre ?? 'mediana'); // Ej: 'mediana', 'grande'

                        $extras = $detalle->extras->map(function ($e) use (&$extrasTotal, $tamanoNombre) {
                            $precio = match ($tamanoNombre) {
                                'pequena' => $e->extra->precio_pequena,
                                'mediana' => $e->extra->precio_mediana,
                                'grande' => $e->extra->precio_grande,
                                'extragrande' => $e->extra->precio_extragrande,
                                default => 0,
                            };

                            $extrasTotal += floatval($precio);

                            return [
                                'id' => $e->extra->id,
                                'nombre' => $e->extra->nombre,
                                'precio' => $precio,
                            ];
                        });

                        return [
                            'tipo' => 'pizza',
                            'sabor' => [
                                'nombre' => $detalle->sabor->nombre ?? 'N/A'
                            ],
                            'masa' => [
                                'nombre' => $detalle->masa->tipo ?? 'N/A'
                            ],
                            'tamano' => [
                                'nombre' => ucfirst($tamanoNombre)
                            ],
                            'nota_cliente' => $detalle->nota_cliente,
                            'extras' => $extras->toArray(),
                        ];
                    } elseif ($detalle->tipo === 'bebida') {
                        return [
                            'tipo' => 'bebida',
                            'producto' => [
                                'nombre' => $detalle->producto->nombre ?? 'N/A'
                            ]
                        ];
                    }

                    return ['tipo' => 'desconocido'];
                });

                $total += $precioBD;

                $items[] = [
                    'id' => $item->id,
                    'tipo' => 'promocion',
                    'nombre' => $item->promocion->nombre,
                    'descripcion' => $item->promocion->descripcion,
                    'imagen' => $item->promocion->imagen ?? null,
                    'pizzas' => $componentes,
                    'precio_total' => $precioBD,
                    'desglose' => [
                        'base' => $precioBD - $extrasTotal,
                        'extras' => $extrasTotal,
                    ]
                ];
            }
        }

        return response()->json([
            'items' => $items,
            'total' => $total,
        ]);
    }

    // Agregar producto al carrito
    public function add(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'masa_id' => 'nullable|exists:masas,id',
            'nota_cliente' => 'nullable|string',
            'extras' => 'array',
            'extras.*' => 'exists:extras,id',
        ]);

        $user = Auth::user();
        $carrito = $user->carrito()->firstOrCreate(['user_id' => $user->id]);

        $producto = Producto::with('tamano')->findOrFail($request->producto_id);
        $tamano = $producto->tamano;

        $precioProducto = $tamano->precio_base ?? 0;

        // Precio masa
        $precioMasa = 0;
        if ($request->filled('masa_id')) {
            $masa = Masa::find($request->masa_id);
            $precioMasa = $masa ? $masa->precio_extra : 0;
        }

        // Precio extras
        $precioExtras = 0;
        $extras = collect();
        if ($request->filled('extras')) {
            $extras = Extra::whereIn('id', $request->extras)->get();
            $tamanoNombre = strtolower($tamano->nombre);

            foreach ($extras as $extra) {
                if (str_contains($tamanoNombre, 'mediana')) {
                    $precioExtras += $extra->precio_mediana;
                } elseif (str_contains($tamanoNombre, 'grande') && !str_contains($tamanoNombre, 'extra')) {
                    $precioExtras += $extra->precio_grande;
                } elseif (str_contains($tamanoNombre, 'extra')) {
                    $precioExtras += $extra->precio_extragrande;
                } else {
                    $precioExtras += $extra->precio_pequena;
                }
            }
        }

        $precioTotal = ($precioProducto + $precioMasa + $precioExtras) * $request->cantidad;

        // Creamos nuevo ítem
        $item = new CarritoItem([
            'producto_id'   => $producto->id,
            'masa_id'       => $request->masa_id,
            'cantidad'      => $request->cantidad,
            'nota_cliente'  => $request->nota_cliente,
            'precio_total'  => $precioTotal,
        ]);

        $carrito->items()->save($item);

        if ($extras->isNotEmpty()) {
            $item->extras()->sync($extras->pluck('id')->toArray());
        }

        return response()->json(['message' => 'Producto agregado al carrito']);
    }
    public function agregarPromocion(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            $carrito = Carrito::firstOrCreate(
                ['user_id' => $user->id],
                ['estado' => 'activo']
            );

            $promocion = Promocion::with('componentes')->findOrFail($request->promocion_id);

            $precioBase = floatval($promocion->precio_total);
            $precioExtras = 0;
            $detalles = [];

            foreach ($request->productos as $producto) {
                if ($producto['tipo'] === 'pizza') {
                    $detalle = new CarritoItemPromocionDetalle([
                        'tipo' => 'pizza',
                        'sabor_id' => $producto['sabor_id'],
                        'masa_id' => $producto['masa_id'],
                        'nota_cliente' => $producto['nota_cliente'] ?? null,
                    ]);

                    // ⚠️ Necesitamos el tamaño para calcular el precio extra más adelante
                    $detalle->tamano = strtolower($producto['tamano'] ?? 'mediana');

                    $detalles[] = ['detalle' => $detalle, 'extras' => $producto['extras'] ?? []];
                }

                if ($producto['tipo'] === 'bebida') {
                    $detalle = new CarritoItemPromocionDetalle([
                        'tipo' => 'bebida',
                        'producto_id' => $producto['producto_id'],
                    ]);

                    $detalles[] = ['detalle' => $detalle, 'extras' => []];
                }
            }

            // ✅ Creamos el item con precio temporal
            $item = CarritoItem::create([
                'carrito_id' => $carrito->id,
                'user_id' => $user->id,
                'tipo' => 'promocion',
                'promocion_id' => $promocion->id,
                'precio_total' => 0
            ]);

            foreach ($detalles as $data) {
                $detalle = $data['detalle'];
                $tamano = $detalle->tamano ?? 'mediana';
                unset($detalle->tamano); // no existe en DB, solo lo usamos aquí

                $detalle->carrito_item_id = $item->id;
                $detalle->save();

                foreach ($data['extras'] as $extraId) {
                    $extra = Extra::find($extraId);
                    if (!$extra) continue;

                    // 🔍 Detectar el precio correcto según el tamaño
                    $precioExtra = match (strtolower($tamano)) {
                        'pequeña'     => floatval($extra->precio_pequena ?? 0),
                        'grande'      => floatval($extra->precio_grande ?? 0),
                        'extragrande' => floatval($extra->precio_extragrande ?? 0),
                        default       => floatval($extra->precio_mediana ?? 0),
                    };

                    $precioExtras += $precioExtra;

                    CarritoItemsPromocionExtra::create([
                        'detalle_id' => $detalle->id,
                        'extra_id' => $extra->id,
                        'precio' => $precioExtra,
                    ]);
                }
            }

            // 💰 Sumar base + extras
            $precioTotal = $precioBase + $precioExtras;

            $item->update(['precio_total' => $precioTotal]);

            DB::commit();
            return response()->json([
                'message' => '✅ Promoción agregada correctamente al carrito',
                'carrito_item_id' => $item->id,
                'precio_total' => $precioTotal,
                'desglose' => [
                    'base' => $precioBase,
                    'extras' => $precioExtras
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => '❌ No se pudo agregar la promoción',
                'debug' => $e->getMessage()
            ], 500);
        }
    }


    // Eliminar producto del carrito
    public function remove($id)
    {
        $user = Auth::user();
        $carrito = $user->carrito;

        // Verifica si el item pertenece al carrito del usuario
        $existe = DB::table('carrito_items')
            ->where('id', $id)
            ->where('carrito_id', $carrito->id)
            ->exists();

        if (!$existe) {
            return response()->json(['error' => 'No se pudo eliminar este producto.'], 404);
        }

        // Elimina los extras relacionados
        DB::table('carrito_item_extra')->where('carrito_item_id', $id)->delete();

        // Elimina el item del carrito
        DB::table('carrito_items')->where('id', $id)->delete();

        return response()->json(['success' => 'Producto eliminado correctamente.']);
    }


    // Vaciar carrito
    public function clear()
    {
        $user = Auth::user();
        $carrito = $user->carrito;

        if ($carrito) {
            $carrito->productos()->detach();
            return response()->json(['message' => 'Carrito vaciado.']);
        }

        return response()->json(['message' => 'No se encontró carrito.'], 404);
    }

    public function checkout(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $carrito = $user->carrito;
        $carrito->load('productos');

        if (!$carrito || $carrito->productos->isEmpty()) {
            return response()->json(['message' => 'Tu carrito está vacío.'], 400);
        }

        try {
            DB::beginTransaction();

            $pedido = new Pedido();
            $pedido->user_id = $user->id;
            $pedido->estado = 'pendiente';
            $pedido->tipo_pedido = $request->input('tipo_pedido', 'express');
            $pedido->metodo_pago = $request->input('metodo_pago', 'efectivo');
            $pedido->sucursal_id = $user->sucursal_id;
            $pedido->save();

            foreach ($carrito->productos as $producto) {
                $pedido->productos()->attach($producto->id, [
                    'cantidad' => $producto->pivot->cantidad ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $carrito->productos()->detach();

            DB::commit();

            // ✅ Enviar correo al usuario
            Mail::to($user->email)->send(new PedidoConfirmadoMail($pedido));

            return response()->json([
                'message' => '✅ Pedido creado correctamente',
                'pedido_id' => $pedido->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '❌ Error al procesar el pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

