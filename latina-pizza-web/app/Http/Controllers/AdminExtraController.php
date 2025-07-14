<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminExtraController extends Controller
{
    private $apiBase = 'http://127.0.0.1:8001/api/admin';

    public function index()
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login')->with('error', 'Debe iniciar sesión');

        $response = Http::withToken($token)->get("$this->apiBase/extras-productos");

        if ($response->successful()) {
            $extras = $response->json();
            return view('admin.extras.index', compact('extras'));
        }

        return back()->with('error', 'No se pudieron cargar los extras.');
    }

    public function create()
    {
        return view('admin.extras.create');
    }

    public function store(Request $request)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login')->with('error', 'Debe iniciar sesión');

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_pequena' => 'nullable|numeric|min:0',
            'precio_mediana' => 'nullable|numeric|min:0',
            'precio_grande' => 'nullable|numeric|min:0',
            'precio_extragrande' => 'nullable|numeric|min:0',
        ]);

        $response = Http::withToken($token)->post("$this->apiBase/extras-productos", $validated);

        if ($response->successful()) {
            return redirect()->route('admin.extras.index')->with('success', 'Extra creado correctamente.');
        }

        return back()->with('error', 'Error al crear el extra.')->withInput();
    }

    public function edit($id)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login')->with('error', 'Debe iniciar sesión');

        $response = Http::withToken($token)->get("$this->apiBase/extras-productos/{$id}");

        if ($response->successful()) {
            $extra = (object) $response->json();
            return view('admin.extras.edit', compact('extra'));
        }

        return redirect()->route('admin.extras.index')->with('error', 'No se pudo cargar el extra.');
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login')->with('error', 'Debe iniciar sesión');

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_pequena' => 'nullable|numeric|min:0',
            'precio_mediana' => 'nullable|numeric|min:0',
            'precio_grande' => 'nullable|numeric|min:0',
            'precio_extragrande' => 'nullable|numeric|min:0',
        ]);

        $response = Http::withToken($token)->put("$this->apiBase/extras-productos/{$id}", $validated);

        if ($response->successful()) {
            return redirect()->route('admin.extras.index')->with('success', 'Extra actualizado correctamente.');
        }

        return back()->with('error', 'No se pudo actualizar el extra.')->withInput();
    }

    public function destroy($id)
    {
        $token = Session::get('token');
        if (!$token) return redirect()->route('login')->with('error', 'Debe iniciar sesión');

        $response = Http::withToken($token)->delete("$this->apiBase/extras-productos/{$id}");

        if ($response->successful()) {
            return redirect()->route('admin.extras.index')->with('success', 'Extra eliminado correctamente.');
        }

        return back()->with('error', 'No se pudo eliminar el extra.');
    }
}