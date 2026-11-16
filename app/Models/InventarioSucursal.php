<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioSucursal extends Model
{
    protected $table = 'inventario_sucursales';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'stock',
        'stock_minimo',
    ];

    protected $casts = [
        'stock' => 'integer',
        'stock_minimo' => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
