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

        $response = Http::withToken($token)->get('http://127.0.0.1:8001/api/categorias');

        if ($response->successful()) {
            $categorias = $response->json();
            return view('admin.productos.create', compact('categorias'));
        }

        return back()->with('error', 'No se pudieron cargar las categorías');
    }

    public function store(Request $request)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login');

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string',
            'categoria_id' => 'required',
            'estado' => 'nullable|boolean',
        ]);

        $data = [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'imagen' => $request->imagen,
            'categoria_id' => $request->categoria_id,
            'estado' => $request->has('estado') ? true : false,
        ];

        $response = Http::withToken($token)->post("{$this->apiBase}/productos", $data);

        if ($response->successful()) {
            return redirect()->route('admin.productos.index')->with('success', 'Producto creado correctamente');
        }

        return back()->with('error', 'Error al crear el producto')->withInput();
    }

    public function edit($id)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login')->with('error', 'Debe iniciar sesión');

        $productoResponse = Http::withToken($token)->get("{$this->apiBase}/productos/{$id}");
        $categoriasResponse = Http::withToken($token)->get("{$this->apiBase}/categorias");

        if ($productoResponse->successful() && $categoriasResponse->successful()) {
            $producto = $productoResponse->json();
            $categorias = $categoriasResponse->json();
            return view('admin.productos.edit', compact('producto', 'categorias'));
        }

        return redirect()->route('admin.productos.index')->with('error', 'No se pudo cargar el producto');
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login')->with('error', 'Debe iniciar sesión');

        // ✅ Validación
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_total' => 'required|numeric|min:0',
            'precio_sugerido' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|string',
            'incluye_bebida' => 'nullable|boolean',
            'componentes' => 'required|array|min:1',
            'componentes.*.tipo' => 'required|in:pizza,bebida',
            'componentes.*.cantidad' => 'required|integer|min:1',
            'componentes.*.tamano_id' => 'nullable|integer|exists:tamanos,id',
            'componentes.*.sabor_id' => 'nullable|integer|exists:sabores,id',
            'componentes.*.masa_id' => 'nullable|integer|exists:masas,id',
        ]);

        // ✅ Enviar datos a la API
        $response = Http::withToken($token)->put("{$this->apiBase}/promociones/{$id}", $validated);

        if ($response->successful()) {
            return redirect()->route('admin.promociones.index')->with('success', 'Promoción actualizada correctamente');
        }

        return back()->with('error', 'Hubo un problema al actualizar la promoción')->withInput();
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

