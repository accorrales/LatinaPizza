@extends('layouts.app')

@section('content')
    @include('catalogo.partials.menu', [
        'sabores' => $sabores,
        'categorias' => $categorias,
        'categoriaSeleccionada' => $categoriaSeleccionada,
        'promociones' => $promociones
    ])
@endsection
