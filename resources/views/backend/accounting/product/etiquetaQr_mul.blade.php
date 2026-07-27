<!DOCTYPE html>
<html>
<head>
    <title>Imprimir Múltiples QR</title>
</head>
<body>
    {{-- Estilos para la vista previa en el Modal / Panel de Administración --}}
    <style>
        .etiqueta-qr-modal-root {
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f0f0f0;
            gap: 15px;
            font-family: Arial, sans-serif;
        }

        /* Contenedor que agrupa todas las etiquetas en pantalla */
        .contenedor-masivo-vista {
            display: flex;
            flex-direction: column;
            gap: 10px; /* Separación visual solo para la pantalla */
            max-height: 450px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ccc;
            background: #e0e0e0;
        }

        .print-container {
            width: 64mm;
            height: 32mm;
            border: 1px dashed #999;
            box-sizing: border-box;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            background-color: #fff;
            padding: 1.5mm 2.5mm;
            gap: 2.5mm;
            overflow: hidden;
        }

        .print-container .qr-code {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 21mm;
            width: 21mm;
            height: 21mm;
        }

        .print-container .qr-code svg,
        .print-container .qr-code img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .print-container .label-text {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: left;
            overflow: hidden;
        }

        .print-container .label-text p {
            margin: 0 0 1px 0;
            line-height: 1.1;
            font-size: 8.5px;
            color: #000;
            word-break: break-word;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .print-container .label-text p strong {
            font-size: 10px;
        }
    </style>

    <div class="etiqueta-qr-modal-root">
        
        <div class="contenedor-masivo-vista" id="loteParaImprimir">
            
            @foreach($productos as $producto)
                @php
                      $company = ($producto->company_id == 1) ? 'PM-' : 'PC-';
            $interno = $company . $producto->nro_interno;
            $marcaModelo = trim(($producto->marcaModelo->marca->marca ?? '') . " " . ($producto->marcaModelo->modelo->modelo ?? ''));
            
            $deposito = $producto->deposito->nombre ?? 'N/A';			
			$ultimoInvoiceItem = $producto->invoiceItems->last(); // O la primera con ->first();
			$factura = $ultimoInvoiceItem->invoice ?? null;
			$cotizacion = $factura->invoice_number 
                ?? 'N/A';
			
			$vendedor = $factura->vendedor->name 
              ?? 'N/A';
                    $qrDataClean = "{$producto->id}|{$producto->nro_interno}|{$cotizacion}|{$deposito}|{$vendedor}";
                    $qrCodeOutput = QrCode::size(150)->format('svg')->margin(0)->generate($qrDataClean);
                @endphp

                <div class="print-container">
                    <div class="qr-code">
                        {!! $qrCodeOutput !!}
                    </div>
                    <div class="label-text">
                        <p><strong>ID: {{ $producto->id }}</strong> | Cot: <strong>#{{ $cotizacion }}</strong></p>
                <p>{{ $producto->item->item_name ?? 'Sin Nombre' }}</p>
                @if(!empty($marcaModelo))
                    <p>{{ $marcaModelo }}</p>
                @endif
                <p>Int: {{ $interno }}</p>
                <p>Dep: {{ $deposito }}</p>
                <p>Vend: {{ $vendedor }}</p>
                    </div>
                </div>
            @endforeach

        </div>

        <div>
            <button type="button" onclick="imprimirLote()" style="padding: 10px 22px; font-weight: bold; cursor: pointer;">
                🖨️ Imprimir Lote ({{ count($productos) }} etiquetas)
            </button>
        </div>
    </div>

<script>
    function imprimirLote() {
        const contenidoLote = document.getElementById('loteParaImprimir').innerHTML;
        const ventanaImpresion = window.open('', '_blank', 'width=500,height=400');

        ventanaImpresion.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Impresión en Serie</title>
                <style>
                    @page {
                        size: 64mm 32mm;
                        margin: 0;
                    }
                    
                    html, body {
                        width: 64mm;
                        margin: 0;
                        padding: 0;
                        background-color: #fff;
                        -webkit-print-color-adjust: exact;
                    }

                    .print-container {
                        width: 64mm;
                        height: 32mm;
                        box-sizing: border-box;
                        display: flex;
                        flex-direction: row;
                        align-items: center;
                        justify-content: flex-start;
                        padding: 1.5mm 2.5mm;
                        gap: 2.5mm;
                        border: none !important;
                        background-color: #fff;
                        overflow: hidden;
                        
                        /* Fuerza el salto físico de página tras cada etiqueta en la impresora térmica */
                        page-break-after: always;
                        break-after: page;
                    }

                    .print-container .qr-code {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex: 0 0 21mm;
                        width: 21mm;
                        height: 21mm;
                    }

                    .print-container .qr-code svg,
                    .print-container .qr-code img {
                        width: 100%;
                        height: 100%;
                        display: block;
                    }

                    .print-container .label-text {
                        flex: 1;
                        min-width: 0;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        font-family: Arial, sans-serif;
                        overflow: hidden;
                    }

                    .print-container .label-text p {
                        margin: 0 0 1px 0;
                        line-height: 1.1;
                        font-size: 8.5px;
                        color: #000;
                        word-break: break-word;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }

                    .print-container .label-text p strong {
                        font-size: 10px;
                    }
                </style>
            </head>
            <body>
                ${contenidoLote}
            </body>
            </html>
        `);

        ventanaImpresion.document.close();
        
        ventanaImpresion.onload = function() {
            ventanaImpresion.focus();
            ventanaImpresion.print();
            ventanaImpresion.close();
        };
    }
</script>
</body>
</html>