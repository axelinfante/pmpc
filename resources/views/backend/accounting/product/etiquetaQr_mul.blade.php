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
        }

        /* Contenedor que agrupa todas las etiquetas en pantalla */
        .contenedor-masivo-vista {
            display: flex;
            flex-direction: column;
            gap: 10px; /* Separación visual solo para la pantalla */
            max-height: 400px;
            overflow-y: auto;
            padding: 5px;
            border: 1px solid #ccc;
            background: #e0e0e0;
        }

        .print-container {
            width: 64mm;
            height: 32mm;
            border: 1px solid #ccc;
            box-sizing: border-box;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            background-color: #fff;
            padding: 2mm 3mm;
            gap: 4mm;
        }

        .print-container .qr-code {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 20mm;
            width: 20mm;
            height: 20mm;
        }

        .print-container .qr-code svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .print-container .label-text {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: left;
            font-family: Arial, sans-serif;
        }

        .print-container .label-text p {
            margin: 0 0 3px 0;
            line-height: 1.2;
            font-size: 11px;
            color: #000;
        }
        .print-container .label-text p strong {
            font-size: 13px;
        }
    </style>

    <div class="etiqueta-qr-modal-root">
        
        <div class="contenedor-masivo-vista" id="loteParaImprimir">
            
            @foreach($productos as $producto)
                @php
                    $company = '';
                    $interno = $company . $producto->nro_interno;
                    $macar_modelo = ($producto->marcaModelo->marca->marca ?? '') ." ".($producto->marcaModelo->modelo->modelo ?? '');
                    
                    // String optimizado para lectura ultra rápida
                    $qrDataClean = "{$producto->id}|{$producto->nro_interno}|{$macar_modelo}|{$producto->nro_oblea}";
                    
                    // Generar QR limpio en formato SVG
                    $qrCodeOutput = QrCode::size(150)->format('svg')->margin(0)->generate($qrDataClean);
                @endphp

                <div class="print-container">
                    <div class="qr-code">
                        {!! $qrCodeOutput !!}
                    </div>
                    <div class="label-text">
                        <p><strong>ID: {!! $producto->id !!}</strong></p>
                        <p>{!! $producto->item->item_name ?? 'Sin Nombre' !!}</p>
                        <p>{!! $macar_modelo !!}</p>
                        <p>Int: {!! $interno !!}</p>
                    </div>
                </div>
            @endforeach

        </div>

        <div>
            <button onclick="imprimirLote()" style="padding: 10px 20px; font-weight: bold; cursor: pointer;">
                Imprimir Lote ({{ count($productos) }} etiquetas)
            </button>
        </div>
    </div>
</body>

<script>
    function imprimirLote() {
        // Obtenemos el HTML de todas las etiquetas juntas
        const contenidoLote = document.getElementById('loteParaImprimir').innerHTML;
        const ventanaImpresion = window.open('', '_blank');

        ventanaImpresion.document.write(`
            <html>
            <head>
                <title>Impresión en Serie</title>
                <style>
                    /* Configuración de la bobina de la impresora */
                    @page {
                        size: 64mm 32mm;
                        margin: 0;
                    }
                    
                    body {
                        margin: 0;
                        padding: 0;
                        background-color: #fff;
                    }

                    /* Forzar el salto de página físico después de cada etiqueta */
                    .print-container {
                        width: 64mm;
                        height: 32mm;
                        box-sizing: border-box;
                        display: flex;
                        flex-direction: row;
                        align-items: center;
                        justify-content: flex-start;
                        padding: 2mm 3mm;
                        gap: 4mm;
                        border: none;
                        background-color: #fff;
                        
                        /* TRUCO MAESTRO: Esto le dice a la impresora térmica dónde termina cada etiqueta */
                        page-break-after: always;
                        break-after: page;
                    }

                    .print-container .qr-code {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex: 0 0 22mm;
                        width: 22mm;
                        height: 22mm;
                    }

                    .print-container .qr-code svg {
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
                    }

                    .print-container .label-text p {
                        margin: 0 0 2px 0;
                        line-height: 1.1;
                        font-size: 10px;
                        color: #000;
                    }

                    .print-container .label-text p strong {
                        font-size: 12px;
                    }
                </style>
            </head>
            <body>
                ${contenidoLote}
            </body>
            </html>
        `);

        ventanaImpresion.document.close();
        
        // Pequeño delay para asegurar que los múltiples SVG rendericen en el DOM de la nueva ventana
        setTimeout(() => {
            ventanaImpresion.focus();
            ventanaImpresion.print();
            ventanaImpresion.close();
        }, 350);
    }
</script>
</html>