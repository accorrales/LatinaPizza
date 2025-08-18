<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Carrito extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tipo_entrega',
        'sucursal_id',
        'direccion_usuario_id',

        // 🔽 nuevos
        'delivery_fee',
        'delivery_distance_km',
        'delivery_currency',
        'stripe_payment_intent_id',
    ];

    protected $casts = [
        'sucursal_id'           => 'integer',
        'direccion_usuario_id'  => 'integer',

        // 🔽 nuevos
        'delivery_fee'          => 'decimal:2',
        'delivery_distance_km'  => 'decimal:2',
    ];

    // (Opcional) relaciones directas
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function direccionUsuario()
    {
        return $this->belongsTo(DireccionUsuario::class, 'direccion_usuario_id');
    }

    // Relaciones que ya tenías
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'carrito_producto')
            ->withPivot(['cantidad', 'masa_id', 'nota_cliente', 'precio_total'])
            ->with(['tamano', 'sabor'])
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function extras()
    {
        return $this->hasManyThrough(
            Extra::class,
            CarritoProductoExtra::class,
            'carrito_id',
            'id',
            'id',
            'extra_id'
        );
    }
    public function items()
    {
        return $this->hasMany(CarritoItem::class);
    }
    /** Subtotal sin envío: suma de precio_total de items + extras de promo */
    public function calcSubtotal(): float
    {
        $items = (float) $this->items()->sum('precio_total');

        // Extras de promociones
        $extrasPromo = (float) CarritoItemsPromocionExtra::query()
            ->whereIn('detalle_id', function ($q) {
                $q->select('id')->from('carrito_items_promocion_detalles')
                  ->whereIn('carrito_item_id', function ($q2) {
                      $q2->select('id')->from('carrito_items')->where('carrito_id', $this->id);
                  });
            })
            ->sum('precio');

        return $items + $extrasPromo;
    }

    /** Total con envío (si aplica) */
    public function calcTotal(): float
    {
        $subtotal = $this->calcSubtotal();
        $envio = ($this->tipo_entrega === 'express') ? (float) ($this->delivery_fee ?? 0) : 0.0;
        return $subtotal + $envio;
    }
}