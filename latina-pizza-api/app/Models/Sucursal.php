<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales'; // 👈 esto es lo importante
    protected $fillable = ['nombre', 'direccion', 'latitud', 'longitud'];

}
