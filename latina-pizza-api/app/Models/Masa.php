<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Masa extends Model
{
    protected $fillable = ['tipo', 'precio_extra'];

    public function detallesPedido()
    {
        return $this->hasMany(DetallePedido::class);
    }
}
