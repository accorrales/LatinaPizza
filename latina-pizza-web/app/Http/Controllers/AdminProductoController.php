<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminProductoController extends Controller
{
    public function index()
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->get('http://127.0.0.1:8001/api/productos');

        if ($response->successful()) {
            $productos = $response->json();
            return view('admin.productos.index', compact('productos'));
        } else {
            return back()->with('error', 'Error al obtener los productos');
        }
    }

    public function create()
    {
        $categorias = Http::get('http://127.0.0.1:8001/api/categorias')->json();
        return view('admin.productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $token = Session::get('token');

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string',
            'categoria_id' => 'required',
            'estado' => 'nullable|boolean',
        ]);

        $response = Http::withToken($token)->post('http://127.0.0.1:8001/api/productos', [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'imagen' => $request->imagen,
            'categoria_id' => $request->categoria_id,
            'estado' => $request->has('estado') ? true : false,
        ]);

        if ($response->successful()) {
            return redirect()->route('admin.productos.index')->with('success', 'Producto creado correctamente');
        } else {
            return back()->with('error', 'Error al crear el producto');
        }
    }
    public function edit($id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $productoResponse = Http::withToken($token)->get("http://127.0.0.1:8001/api/productos/{$id}");
        $categoriasResponse = Http::get('http://127.0.0.1:8001/api/categorias');

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

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|string',
            'categoria_id' => 'required',
            'estado' => 'nullable',
        ]);

        // Normalizá el estado a booleano
        $data = [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'imagen' => $request->imagen,
            'categoria_id' => $request->categoria_id,
            'estado' => $request->has('estado') ? true : false,
        ];

        $response = Http::withToken($token)->put("http://localhost:8001/api/productos/{$id}", $data);

        if ($response->successful()) {
            return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado correctamente');
        } else {
            return back()->with('error', 'Hubo un problema al actualizar el producto');
        }
    }

    public function destroy($id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->delete("http://127.0.0.1:8001/api/productos/{$id}");

        if ($response->successful()) {
            return redirect()->route('admin.productos.index')->with('success', 'Producto eliminado correctamente');
        } else {
            return back()->with('error', 'Hubo un problema al eliminar el producto');
        }
    }

}
