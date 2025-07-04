<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedidoPromocion extends Model
{
    protected $table = 'detalle_pedido_promocion';

    protected $fillable = [
        'pedido_id',
        'promocion_id',
        'sabor_id',
        'tamano_id',
        'masa_id',
        'nota_cliente',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
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
        return $this->belongsToMany(Extra::class, 'detalle_promocion_extra')
                    ->withPivot('precio_extra')
                    ->withTimestamps();
    }
}
