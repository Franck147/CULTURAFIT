<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracion';

    protected $fillable = [
        'nombre_sistema',
        'ruc',
        'direccion',
        'telefono',
        'email',
        'logo',
        'moneda',
    ];

    public static function getInfo()
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'nombre_sistema' => 'CULTURA FIT',
                'ruc' => '20600000001',
                'direccion' => 'Av. Principal 123, Lima',
                'telefono' => '987654321',
                'email' => 'admin@gym.com',
                'logo' => 'logo_empresa.png',
                'moneda' => 'S/',
            ]
        );
    }
}
