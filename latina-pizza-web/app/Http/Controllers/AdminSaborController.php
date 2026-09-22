<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminSaborController extends Controller
{
    public function index()
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get($this->apiUrl('/admin/sabores'))->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            $sabores = $response->json();

            return view('admin.sabores.index', compact('sabores'));
        }

        return back()->with('error', 'No se pudieron cargar los sabores: '.$response->body());
    }

    public function create()
    {
        return view('admin.sabores.create');
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
            'imagen' => 'nullable|url:http,https|max:2048',
        ]);

        if (array_key_exists('imagen', $validated) && $validated['imagen'] === '') {
            $validated['imagen'] = null;
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->acceptJson()
                ->post($this->apiUrl('/admin/sabores'), $validated)->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.sabores.index')->with('success', 'Sabor creado correctamente.');
        }

        return back()->withInput()->with('error', 'Error al crear el sabor: '.$response->body());
    }

    public function edit($id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get($this->apiUrl("/admin/sabores/{$id}"))->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            $sabor = (object) $response->json();

            return view('admin.sabores.edit', compact('sabor'));
        }

        return redirect()->route('admin.sabores.index')->with('error', 'No se pudo cargar el sabor: '.$response->body());
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|url:http,https|max:2048',
        ]);

        if (array_key_exists('imagen', $validated) && $validated['imagen'] === '') {
            $validated['imagen'] = null;
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->acceptJson()
                ->put($this->apiUrl("/admin/sabores/{$id}"), $validated)->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.sabores.index')->with('success', 'Sabor actualizado correctamente.');
        }

        return back()->withInput()->with('error', 'No se pudo actualizar el sabor: '.$response->body());
    }

    public function destroy($id)
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->delete($this->apiUrl("/admin/sabores/{$id}"))->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.sabores.index')->with('success', 'Sabor eliminado correctamente.');
        }

        return back()->with('error', 'Error al eliminar el sabor: '.$response->body());
    }
}
