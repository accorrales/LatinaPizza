<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sucursal_id',
        'total',
        'estado',
        'tipo_pedido',
        'payment_provider',
        'payment_ref',
        'payment_status',
        'paid_at',
    ];
    public function markPaid(string $provider, string $ref): void
    {
        $this->forceFill([
            'payment_provider' => $provider,
            'payment_ref'      => $ref,
            'payment_status'   => 'paid',
            'paid_at'          => now(),
        ])->save();
    }
    // En Producto.php
    public function masa()
    {
        return $this->belongsTo(Masa::class, 'masa_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function guardarHistorial($estado)
    {
        $this->historial()->create([
            'estado' => $estado,
        ]);
    }
    public function historial()
    {
        return $this->hasMany(HistorialPedido::class);
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
    public function promociones()
    {
        return $this->hasMany(DetallePedidoPromocion::class);
    }
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }
}

