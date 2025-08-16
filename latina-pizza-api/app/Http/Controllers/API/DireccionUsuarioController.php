<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DireccionUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DireccionUsuarioController extends Controller
{
    // ✅ Listar direcciones del usuario autenticado
    public function index()
    {
        $user = Auth::user();
        $direcciones = DireccionUsuario::where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $direcciones]);
    }

    // ✅ Guardar nueva dirección
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nombre'             => 'required|string|max:255',
            'direccion_exacta'   => 'required|string|max:255',
            'provincia'          => 'required|string|max:255',
            'canton'             => 'required|string|max:255',
            'distrito'           => 'required|string|max:255',
            'telefono_contacto'  => 'required|string|max:50',
            'referencias'        => 'nullable|string|max:255',
            'latitud'            => 'nullable|numeric|between:-90,90',
            'longitud'           => 'nullable|numeric|between:-180,180',
        ]);

        $direccion = new DireccionUsuario($validated);
        $direccion->user_id = $user->id;
        $direccion->save();

        return response()->json([
            'message' => 'Dirección guardada correctamente',
            'data' => $direccion
        ]);
    }

    // ✅ Eliminar dirección
    public function destroy($id)
    {
        $user = Auth::user();

        $direccion = DireccionUsuario::where('user_id', $user->id)->findOrFail($id);
        $direccion->delete();

        return response()->json(['message' => 'Dirección eliminada correctamente']);
    }

    public function show($id)
    {
        $user = Auth::user();
        $dir = DireccionUsuario::where('user_id', $user->id)->findOrFail($id);
        return response()->json(['data' => $dir]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $dir = DireccionUsuario::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'nombre'             => 'sometimes|required|string|max:255',
            'direccion_exacta'   => 'sometimes|required|string|max:255',
            'provincia'          => 'sometimes|required|string|max:255',
            'canton'             => 'sometimes|required|string|max:255',
            'distrito'           => 'sometimes|required|string|max:255',
            'telefono_contacto'  => 'sometimes|required|string|max:50',
            'referencias'        => 'nullable|string|max:255',
            'latitud'            => 'nullable|numeric|between:-90,90',
            'longitud'           => 'nullable|numeric|between:-180,180',
        ]);

        $dir->update($validated);
        return response()->json(['message' => 'Dirección actualizada', 'data' => $dir]);
    }
}

