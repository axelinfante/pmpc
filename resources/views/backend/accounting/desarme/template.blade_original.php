<!DOCTYPE html>
<html>
<head>
<title>{{ get_option('site_title', 'Orden Desarme') }}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style type="text/css">
@php 
include public_path('backend/assets/css/bootstrap.min.css');
include public_path('backend/assets/css/styles.css');
$client= $order_desarme->venta->client;
$date_format = get_company_option('date_format','Y-m-d');
@endphp 

body {
   -webkit-print-color-adjust: exact; !important;
   background: #FFF;
   font-size: 14px;
   font-family: DejaVu Sans, sans-serif;
}

.classic-table{
	width:100%;
	color: #000;
}
.classic-table td{
	color: #000;
}

#order_desarme-item-table th, #order_desarme-item-table td{
	border: 1px solid #000;
}

#order_desarme-summary-table td{
	border: 1px solid #000 !important;
}

#order_desarme-payment-history-table{
	margin-bottom: 50px;
}

#order_desarme-payment-history-table th, #order_desarme-payment-history-table td{
	border: 1px solid #000 !important;
}

#order_desarme-view{
    padding:15px 0px;
}

.order_desarme-note{
	margin-bottom: 50px;
}

.table th {
    background-color: whitesmoke !important;
    color: #000;
}

.table td {
   color: #2d2d2d;
}

.base_color{
   background-color: whitesmoke !important;
}

.order_desarme-col-6{
  width: 50%;
  float:left;
  padding-right: 0px;
  padding-left: 0px;
}

</style>
</head>

<body>


<div id="order_desarme-view" class="pdf">
	<div>
		<table class="classic-table">
			<tbody>
				 <tr class="top">
					<td colspan="2">
						 <table class="classic-table">
							<tbody>
								 <tr>
									<td>
										<h3><b>{{ get_company_field($order_desarme->company_id,'company_name') }}</b></h3>
										{{ get_company_field($order_desarme->company_id,'address') }}<br>
										{{ get_company_field($order_desarme->company_id,'email') }}<br>
										{!! get_company_field($order_desarme->company_id,'vat_id') != '' ? _lang('VAT ID').': '.clean(get_company_field($order_desarme->company_id,'vat_id')).'<br>' : '' !!}
										{!! get_company_field($order_desarme->company_id,'reg_no')!= '' ? _lang('REG NO').': '.clean(get_company_field($order_desarme->company_id,'reg_no')).'<br>' : '' !!}
									</td>
									<td class="text-right">
										<img src="{{ get_pdf_company_logo($order_desarme->company_id) }}" class="wp-100">
									</td>
								 </tr>
							</tbody>
					    </table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="2" class="pt-5">
						<div class="order_desarme-col-6 pt-3">
							 <h5><b>{{ _lang('Cliente:') }}</b></h5>
							 {{ $client->contact_name }}<br>
							 {{ $client->contact_email }}<br>
							 {!! $client->company_name != '' ? clean($client->company_name).'<br>' : '' !!}
							 {!! $client->address != '' ? clean($client->address).'<br>' : '' !!}
							 {!! $client->vat_id != '' ? _lang('VAT ID').': '.clean($client->vat_id).'<br>' : '' !!}
							 {!! $client->reg_no != '' ? _lang('REG NO').': '.clean($client->reg_no).'<br>' : '' !!}

						</div>

						<!--Company Address-->
						<div class="order_desarme-col-6 pt-3">
							<div class="d-inline-block float-md-right">
								<h5><b>{{ _lang('Detalle') }}</b></h5>

								<b>{{ _lang('Número') }} #:</b> {{ $order_desarme->id }}<br>

								<b>{{ _lang('Fecha') }}:</b> {{ date($date_format, strtotime( $order_desarme->fecha_venta)) }}<br>

							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	 </div>
	 <!--End order_desarme Information-->
	 <div class="clearfix"></div>
	 <!--order_desarme Product-->
	 <div>
		<table class="table table-bordered mt-2" id="order_desarme-item-table">
			 <thead class="base_color">
				 <tr>
					<th>{{ _lang('Name') }}</th>
									 <th>{{ _lang('Marca y modelo') }}</th>
									 <th>Nro interno Vehiculo</th>
									
				 </tr>
			 </thead>
			 <tbody id="order_desarme">
					 <tr id="product-{{ $order_desarme->producto->id }}">
										
										<td>
											<b>{{ $order_desarme->producto->item->item_name }}</b><br>{{ $order_desarme->producto->description }}
										</td>

										<td>
											
											<b>
												@isset($order_desarme->producto->marcaModelo)
													{{ $order_desarme->producto->marcaModelo->marca->marca ?? ''}} {{ $order_desarme->producto->marcaModelo->modelo->modelo ?? '' }}

													@elseif ( isset($order_desarme->producto->car->marca_modelo))
														{{ $order_desarme->producto->car->marca_modelo->marca->marca ?? '' }} {{ $order_desarme->producto->vehiculo->marca_modelo->modelo->modelo ?? '' }}
														@else
														Sin marca Sin modelo

													
												@endisset
											</b>
										</td>
										<td class="text-center">
											@isset($order_desarme->producto->vehiculo)
											{{-- nroInternoAlias($item->product->vehiculo->company_id,$item->product->vehiculo->tipo_vehiculo,$item->product->vehiculo->id) --}}
													
											<!-- {{ $order_desarme->car->company_id == 1 ? 'PM-': 'PC-' }}{{ $order_desarme->car->id }} -->
													@else
													<!-- {{ $order_desarme->company_id == 1 ? 'PM-': 'PC-' }}{{$order_desarme->nro_interno }} -->
													{{ ''  }}
												@endisset	
										</td>
										
		</table>
	 </div>
	 <!--End order_desarme Product-->



	 <div class="clearfix"></div>


	 <!--order_desarme Note-->
	 @if($order_desarme->note  != '')
		<div>
			<div class="order_desarme-note">{{ $order_desarme->note }}</div>
		</div>
	 @endif
	 <!--End order_desarme Note-->

	 <!--order_desarme Footer Text-->
	 @if(get_company_field($order_desarme->company_id,'order_desarme_footer') != '')
		<div>
			<div class="order_desarme-note">{!! xss_clean(get_company_field($order_desarme->company_id,'order_desarme_footer')) !!}</div>
		</div>
	 @endif
	 <!--End order_desarme Note-->
</div>
</body>
</html>
