<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GastosController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProgresoController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\SociosController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UsuariosController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - CULTURA FIT
|--------------------------------------------------------------------------
*/

// Rutas Públicas de Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas Protegidas por Autenticación
Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- MÓDULO CLIENTES ---
    Route::prefix('socios')->name('socios.')->group(function () {
        Route::get('/', [SociosController::class, 'index'])->name('index');
        Route::get('/ventas/{socioId}', [SociosController::class, 'ventas'])->name('ventas');
        
        // Operaciones de gestión (Admin y Cajero)
        Route::middleware(['rol:admin,cajero'])->group(function () {
            Route::get('/crear', [SociosController::class, 'crear'])->name('crear');
            Route::post('/guardar', [SociosController::class, 'guardar'])->name('guardar');
            Route::get('/editar/{id}', [SociosController::class, 'editar'])->name('editar');
            Route::post('/actualizar/{id}', [SociosController::class, 'actualizar'])->name('actualizar');
            Route::post('/cambiar-estado/{id}', [SociosController::class, 'cambiarEstado'])->name('cambiar-estado');
        });
    });

    // --- MÓDULO HISTORIAL DE VENTAS (COMPATIBILIDAD CON RUTA ANTERIOR) ---
    Route::prefix('progreso')->name('progreso.')->group(function () {
        Route::get('/ver/{socioId}', [SociosController::class, 'ventas'])->name('ver');
        Route::post('/guardar-medida', [ProgresoController::class, 'guardarMedida'])->name('guardar-medida');
        Route::post('/guardar-rutina', [ProgresoController::class, 'guardarRutina'])->name('guardar-rutina');
        Route::post('/eliminar-medida/{id}', [ProgresoController::class, 'eliminarMedida'])->name('eliminar-medida');
    });

    // --- MÓDULOS DE CAJA Y PUNTO DE VENTA (Admin y Cajero) ---
    Route::middleware(['rol:admin,cajero'])->group(function () {
        // Caja
        Route::prefix('caja')->name('caja.')->group(function () {
            Route::get('/', [CajaController::class, 'index'])->name('index');
            Route::post('/abrir', [CajaController::class, 'abrir'])->name('abrir');
            Route::post('/cerrar', [CajaController::class, 'cerrar'])->name('cerrar');
        });

        // POS
        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('/', [PosController::class, 'index'])->name('index');
            Route::post('/procesar', [PosController::class, 'procesarVenta'])->name('procesar');
            Route::get('/historial', [PosController::class, 'historial'])->name('historial');
            Route::get('/detalle/{id}', [PosController::class, 'detalleVenta'])->name('detalle');
            Route::post('/consultar-dni', [PosController::class, 'consultarDni'])->name('consultar-dni');
            Route::post('/crear-cliente-rapido', [PosController::class, 'crearClienteRapido'])->name('crear-cliente-rapido');
        });

        // Gastos
        Route::prefix('gastos')->name('gastos.')->group(function () {
            Route::get('/', [GastosController::class, 'index'])->name('index');
            Route::get('/crear', [GastosController::class, 'crear'])->name('crear');
            Route::post('/guardar', [GastosController::class, 'guardar'])->name('guardar');
            Route::post('/anular', [GastosController::class, 'anular'])->name('anular');
        });

        // Ticket
        Route::get('/ticket/ver/{id}', [TicketController::class, 'ver'])->name('ticket.ver');
    });

    // --- MÓDULOS EXCLUSIVOS DE ADMINISTRADOR ---
    Route::middleware(['rol:admin'])->group(function () {
        // Inventario y Productos
        Route::prefix('inventario')->name('inventario.')->group(function () {
            Route::get('/productos', [InventarioController::class, 'productos'])->name('productos');
            Route::post('/productos/guardar', [InventarioController::class, 'guardarProducto'])->name('productos.guardar');
            Route::post('/productos/cambiar-estado/{id}', [InventarioController::class, 'cambiarEstadoProducto'])->name('productos.cambiar-estado');
            Route::post('/productos/ajuste-stock', [InventarioController::class, 'ajusteStock'])->name('productos.ajuste-stock');

            Route::get('/categorias', [InventarioController::class, 'categorias'])->name('categorias');
            Route::post('/categorias/guardar', [InventarioController::class, 'guardarCategoria'])->name('categorias.guardar');
            Route::post('/categorias/cambiar-estado/{id}', [InventarioController::class, 'cambiarEstadoCategoria'])->name('categorias.cambiar-estado');
        });

        // Reportes
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/', [ReportesController::class, 'index'])->name('index');
            Route::get('/exportar-excel', [ReportesController::class, 'exportarExcel'])->name('exportar-excel');
        });

        // Usuarios
        Route::prefix('usuarios')->name('usuarios.')->group(function () {
            Route::get('/', [UsuariosController::class, 'index'])->name('index');
            Route::get('/crear', [UsuariosController::class, 'crear'])->name('crear');
            Route::post('/guardar', [UsuariosController::class, 'guardar'])->name('guardar');
            Route::get('/editar/{id}', [UsuariosController::class, 'editar'])->name('editar');
            Route::post('/actualizar/{id}', [UsuariosController::class, 'actualizar'])->name('actualizar');
            Route::post('/cambiar-estado/{id}', [UsuariosController::class, 'cambiarEstado'])->name('cambiar-estado');
        });

        // Configuración de la Empresa
        Route::prefix('configuracion')->name('configuracion.')->group(function () {
            Route::get('/', [ConfiguracionController::class, 'index'])->name('index');
            Route::post('/actualizar', [ConfiguracionController::class, 'actualizar'])->name('actualizar');
        });

        // Mantenimiento & Copias de Seguridad de BD
        Route::prefix('mantenimiento')->name('mantenimiento.')->group(function () {
            Route::get('/', [MantenimientoController::class, 'index'])->name('index');
            Route::post('/crear-backup', [MantenimientoController::class, 'crearBackup'])->name('crear-backup');
            Route::get('/descargar-backup/{archivo}', [MantenimientoController::class, 'descargarBackup'])->name('descargar-backup');
            Route::post('/eliminar-backup', [MantenimientoController::class, 'eliminarBackup'])->name('eliminar-backup');
        });
    });
});
