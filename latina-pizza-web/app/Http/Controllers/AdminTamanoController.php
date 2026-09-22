<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminTamanoController extends Controller
{
    public function index()
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }
        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get("$this->apiBase/admin/tamanos")->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            $tamanos = $response->json()['data'] ?? []; // ✅ Corrección

            return view('admin.tamanos.index', compact('tamanos'));
        }

        return back()->with('error', 'No se pudieron cargar los tamaños.');
    }

    public function create()
    {
        return view('admin.tamanos.create');
    }

    public function store(Request $request)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_base' => 'required|numeric|min:0',
        ]);

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->post("$this->apiBase/admin/tamanos", $validated)->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.tamanos.index')->with('success', 'Tamaño creado correctamente.');
        }

        return back()->with('error', 'Error al crear el tamaño.')->withInput();
    }

    public function edit($id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get("$this->apiBase/admin/tamanos/$id")->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            $tamano = (object) $response->json();

            return view('admin.tamanos.edit', compact('tamano'));
        }

        return redirect()->route('admin.tamanos.index')->with('error', 'No se pudo cargar el tamaño.');
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_base' => 'required|numeric|min:0',
        ]);

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->put("$this->apiBase/admin/tamanos/$id", $validated)->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.tamanos.index')->with('success', 'Tamaño actualizado correctamente.');
        }

        return back()->with('error', 'No se pudo actualizar el tamaño.')->withInput();
    }

    public function destroy($id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->delete("$this->apiBase/admin/tamanos/$id")->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.tamanos.index')->with('success', 'Tamaño eliminado correctamente.');
        }

        return back()->with('error', 'Error al eliminar el tamaño.');
    }
}
