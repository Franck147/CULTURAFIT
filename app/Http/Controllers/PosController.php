<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\DetalleVenta;
use App\Models\Gasto;
use App\Models\InventarioSucursal;
use App\Models\Producto;
use App\Models\Socio;
use App\Models\Sucursal;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PosController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cajaAbierta = Caja::obtenerCajaAbierta($user->id);

        if (!$cajaAbierta) {
            return redirect()->route('caja.index')->with('warning', 'Debes abrir caja antes de realizar ventas.');
        }

        $cajaAbierta->load('sucursal');

        $config = Configuracion::getInfo();
        $sucursalId = $cajaAbierta->sucursal_id ?: optional(Sucursal::principal())->id;

        $productos = Producto::where('estado', 'activo')
            ->with(['categoria', 'inventarios'])
            ->orderBy('nombre', 'asc')
            ->get();

        // Asignar a cada producto el stock disponible en la sucursal activa de esta caja
        foreach ($productos as $p) {
            $p->stock = $p->stockEnSucursal($sucursalId);
        }

        $categorias = Categoria::where('estado', 'activo')->orderBy('nombre', 'asc')->get();
        $socios = Socio::where('estado', 'activo')->orderBy('nombre', 'asc')->get();

        return view('pos.index', compact('config', 'cajaAbierta', 'productos', 'categorias', 'socios'));
    }

    public function procesarVenta(Request $request)
    {
        $request->validate([
            'carrito' => ['required', 'string'],
            'metodo_pago' => ['required', 'in:efectivo,tarjeta,transferencia,yape_plin,yape,plin'],
            'socio_id' => ['nullable', 'exists:socios,id'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = Auth::user();
        $cajaAbierta = Caja::obtenerCajaAbierta($user->id);

        if (!$cajaAbierta) {
            return response()->json(['status' => 'error', 'message' => 'No hay una caja abierta.'], 422);
        }

        $carrito = json_decode($request->carrito, true);

        if (empty($carrito)) {
            return response()->json(['status' => 'error', 'message' => 'El carrito está vacío.'], 422);
        }

        try {
            $ventaId = DB::transaction(function () use ($carrito, $request, $cajaAbierta, $user) {
                $subtotalBruto = 0;
                $itemsAProcesar = [];

                $sucursalId = $cajaAbierta->sucursal_id ?: ($user->sucursal_id ?: optional(Sucursal::principal())->id);
                $sucursal = Sucursal::find($sucursalId);
                $sucursalNombre = $sucursal ? $sucursal->nombre : 'la sucursal actual';

                // 1. Verificar stock en la sucursal y calcular precios en el servidor
                foreach ($carrito as $item) {
                    $producto = Producto::lockForUpdate()->find($item['id']);

                    if (!$producto) {
                        throw new \Exception("El producto ID {$item['id']} ya no existe.");
                    }

                    $cantidad = max(1, (int)$item['cantidad']);

                    $inv = InventarioSucursal::where('producto_id', $producto->id)
                        ->where('sucursal_id', $sucursalId)
                        ->lockForUpdate()
                        ->first();

                    $stockDisponible = $inv ? (int)$inv->stock : 0;

                    if ($stockDisponible < $cantidad) {
                        throw new \Exception("Stock insuficiente en {$sucursalNombre} para: {$producto->nombre} (Disponible: {$stockDisponible})");
                    }

                    $precioUnit = (float)$producto->precio_venta;
                    $lineaSubtotal = $precioUnit * $cantidad;
                    $subtotalBruto += $lineaSubtotal;

                    $itemsAProcesar[] = [
                        'producto' => $producto,
                        'inventario' => $inv,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnit,
                        'subtotal' => $lineaSubtotal,
                    ];
                }

                $descuento = min($subtotalBruto, max(0, (float)$request->descuento));
                $totalFinal = max(0, $subtotalBruto - $descuento);

                // 2. Crear Venta
                $venta = Venta::create([
                    'caja_id' => $cajaAbierta->id,
                    'sucursal_id' => $sucursalId,
                    'socio_id' => $request->socio_id ?: null,
                    'total' => $totalFinal,
                    'descuento' => $descuento,
                    'metodo_pago' => $request->metodo_pago,
                    'fecha' => Carbon::now(),
                ]);

                // 3. Crear Detalle y Descontar Stock de la sucursal
                foreach ($itemsAProcesar as $it) {
                    DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $it['producto']->id,
                        'cantidad' => $it['cantidad'],
                        'precio_unitario' => $it['precio_unitario'],
                        'subtotal' => $it['subtotal'],
                    ]);

                    if ($it['inventario']) {
                        $it['inventario']->decrement('stock', $it['cantidad']);
                    }
                    $it['producto']->sincronizarStockTotal();
                }

                // 4. Actualizar total_ventas en la caja activa
                $cajaAbierta->increment('total_ventas', $totalFinal);

                // 5. Si hubo descuento comercial, registrarlo en Gastos apuntando al ticket
                if ($descuento > 0) {
                    $nombreCliente = 'Venta Directa';
                    if ($request->socio_id) {
                        $socioObj = Socio::find($request->socio_id);
                        if ($socioObj) {
                            $nombreCliente = $socioObj->nombre . ' (DNI: ' . $socioObj->dni . ')';
                        }
                    }
                    $ticketPad = str_pad($venta->id, 5, '0', STR_PAD_LEFT);
                    Gasto::create([
                        'caja_id' => $cajaAbierta->id,
                        'usuario_id' => Auth::id() ?? ($user ? $user->id : null),
                        'venta_id' => $venta->id,
                        'tipo' => 'descuento_venta',
                        'descripcion' => "Descuento comercial en Ticket #{$ticketPad} (Venta #{$ticketPad}) - Cliente: {$nombreCliente}",
                        'monto' => $descuento,
                        'fecha' => Carbon::now()->format('Y-m-d'),
                        'estado' => 'creado',
                    ]);
                }

                return $venta->id;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Venta registrada con éxito',
                'venta_id' => $ventaId,
                'redirect_url' => route('ticket.ver', $ventaId),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function historial(Request $request)
    {
        $config = Configuracion::getInfo();

        $desde = $request->input('desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->input('hasta', Carbon::now()->format('Y-m-d'));
        $busqueda = trim($request->input('busqueda', ''));
        $sucursalId = $request->input('sucursal_id', session('sucursal_activa_id'));

        $query = Venta::with(['caja.usuario', 'caja.sucursal', 'socio', 'sucursal'])
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta);

        if (!empty($sucursalId) && $sucursalId !== 'todas') {
            $query->where('sucursal_id', $sucursalId);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('socio', function ($sq) use ($busqueda) {
                    $sq->where('nombre', 'ilike', "%{$busqueda}%");
                })->orWhereHas('caja.usuario', function ($uq) use ($busqueda) {
                    $uq->where('nombre', 'ilike', "%{$busqueda}%");
                });
            });
        }

        $ventas = $query->orderBy('fecha', 'desc')->paginate(25);

        $totalesQuery = Venta::whereDate('fecha', '>=', $desde)->whereDate('fecha', '<=', $hasta);
        if (!empty($sucursalId) && $sucursalId !== 'todas') {
            $totalesQuery->where('sucursal_id', $sucursalId);
        }

        $totalRecaudado = (float)$totalesQuery->sum('total');
        $cantVentas = $totalesQuery->count();
        $ticketPromedio = $cantVentas > 0 ? $totalRecaudado / $cantVentas : 0;

        $sucursales = Sucursal::where('estado', 'activo')->orderBy('nombre', 'asc')->get();

        return view('pos.historial', compact(
            'config',
            'ventas',
            'desde',
            'hasta',
            'busqueda',
            'sucursalId',
            'sucursales',
            'totalRecaudado',
            'cantVentas',
            'ticketPromedio'
        ));
    }

    public function detalleVenta($id)
    {
        $venta = Venta::with(['detalles.producto', 'socio', 'caja.usuario', 'sucursal'])->findOrFail($id);
        return response()->json($venta);
    }

    /**
     * Consulta un DNI en la base de datos local y opcionalmente en el API de RENIEC.
     */
    public function consultarDni(Request $request)
    {
        $dni = trim($request->input('dni', ''));

        if (empty($dni)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Por favor, ingrese un número de DNI o documento.'
            ], 422);
        }

        // 1. Verificar si ya existe en la base de datos local (Socios)
        $socioExistente = Socio::where('dni', $dni)->first();
        if ($socioExistente) {
            return response()->json([
                'status' => 'exists',
                'message' => 'El cliente ya se encuentra registrado en el sistema.',
                'socio' => [
                    'id' => $socioExistente->id,
                    'nombre' => $socioExistente->nombre,
                    'dni' => $socioExistente->dni,
                    'telefono' => $socioExistente->telefono,
                    'email' => $socioExistente->email,
                ]
            ]);
        }

        // 2. Si hay un API Token configurado para RENIEC (ej. apiperu.dev / apisperu)
        $reniecToken = env('RENIEC_API_TOKEN', env('APIS_PERU_TOKEN', null));
        $reniecUrl = env('RENIEC_API_URL', null);

        if ($reniecToken && strlen($dni) === 8 && ctype_digit($dni)) {
            try {
                $url = $reniecUrl ?: "https://apiperu.dev/api/dni/{$dni}";

                $response = Http::timeout(3)
                    ->withToken($reniecToken)
                    ->get($url);

                if ($response->successful()) {
                    $json = $response->json();
                    $nombreCompleto = null;

                    if (!empty($json['data']['nombre_completo'])) {
                        $nombreCompleto = $json['data']['nombre_completo'];
                    } elseif (!empty($json['nombre_completo'])) {
                        $nombreCompleto = $json['nombre_completo'];
                    } elseif (!empty($json['nombres'])) {
                        $nombreCompleto = trim(($json['nombres'] ?? '') . ' ' . ($json['apellido_paterno'] ?? ($json['apellidoPaterno'] ?? '')) . ' ' . ($json['apellido_materno'] ?? ($json['apellidoMaterno'] ?? '')));
                    } elseif (!empty($json['data']['nombres'])) {
                        $d = $json['data'];
                        $nombreCompleto = trim(($d['nombres'] ?? '') . ' ' . ($d['apellido_paterno'] ?? ($d['apellidoPaterno'] ?? '')) . ' ' . ($d['apellido_materno'] ?? ($d['apellidoMaterno'] ?? '')));
                    }

                    if ($nombreCompleto) {
                        return response()->json([
                            'status' => 'found',
                            'source' => 'reniec',
                            'message' => 'Datos encontrados con éxito en RENIEC.',
                            'nombre' => $nombreCompleto,
                            'dni' => $dni
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Error consultando API RENIEC: ' . $e->getMessage());
            }
        }

        // 3. Fallback: No existe en BD local y no se obtuvo de la API (o no está configurada)
        return response()->json([
            'status' => 'manual',
            'message' => 'DNI no registrado previamente. Puedes completar los datos manualmente.',
            'dni' => $dni
        ]);
    }

    /**
     * Registro rápido de cliente desde la pantalla de POS.
     */
    public function crearClienteRapido(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
        ], [
            'dni.required' => 'El DNI o documento es obligatorio.',
            'nombre.required' => 'El nombre completo del cliente es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $dni = trim($request->input('dni'));
        $nombre = trim($request->input('nombre'));
        $telefono = trim($request->input('telefono', '')) ?: null;
        $email = trim($request->input('email', '')) ?: null;

        // Si ya existe por DNI, devolver el existente para seleccionarlo
        $existente = Socio::where('dni', $dni)->first();
        if ($existente) {
            return response()->json([
                'status' => 'exists',
                'message' => 'Este cliente ya existía en la base de datos.',
                'socio' => [
                    'id' => $existente->id,
                    'nombre' => $existente->nombre,
                    'dni' => $existente->dni,
                    'telefono' => $existente->telefono,
                    'email' => $existente->email,
                ]
            ]);
        }

        try {
            $socio = Socio::create([
                'nombre' => $nombre,
                'dni' => $dni,
                'telefono' => $telefono,
                'email' => $email,
                'estado' => 'activo',
                'fecha_registro' => Carbon::now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente registrado exitosamente.',
                'socio' => [
                    'id' => $socio->id,
                    'nombre' => $socio->nombre,
                    'dni' => $socio->dni,
                    'telefono' => $socio->telefono,
                    'email' => $socio->email,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo guardar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }
}
