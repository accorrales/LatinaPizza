<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Extra;
use App\Models\Masa;

class OpcionesPizzaController extends Controller
{
    public function masas()
    {
        return response()->json(Masa::select('id', 'tipo', 'precio_extra')->get());
    }

    public function extras()
    {
        return response()->json(Extra::select(
            'id',
            'nombre',
            'precio_pequena',
            'precio_mediana',
            'precio_grande',
            'precio_extragrande'
        )->get());
    }
}
