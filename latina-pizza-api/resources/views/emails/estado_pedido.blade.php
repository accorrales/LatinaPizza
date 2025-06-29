<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estado del Pedido</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2>Hola {{ $pedido->usuario->name }},</h2>
    <p>Tu pedido con ID <strong>#{{ $pedido->id }}</strong> ha cambiado de estado.</p>
    <p><strong>Nuevo estado:</strong> {{ ucfirst($pedido->estado) }}</p>
    <p>Gracias por confiar en Latina Pizza 🍕</p>
</body>
</html>
