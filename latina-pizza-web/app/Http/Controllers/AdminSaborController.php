<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
class AdminSaborController extends Controller
{
    private $apiBase = 'http://127.0.0.1:8001/api/admin';

    public function index()
    {
        
        $token = Session::get('token');
        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }
        $response = Http::withToken($token)->get("$this->apiBase/sabores");

        if ($response->successful()) {
            $sabores = $response->json();
            return view('admin.sabores.index', compact('sabores'));
        }

        return back()->with('error', 'No se pudieron cargar los sabores.');
    }

    public function create()
    {
        return view('admin.sabores.create');
    }

    public function store(Request $request)
    {
        $token = Session::get('token');
        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|url'
        ]);

        $response = Http::withToken($token)->post("$this->apiBase/sabores", $validated);

        if ($response->successful()) {
            return redirect()->route('admin.sabores.index')->with('success', 'Sabor creado correctamente.');
        }

        return back()->with('error', 'Error al crear el sabor.');
    }

    public function edit($id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->get("{$this->apiBase}/sabores/{$id}");

        if ($response->successful()) {
            $sabor = (object) $response->json(); // <-- convertir a objeto
            return view('admin.sabores.edit', compact('sabor'));
        } else {
            return redirect()->route('admin.sabores.index')->with('error', 'No se pudo cargar el sabor.');
        }
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->put("{$this->apiBase}/sabores/{$id}", [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'imagen' => $request->imagen,
        ]);

        if ($response->successful()) {
            return redirect()->route('admin.sabores.index')->with('success', 'Sabor actualizado correctamente.');
        } else {
            return back()->with('error', 'No se pudo actualizar el sabor.')->withInput();
        }
    }


    public function destroy($id)
    {
        $token = Session::get('token');
        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }
        $response = Http::withToken($token)->delete("$this->apiBase/sabores/$id");

        if ($response->successful()) {
            return redirect()->route('admin.sabores.index')->with('success', 'Sabor eliminado correctamente.');
        }

        return back()->with('error', 'Error al eliminar el sabor.');
    }
}


