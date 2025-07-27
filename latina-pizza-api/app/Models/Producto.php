<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'descripcion', 'precio', 'imagen', 'estado',
        'categoria_id', 'sabor_id', 'tamano_id'
    ];

    public function carritos()
    {
        return $this->belongsToMany(Carrito::class, 'carrito_producto')
                    ->withPivot(['cantidad', 'masa_id', 'nota_cliente', 'precio_total'])
                    ->withTimestamps();
    }

    public function carritoExtras()
    {
        return $this->belongsToMany(Extra::class, 'carrito_producto_extra', 'producto_id', 'extra_id')
                    ->withPivot('carrito_id')
                    ->withTimestamps();
    }

    public function sabor()    { return $this->belongsTo(Sabor::class); }
    public function tamano()   { return $this->belongsTo(Tamano::class); }
    public function categoria(){ return $this->belongsTo(Categoria::class); }
    public function pedidos()  { return $this->belongsToMany(Pedido::class, 'pedido_producto')->withTimestamps(); }
    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }
}

