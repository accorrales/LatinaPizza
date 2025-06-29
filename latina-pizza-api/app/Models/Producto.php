<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'imagen',
        'estado',
        'categoria_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedido_producto')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }
    public function carritos()
    {
        return $this->belongsToMany(Carrito::class, 'carrito_producto')
            ->withPivot('cantidad')
            ->withTimestamps();
    }
}

