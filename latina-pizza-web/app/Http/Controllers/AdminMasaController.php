<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminMasaController extends Controller
{
    public function index()
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get($this->apiUrl('/admin/masas'))->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            $masas = $response->json();

            return view('admin.masas.index', compact('masas'));
        }

        return back()->with('error', 'No se pudieron cargar las masas: '.$response->body());
    }

    public function create()
    {
        return view('admin.masas.create');
    }

    public function store(Request $request)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $validated = $request->validate([
            'tipo' => 'required|string|max:255',
            'precio_extra' => 'nullable|numeric|min:0',
        ]);

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->acceptJson()
                ->post($this->apiUrl('/admin/masas'), $validated)->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.masas.index')->with('success', 'Masa creada correctamente.');
        }

        return back()->withInput()->with('error', 'Error al crear la masa: '.$response->body());
    }

    public function edit($id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get($this->apiUrl("/admin/masas/{$id}"))->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            $masa = (object) $response->json();

            return view('admin.masas.edit', compact('masa'));
        }

        return redirect()->route('admin.masas.index')->with('error', 'No se pudo cargar la masa: '.$response->body());
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $validated = $request->validate([
            'tipo' => 'required|string|max:255',
            'precio_extra' => 'nullable|numeric|min:0',
        ]);

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->acceptJson()
                ->put($this->apiUrl("/admin/masas/{$id}"), $validated)->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.masas.index')->with('success', 'Masa actualizada correctamente.');
        }

        return back()->withInput()->with('error', 'No se pudo actualizar la masa: '.$response->body());
    }

    public function destroy($id)
    {
        $token = Session::get('token');

        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->delete($this->apiUrl("/admin/masas/{$id}"))->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.masas.index')->with('success', 'Masa eliminada correctamente.');
        }

        return back()->with('error', 'Error al eliminar la masa: '.$response->body());
    }
}
