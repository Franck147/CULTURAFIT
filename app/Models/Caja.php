<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = [
        'usuario_id',
        'sucursal_id',
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

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
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
        $ventasTotales = (float)$this->ventas()->sum('total');
        $totalDescuentos = (float)$this->ventas()->sum('descuento');
        $ventasBrutas = $ventasTotales + $totalDescuentos;

        $ventasEfectivo = (float)$this->ventas()->whereRaw("LOWER(metodo_pago) = 'efectivo'")->sum('total');
        $ventasTarjeta = (float)$this->ventas()->whereRaw("LOWER(metodo_pago) = 'tarjeta'")->sum('total');
        $ventasTransferencia = (float)$this->ventas()->whereRaw("LOWER(metodo_pago) = 'transferencia'")->sum('total');
        $ventasYapePlin = (float)$this->ventas()->whereRaw("LOWER(metodo_pago) IN ('yape_plin', 'yape', 'plin')")->sum('total');

        // 2. Gastos operativos (salidas físicas de efectivo del cajón) vs Descuentos en ventas
        $gastosOperativos = (float)$this->gastos()->where('estado', '!=', 'anulado')->whereNull('venta_id')->sum('monto');
        $gastosDescuentos = (float)$this->gastos()->where('estado', '!=', 'anulado')->whereNotNull('venta_id')->sum('monto');
        $totalGastos = $gastosOperativos + $gastosDescuentos;

        // 3. Saldo esperado en cajón físico: Monto Inicial + Cobrado en Efectivo - Salidas Físicas de Efectivo
        // Al descontar solo salidas físicas, la caja NUNCA se descuadra por descuentos comerciales.
        $saldoEsperado = (float)$this->monto_inicial + (float)$ventasEfectivo - (float)$gastosOperativos;

        return [
            'monto_inicial' => (float)$this->monto_inicial,
            'total_ventas' => (float)$ventasTotales,
            'ventas_brutas' => (float)$ventasBrutas,
            'total_descuentos' => (float)$totalDescuentos,
            'ventas_efectivo' => (float)$ventasEfectivo,
            'ventas_tarjeta' => (float)$ventasTarjeta,
            'ventas_transferencia' => (float)$ventasTransferencia,
            'ventas_yape_plin' => (float)$ventasYapePlin,
            'gastos_operativos' => (float)$gastosOperativos,
            'gastos_descuentos' => (float)$gastosDescuentos,
            'total_gastos' => (float)$totalGastos,
            'gastos' => (float)$gastosOperativos,
            'saldo_esperado' => (float)$saldoEsperado,
            'esperado_efectivo' => (float)$saldoEsperado,
        ];
    }
}
