<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rutina extends Model
{
    protected $table = 'rutinas';

    protected $fillable = [
        'socio_id',
        'dia1',
        'dia2',
        'dia3',
        'dia4',
        'dia5',
        'dia6',
        'observaciones',
        'fecha_asignacion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
    ];

    public function socio()
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }
}
