<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rutinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('socio_id')->constrained('socios')->onDelete('cascade');
            $table->text('dia1')->nullable();
            $table->text('dia2')->nullable();
            $table->text('dia3')->nullable();
            $table->text('dia4')->nullable();
            $table->text('dia5')->nullable();
            $table->text('dia6')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_asignacion')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutinas');
    }
};
