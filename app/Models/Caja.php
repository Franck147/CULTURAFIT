<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = [
        'usuario_id',
        'monto_inicial',
        'monto_final',
        'total_ventas',
        'total_gastos',
        'diferencia',
        'fecha_apertura',
        'fecha_cierre',
        'estado',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'monto_inicial' => 'decimal:2',
        'monto_final' => 'decimal:2',
        'total_ventas' => 'decimal:2',
        'total_gastos' => 'decimal:2',
        'diferencia' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'caja_id');
    }

    public function gastos()
    {
        return $this->hasMany(Gasto::class, 'caja_id');
    }

    public static function obtenerCajaAbierta($usuarioId)
    {
        return static::where('usuario_id', $usuarioId)
            ->where('estado', 'abierta')
            ->first();
    }

    public function calcularTotalesServidor(): array
    {
        // 1. Sumar ventas por método de pago de esta caja
        $ventasTotales = $this->ventas()->sum('total');
        $ventasEfectivo = $this->ventas()->whereRaw("LOWER(metodo_pago) = 'efectivo'")->sum('total');
        $ventasTarjeta = $this->ventas()->whereRaw("LOWER(metodo_pago) = 'tarjeta'")->sum('total');
        $ventasTransferencia = $this->ventas()->whereRaw("LOWER(metodo_pago) = 'transferencia'")->sum('total');
        $ventasYapePlin = $this->ventas()->whereRaw("LOWER(metodo_pago) IN ('yape_plin', 'yape', 'plin')")->sum('total');

        // 2. Sumar gastos no anulados vinculados a esta caja
        $totalGastos = $this->gastos()->where('estado', '!=', 'anulado')->sum('monto');

        // 3. Saldo esperado en cajón físico: Monto Inicial + Ventas Efectivo - Gastos
        $saldoEsperado = (float)$this->monto_inicial + (float)$ventasEfectivo - (float)$totalGastos;

        return [
            'monto_inicial' => (float)$this->monto_inicial,
            'total_ventas' => (float)$ventasTotales,
            'ventas_efectivo' => (float)$ventasEfectivo,
            'ventas_tarjeta' => (float)$ventasTarjeta,
            'ventas_transferencia' => (float)$ventasTransferencia,
            'ventas_yape_plin' => (float)$ventasYapePlin,
            'total_gastos' => (float)$totalGastos,
            'gastos' => (float)$totalGastos,
            'saldo_esperado' => (float)$saldoEsperado,
            'esperado_efectivo' => (float)$saldoEsperado,
        ];
    }
}
