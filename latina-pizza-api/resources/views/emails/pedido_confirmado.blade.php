<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedido Confirmado</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px;">
    <div style="background: #fff; border-radius: 10px; padding: 30px; max-width: 600px; margin: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #dc3545;">🍕 Gracias por tu pedido en <span style="color:#007bff;">Latina Pizza</span></h2>
        <p>Hola {{ $pedido->usuario?->name ?? 'Cliente' }},</p>
        <p>Hemos recibido tu pedido con el número <strong>#{{ $pedido->id }}</strong>.</p>

        <h4>📦 Detalles del pedido:</h4>
        <ul>
            @foreach($pedido->productos as $producto)
                <li>{{ $producto->nombre }} × {{ $producto->pivot->cantidad }}</li>
            @endforeach
        </ul>

        <p>🕒 Estado actual: <strong>{{ ucfirst($pedido->estado) }}</strong></p>
        <p>📍 Sucursal: <strong>{{ $pedido->sucursal->nombre ?? 'Principal' }}</strong></p>

        <br>
        <p>¡Gracias por elegirnos! Te avisaremos cuando esté listo. 🍽️</p>
        <hr>
        <p style="font-size: 12px; color: #888;">Latina Pizza - Este correo es informativo. No responder.</p>
    </div>
</body>
</html>
