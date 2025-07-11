<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Carrito;
use App\Models\Producto;
use App\Models\Extra;
class CarritoProductoExtra extends Model
{
    protected $table = 'carrito_producto_extra';

    protected $fillable = ['carrito_id', 'producto_id', 'extra_id'];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function extra()
    {
        return $this->belongsTo(Extra::class);
    }
}
