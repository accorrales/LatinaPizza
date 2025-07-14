<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sabor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class SaborController extends Controller
{
    public function index(): JsonResponse
    {
        $sabores = Sabor::select('id', 'nombre', 'descripcion', 'imagen')
                        ->orderBy('nombre')
                        ->get();

        return response()->json($sabores);
    }
}