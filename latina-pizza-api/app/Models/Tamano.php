<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tamano extends Model
{
    protected $fillable = ['nombre', 'precio_base'];

    public function detallesPedido()
    {
        return $this->hasMany(DetallePedido::class);
    }
    public function tamano()
    {
        return $this->belongsTo(Tamano::class);
    }

}
