<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->decimal('monto_inicial', 10, 2)->default(0.00);
            $table->decimal('monto_final', 10, 2)->default(0.00);
            $table->decimal('total_ventas', 10, 2)->default(0.00);
            $table->decimal('total_gastos', 10, 2)->default(0.00);
            $table->decimal('diferencia', 10, 2)->default(0.00);
            $table->timestamp('fecha_apertura')->useCurrent();
            $table->timestamp('fecha_cierre')->nullable();
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
