<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guía de Traslado Masivo - {{ $datos->reference ?? '' }}</title>
<style>
    body { 
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
        font-size: 11px; 
        color: #333; 
        line-height: 1.4; 
        margin: 0;
        padding: 0;
    }
    
    .header-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
        margin-bottom: 20px;
    }
    .header-table td {
        border: none;
        vertical-align: middle;
    }
    
    .document-box { 
        border: 2px solid #000; 
        padding: 8px; 
        text-align: center; 
        background: #f8f9fa; 
    }
    .document-box h2 { 
        margin: 0; 
        font-size: 13px; 
        letter-spacing: 1px; 
    }
    
    .section-title { 
        font-weight: bold; 
        background: #e9ecef; 
        padding: 4px 8px; 
        margin-top: 15px; 
        margin-bottom: 5px; 
        text-transform: uppercase; 
        font-size: 10px; 
        border-left: 3px solid #000; 
    }
    
    .info-table, .items-table { 
        width: 100%; 
        border-collapse: collapse; 
        margin-bottom: 10px; 
    }
    .info-table td { 
        padding: 4px; 
        vertical-align: top; 
    }
    .info-table .label { 
        font-weight: bold; 
        width: 18%; 
    }
    
    .items-table th, .items-table td { 
        border: 1px solid #dee2e6; 
        padding: 6px 8px; 
        text-align: left; 
    }
    .items-table th { 
        background-color: #343a40; 
        color: #fff; 
        font-size: 10px; 
        text-transform: uppercase; 
    }
    .text-center { 
        text-align: center; 
    }
    
    .signature-table {
        width: 100%;
        margin-top: 60px;
        border-collapse: collapse;
        border: none;
    }
    .signature-table td {
        width: 50%;
        border: none;
        text-align: center;
    }
    .signature-line {
        width: 70%;
        margin: 0 auto;
        border-top: 1px solid #999;
        padding-top: 5px;
    }

    header, footer {
        display: block;
        width: 100%;
        background: #fff;
    }
    footer {
        margin-top: 50px;
        text-align: center;
        font-size: 9px;
        color: #555;
    }
    .page-number:after { 
        content: "1 (Vista previa)"; 
    }

    @media print, (max-width: 0px) {
        @page { 
            margin: 110px 25px 60px 25px; 
        }
        header { 
            position: fixed; 
            top: -95px; 
            left: 0px; 
            right: 0px; 
            height: 85px; 
            border-bottom: 1px solid #333;
        }
        footer { 
            position: fixed; 
            bottom: -40px; 
            left: 0px; 
            right: 0px; 
            height: 30px; 
            margin-top: 0;
        }
        .page-number:after { 
            content: counter(page); 
        }
    }
</style>
</head>
<body>

    <!-- Cabecera fija para todas las páginas -->
    <header>
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <h3 style="margin: 0; font-size: 16px;">{{ ($datos->user?->company_id == 1) ? 'Paternal' : 'Pentacar' }}</h3>
                    <p style="margin: 2px 0; color: #666; font-size: 10px; line-height: 1.2;">
                        Control de Movimiento Interno de Mercancías<br>
                        Soporte de Inventario Masivo entre Depósitos
                    </p>
                </td>
                <td style="width: 45%;">
                    <div class="document-box">
                        <h2>GUÍA DE TRASLADO</h2>
                        <p style="font-size: 12px; font-weight: bold; margin: 4px 0; color: #dc3545;">
                            N° {{ $datos->reference ?? 'S/N' }}
                        </p>
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <!-- Pie de página fijo con numeración automática -->
    <footer>
        <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 5px;">
        <span>Documento de control interno. Generado por sistema. Página </span><span class="page-number"></span>
    </footer>

    <!-- Datos del traslado -->
    <div class="section-title">Información de la Ruta</div>
    <table class="info-table">
        <tr>
            <td class="label">Depósito Origen:</td>
            <td>{{ $depositoOrigen->nombre ?? '' }}</td>
            <td class="label">Fecha Emisión:</td>
            <td>{{ isset($datos->created_at) ? $datos->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Depósito Destino:</td>
            <td>{{ $depositoDestino->nombre ?? '' }}</td>
            <td class="label">Estado Inicial:</td>
            <td style="text-transform: uppercase; font-weight: bold; color: #0d6efd;">{{ $datos->status ?? 'Pendiente' }}</td>
        </tr>
    </table>

    <!-- Tabla Dinámica con la carga masiva de productos -->
    <div class="section-title">Listado de Artículos Solicitados (Total: {{ isset($datos->TransfersProduct) ? $datos->TransfersProduct->count() : 0 }})</div>
    <table class="items-table">
        <thead>
            <tr>
                   <th style="width: 15%;">Id Producto</th>
                <th style="width: 50%;">Descripción del Artículo / Producto</th>
                <th style="width: 20%;">Nro Interno</th>
                <th style="width: 15%;">Nro Oblea</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($datos->TransfersProduct) && count($datos->TransfersProduct) > 0)
                @foreach($datos->TransfersProduct as $items)
                    <tr>
                         <td>{{ $items->product_id ?? '' }}</td>
                                        <td>{{ $items->inventario->item->item_name  ?? '' }}</td>
										<td>{{ nroInternoAlias($items->inventario->company_id, $items->inventario->tipo_vehiculo, $items->inventario->nro_interno)  }}</td>
										<td>{{ $items->inventario->nro_oblea ?? '' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="text-center" style="color: #999; padding: 15px;">
                        No se encontraron productos asociados a este traslado.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    @if(isset($datos->detalles) && $datos->detalles)
        <div class="section-title">Observaciones de Despacho</div>
        <p style="padding: 0 8px; margin: 5px 0; font-style: italic;">{{ $datos->detalles }}</p>
    @endif

    <!-- Bloque de Firmas -->
    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-line">
                    Firma Responsable Despacho<br>
                    <strong>Depósito Origen</strong>
                </div>
            </td>
            <td>
                <div class="signature-line">
                    Firma Responsable Recepción<br>
                    <strong>Depósito Destino</strong>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
