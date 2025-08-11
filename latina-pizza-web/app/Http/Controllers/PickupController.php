<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
class PickupController extends Controller
{
    public function index()
    {
        $token = session('token'); // o como estés guardando el token

        $sucursales = Http::withToken($token)->get('http://127.0.0.1:8001/api/sucursales')->json();

        return view('pickup.index', compact('sucursales'));
    }
}
