<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminResenaController extends Controller
{
    public function index()
    {
        $sabores = [];

        try {
            $response = Http::connectTimeout(3)->timeout(5)->get($this->apiUrl('/sabores-con-resenas'))->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            $sabores = $response->json();
        }

        return view('admin.resenas.index', compact('sabores'));
    }

    public function destroy($id)
    {
        $token = Session::get('token');

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->delete($this->apiUrl("/resenas/$id"))->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return back()->with('success', 'Reseña eliminada correctamente.');
        }

        return back()->with('error', 'Error al eliminar la reseña.');
    }
}
