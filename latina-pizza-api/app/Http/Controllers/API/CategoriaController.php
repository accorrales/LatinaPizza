<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\Producto;

class CategoriaController extends Controller
{
    public function index()
    {
        return Categoria::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
            'descripcion' => 'nullable|string'
        ]);

        $categoria = Categoria::create($validated);

        return response()->json($categoria, 201);
    }

    public function show($id)
    {
        return Categoria::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255|unique:categorias,nombre,'.$categoria->id,
            'descripcion' => 'nullable|string'
        ]);

        $categoria->update($validated);

        return response()->json($categoria);
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        if (Producto::where('categoria_id', $categoria->id)->exists()) {
            return response()->json(['message' => 'La categoría contiene productos y no se puede eliminar.'], 409);
        }
        $categoria->delete();
        return response()->json(['message' => 'Categoría eliminada.']);
    }
}
