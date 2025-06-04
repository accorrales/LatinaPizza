<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'total', 'estado'];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function guardarHistorial($estado)
    {
        $this->historial()->create([
            'estado' => $estado,
        ]);
    }
    public function historial()
    {
        return $this->hasMany(HistorialPedido::class);
    }
}

