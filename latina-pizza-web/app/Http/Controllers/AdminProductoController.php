<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminProductoController extends Controller
{
    private $apiBase = 'http://127.0.0.1:8001/api/admin';

    public function index()
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login')->with('error', 'Debe iniciar sesión');

        $response = Http::withToken($token)->get("{$this->apiBase}/productos");

        if ($response->successful()) {
            $productos = $response->json();
            return view('admin.productos.index', compact('productos'));
        }

        return back()->with('error', 'Error al obtener los productos');
    }

    public function create()
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $categorias = Http::withToken($token)
                ->get('http://127.0.0.1:8001/api/categorias')
                ->json();

            $sabores = Http::withToken($token)
                ->get('http://127.0.0.1:8001/api/admin/sabores')
                ->json();

            $tamanos = Http::withToken($token)
                ->get('http://127.0.0.1:8001/api/admin/tamanos')
                ->json()['data'] ?? [];

            return view('admin.productos.create', compact('categorias', 'sabores', 'tamanos'));

        } catch (\Exception $e) {
            return back()->with('error', 'No se pudieron cargar los datos.')->withInput();
        }
    }


    public function store(Request $request)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        // Validación común
        $rules = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'estado' => 'nullable|boolean',
        ];

        // Si es categoría tipo pizza, también validamos sabor y tamaño
        if ($request->categoria_id && strtolower($request->categoria_nombre) === 'pizza') {
            $rules['sabor_id'] = 'required|exists:sabores,id';
            $rules['tamano_id'] = 'required|exists:tamanos,id';
        }

        $validated = $request->validate($rules);

        try {
            // Enviar los datos a la API backend
            $response = Http::withToken($token)
                ->post('http://127.0.0.1:8001/api/admin/productos', $validated);

            if ($response->successful()) {
                return redirect()->route('admin.productos.index')->with('success', 'Producto creado correctamente');
            }

            return back()->with('error', 'No se pudo crear el producto.')->withInput();

        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error inesperado.')->withInput();
        }
    }

    public function edit($id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            // Obtener el producto
            $productoResponse = Http::withToken($token)->get("http://127.0.0.1:8001/api/admin/productos/{$id}");
            if (!$productoResponse->successful()) {
                return back()->with('error', 'No se pudo cargar el producto');
            }
            $producto = $productoResponse->json();

            // Obtener categorías
            $categorias = Http::withToken($token)
                ->get('http://127.0.0.1:8001/api/categorias')
                ->json();

            // Obtener sabores
            $sabores = Http::withToken($token)
                ->get('http://127.0.0.1:8001/api/admin/sabores')
                ->json();

            // Obtener tamaños
            $tamanos = Http::withToken($token)
                ->get('http://127.0.0.1:8001/api/admin/tamanos')
                ->json()['data'] ?? [];

            return view('admin.productos.edit', compact('producto', 'categorias', 'sabores', 'tamanos'));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar los datos: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        $data = $request->validate([
            'nombre'       => 'nullable|string|max:255',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'imagen'       => 'nullable|string',
            'categoria_id' => 'required|integer',
            'sabor_id'     => 'nullable|integer',
            'tamano_id'    => 'nullable|integer',
            'estado'       => 'nullable|boolean',
        ]);

        $response = Http::withToken($token)->put("http://127.0.0.1:8001/api/admin/productos/{$id}", $data);

        if ($response->successful()) {
            return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado correctamente');
        }

        return back()->with('error', 'No se pudo actualizar el producto');
    }



    public function destroy($id)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login')->with('error', 'Debe iniciar sesión');

        $response = Http::withToken($token)->delete("{$this->apiBase}/productos/{$id}");

        if ($response->successful()) {
            return redirect()->route('admin.productos.index')->with('success', 'Producto eliminado correctamente');
        }

        return back()->with('error', 'Hubo un problema al eliminar el producto');
    }
}

