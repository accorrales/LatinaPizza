<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = ['nombre', 'direccion', 'latitud', 'longitud'];

    public function mesas()
    {
        return $this->hasMany(Mesa::class);
    }

    public function mesaSesiones()
    {
        return $this->hasMany(MesaSesion::class);
    }
}
