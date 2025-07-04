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
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePedidoPromocion::class, 'promocion_id')
                    ->with(['sabor', 'tamano', 'masa']);
    }
}
