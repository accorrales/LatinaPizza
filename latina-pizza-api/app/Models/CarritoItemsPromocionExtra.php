<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarritoItemsPromocionExtra extends Model
{
    protected $table = 'carrito_items_promocion_extras';

    protected $fillable = [
        'detalle_id',
        'extra_id',
        'precio',
    ];

    public function detalle()
    {
        return $this->belongsTo(CarritoItemPromocionDetalle::class, 'detalle_id');
    }
    public function extra()
    {
        return $this->belongsTo(Extra::class);
    }
}
