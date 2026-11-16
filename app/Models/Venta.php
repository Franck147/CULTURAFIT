<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'caja_id',
        'socio_id',
        'total',
        'descuento',
        'metodo_pago',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'decimal:2',
        'descuento' => 'decimal:2',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function socio()
    {
        return $this->belongsTo(Socio::class, 'socio_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    /**
     * Nombre legible y formateado del método de pago
     */
    public function getMetodoPagoNombreAttribute(): string
    {
        return match(strtolower((string)$this->metodo_pago)) {
            'efectivo' => 'Efectivo',
            'tarjeta' => 'Tarjeta',
            'transferencia' => 'Transferencia',
            'yape_plin', 'yape', 'plin' => 'Yape / Plin',
            default => ucfirst((string)$this->metodo_pago)
        };
    }

    /**
     * Icono FontAwesome y color representativo del método de pago
     */
    public function getMetodoPagoIconoAttribute(): string
    {
        return match(strtolower((string)$this->metodo_pago)) {
            'efectivo' => 'fas fa-money-bill-wave text-success',
            'tarjeta' => 'fas fa-credit-card text-primary',
            'transferencia' => 'fas fa-university text-info',
            'yape_plin', 'yape', 'plin' => 'fas fa-qrcode text-purple',
            default => 'fas fa-receipt text-secondary'
        };
    }
}
