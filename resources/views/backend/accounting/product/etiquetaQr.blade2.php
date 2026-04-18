<!DOCTYPE html>
<html>
<head>
    <title>QR Producto</title>
    <style>
        /* Estilos generales para la página */
        body {
            margin: 0;
            padding: 20px; /* Espacio alrededor del contenedor principal */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0; /* Fondo para resaltar el contenedor */
        }

        /* Contenedor principal con tamaño y borde específico */
        .print-container {
            width: 64mm;
            height: 32mm;
            border: 1px solid #000; /* Borde del contenedor */
            box-sizing: border-box; /* Incluir el borde en el tamaño total */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between; /* Espacio uniforme entre el QR y los párrafos */
            text-align: center;
            overflow: hidden; /* Asegura que nada se salga del contenedor */
            background-color: #fff; /* Fondo blanco para impresión */
            padding: 2mm; /* Pequeño relleno para evitar que el contenido toque el borde */
            position: relative;
        }

        /* Ajustar el tamaño del QR */
        .row.qr-code {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        .qr-code img {
            max-width: 16mm; /* Tamaño reducido del código QR */
            max-height: 16mm; /* Tamaño reducido del código QR */
        }

        /* Estilo para los párrafos */
        .row p {
            margin: 0;
            line-height: 1.1; /* Espaciado entre líneas */
            font-size: 12px; /* Tamaño ajustado para que el texto no se superponga */
        }

        /* Ocultar el botón de impresión y otros elementos fuera del contenedor al imprimir */
        @media print {
            body * {
                visibility: hidden;
            }
            .print-container, .print-container * {
                visibility: visible;
            }
            .print-container {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                margin: auto;
            }
        }
    </style>
</head>
<body>

    <!-- Contenedor para imprimir -->
    <div class="print-container" style="width: 250px; display: flex; flex-direction: row;">
        @php
            // Determinar la compañía y número interno
            $company = ($producto->company_id == 1) ? 'PM-' : 'PC-';
            $interno = $company . $producto->nro_interno;
        @endphp

        <!-- Contenedor para el código QR -->
        <div class="row qr-code" style="width: 50%;">
            {!! QrCode::size(80)->generate($producto->id) !!} <!-- Tamaño reducido del QR -->
        </div>

        <!-- Contenedores para los párrafos -->
        <div class="row" style="width: 45%;">
            <p>{!! $producto->id !!} - {!! $producto->item->item_name !!}</p>
            <p>{!! $producto->marcaModelo->marca->marca !!} - {!! $producto->marcaModelo->modelo->modelo !!} - {!! $interno !!}</p>
        </div>
    </div>

    <!-- Botón de impresión fuera del contenedor -->
    <button onclick="window.print()">Imprimir</button>

      <!-- Contenedor para imprimir -->
    <div class="print-container" style="width: 250px; display: flex; flex-direction: row;">
        @php
            // Determinar la compañía y número interno
            $company = ($producto->company_id == 1) ? 'PM-' : 'PC-';
            $interno = $company . $producto->nro_interno;
        @endphp


        <!-- Contenedores para los párrafos -->
        <div class="row" style="width: 100%; margin-left: 10px;">
            <p style="font-size: 35px; font-weight: bold;">{!! $producto->id !!}</p>
            <p style="text-align: left;">{!! $producto->marcaModelo->marca->marca !!} - {!! $producto->marcaModelo->modelo->modelo !!} - {!! $interno !!}</p>
        </div>
    </div>

    <!-- Botón de impresión fuera del contenedor -->
    <button onclick="window.print()">Imprimir</button>


</body>
</html>
