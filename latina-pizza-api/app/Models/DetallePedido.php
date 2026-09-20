<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $fillable = [
        'pedido_id',
        'producto_id',
        'sabor_id',
        'tamano_id',
        'masa_id',
        'nota_cliente',
        'precio_total',
        'cantidad',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function sabor()
    {
        return $this->belongsTo(Sabor::class);
    }

    public function tamano()
    {
        return $this->belongsTo(Tamano::class);
    }

    public function masa()
    {
        return $this->belongsTo(Masa::class);
    }

    public function extras()
    {
        return $this->belongsToMany(Extra::class, 'detalle_pedido_extra', 'detalle_pedido_id', 'extra_id')
            ->withPivot('precio_extra')
            ->withTimestamps();
    }
    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
    }
}
