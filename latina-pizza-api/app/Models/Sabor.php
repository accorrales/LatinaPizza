<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sabor extends Model
{
    protected $table = 'sabores';
    protected $fillable = ['nombre', 'descripcion', 'imagen'];

    public function detallesPedido()
    {
        return $this->hasMany(DetallePedido::class);
    }
    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }
}
