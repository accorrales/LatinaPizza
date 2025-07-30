<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DireccionUsuario extends Model
{
    use HasFactory;

    protected $table = 'direcciones_usuario';

    protected $fillable = [
        'user_id',
        'nombre',
        'direccion_exacta',
        'provincia',
        'canton',
        'distrito',
        'telefono_contacto',
        'referencias',
        'latitud',
        'longitud',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
