<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'codigo',
        'nombre',
        'precio_compra',
        'precio_venta',
        'stock',
        'foto',
        'estado',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    public function inventarios()
    {
        return $this->hasMany(InventarioSucursal::class, 'producto_id');
    }

    /**
     * Retorna el stock disponible en una sucursal específica
     */
    public function stockEnSucursal($sucursalId)
    {
        if (empty($sucursalId)) {
            return (int)$this->stock;
        }

        if ($this->relationLoaded('inventarios')) {
            $inv = $this->inventarios->firstWhere('sucursal_id', $sucursalId);
            return $inv ? (int)$inv->stock : 0;
        }

        $inv = InventarioSucursal::where('producto_id', $this->id)
            ->where('sucursal_id', $sucursalId)
            ->first();

        return $inv ? (int)$inv->stock : 0;
    }

    /**
     * Ajusta el stock en una sucursal y recalcula el stock total
     */
    public function ajustarStockEnSucursal($sucursalId, $cantidad)
    {
        $inv = InventarioSucursal::firstOrCreate(
            ['producto_id' => $this->id, 'sucursal_id' => $sucursalId],
            ['stock' => 0, 'stock_minimo' => 5]
        );

        $inv->stock = max(0, $inv->stock + (int)$cantidad);
        $inv->save();

        $this->sincronizarStockTotal();

        return $inv->stock;
    }

    /**
     * Sincroniza la columna 'stock' de la tabla 'productos' con la suma de todas las sucursales
     */
    public function sincronizarStockTotal()
    {
        $total = (int)InventarioSucursal::where('producto_id', $this->id)->sum('stock');
        $this->update(['stock' => $total]);
        return $total;
    }
}
