<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialPedido extends Model
{
    use HasFactory;

    protected $table = 'historial_pedidos';

    protected $fillable = [
        'pedido_id',
        'estado',
        'fecha'
    ];

    public $timestamps = true; // ✅ Esto permite que se llenen automáticamente

    // Relación con pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}

