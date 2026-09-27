<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    public const EN_CAMINO = 'en_camino';

    public const CANAL_WEB = 'web';

    public const CANAL_SALON = 'salon';

    public const CANAL_MOSTRADOR = 'mostrador';

    public const CANAL_TELEFONO = 'telefono';

    public const CANAL_WHATSAPP = 'whatsapp';

    // GPS is exposed only by the authorized tracking endpoints, never generic serialization.
    protected $hidden = ['delivery_latitude', 'delivery_longitude', 'delivery_accuracy', 'delivery_recorded_at', 'delivery_received_at'];

    protected static function booted(): void
    {
        static::saving(function (Pedido $pedido) {
            if ($pedido->isDirty('estado') && in_array($pedido->estado, ['entregado', 'cancelado'], true)) {
                $pedido->forceFill(['delivery_latitude' => null, 'delivery_longitude' => null, 'delivery_accuracy' => null, 'delivery_recorded_at' => null, 'delivery_received_at' => null]);
            }
        });
    }

    public function liveLocation(): ?array
    {
        if ($this->estado !== self::EN_CAMINO || $this->delivery_latitude === null || $this->delivery_longitude === null) {
            return null;
        }

        return [
            'latitude' => $this->delivery_latitude,
            'longitude' => $this->delivery_longitude,
            'accuracy' => $this->delivery_accuracy,
            'recorded_at' => $this->delivery_recorded_at?->toIso8601String(),
            'received_at' => $this->delivery_received_at?->toIso8601String(),
        ];
    }

    protected $fillable = [
        'user_id',
        'sucursal_id',

        // Totales / logística
        'tipo_entrega',              // pickup | express | salon
        'direccion_usuario_id',
        'subtotal',
        'delivery_fee',
        'delivery_currency',
        'delivery_distance_km',
        'total',

        // Estado comercial y origen de la venta
        'estado',
        'tipo_pedido',               // alias histórico de tipo_entrega
        'canal_venta',               // web | salon | mostrador | telefono | whatsapp
        'mesa_sesion_id',
        'created_by_user_id',

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
        'delivery_address_json',
    ];

    protected $casts = [
        'delivery_latitude' => 'float',
        'delivery_longitude' => 'float',
        'delivery_accuracy' => 'float',
        'delivery_recorded_at' => 'datetime',
        'delivery_received_at' => 'datetime',
        'paid_at' => 'datetime',
        'promised_at' => 'datetime',
        'ready_at' => 'datetime',
        'priority' => 'boolean',
        'subtotal' => 'float',
        'total' => 'float',
        'delivery_fee' => 'float',
        'delivery_distance_km' => 'float',
        'detalle_json' => 'array',
        'delivery_address_json' => 'array',
    ];

    protected $attributes = [
        'kitchen_status' => 'nuevo',
        'priority' => false,
        'canal_venta' => self::CANAL_WEB,
    ];

    /* ----------------- Helpers de pago ----------------- */
    public function markPaid(string $provider, string $ref): void
    {
        $this->forceFill([
            'payment_provider' => $provider,
            'payment_ref' => $ref,
            'payment_status' => 'paid',
            'paid_at' => now(),
        ])->save();
    }

    /* ----------------- Relaciones ----------------- */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->usuario();
    }

    public function direccionUsuario()
    {
        return $this->belongsTo(DireccionUsuario::class, 'direccion_usuario_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function mesaSesion()
    {
        return $this->belongsTo(MesaSesion::class, 'mesa_sesion_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function takenBy()
    {
        return $this->belongsTo(User::class, 'taken_by_user_id');
    }

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

    public function guardarHistorial(string $estado): HistorialPedido
    {
        return $this->historial()->create([
            'estado' => $estado,
            'fecha' => now(),
        ]);
    }

    /* ----------------- Scopes ----------------- */
    public function scopeKitchenOpen($q)
    {
        return $q->whereIn('kitchen_status', ['nuevo', 'preparacion', 'listo']);
    }

    public function scopeByStatus($q, string $status)
    {
        return $q->where('kitchen_status', $status);
    }

    public function scopeBySalesChannel($q, string $channel)
    {
        return $q->where('canal_venta', $channel);
    }

    /* ----------------- Helpers de cocina ----------------- */
    public function markKitchenStatus(string $status): void
    {
        $this->update(['kitchen_status' => $status]);

        if (method_exists($this, 'guardarHistorial')) {
            $this->guardarHistorial($status);
        }
    }

    public function isLate(): bool
    {
        return $this->promised_at && ! $this->ready_at && now()->greaterThan($this->promised_at);
    }

    public function dueInMinutes(): ?int
    {
        if (! $this->promised_at) {
            return null;
        }

        return now()->diffInMinutes($this->promised_at, false);
    }

    public function productos()
    {
        return $this->belongsToMany(
            Producto::class,
            'pedido_producto',
            'pedido_id',
            'producto_id'
        )->withPivot('cantidad')->withTimestamps();
    }
}
