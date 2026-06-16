<!DOCTYPE html>
<html>
<head>
    <title>QR Producto</title>
</head>
<body>
    {{-- Estilos para la vista previa en el Modal --}}
    <style>
        .etiqueta-qr-modal-root {
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
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

        .print-container .qr-code img,
        .print-container .qr-code svg {
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
        }

        .print-container .label-text p {
            margin: 0 0 3px 0;
            line-height: 1.2;
            font-size: 11px;
            color: #000;
            word-break: break-word;
        }
        .print-container .label-text p strong {
            font-size: 13px;
        }
    </style>

    <div class="etiqueta-qr-modal-root">
        @php
            $company = ($producto->company_id == 1) ? 'PM-' : 'PC-';
            $interno = $company . $producto->nro_interno;
            $macar_modelo = ($producto->marcaModelo->marca->marca ?? '') ." ".($producto->marcaModelo->modelo->modelo ?? '');
            
            // OPTIMIZACIÓN CRUCIAL: Quitamos las cabeceras pesadas del CSV. Reducimos el string al mínimo.
            // Si tu lector necesita procesarlo, usa separadores cortos como pipes (|) en lugar de CSV con títulos.
            $qrDataClean = "{$producto->id}|{$producto->nro_interno}|{$macar_modelo}|{$producto->nro_oblea}";
            
            // Generamos en formato SVG (Vectores perfectos que no se pixelan jamás)
            // Si tu librería da error con SVG, cambia ->format('svg') por ->format('png') y añade ->margin(0)
            $qrCodeOutput = QrCode::size(150)->format('svg')->margin(0)->generate($qrDataClean);
        @endphp

        <div class="print-container" id="divParaImprimir">
            <div class="qr-code">
                {{-- Si es SVG entra directo como HTML nativo, si cambiaste a PNG usa la etiqueta img base64 anterior --}}
                {!! $qrCodeOutput !!}
            </div>

            <div class="label-text">
                <p><strong>ID: {!! $producto->id !!}</strong></p>
                <p>{!! $producto->item->item_name ?? 'Sin Nombre' !!}</p>
                <p>{!! $macar_modelo !!}</p>
                <p>Int: {!! $interno !!}</p>
            </div>
        </div>

        <div style="margin-top: 15px;">
            <button onclick="imprimirDiv()" style="padding: 8px 16px; cursor: pointer;">Imprimir Etiqueta</button>
        </div>
    </div>
</body>

<script>
    function imprimirDiv() {
        const contenido = document.getElementById('divParaImprimir').outerHTML; // Usamos outerHTML para capturar la estructura limpia
        const ventanaImpresion = window.open('', '_blank');

        ventanaImpresion.document.write(`
            <html>
            <head>
                <title>Imprimir Etiqueta</title>
                <style>
                    /* Estilos puros para la impresora térmica / hojas de etiquetas */
                    @page {
                        size: 64mm 32mm;
                        margin: 0;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        background-color: #fff;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    }
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
                        border: none; /* Quitamos el borde para que no se imprima una línea negra en el contorno */
                    }
                    .print-container .qr-code {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex: 0 0 22mm; /* Le damos un poco más de tamaño real al QR en la impresión */
                        width: 22mm;
                        height: 22mm;
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
                ${contenido}
            </body>
            </html>
        `);

        ventanaImpresion.document.close();
        
        // Esperar un momento a que el SVG/Contenido renderice antes de lanzar el diálogo
        setTimeout(() => {
            ventanaImpresion.focus();
            ventanaImpresion.print();
            ventanaImpresion.close();
        }, 250);
    }
</script>
</html>