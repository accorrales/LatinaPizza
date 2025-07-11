<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Carrito extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'carrito_producto')
            ->withPivot(['cantidad', 'masa_id', 'nota_cliente', 'precio_total'])
            ->with('tamano', 'sabor') // para que también venga el tamaño y sabor del producto
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function extras()
    {
        return $this->hasManyThrough(
            Extra::class,
            CarritoProductoExtra::class,
            'carrito_id',
            'id',
            'id',
            'extra_id'
        );
    }
    public function items()
    {
        return $this->hasMany(CarritoItem::class);
    }

}
