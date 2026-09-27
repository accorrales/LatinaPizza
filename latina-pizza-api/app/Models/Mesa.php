<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;

    public const DISPONIBLE = 'disponible';

    public const OCUPADA = 'ocupada';

    public const RESERVADA = 'reservada';

    public const FUERA_SERVICIO = 'fuera_servicio';

    protected $table = 'mesas';

    protected $fillable = [
        'sucursal_id',
        'numero',
        'nombre',
        'zona',
        'capacidad',
        'estado',
        'activo',
    ];

    protected $casts = [
        'capacidad' => 'integer',
        'activo' => 'boolean',
    ];

    protected $attributes = [
        'estado' => self::DISPONIBLE,
        'activo' => true,
        'capacidad' => 4,
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function sesiones()
    {
        return $this->hasMany(MesaSesion::class);
    }

    public function sesionActiva()
    {
        return $this->hasOne(MesaSesion::class)
            ->where('estado', MesaSesion::ABIERTA)
            ->latestOfMany('opened_at');
    }
}
