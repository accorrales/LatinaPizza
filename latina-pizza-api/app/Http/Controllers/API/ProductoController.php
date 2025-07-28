<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Sabor;
use App\Models\Tamano;
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
                'id' => $sabor->id, // 👈 Este campo es clave para poder consultar las reseñas por ID real
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
            'nombre'       => 'nullable|string|max:255',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'imagen'       => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'sabor_id'     => 'nullable|exists:sabores,id',
            'tamano_id'    => 'nullable|exists:tamanos,id',
            'estado'       => 'nullable|boolean',
        ]);

        try {
            $nombre       = $request->input('nombre');
            $descripcion  = $request->input('descripcion');
            $imagen       = $request->input('imagen');
            $saborId      = $request->input('sabor_id');
            $tamanoId     = $request->input('tamano_id');

            // 🧀 Si es pizza, generamos nombre, imagen y descripción desde el sabor
            if ($saborId && $tamanoId) {
                $sabor = Sabor::findOrFail($saborId);
                $tamano = Tamano::findOrFail($tamanoId);

                $nombre       = $sabor->nombre . ' ' . $tamano->nombre;
                $descripcion  = $sabor->descripcion;
                $imagen       = $imagen ?? $sabor->imagen;
            }

            // 🍕 Crear producto
            $producto = Producto::create([
                'nombre'       => $nombre ?? 'Producto sin nombre',
                'descripcion'  => $descripcion,
                'precio'       => $request->precio,
                'imagen'       => $imagen,
                'categoria_id' => $request->categoria_id,
                'sabor_id'     => $saborId,
                'tamano_id'    => $tamanoId,
                'estado'       => $request->estado ?? true,
            ]);


            return redirect()->route('admin.productos.index')->with('success', 'Producto creado correctamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al guardar el producto: ' . $e->getMessage())->withInput();
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
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'sabor_id' => 'nullable|exists:sabores,id',
            'tamano_id' => 'nullable|exists:tamanos,id',
            'estado' => 'nullable|boolean',
        ]);

        $producto = Producto::findOrFail($id);

        // Si es una pizza, regenerar nombre y descripción automáticamente
        if ($request->sabor_id && $request->tamano_id) {
            $sabor = Sabor::findOrFail($request->sabor_id);
            $tamano = Tamano::findOrFail($request->tamano_id);

            $producto->nombre      = $sabor->nombre . ' ' . $tamano->nombre;
            $producto->descripcion = $sabor->descripcion;
            $producto->imagen      = $request->imagen ?? $sabor->imagen;
            $producto->sabor_id    = $sabor->id;
            $producto->tamano_id   = $tamano->id;
        } else {
            // Si no es pizza, se usa lo que venga del form
            $producto->nombre      = $request->nombre;
            $producto->descripcion = $request->descripcion;
            $producto->imagen      = $request->imagen;
            $producto->sabor_id    = null;
            $producto->tamano_id   = null;
        }

        $producto->precio       = $request->precio;
        $producto->categoria_id = $request->categoria_id;
        $producto->estado       = $request->estado ?? true;
        $producto->save();

        return response()->json(['message' => 'Producto actualizado correctamente'], 200);
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

