<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ str_pad($venta->id, 5, '0', STR_PAD_LEFT) }} - {{ $config->nombre_sistema ?? 'CULTURA FIT' }}</title>

    <style id="dynamicPageStyle">
        @page {
            size: 80mm auto;
            margin: 0;
        }
    </style>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
        }
        body {
            background-color: #F1F5F9;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 10px;
        }
        .ticket-wrapper {
            width: 78mm;
            max-width: 78mm;
            background: #ffffff;
            padding: 12px 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-size: 11px;
            line-height: 1.35;
            color: #000000;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000000;
            margin: 8px 0;
        }
        .table-ticket {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .table-ticket th {
            border-bottom: 1px dashed #000000;
            padding-bottom: 4px;
        }
        .table-ticket td {
            padding: 3px 0;
            vertical-align: top;
        }
        .no-print-bar {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 20px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .btn-print { background: #6366F1; color: white; }
        .btn-pdf { background: #059669; color: white; }
        .btn-size { background: #475569; color: white; }
        .btn-close { background: #E2E8F0; color: #334155; }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            html, body {
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                background: #ffffff !important;
            }
            .ticket-wrapper {
                width: 78mm !important;
                max-width: 78mm !important;
                min-width: 78mm !important;
                margin: 0 auto !important;
                padding: 4mm 3mm !important;
                box-shadow: none !important;
                border: none !important;
            }
            .no-print-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <button onclick="window.print()" class="btn btn-print">🖨️ Imprimir Ticket (80mm)</button>
    <button onclick="guardarComoPdfEtiquetera()" class="btn btn-pdf" id="btnGuardarPdf">📄 Guardar como PDF (Etiquetera)</button>
    <button onclick="cambiarAnchoTicket()" class="btn btn-size" id="btnCambiarAncho">📏 80mm</button>
    <button onclick="window.close()" class="btn btn-close">Cerrar</button>
</div>

<div class="ticket-wrapper" id="ticketPrintArea">
    <!-- Encabezado Tienda -->
    <div class="text-center">
        <h2 style="font-size: 15px; text-transform: uppercase;">{{ $config->nombre_sistema ?? 'CULTURA FIT' }}</h2>
        @if($venta->sucursal)
            <div style="font-weight: bold; font-size: 11px; margin-top: 1px;">{{ strtoupper($venta->sucursal->nombre) }}</div>
        @endif
        @if(!empty($config->ruc))
            <div>RUC: {{ $config->ruc }}</div>
        @endif
        @php
            $direccionTicket = $venta->sucursal && !empty($venta->sucursal->direccion) ? $venta->sucursal->direccion : ($config->direccion ?? '');
            $telefonoTicket = $venta->sucursal && !empty($venta->sucursal->telefono) ? $venta->sucursal->telefono : ($config->telefono ?? '');
        @endphp
        @if(!empty($direccionTicket))
            <div>{{ $direccionTicket }}</div>
        @endif
        @if(!empty($telefonoTicket))
            <div>Tel: {{ $telefonoTicket }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <!-- Metadatos de la Venta -->
    <div>
        <div><strong>TICKET:</strong> #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</div>
        <div><strong>FECHA:</strong> {{ $venta->fecha->format('d/m/Y H:i:s') }}</div>
        @if($venta->sucursal)
            <div><strong>SUCURSAL:</strong> {{ strtoupper($venta->sucursal->nombre) }}</div>
        @endif
        <div><strong>CAJERO:</strong> {{ strtoupper($venta->caja->usuario->nombre ?? 'CAJA GENERAL') }}</div>
        <div><strong>CLIENTE:</strong> {{ strtoupper($venta->socio->nombre ?? 'VENTA DIRECTA') }}</div>
        @if($venta->socio && $venta->socio->dni)
            <div><strong>DNI/DOC:</strong> {{ $venta->socio->dni }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <!-- Artículos -->
    <table class="table-ticket">
        <thead>
            <tr>
                <th style="text-align: left; width: 50%;">DESCRIPCIÓN</th>
                <th style="text-align: center; width: 15%;">CANT</th>
                <th style="text-align: right; width: 35%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $d)
                <tr>
                    <td>
                        {{ $d->producto->nombre ?? 'Producto' }}
                        <div style="font-size: 9px; color: #555;">{{ $config->moneda ?? 'S/' }} {{ number_format($d->precio_unitario, 2) }} c/u</div>
                    </td>
                    <td class="text-center">{{ $d->cantidad }}</td>
                    <td class="text-end">{{ $config->moneda ?? 'S/' }} {{ number_format($d->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- Totales -->
    <div>
        <table style="width: 100%;">
            @if($venta->descuento > 0)
                <tr>
                    <td>SUBTOTAL:</td>
                    <td class="text-end">{{ $config->moneda ?? 'S/' }} {{ number_format($venta->total + $venta->descuento, 2) }}</td>
                </tr>
                <tr>
                    <td>DESCUENTO:</td>
                    <td class="text-end">- {{ $config->moneda ?? 'S/' }} {{ number_format($venta->descuento, 2) }}</td>
                </tr>
            @endif
            <tr style="font-size: 13px; font-weight: bold;">
                <td>TOTAL:</td>
                <td class="text-end">{{ $config->moneda ?? 'S/' }} {{ number_format($venta->total, 2) }}</td>
            </tr>
            <tr>
                <td>FORMA DE PAGO:</td>
                <td class="text-end fw-bold">
                    @if(in_array(strtolower((string)$venta->metodo_pago), ['yape_plin', 'yape', 'plin']))
                        YAPE / PLIN
                    @else
                        {{ strtoupper($venta->metodo_pago) }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <!-- Mensaje de Despedida -->
    <div class="text-center" style="font-size: 10px; margin-top: 6px;">
        <p style="font-weight: bold;">¡Gracias por tu compra en Cultura Fit!</p>
        <p>Nutrición Deportiva & Suplementos</p>
        <p>Conserve este comprobante.</p>
    </div>
</div>

<script src="{{ asset('html2pdf.bundle.min.js') }}"></script>
<script>
    let anchoActual = 80;

    function cambiarAnchoTicket() {
        const wrapper = document.querySelector('.ticket-wrapper');
        const btn = document.getElementById('btnCambiarAncho');
        const dynamicStyle = document.getElementById('dynamicPageStyle');
        
        if (anchoActual === 80) {
            anchoActual = 58;
            wrapper.style.width = '56mm';
            wrapper.style.maxWidth = '56mm';
            btn.innerHTML = '📏 58mm';
            if (dynamicStyle) {
                dynamicStyle.innerHTML = '@page { size: 58mm auto; margin: 0; } body { width: 58mm !important; } .ticket-wrapper { width: 56mm !important; max-width: 56mm !important; }';
            }
        } else {
            anchoActual = 80;
            wrapper.style.width = '78mm';
            wrapper.style.maxWidth = '78mm';
            btn.innerHTML = '📏 80mm';
            if (dynamicStyle) {
                dynamicStyle.innerHTML = '@page { size: 80mm auto; margin: 0; } body { width: 80mm !important; } .ticket-wrapper { width: 78mm !important; max-width: 78mm !important; }';
            }
        }
    }

    function guardarComoPdfEtiquetera() {
        const btn = document.getElementById('btnGuardarPdf');
        const originalText = btn.innerHTML;
        btn.innerHTML = '⏳ Generando PDF...';
        btn.disabled = true;

        const elemento = document.getElementById('ticketPrintArea');
        
        // Calcular altura real del ticket en mm (1px ~ 0.264583mm) + margen inferior
        const anchoMm = anchoActual;
        const altoMm = Math.ceil(elemento.scrollHeight * 0.264583) + 8;

        const opt = {
            margin: [2, 1, 2, 1],
            filename: 'Ticket_{{ str_pad($venta->id, 6, "0", STR_PAD_LEFT) }}_' + anchoMm + 'mm.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 3, 
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false
            },
            jsPDF: { 
                unit: 'mm', 
                format: [anchoMm, altoMm], 
                orientation: 'portrait' 
            }
        };

        if (typeof html2pdf !== 'undefined') {
            html2pdf().set(opt).from(elemento).save().then(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(err => {
                console.error(err);
                btn.innerHTML = originalText;
                btn.disabled = false;
                window.print();
            });
        } else {
            btn.innerHTML = originalText;
            btn.disabled = false;
            window.print();
        }
    }
</script>
</body>
</html>
