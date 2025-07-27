<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Resena extends Model
{
    use HasFactory;
    
    protected $table = 'resenas';

    protected $fillable = [
        'sabor_id',
        'user_id',
        'calificacion',
        'comentario',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function sabor()
    {
        return $this->belongsTo(Sabor::class);
    }

}
