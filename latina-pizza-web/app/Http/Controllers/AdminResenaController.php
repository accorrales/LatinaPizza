<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
class AdminResenaController extends Controller
{
    public function index()
    {
        $sabores = [];

        $response = Http::get('http://127.0.0.1:8001/api/sabores-con-resenas');

        if ($response->successful()) {
            $sabores = $response->json();
        }

        return view('admin.resenas.index', compact('sabores'));
    }

    public function edit($id)
    {
        $response = Http::get("http://127.0.0.1:8001/api/resenas/$id");

        if (!$response->successful()) {
            return back()->with('error', 'No se pudo cargar la reseña');
        }

        $resena = $response->json();

        return view('admin.resenas.edit', compact('resena'));
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        $request->validate([
            'comentario' => 'nullable|string|max:500',
            'calificacion' => 'required|integer|min:1|max:5',
        ]);

        $response = Http::withToken($token)->put("http://127.0.0.1:8001/api/resenas/$id", [
            'comentario' => $request->comentario,
            'calificacion' => $request->calificacion,
        ]);

        if ($response->successful()) {
            return redirect()->route('admin.resenas.index')->with('success', 'Reseña actualizada correctamente.');
        }

        return back()->with('error', 'Error al actualizar la reseña.');
    }

    public function destroy($id)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->delete("http://127.0.0.1:8001/api/resenas/$id");

        if ($response->successful()) {
            return back()->with('success', 'Reseña eliminada correctamente.');
        }

        return back()->with('error', 'Error al eliminar la reseña.');
    }
}
