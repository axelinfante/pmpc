<!DOCTYPE html>
<html>
<head>
    <title>QR Producto</title>
</head>
<body>
    {{-- Estilos aquí: el modal carga solo el body por AJAX (.modal-body.html) y no aplica el <head>. --}}
    <style>
        /* Vista (modal / pestaña): no usar selector `body` para no afectar el admin. */
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
            border: 1px solid #000;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            overflow: hidden;
            background-color: #fff;
            padding: 2mm;
            position: relative;
        }

        /* Sin clase Bootstrap `row` (en el modal rompe el layout con márgenes negativos). */
        .print-container .qr-code {
            display: flex;
            justify-content: flex-start;
            flex: 0 0 auto;
            width: auto;
        }
        .print-container .qr-code img {
            display: block;
            max-width: 20mm;
            max-height: 20mm;
        }

        .print-container .label-text p {
            margin: 0;
            line-height: 1.1;
            font-size: 12px;
        }

        .print-container .label-text {
            flex: 0 1 auto;
            min-width: 0;
            max-width: 160px;
            text-align: left;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        /* Modal: un poco más de aire entre QR y texto (la impresión usa otra hoja de estilos). */
        .print-container .label-row {
            column-gap: 14px;
        }
    </style>
    <div class="etiqueta-qr-modal-root">
    <!-- Contenedor para imprimir -->
    <div class="print-container" style="width: 250px; display: flex; flex-direction: row;" id="divParaImprimir">
    <div class="label-row" style="width: 250px; display: flex; flex-direction: row; align-items: flex-start;">
        @php
            // Determinar la compañía y número interno
            $company = ($producto->company_id == 1) ? 'PM-' : 'PC-';
            $interno = $company . $producto->nro_interno;
			$macar_modelo= ($producto->marcaModelo->marca->marca ?? '') ." ".($producto->marcaModelo->modelo->modelo ?? '');
			$csvData = "Id_Producto,Interno,Marca_Modelo,Oblea\n{$producto->id},$producto->nro_interno,$macar_modelo,$producto->nro_oblea";
			$qrText = QrCode::size(80)->format('png')->generate($csvData);
        @endphp

        <!-- Contenedor para el código QR -->
        <div class="qr-code">
            {{-- QrCode::size(80)->generate($producto->id) --}} <!-- Tamaño reducido del QR -->
			<img src="data:image/png;base64, {!! base64_encode($qrText) !!} ">
        </div>

        <!-- Contenedores para los párrafos -->
        <div class="label-text">
            <p>{!! $producto->id !!} - {!! $producto->item->item_name !!}</p>
            <p>{!! isset($producto->marcaModelo->marca->marca) ? $producto->marcaModelo->marca->marca : ''  !!} - {!! isset($producto->marcaModelo->modelo->modelo) ? $producto->marcaModelo->modelo->modelo : '' !!} - {!! $interno !!}</p>
        </div>
        </div>
    </div>

    <!-- Botón de impresión fuera del contenedor -->
    <div style="margin-top: 10px;">
        <button onclick="imprimirDiv()">Imprimir</button>
    </div>

    </div>

</body>
<script>
        function imprimirDiv() {
            // Obtener el contenido del div
            const contenido = document.getElementById('divParaImprimir').innerHTML;

            // Crear una nueva ventana
            const ventanaImpresion = window.open('', '_blank');

            // Escribir el contenido en la nueva ventana
            ventanaImpresion.document.write(`
                <html>
                <head>
                    <title>Imprimir</title>
                   <style>
        body {
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
            display: flex;
            flex-direction: row;
            width: 64mm;
            height: 32mm;
            border: 1px solid #000;
            box-sizing: border-box;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            overflow: hidden;
            background-color: #fff;
            padding: 2mm;
            position: relative;
        }

        .label-row {
            column-gap: 8px;
        }

        .qr-code {
            display: flex;
            justify-content: flex-start;
            flex: 0 0 auto;
            width: auto;
        }
        .qr-code img {
            display: block;
            max-width: 16mm;
            max-height: 16mm;
        }

        .label-text p {
            margin: 0;
            line-height: 1.1;
            font-size: 12px;
        }

        .label-text {
            flex: 0 1 auto;
            min-width: 0;
            max-width: 140px;
            text-align: left;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

    </style>
                </head>
                <body>
                    ${contenido}
                </body>
                </html>
            `);

            // Cerrar el documento y abrir el diálogo de impresión
            ventanaImpresion.document.close();
            ventanaImpresion.focus();
            ventanaImpresion.print();
            ventanaImpresion.close(); // Cierra la ventana después de imprimir
        }
    </script>
</html>
