<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    protected $fillable = [
        'nombre',
        'precio_pequena',
        'precio_mediana',
        'precio_grande',
        'precio_extragrande'
    ];

    public function detalles()
    {
        return $this->belongsToMany(DetallePedido::class, 'detalle_pedido_extra', 'extra_id', 'detalle_pedido_id')->withTimestamps();
    }
    public function productosEnCarrito()
    {
        return $this->belongsToMany(Producto::class, 'carrito_producto_extra', 'extra_id', 'producto_id')
                    ->withPivot('carrito_id')
                    ->withTimestamps();
    }

}
