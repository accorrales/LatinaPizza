<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarritoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrito_id',
        'producto_id',
        'promocion_id',
        'sabor_id',
        'tamano_id',
        'masa_id',
        'cantidad',
        'nota_cliente',
        'precio_total',
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function masa()
    {
        return $this->belongsTo(Masa::class);
    }

    public function extras()
    {
        return $this->belongsToMany(Extra::class, 'carrito_item_extra')->withTimestamps();
    }
    public function carritoItems()
    {
        return $this->belongsToMany(CarritoItem::class, 'carrito_item_extra')->withTimestamps();
    }

    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
    }
    public function detallesPromocion()
    {
        return $this->hasMany(CarritoItemPromocionDetalle::class);
    }
    public function extrasPromocion()
    {
        return $this->hasManyThrough(
            CarritoItemsPromocionExtra::class,
            CarritoItemPromocionDetalle::class,
            'carrito_item_id', // Foreign key en detalles
            'detalle_id',       // Foreign key en extras
            'id',               // Local key en carrito_items
            'id'                // Local key en detalles
        );
    }
}
