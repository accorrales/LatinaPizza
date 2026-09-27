<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MesaSesion extends Model
{
    use HasFactory;

    public const ABIERTA = 'abierta';

    public const CERRADA = 'cerrada';

    public const CANCELADA = 'cancelada';

    protected $table = 'mesa_sesiones';

    protected $fillable = [
        'mesa_id',
        'sucursal_id',
        'mesero_user_id',
        'estado',
        'personas',
        'opened_at',
        'closed_at',
        'notas',
    ];

    protected $casts = [
        'personas' => 'integer',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected $attributes = [
        'estado' => self::ABIERTA,
        'personas' => 1,
    ];

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function mesero()
    {
        return $this->belongsTo(User::class, 'mesero_user_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'mesa_sesion_id');
    }

    public function scopeAbiertas($query)
    {
        return $query->where('estado', self::ABIERTA);
    }

    public function cerrar(): void
    {
        $this->forceFill([
            'estado' => self::CERRADA,
            'closed_at' => now(),
        ])->save();
    }
}
