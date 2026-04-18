<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Orden de Entrega</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        .titulo {
            font-size: 18px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <?php
    
    $in = 'VEN-';
    if (!isset($orden->cotizacion)) {
        return '';
    }
    if ($orden->cotizacion->company_id == 1) {
        $in .= 'PM-';
    } elseif ($orden->cotizacion->company_id == 2) {
        $in .= 'PC-';
    }
    $text = $in . ($orden->cotizacion->invoice_number ?? '');
    
    $date_format = get_company_option('date_format', 'Y-m-d');
    $fecha_venta= isset($orden->cotizacion->invoice_date) ? date($date_format, strtotime($orden->cotizacion->invoice_date)) : null;
    
    ?>
    <div class="titulo">Orden de Entrega</div>

    <p><strong>Fecha:</strong> {{ $fecha_impresion }}</p>
    <p><strong>Número de Orden:</strong> {{ $orden->id }}</p>
    <p><strong>Cotización N°:</strong> {{ $text ?? 'N/A' }}</p>
    <p><strong>Fecha de Venta:</strong> {{   $fecha_venta }}</p>
    <p><strong>Cliente:</strong> {{ $orden->cotizacion->client->contact_name ?? ''}}</p>
    <p><strong>Vendedor:</strong> {{ $orden->cotizacion->vendedor->name ?? '' }}</p>

    <h4>Pieza a Despachar</h4>
    <table>
        <thead>
            <tr>
                <th>Pieza</th>
                <th>Marca</th>
                <th>Modelo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $orden->itemInvoice->item->item_name ?? 'N/A' }}</td>
                <td>{{ $orden->itemInvoice->product->marcaModelo->marca->marca ?? 'N/A' }}</td>
                <td>{{ $orden->itemInvoice->product->marcaModelo->modelo->modelo ?? 'N/A' }}</td>
            </tr>
        </tbody>
    </table>

    <p><strong>Forma de Entrega:</strong> {{ $orden->forma_entrega }}</p>
    <p><strong>Observaciones:</strong> {{ $orden->observaciones }}</p>

</body>

</html>
