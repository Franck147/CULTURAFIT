<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'ciudad',
        'es_principal',
        'estado',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'sucursal_id');
    }

    public function cajeros()
    {
        return $this->hasMany(User::class, 'sucursal_id')->where('rol', 'cajero');
    }

    public function cajas()
    {
        return $this->hasMany(Caja::class, 'sucursal_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'sucursal_id');
    }

    public function inventarios()
    {
        return $this->hasMany(InventarioSucursal::class, 'sucursal_id');
    }

    public static function principal()
    {
        return static::where('es_principal', true)->where('estado', 'activo')->first() 
            ?? static::where('estado', 'activo')->first();
    }
}
