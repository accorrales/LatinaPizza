<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Masa;
class MasaController extends Controller
{
    public function index()
    {
        return response()->json(Masa::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|string|max:255',
            'precio_extra' => 'nullable|numeric|min:0',
        ]);

        $masa = Masa::create($validated);
        return response()->json($masa, 201);
    }

    public function show($id)
    {
        return response()->json(Masa::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $masa = Masa::findOrFail($id);
        $masa->update($request->only('tipo', 'precio_extra'));
        return response()->json($masa);
    }

    public function destroy($id)
    {
        Masa::findOrFail($id)->delete();
        return response()->json(['message' => 'Masa eliminada correctamente.']);
    }
}
