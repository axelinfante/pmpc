<!DOCTYPE html>
<html>

<head>
    <title>{{ get_option('site_title', 'Orden Desarme') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style type="text/css">
        body {
            -webkit-print-color-adjust: exact !important;
            background: #FFF;
            font-size: 12px;
            font-family: DejaVu Sans, sans-serif;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        .table thead th {
            background-color: whitesmoke !important;
            font-weight: bold;
            padding: 4px;
            text-align: left;
            font-size: 12px;
        }

        .table tbody td {
            padding: 4px;
            font-size: 11px;
            border-bottom: 0.5px solid #ccc;
        }

        .clearfix {
            clear: both;
        }
    </style>
</head>

<body>
	<div>
		<table class="classic-table" style="width: 100%;">
			<tr class="top">
				
				<td style="width: 70%;">
					<p style="margin: 2px 0;"><b>OD Nro: {{ $order_desarme->id  }}</b></p>
					<p style="margin: 2px 0;"><b>Fecha Hora: {{ $order_desarme->f_ingreso_puesto ?? ''  }}</b></p>
					<p style="margin: 2px 0;"><b>Puesto asignado: {{ $order_desarme->puesto ?? ''  }}</b></p>
					<p style="margin: 2px 0;"><b>Puesto desarme: {{ $order_desarme->Puesto_final ?? $order_desarme->puesto  }}</b></p>
					<p style="margin: 2px 0;"><b>Fecha desarme: {{ $order_desarme->fecha_desarmado_anulado  }}</b></p>
					<!--<h3 style="margin: 0;"><b>{{ get_company_field(company_id(), 'company_name') }}</b></h3>-->
				</td>
				<td style="width: 30%; text-align: right; vertical-align: top;">
					<img src="{{ get_pdf_company_logo(company_id()) }}" style="width: 130px; display: block;">
					<p style="margin: 2px 0;">{{ now()}}</p>
				</td>
			</tr>
		</table>
	</div>
	
    <div id="order_desarme-view" class="pdf">

		<p style="margin: 2px 0;">No. INTERNO {{ nroInternoAlias($order_desarme->car->company_id ?? '', $order_desarme->car->tipo_vehiculo ?? '', $order_desarme->car->id ?? ''), }}</p>
	<br>

        <table id="orden-desarme-table" class="table">
            <thead>
                <tr>
                    <th>{{ _lang('Puesto') }}</th>
                    <th>{{ _lang('Ingreso a Puesto') }}</th>
                    <th>{{ _lang('Prioridad') }}</th>
                    <th>{{ _lang('Interno') }}</th>
                    <th>{{ _lang('Venta') }}</th>
                    <th>{{ _lang('Fecha de venta') }}</th>
                    <th>{{ _lang('Cliente') }}</th>
                    <th>{{ _lang('Marca y modelo') }}</th>
                    <th>{{ _lang('Pieza') }}</th>
                    <th>{{ _lang('Vendedor') }}</th>
                    <th>{{ _lang('Ubicación') }}</th>
                    <th>{{ _lang('Estado') }}</th>
                </tr>
            </thead>
            <tbody>
                    <tr>
                        <td>{{ $order_desarme->puesto ?? '' }}</td>
                        <td>{{ $order_desarme->f_ingreso_puesto ?? '' }}</td>
                        <td>{{ $order_desarme->prioridad ?? '' }}</td>
                        <td>{{ $order_desarme->interno ?? '' }}</td>
                        <td>{{ isset($order_desarme->venta) ? 'VEN-' . ($order_desarme->venta->company_id == 1 ? 'PM-' : 'PC-') . $order_desarme->venta->invoice_number : ''   }} </td>
                        <td>{{ $order_desarme->fecha_venta ?? '' }}</td>
                        <td>{{ $order_desarme->cotizacion->client->contact_name ?? $order_desarme->venta->client->contact_name ?? '' }}</td>
                        <td>{{ ($order_desarme->producto->marcaModelo->marca->marca ?? '') . ' ' . ($order_desarme->producto->marcaModelo->modelo->modelo ?? '') }}</td>
                        <td>{{ $order_desarme->item->item_name ??  '' }}</td>
                        <td>{{ $order_desarme->cotizacion->vendedor->name ?? $order_desarme->venta->vendedor->name ?? '' }}</td>
                        <td>{{ $order_desarme->car->lugar_entrega->nombre ?? '' }}</td>
                        <td>{{ $order_desarme->estado ?? '' }}</td>
                    </tr>
            </tbody>
        </table>

        <div class="clearfix"></div>
    </div>
</body>

</html>
