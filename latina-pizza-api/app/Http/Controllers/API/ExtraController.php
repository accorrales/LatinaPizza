<?php 

namespace App\Http\Controllers\Api;

use App\Models\Extra;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExtraController extends Controller
{
    public function index()
    {
        return response()->json(Extra::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_pequena' => 'required|numeric|min:0',
            'precio_mediana' => 'required|numeric|min:0',
            'precio_grande' => 'required|numeric|min:0',
            'precio_extragrande' => 'required|numeric|min:0',
        ]);

        $extra = Extra::create($validated);
        return response()->json($extra, 201);
    }

    public function show($id)
    {
        return response()->json(Extra::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $extra = Extra::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_pequena' => 'required|numeric|min:0',
            'precio_mediana' => 'required|numeric|min:0',
            'precio_grande' => 'required|numeric|min:0',
            'precio_extragrande' => 'required|numeric|min:0',
        ]);

        $extra->update($validated);
        return response()->json($extra);
    }

    public function destroy($id)
    {
        Extra::findOrFail($id)->delete();
        return response()->json(['message' => 'Extra eliminado correctamente.']);
    }
}
