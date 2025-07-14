<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminMasaController extends Controller
{
    private $apiBase = 'http://127.0.0.1:8001/api/admin';

    public function index()
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->get("$this->apiBase/masas");

        if ($response->successful()) {
            $masas = $response->json();
            return view('admin.masas.index', compact('masas'));
        }

        return back()->with('error', 'No se pudieron cargar las masas.');
    }

    public function create()
    {
        return view('admin.masas.create');
    }

    public function store(Request $request)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $validated = $request->validate([
            'tipo' => 'required|string|max:255',
            'precio_extra' => 'nullable|numeric|min:0',
        ]);

        $response = Http::withToken($token)->post("$this->apiBase/masas", $validated);

        if ($response->successful()) {
            return redirect()->route('admin.masas.index')->with('success', 'Masa creada correctamente.');
        }

        return back()->with('error', 'Error al crear la masa.');
    }

    public function edit($id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->get("$this->apiBase/masas/{$id}");

        if ($response->successful()) {
            $masa = (object) $response->json();
            return view('admin.masas.edit', compact('masa'));
        }

        return redirect()->route('admin.masas.index')->with('error', 'No se pudo cargar la masa.');
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->put("$this->apiBase/masas/{$id}", [
            'tipo' => $request->tipo,
            'precio_extra' => $request->precio_extra,
        ]);

        if ($response->successful()) {
            return redirect()->route('admin.masas.index')->with('success', 'Masa actualizada correctamente.');
        }

        return back()->with('error', 'No se pudo actualizar la masa.')->withInput();
    }

    public function destroy($id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->delete("$this->apiBase/masas/{$id}");

        if ($response->successful()) {
            return redirect()->route('admin.masas.index')->with('success', 'Masa eliminada correctamente.');
        }

        return back()->with('error', 'Error al eliminar la masa.');
    }
}
