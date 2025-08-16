<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Carrito extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tipo_entrega',
        'sucursal_id',
        'direccion_usuario_id',
    ];

    protected $casts = [
        'sucursal_id' => 'integer',
        'direccion_usuario_id' => 'integer',
    ];

    // (Opcional) relaciones directas
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function direccionUsuario()
    {
        return $this->belongsTo(DireccionUsuario::class, 'direccion_usuario_id');
    }

    // Relaciones que ya tenías
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'carrito_producto')
            ->withPivot(['cantidad', 'masa_id', 'nota_cliente', 'precio_total'])
            ->with(['tamano', 'sabor'])
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