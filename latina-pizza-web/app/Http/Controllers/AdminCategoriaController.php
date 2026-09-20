<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminCategoriaController extends Controller
{
    public function index()
    {
        $token = Session::get('token');
        $response = Http::withToken($token)->get($this->apiUrl('/categorias'));

        $categorias = $response->successful() ? $response->json() : [];

        return view('admin.categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $token = Session::get('token');

        $response = Http::withToken($token)->post($this->apiUrl('/categorias'), [
            'nombre' => $request->nombre,
        ]);

        if ($response->successful()) {
            return redirect()->route('admin.categorias.index')->with('success', '✅ Categoría creada correctamente');
        }

        return redirect()->route('admin.categorias.index')->with('error', '❌ Error al crear la categoría: '.$response->body());
    }

    public function edit($id)
    {
        $token = Session::get('token');
        $response = Http::withToken($token)->get($this->apiUrl("/categorias/{$id}"));

        if ($response->successful()) {
            return view('admin.categorias.edit', ['categoria' => $response->json()]);
        }

        return redirect()->route('admin.categorias.index')->with('error', 'Error al obtener la categoría.');
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');
        $response = Http::withToken($token)->put($this->apiUrl("/categorias/{$id}"), [
            'nombre' => $request->input('nombre'),
        ]);

        if ($response->successful()) {
            return redirect()->route('admin.categorias.index')->with('success', 'Categoría actualizada correctamente');
        }

        return redirect()->route('admin.categorias.index')->with('error', 'Error al actualizar la categoría.');
    }

    public function destroy($id)
    {
        $token = Session::get('token');
        $response = Http::withToken($token)->delete($this->apiUrl("/categorias/{$id}"));

        if ($response->successful()) {
            return redirect()->route('admin.categorias.index')->with('success', 'Categoría eliminada correctamente');
        }

        return redirect()->route('admin.categorias.index')->with('error', 'Error al eliminar la categoría.');
    }
}
