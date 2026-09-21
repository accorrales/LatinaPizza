<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Sabor;
use App\Models\Tamano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'sabor', 'tamano']);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        return response()->json($query->get());
    }

    public function saboresConTamanos()
    {
        $productos = Producto::with([
            'sabor' => fn ($query) => $query->withAvg('resenas', 'calificacion')->withCount('resenas'),
            'tamano',
        ])
            ->whereHas('sabor')
            ->whereHas('tamano')
            ->where('estado', true)
            ->get();

        $agrupado = $productos->groupBy(function ($producto) {
            return $producto->sabor->id;
        });

        $resultado = [];

        foreach ($agrupado as $saborId => $productosDelSabor) {
            $primerProducto = $productosDelSabor->first();
            $sabor = $primerProducto->sabor;

            $resultado[] = [
                'id' => $sabor->id, // 👈 Este campo es clave para poder consultar las reseñas por ID real
                'sabor_id' => $sabor->id,
                'sabor_nombre' => $sabor->nombre,
                'descripcion' => $sabor->descripcion ?? $primerProducto->descripcion,
                'imagen' => $sabor->imagen,
                'categoria_id' => $primerProducto->categoria_id,
                'promedio' => round((float) ($sabor->resenas_avg_calificacion ?? 0), 1),
                'total_resenas' => (int) ($sabor->resenas_count ?? 0),
                'tamanos' => $productosDelSabor->map(function ($p) {
                    return [
                        'producto_id' => $p->id,
                        'tamano_id' => $p->tamano->id,
                        'tamano_nombre' => $p->tamano->nombre,
                        'precio' => $p->precio, // Este es el precio total actual del producto (si se usa)
                        'precio_base' => $p->tamano->precio_base, // 👈 Este es el real por tamaño
                    ];
                })->values(),
            ];
        }

        return response()->json($resultado);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'nullable|string|max:255|required_without:sabor_id',
            'descripcion' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0|required_without:sabor_id',
            'imagen' => 'nullable|url:http,https|max:2048',
            'categoria_id' => 'required|exists:categorias,id',
            'sabor_id' => 'nullable|required_with:tamano_id|exists:sabores,id',
            'tamano_id' => 'nullable|required_with:sabor_id|exists:tamanos,id',
            'estado' => 'nullable|boolean',
        ]);

        try {
            $nombre = $request->input('nombre');
            $descripcion = $request->input('descripcion');
            $imagen = $request->input('imagen');
            $saborId = $request->input('sabor_id');
            $tamanoId = $request->input('tamano_id');

            // 🧀 Si es pizza, generamos nombre, imagen y descripción desde el sabor
            if ($saborId && $tamanoId) {
                $sabor = Sabor::findOrFail($saborId);
                $tamano = Tamano::findOrFail($tamanoId);

                $nombre = $sabor->nombre.' '.$tamano->nombre;
                $descripcion = $sabor->descripcion;
                $imagen = $imagen ?? $sabor->imagen;
            }

            $precio = ($saborId && $tamanoId)
                ? (float) $tamano->precio_base
                : (float) $request->precio;

            $producto = Producto::create([
                'nombre' => $nombre ?? 'Producto sin nombre',
                'descripcion' => $descripcion,
                'precio' => $precio,
                'imagen' => $imagen,
                'categoria_id' => $request->categoria_id,
                'sabor_id' => $saborId,
                'tamano_id' => $tamanoId,
                'estado' => $request->estado ?? true,
            ]);

            return response()->json($producto->load(['categoria', 'sabor', 'tamano']), 201);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['message' => 'No se pudo guardar el producto.'], 500);
        }
    }

    public function show($id)
    {
        $producto = Producto::with(['categoria', 'sabor', 'tamano'])->findOrFail($id);

        return response()->json($producto);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'nullable|string|max:255|required_without:sabor_id',
            'descripcion' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0|required_without:sabor_id',
            'imagen' => 'nullable|url:http,https|max:2048',
            'categoria_id' => 'required|exists:categorias,id',
            'sabor_id' => 'nullable|required_with:tamano_id|exists:sabores,id',
            'tamano_id' => 'nullable|required_with:sabor_id|exists:tamanos,id',
            'estado' => 'nullable|boolean',
        ]);

        $producto = Producto::findOrFail($id);

        // Si es una pizza, regenerar nombre y descripción automáticamente
        if ($request->sabor_id && $request->tamano_id) {
            $sabor = Sabor::findOrFail($request->sabor_id);
            $tamano = Tamano::findOrFail($request->tamano_id);

            $producto->nombre = $sabor->nombre.' '.$tamano->nombre;
            $producto->descripcion = $sabor->descripcion;
            $producto->imagen = $request->imagen ?? $sabor->imagen;
            $producto->sabor_id = $sabor->id;
            $producto->tamano_id = $tamano->id;
        } else {
            // Si no es pizza, se usa lo que venga del form
            $producto->nombre = $request->nombre;
            $producto->descripcion = $request->descripcion;
            $producto->imagen = $request->imagen;
            $producto->sabor_id = null;
            $producto->tamano_id = null;
        }

        $producto->precio = ($request->sabor_id && $request->tamano_id)
            ? (float) $tamano->precio_base
            : (float) $request->precio;
        $producto->categoria_id = $request->categoria_id;
        $producto->estado = $request->estado ?? true;
        $producto->save();

        return response()->json(['message' => 'Producto actualizado correctamente'], 200);
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $hasHistory = DB::table('pedido_producto')->where('producto_id', $producto->id)->exists()
            || DB::table('detalle_pedidos')->where('producto_id', $producto->id)->exists()
            || DB::table('detalle_pedido_promocion')->where('producto_id', $producto->id)->exists();

        if ($hasHistory) {
            $producto->update(['estado' => false]);

            return response()->json([
                'message' => 'El producto tiene historial y fue archivado en lugar de eliminarse.',
            ]);
        }

        $producto->delete();

        return response()->json(['message' => 'Producto eliminado.']);
    }

    public function bebidas()
    {
        $bebidas = Producto::query()
            ->where('estado', true)
            ->whereHas('categoria', function ($query) {
                // Tolera nombres como "Bebidas", "Bebidas frías", "Refresco" o "Refrescos".
                // Antes solo funcionaba si el nombre coincidía exactamente con tres valores.
                $query->where(function ($categoria) {
                    $categoria
                        ->whereRaw('LOWER(TRIM(nombre)) LIKE ?', ['%bebid%'])
                        ->orWhereRaw('LOWER(TRIM(nombre)) LIKE ?', ['%refresc%']);
                });
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'categoria_id']);

        return response()->json($bebidas);
    }

    public function publicIndex(Request $request)
    {
        $q = Producto::query()
            ->where('estado', true)
            ->select('id', 'nombre', 'imagen', 'categoria_id', 'sabor_id', 'tamano_id')
            ->with([
                'sabor:id,nombre',
                'tamano:id,nombre',
            ]);

        if ($request->filled('categoria_id')) {
            $q->where('categoria_id', (int) $request->categoria_id);
        }

        if ($s = trim((string) $request->input('search', ''))) {
            $q->where('nombre', 'like', "%{$s}%");
        }

        $items = $q->orderBy('nombre')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'imagen' => $p->imagen,
                'categoria_id' => $p->categoria_id,
                'sabor' => $p->sabor?->nombre,
                'tamano' => $p->tamano?->nombre,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }
}
