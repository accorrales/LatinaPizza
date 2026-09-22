<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
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

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get($this->apiUrl('/admin/productos'))->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

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
            $categorias = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->get($this->apiUrl('/categorias'))->throwIfServerError()
                ->json();

            $sabores = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->get($this->apiUrl('/admin/sabores'))->throwIfServerError()
                ->json();

            $tamanosResponse = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->get($this->apiUrl('/admin/tamanos'))->throwIfServerError()
                ->json();
            $tamanos = $tamanosResponse['data'] ?? $tamanosResponse;

            return view('admin.productos.create', compact('categorias', 'sabores', 'tamanos'));

        } catch (\Throwable $e) {
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

        $isPizza = str_contains(strtolower((string) $request->input('categoria_nombre')), 'pizza');

        $rules = [
            'nombre' => [$isPizza ? 'nullable' : 'required', 'string', 'max:255'],
            'descripcion' => 'nullable|string',
            'precio' => [$isPizza ? 'nullable' : 'required', 'numeric', 'min:0'],
            'imagen' => 'nullable|url:http,https|max:2048',
            'categoria_id' => 'required|integer',
            'estado' => 'nullable|boolean',
            'sabor_id' => [$isPizza ? 'required' : 'nullable', 'integer'],
            'tamano_id' => [$isPizza ? 'required' : 'nullable', 'integer'],
        ];

        $validated = $request->validate($rules);

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->acceptJson()
                ->post($this->apiUrl('/admin/productos'), $validated)->throwIfServerError();

            if ($response->successful()) {
                return redirect()->route('admin.productos.index')->with('success', 'Producto creado correctamente');
            }

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el producto: '.$response->body());

        } catch (\Throwable $e) {
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
            $productoResponse = Http::connectTimeout(3)->timeout(5)->withToken($token)->get($this->apiUrl("/admin/productos/{$id}"))->throwIfServerError();
            if (! $productoResponse->successful()) {
                return back()->with('error', 'No se pudo cargar el producto: '.$productoResponse->body());
            }
            $producto = $productoResponse->json();

            $categorias = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->get($this->apiUrl('/categorias'))->throwIfServerError()
                ->json();

            $sabores = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->get($this->apiUrl('/admin/sabores'))->throwIfServerError()
                ->json();

            $tamanosResponse = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->get($this->apiUrl('/admin/tamanos'))->throwIfServerError()
                ->json();
            $tamanos = $tamanosResponse['data'] ?? $tamanosResponse;

            return view('admin.productos.edit', compact('producto', 'categorias', 'sabores', 'tamanos'));

        } catch (\Throwable $e) {
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

        $isPizza = $request->filled('sabor_id') || $request->filled('tamano_id');

        $data = $request->validate([
            'nombre' => [$isPizza ? 'nullable' : 'required', 'string', 'max:255'],
            'descripcion' => 'nullable|string',
            'precio' => [$isPizza ? 'nullable' : 'required', 'numeric', 'min:0'],
            'imagen' => 'nullable|url:http,https|max:2048',
            'categoria_id' => 'required|integer',
            'sabor_id' => [$isPizza ? 'required' : 'nullable', 'integer'],
            'tamano_id' => [$isPizza ? 'required' : 'nullable', 'integer'],
            'estado' => 'nullable|boolean',
        ]);

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->acceptJson()
                ->put($this->apiUrl("/admin/productos/{$id}"), $data)->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

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

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->delete($this->apiUrl("/admin/productos/{$id}"))->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.productos.index')->with('success', 'Producto eliminado correctamente');
        }

        return back()->with('error', 'Hubo un problema al eliminar el producto: '.$response->body());
    }
}
