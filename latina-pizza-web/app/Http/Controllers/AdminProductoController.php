<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AdminProductoController extends Controller
{
    public function index()
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->get($this->apiUrl('/admin/productos'));

        if ($response->successful()) {
            $productos = $response->json();

            return view('admin.productos.index', compact('productos'));
        }

        return back()->with('error', 'Error al obtener los productos: '.$response->body());
    }

    public function create()
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $categorias = Http::withToken($token)
                ->get($this->apiUrl('/categorias'))
                ->json();

            $sabores = Http::withToken($token)
                ->get($this->apiUrl('/admin/sabores'))
                ->json();

            $tamanos = Http::withToken($token)
                ->get($this->apiUrl('/admin/tamanos'))
                ->json()['data'] ?? [];

            return view('admin.productos.create', compact('categorias', 'sabores', 'tamanos'));

        } catch (\Exception $e) {
            Log::error('No se pudieron cargar los datos para crear un producto.', ['exception' => $e]);

            return back()->with('error', 'No se pudieron cargar los datos.')->withInput();
        }
    }

    public function store(Request $request)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $rules = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|url:http,https|max:2048',
            'categoria_id' => 'required|integer',
            'estado' => 'nullable|boolean',
        ];

        if ($request->categoria_id && strtolower((string) $request->categoria_nombre) === 'pizza') {
            $rules['sabor_id'] = 'required|integer';
            $rules['tamano_id'] = 'required|integer';
        }

        $validated = $request->validate($rules);

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->post($this->apiUrl('/admin/productos'), $validated);

            if ($response->successful()) {
                return redirect()->route('admin.productos.index')->with('success', 'Producto creado correctamente');
            }

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el producto: '.$response->body());

        } catch (\Exception $e) {
            Log::error('No se pudo crear el producto.', ['exception' => $e]);

            return back()->with('error', 'Ocurrió un error inesperado.')->withInput();
        }
    }

    public function edit($id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $productoResponse = Http::withToken($token)->get($this->apiUrl("/admin/productos/{$id}"));
            if (! $productoResponse->successful()) {
                return back()->with('error', 'No se pudo cargar el producto: '.$productoResponse->body());
            }
            $producto = $productoResponse->json();

            $categorias = Http::withToken($token)
                ->get($this->apiUrl('/categorias'))
                ->json();

            $sabores = Http::withToken($token)
                ->get($this->apiUrl('/admin/sabores'))
                ->json();

            $tamanos = Http::withToken($token)
                ->get($this->apiUrl('/admin/tamanos'))
                ->json()['data'] ?? [];

            return view('admin.productos.edit', compact('producto', 'categorias', 'sabores', 'tamanos'));

        } catch (\Exception $e) {
            Log::error('No se pudo cargar el producto para editarlo.', ['exception' => $e]);

            return back()->with('error', 'No se pudieron cargar los datos del producto.');
        }
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $data = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|url:http,https|max:2048',
            'categoria_id' => 'required|integer',
            'sabor_id' => 'nullable|integer',
            'tamano_id' => 'nullable|integer',
            'estado' => 'required|boolean',
        ]);

        $response = Http::withToken($token)
            ->acceptJson()
            ->put($this->apiUrl("/admin/productos/{$id}"), $data);

        if ($response->successful()) {
            return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado correctamente');
        }

        return back()
            ->withInput()
            ->with('error', 'No se pudo actualizar el producto: '.$response->body());
    }

    public function destroy($id)
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->delete($this->apiUrl("/admin/productos/{$id}"));

        if ($response->successful()) {
            return redirect()->route('admin.productos.index')->with('success', 'Producto eliminado correctamente');
        }

        return back()->with('error', 'Hubo un problema al eliminar el producto: '.$response->body());
    }
}
