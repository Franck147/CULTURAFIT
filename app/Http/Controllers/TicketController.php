<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Venta;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    public function ver($id)
    {
        $config = Configuracion::getInfo();
        $venta = Venta::with(['detalles.producto', 'socio', 'caja.usuario', 'sucursal'])->findOrFail($id);

        $qrDataUri = $this->generarQr(route('ticket.ver', $venta->id));
        $whatsappUrl = $this->armarEnlaceWhatsapp($venta, $config);
        $whatsappTieneNumero = !str_contains($whatsappUrl, 'wa.me/?');

        return view('pos.ticket', compact('config', 'venta', 'qrDataUri', 'whatsappUrl', 'whatsappTieneNumero'));
    }

    /**
     * QR en SVG (nítido en impresora térmica) que abre esta venta en el sistema.
     * Si falla la generación, el ticket se imprime igual sin QR.
     */
    private function generarQr(string $contenido): ?string
    {
        try {
            $writer = new Writer(new ImageRenderer(new RendererStyle(220, 1), new SvgImageBackEnd()));

            return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($contenido));
        } catch (\Throwable $e) {
            Log::warning('No se pudo generar el QR del ticket: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Enlace "click to chat" de WhatsApp con el resumen de la compra ya escrito.
     * Con teléfono del cliente abre su chat; sin teléfono deja elegir el contacto.
     */
    private function armarEnlaceWhatsapp(Venta $venta, $config): string
    {
        $moneda = $config->moneda ?? 'S/';
        $tienda = $config->nombre_sistema ?? 'CULTURA FIT';
        $numero = str_pad($venta->id, 6, '0', STR_PAD_LEFT);

        $lineas = [];
        $lineas[] = $venta->socio
            ? "Hola {$venta->socio->nombre}, gracias por tu compra en {$tienda}."
            : "Gracias por tu compra en {$tienda}.";
        $lineas[] = '';
        $lineas[] = "*Nota de venta N° {$numero}*";
        $lineas[] = 'Fecha: ' . $venta->fecha->format('d/m/Y H:i');
        if ($venta->sucursal) {
            $lineas[] = 'Tienda: ' . $venta->sucursal->nombre;
        }
        $lineas[] = '';

        foreach ($venta->detalles as $d) {
            $nombre = $d->producto->nombre ?? 'Producto';
            $lineas[] = "- {$d->cantidad} x {$nombre}: {$moneda} " . number_format($d->subtotal, 2);
        }

        $lineas[] = '';
        if ($venta->descuento > 0) {
            $lineas[] = "Descuento: - {$moneda} " . number_format($venta->descuento, 2);
        }
        $lineas[] = "*Total: {$moneda} " . number_format($venta->total, 2) . '*';
        $lineas[] = 'Forma de pago: ' . $venta->metodo_pago_nombre;

        $texto = rawurlencode(implode("\n", $lineas));
        $telefono = $this->normalizarTelefono($venta->socio->telefono ?? null);

        return $telefono
            ? "https://wa.me/{$telefono}?text={$texto}"
            : "https://wa.me/?text={$texto}";
    }

    /**
     * Deja el teléfono en formato internacional sin símbolos. Un celular peruano
     * de 9 dígitos recibe el prefijo 51; cualquier otro formato dudoso se descarta.
     */
    private function normalizarTelefono(?string $telefono): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $telefono);

        if (strlen($digitos) === 9 && $digitos[0] === '9') {
            return '51' . $digitos;
        }

        if (strlen($digitos) === 11 && str_starts_with($digitos, '519')) {
            return $digitos;
        }

        return null;
    }
}
