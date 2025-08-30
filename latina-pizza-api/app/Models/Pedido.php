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

        // Totales / logística
        'tipo_entrega',              // pickup | express
        'direccion_usuario_id',
        'subtotal',
        'delivery_fee',
        'delivery_currency',
        'delivery_distance_km',
        'total',

        // Estado comercial
        'estado',                    // pagado | pendiente | cancelado ...
        'tipo_pedido',               // alias de tipo_entrega si lo usas

        // Pago
        'metodo_pago',               // efectivo | datafono | stripe
        'payment_provider',
        'payment_ref',
        'payment_status',
        'paid_at',

        // Cocina
        'kitchen_status',            // nuevo | preparacion | listo | entregado
        'priority',                  // bool
        'sla_minutes',
        'promised_at',
        'ready_at',
        'taken_by_user_id',
        'kitchen_notes',

        // Snapshot
        'detalle_json',
    ];

    protected $casts = [
        // tiempos
        'paid_at'      => 'datetime',
        'promised_at'  => 'datetime',
        'ready_at'     => 'datetime',

        // flags y números
        'priority'             => 'boolean',
        'subtotal'             => 'float',
        'total'                => 'float',
        'delivery_fee'         => 'float',
        'delivery_distance_km' => 'float',

        // snapshot
        'detalle_json' => 'array',
    ];

    // Defaults útiles (opcional)
    protected $attributes = [
        'kitchen_status' => 'nuevo',
        'priority'       => false,
    ];

    /* ----------------- Helpers de pago ----------------- */
    public function markPaid(string $provider, string $ref): void
    {
        $this->forceFill([
            'payment_provider' => $provider,
            'payment_ref'      => $ref,
            'payment_status'   => 'paid',
            'paid_at'          => now(),
        ])->save();
    }

    /* ----------------- Relaciones ----------------- */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function takenBy()
    {
        return $this->belongsTo(User::class, 'taken_by_user_id');
    }

    // Detalles del pedido (si los usas)
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function promociones()
    {
        return $this->hasMany(DetallePedidoPromocion::class);
    }

    public function historial()
    {
        return $this->hasMany(HistorialPedido::class);
    }

    /* ----------------- Scopes para cocina ----------------- */
    public function scopeKitchenOpen($q)
    {
        return $q->whereIn('kitchen_status', ['nuevo','preparacion','listo']);
    }

    public function scopeByStatus($q, string $status)
    {
        return $q->where('kitchen_status', $status);
    }

    /* ----------------- Helpers de cocina ----------------- */
    public function markKitchenStatus(string $status): void
    {
        $this->update(['kitchen_status' => $status]);

        // opcional: guardar en historial automáticamente
        if (method_exists($this, 'guardarHistorial')) {
            $this->guardarHistorial($status);
        }
    }

    public function isLate(): bool
    {
        return $this->promised_at && !$this->ready_at && now()->greaterThan($this->promised_at);
    }

    public function dueInMinutes(): ?int
    {
        if (!$this->promised_at) return null;
        return now()->diffInMinutes($this->promised_at, false); // negativo si ya se pasó
    }
}