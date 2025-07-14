<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromocionComponente extends Model
{
    protected $table = 'promocion_componentes';

    protected $fillable = [
        'promocion_id',
        'tipo',        // 'pizza' o 'bebida'
        'sabor_id',
        'tamano_id',
        'masa_id',
    ];

    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
    }

    public function sabor()
    {
        return $this->belongsTo(Sabor::class);
    }

    public function tamano()
    {
        return $this->belongsTo(Tamano::class);
    }

    public function masa()
    {
        return $this->belongsTo(Masa::class);
    }
}
