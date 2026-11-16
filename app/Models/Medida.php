<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medida extends Model
{
    protected $table = 'medidas';

    protected $fillable = [
        'socio_id',
        'peso',
        'grasa',
        'cintura',
        'brazo',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
        'peso' => 'decimal:2',
        'grasa' => 'decimal:2',
        'cintura' => 'decimal:2',
        'brazo' => 'decimal:2',
    ];

    public function socio()
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }
}
