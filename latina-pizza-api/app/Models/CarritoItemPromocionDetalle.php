<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarritoItemPromocionDetalle extends Model
{
    protected $table = 'carrito_items_promocion_detalles';

    protected $fillable = [
        'carrito_item_id',
        'tipo',            // Asegúrate de que esté aquí
        'sabor_id',
        'masa_id',
        'nota_cliente',
        'producto_id',
    ];
    public function carritoItem()
    {
        return $this->belongsTo(CarritoItem::class);
    }

    public function sabor()
    {
        return $this->belongsTo(Sabor::class);
    }

    public function masa()
    {
        return $this->belongsTo(Masa::class);
    }

    public function extras()
    {
        return $this->hasMany(CarritoItemsPromocionExtra::class, 'detalle_id');
    }
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    public function tamano()
    {
        return $this->belongsTo(Tamano::class, 'tamano_id');
    }

}

