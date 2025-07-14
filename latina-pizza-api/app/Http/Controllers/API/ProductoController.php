<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;

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
        $productos = Producto::with(['sabor', 'tamano'])
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
                'sabor_id' => $sabor->id,
                'sabor_nombre' => $sabor->nombre,
                'descripcion' => $sabor->descripcion ?? $primerProducto->descripcion,
                'imagen' => $sabor->imagen,
                'categoria_id' => $primerProducto->categoria_id,
                'tamanos' => $productosDelSabor->map(function ($p) {
                    return [
                        'producto_id'   => $p->id,
                        'tamano_id'     => $p->tamano->id,
                        'tamano_nombre' => $p->tamano->nombre,
                        'precio'        => $p->precio, // Este es el precio total actual del producto (si se usa)
                        'precio_base'   => $p->tamano->precio_base, // 👈 Este es el real por tamaño
                    ];
                })->values(),
            ];
        }

        return response()->json($resultado);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'sabor_id' => 'required|exists:sabores,id',
            'tamano_id' => 'required|exists:tamanos,id',
            'estado' => 'nullable|boolean',
        ]);

        $producto = Producto::create($request->all());

        return response()->json($producto, 201);
    }

    public function show($id)
    {
        $producto = Producto::with(['categoria', 'sabor', 'tamano'])->findOrFail($id);
        return response()->json($producto);
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|required|numeric|min:0',
            'imagen' => 'nullable|string',
            'categoria_id' => 'sometimes|required|exists:categorias,id',
            'sabor_id' => 'sometimes|required|exists:sabores,id',
            'tamano_id' => 'sometimes|required|exists:tamanos,id',
            'estado' => 'nullable|boolean',
        ]);

        $producto->update($request->all());

        return response()->json($producto);
    }

    public function destroy($id)
    {
        Producto::destroy($id);
        return response()->json(['message' => 'Producto eliminado.']);
    }
    public function bebidas()
    {
        // Refresco tiene id = 4 en la tabla categorías
        $bebidas = Producto::where('categoria_id', 4)
                    ->where('estado', true)
                    ->get(['id', 'nombre']);

        return response()->json($bebidas);
    }
}

