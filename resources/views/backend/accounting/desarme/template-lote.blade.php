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
					<h3 style="margin: 0;"><b>{{ get_company_field(company_id(), 'company_name') }}</b></h3>
					<h3 style="margin: 0;"><b>{{ get_company_field(company_id(), 'company_name') }}</b></h3>
					<p style="margin: 2px 0;">{{ get_company_field(company_id(), 'address') }}</p>
					<p style="margin: 2px 0;">{{ get_company_field(company_id(), 'email') }}</p>
					@if (get_company_field(company_id(), 'vat_id'))
						<p style="margin: 2px 0;">{{ _lang('VAT ID') }}: {{ clean(get_company_field(company_id(), 'vat_id')) }}</p>
					@endif
					@if (get_company_field(company_id(), 'reg_no'))
						<p style="margin: 2px 0;">{{ _lang('REG NO') }}: {{ clean(get_company_field(company_id(), 'reg_no')) }}</p>
					@endif
				</td>
				<td style="width: 30%; text-align: right; vertical-align: top;">
					<img src="{{ get_pdf_company_logo(company_id()) }}" style="width: 130px; display: block;">
					<p style="margin: 2px 0;">{{ now()}}</p>
				</td>
			</tr>
		</table>
	</div>
	
    <div id="order_desarme-view" class="pdf">

		<p style="margin: 2px 0;">No. INTERNO {{ $interno}}</p>
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
                @foreach ($ordenes as $orden)
                    <tr>
                        <td>{{ $orden['puesto'] ?? '' }}</td>
                        <td>{{ $orden['f_ingreso_puesto'] ?? '' }}</td>
                        <td>{{ $orden['prioridad'] ?? '' }}</td>
                        <td>{{ $orden['interno'] ?? '' }}</td>
                        <td>{{ $orden['venta'] ?? '' }}</td>
                        <td>{{ $orden['fecha_venta'] ?? '' }}</td>
                        <td>{{ $orden['cliente'] ?? '' }}</td>
                        <td>{{ $orden['marca_modelo'] ?? '' }}</td>
                        <td>{{ $orden['pieza'] ?? '' }}</td>
                        <td>{{ $orden['vendedor'] ?? '' }}</td>
                        <td>{{ $orden['ubicacion'] ?? '' }}</td>
                        <td>{{ $orden['estado'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="clearfix"></div>
    </div>
</body>

</html>
