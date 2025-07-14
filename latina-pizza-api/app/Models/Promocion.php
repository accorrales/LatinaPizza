<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $table = 'promociones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_total',
        'precio_sugerido',
        'imagen', // si la agregaste
    ];

    public function componentes()
    {
        return $this->hasMany(PromocionComponente::class, 'promocion_id')
                    ->with(['sabor', 'tamano', 'masa']);
    }
}


