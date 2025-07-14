<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tamano;
use Illuminate\Http\Request;

class TamanoController extends Controller
{
    public function index()
    {
        return response()->json(Tamano::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string|max:255',
            'precio_base' => 'required|numeric|min:0',
        ]);

        $tamano = Tamano::create($request->all());

        return response()->json($tamano, 201);
    }

    public function show($id)
    {
        $tamano = Tamano::findOrFail($id);
        return response()->json($tamano);
    }

    public function update(Request $request, $id)
    {
        $tamano = Tamano::findOrFail($id);

        $request->validate([
            'tipo' => 'required|string|max:255',
            'precio_base' => 'required|numeric|min:0',
        ]);

        $tamano->update($request->all());

        return response()->json($tamano);
    }

    public function destroy($id)
    {
        $tamano = Tamano::findOrFail($id);
        $tamano->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}

