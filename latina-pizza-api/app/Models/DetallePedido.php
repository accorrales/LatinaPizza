<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $fillable = [
        'pedido_id',
        'sabor_id',
        'tamano_id',
        'masa_id',
        'nota_cliente',
        'precio_total',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
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
        return $this->belongsToMany(Extra::class, 'detalle_pedido_extra', 'detalle_pedido_id', 'extra_id')->withTimestamps();
    }
}
