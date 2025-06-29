<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    public function index()
    {
        return Sucursal::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255'
        ]);

        $sucursal = Sucursal::create($request->only('nombre', 'direccion'));

        return response()->json([
            'message' => 'Sucursal creada correctamente',
            'sucursal' => $sucursal
        ]);
    }

    public function show($id)
    {
        return Sucursal::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $sucursal = Sucursal::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255'
        ]);

        $sucursal->update($request->only('nombre', 'direccion'));

        return response()->json([
            'message' => 'Sucursal actualizada correctamente',
            'sucursal' => $sucursal
        ]);
    }

    public function destroy($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $sucursal->delete();

        return response()->json([
            'message' => 'Sucursal eliminada correctamente'
        ]);
    }
}

