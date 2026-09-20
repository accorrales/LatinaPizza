<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
class AdminResenaController extends Controller
{
    public function index()
    {
        $sabores = [];

        $response = Http::get($this->apiUrl('/sabores-con-resenas'));

        if ($response->successful()) {
            $sabores = $response->json();
        }

        return view('admin.resenas.index', compact('sabores'));
    }

    public function destroy($id)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->delete($this->apiUrl("/resenas/$id"));

        if ($response->successful()) {
            return back()->with('success', 'Reseña eliminada correctamente.');
        }

        return back()->with('error', 'Error al eliminar la reseña.');
    }
}
