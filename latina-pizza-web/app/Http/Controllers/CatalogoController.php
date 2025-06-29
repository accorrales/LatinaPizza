<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CatalogoController extends Controller
{
    public function index()
    {
        $response = Http::get('http://127.0.0.1:8001/api/productos');

        if ($response->successful()) {
            $productos = $response->json();
        } else {
            $productos = []; // <-- importante definir esto si falla
            session()->flash('error', 'No se pudieron obtener los productos.');
        }
        return view('catalogo.index', compact('productos'));
    }
}