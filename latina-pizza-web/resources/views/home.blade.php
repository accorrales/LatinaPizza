@extends('layouts.app')

@section('content')
<div class="text-center py-16 bg-gradient-to-r from-red-600 via-pink-500 to-yellow-400 text-white">
    <h1 class="text-5xl font-extrabold">¡Bienvenido a Latina Pizza! 🍕</h1>
    <p class="mt-4 text-xl">Del horno a tu mesa. Pide online, retira o recibe en minutos.</p>
</div>

<section class="max-w-6xl mx-auto py-12 px-6">
    <h2 class="text-3xl font-bold mb-6 text-center">Explora nuestro menú</h2>
    <div class="grid md:grid-cols-3 gap-6">
        <!-- Aquí se cargarán las categorías y productos en el futuro -->
        <div class="bg-white p-6 rounded shadow">
            <h3 class="text-xl font-semibold">🍕 Pizzas</h3>
            <p class="mt-2 text-gray-600">Clásicas, gourmet y especiales</p>
        </div>
        <div class="bg-white p-6 rounded shadow">
            <h3 class="text-xl font-semibold">🥤 Bebidas</h3>
            <p class="mt-2 text-gray-600">Refrescos, cervezas y más</p>
        </div>
        <div class="bg-white p-6 rounded shadow">
            <h3 class="text-xl font-semibold">🍟 Complementos</h3>
            <p class="mt-2 text-gray-600">Papas, alitas y postres</p>
        </div>
    </div>
</section>
@endsection
