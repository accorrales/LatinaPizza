<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminPromocionController extends Controller
{
    public function index()
    {
        $token = Session::get('token');

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get("{$this->apiBase}/promociones")->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            $promociones = $response->json()['data'];

            return view('admin.promociones.index', compact('promociones'));
        }

        return back()->with('error', 'No se pudieron cargar las promociones.');
    }

    public function create()
    {
        $token = Session::get('token');

        try {
            $tamanosResponse = Http::connectTimeout(3)->timeout(5)->withToken($token)->get("{$this->apiBase}/admin/tamanos")->throw()->json();
            $saboresResponse = Http::connectTimeout(3)->timeout(5)->withToken($token)->get("{$this->apiBase}/admin/sabores")->throw()->json();
            $masasResponse = Http::connectTimeout(3)->timeout(5)->withToken($token)->get("{$this->apiBase}/admin/masas")->throw()->json();
            $bebidasResponse = Http::connectTimeout(3)->timeout(5)->get("{$this->apiBase}/bebidas")->throw()->json();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }
        $tamanos = $tamanosResponse['data'] ?? $tamanosResponse;
        $sabores = $saboresResponse['data'] ?? $saboresResponse;
        $masas = $masasResponse['data'] ?? $masasResponse;
        $bebidas = $bebidasResponse['data'] ?? $bebidasResponse;

        return view('admin.promociones.create', compact('tamanos', 'sabores', 'masas', 'bebidas'));
    }

    public function store(Request $request)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_total' => 'required|numeric|min:0',
            'precio_sugerido' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|url:http,https|max:2048',
            'incluye_bebida' => 'nullable|boolean',
            'componentes' => 'required|array|min:1',
            'componentes.*.tipo' => 'required|in:pizza,bebida',
            'componentes.*.cantidad' => 'required|integer|min:1',
            'componentes.*.tamano_id' => 'nullable|integer',
            'componentes.*.producto_id' => 'nullable|integer',
        ]);

        $data = $validated;
        $data['incluye_bebida'] = $request->has('incluye_bebida');

        // Agregar campos por defecto a los componentes si no vienen
        foreach ($data['componentes'] as &$componente) {
            $componente['sabor_id'] = $componente['sabor_id'] ?? null;
            $componente['masa_id'] = $componente['masa_id'] ?? null;
        }
        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->post("{$this->apiBase}/promociones", $data)->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.promociones.index')->with('success', 'Promoción creada correctamente');
        }

        return back()->with('error', 'Error al guardar la promoción.')->withInput();
    }

    public function edit($id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $promocion = Http::connectTimeout(3)->timeout(5)->withToken($token)->get("{$this->apiBase}/promociones/{$id}")->throw()->json()['data'] ?? null;
            $tamanosResponse = Http::connectTimeout(3)->timeout(5)->withToken($token)->get("{$this->apiBase}/admin/tamanos")->throw()->json();
            $saboresResponse = Http::connectTimeout(3)->timeout(5)->withToken($token)->get("{$this->apiBase}/admin/sabores")->throw()->json();
            $masasResponse = Http::connectTimeout(3)->timeout(5)->withToken($token)->get("{$this->apiBase}/admin/masas")->throw()->json();
            $bebidasResponse = Http::connectTimeout(3)->timeout(5)->get("{$this->apiBase}/bebidas")->throw()->json();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }
        $tamanos = $tamanosResponse['data'] ?? $tamanosResponse;
        $sabores = $saboresResponse['data'] ?? $saboresResponse;
        $masas = $masasResponse['data'] ?? $masasResponse;
        $bebidas = $bebidasResponse['data'] ?? $bebidasResponse;

        return view('admin.promociones.edit', compact('promocion', 'tamanos', 'sabores', 'masas', 'bebidas'));
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_total' => 'required|numeric|min:0',
            'precio_sugerido' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|url:http,https|max:2048',
            'incluye_bebida' => 'nullable|boolean',
            'componentes' => 'required|array|min:1',
            'componentes.*.tipo' => 'required|in:pizza,bebida',
            'componentes.*.cantidad' => 'required|integer|min:1',
            'componentes.*.tamano_id' => 'nullable|integer',
            'componentes.*.producto_id' => 'nullable|integer',
        ]);

        $data = $validated;
        $data['incluye_bebida'] = $request->has('incluye_bebida');

        foreach ($data['componentes'] as &$componente) {
            $componente['sabor_id'] = $componente['sabor_id'] ?? null;
            $componente['masa_id'] = $componente['masa_id'] ?? null;
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->put("{$this->apiBase}/promociones/{$id}", $data)->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.promociones.index')->with('success', 'Promoción actualizada correctamente');
        }

        return back()->with('error', 'Error al actualizar la promoción.')->withInput();
    }

    public function destroy($id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->delete("{$this->apiBase}/promociones/{$id}")->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.promociones.index')->with('success', 'Promoción eliminada correctamente');
        }

        return back()->with('error', 'No se pudo eliminar la promoción');
    }
}
