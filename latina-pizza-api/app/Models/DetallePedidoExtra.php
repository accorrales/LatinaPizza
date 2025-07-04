<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class DetallePedidoExtra extends Model
{
    protected $fillable = [
        'detalle_pedido_id',
        'extra_id',
        'precio_extra',
    ];

    public function detalle()
    {
        return $this->belongsTo(DetallePedido::class, 'detalle_pedido_id');
    }

    public function extra()
    {
        return $this->belongsTo(Extra::class);
    }
}
