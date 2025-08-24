<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
        'precio_total' => 'float',
        'precio'       => 'float',
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
        return (float) DB::table('carrito_items')
            ->where('carrito_id', $this->id)
            ->sum('precio_total');
    }

    /**
     * Total general con envío (si aplica)
     */
    public function calcTotal(): float
    {
        $subtotal = $this->calcSubtotal();
        $envio    = ($this->tipo_entrega === 'express')
                    ? (float) ($this->delivery_fee ?? 0)
                    : 0.0;

        return round($subtotal + $envio, 2);
    }
    public function subtotalBreakdown(): array
    {
        $items = DB::table('carrito_items')
            ->select('id','producto_id','promocion_id','cantidad','precio_total')
            ->where('carrito_id', $this->id)
            ->get();

        $sum_items_raw = (float) $items->sum('precio_total');

        // Extras de promos solo para inspección (no se suman al subtotal)
        $extras = DB::table('carrito_items_promocion_extras as x')
            ->join('carrito_items_promocion_detalles as d','d.id','=','x.detalle_id')
            ->join('carrito_items as ci','ci.id','=','d.carrito_item_id')
            ->where('ci.carrito_id', $this->id)
            ->select('x.detalle_id','ci.id as carrito_item_id','ci.cantidad as cantidad_item','x.precio')
            ->get()
            ->map(function($row){
                $row->precio_x_qty = (float)$row->precio * (int)$row->cantidad_item;
                return $row;
            });

        $sum_extras = (float) $extras->sum('precio_x_qty');

        return [
            'items'          => $items,
            'extras'         => $extras,
            'sum_items_raw'  => $sum_items_raw,
            'sum_extras'     => $sum_extras,
            'subtotal'       => $sum_items_raw, // ¡clave! no volver a sumar extras
        ];
    }
}