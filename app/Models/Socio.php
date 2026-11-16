<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Socio extends Model
{
    protected $table = 'socios';

    protected $fillable = [
        'nombre',
        'dni',
        'email',
        'telefono',
        'whatsapp_api_key',
        'fecha_registro',
        'estado',
        'foto',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    public function medidas()
    {
        return $this->hasMany(Medida::class, 'socio_id')->orderBy('fecha', 'asc');
    }

    public function rutinas()
    {
        return $this->hasMany(Rutina::class, 'socio_id')->orderBy('id', 'desc');
    }

    public function ultimaRutina()
    {
        return $this->hasOne(Rutina::class, 'socio_id')->latestOfMany();
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'socio_id');
    }
}
