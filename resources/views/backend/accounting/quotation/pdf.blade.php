<!DOCTYPE html>
<html lang="en">
<head>
<title>{{ get_option('site_title', 'ElitKit Quotation') }}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">


<style type="text/css">
@php include public_path('backend/assets/css/bootstrap.min.css') @endphp
@php include public_path('backend/assets/css/styles.css') @endphp

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

	#invoice-item-table th, #invoice-item-table td{
		border: 1px solid #000;
	}

	#invoice-summary-table td{
		border: 1px solid #000 !important;
	}

	#invoice-payment-history-table{
		margin-bottom: 50px;
	}

	#invoice-payment-history-table th, #invoice-payment-history-table td{
		border: 1px solid #000 !important;
	}

	#invoice-view{
	   padding:15px 0px;
	}

	.invoice-note{
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
	.invoice-col-6{
	  width: 50%;
	  float:left;
	  padding-right: 0px;
	  padding-left: 0px;
	}
</style>
</head>

<body>
	@php $base_currency = get_company_field( $quotation->company_id, 'base_currency', 'USD' ); @endphp
	@php $date_format = get_company_field($quotation->company_id, 'date_format','Y-m-d'); @endphp
	@php $currency = get_currency_symbol($base_currency); @endphp

	<div id="quotation-view" class="pdf">
		<div>
			<table class="classic-table">
				<tbody>
					 <tr class="top">
						<td colspan="2">
							 <table class="classic-table vt">
								<tbody>
											 <tr>
												<td>
													<h3><b>{{ get_company_option('company_name') }}</b></h3>
													{{ get_company_option('address') }}<br>
													{{ get_company_option('email') }}<br>
													{!! get_company_option('vat_id') != '' ? _lang('VAT ID').': '.clean(get_company_option('vat_id')).'<br>' : '' !!}
													{!! get_company_option('reg_no')!= '' ? _lang('REG NO').': '.clean(get_company_option('reg_no')).'<br>' : '' !!}
												</td>
												<td class="float-right">
													 @if($quotation->company_id == 2)
                                                        <img src="../public/images/PC-marca_agua.png" class="wp-250">
                                                        @elseif($quotation->company_id == 1)
                                                        <img src="../public/images/PM-marca_agua.png" class="wp-250">
                                                    @endif
												</td>
											 </tr>
										</tbody>
							 </table>
						</td>
					 </tr>

					 <tr class="information">
								<td colspan="2" class="pt-5">
									<div class="row">
										<div class="invoice-col-6 pt-3">
											 <h5><b>{{ _lang('Quotation To') }}</b></h5>
											 @if($quotation->related_to == 'contacts' && isset($quotation->client))
												 {{ $quotation->client->dni_cuit}}<br>
												 {{ $quotation->client->contact_name }}<br>
												 {{ $quotation->client->contact_email }}<br>
												 {!! $quotation->client->company_name != '' ? clean($quotation->client->company_name).'<br>' : '' !!}
												 {!! $quotation->client->address != '' ? clean($quotation->client->address).'<br>' : '' !!}
												 {!! $quotation->client->vat_id != '' ? _lang('VAT ID').': '.clean($quotation->client->vat_id).'<br>' : '' !!}
												 {!! $quotation->client->reg_no != '' ? _lang('REG NO').': '.clean($quotation->client->reg_no).'<br>' : '' !!}
											 @elseif($quotation->related_to == 'leads' && isset($quotation->lead))	 
												 {{ $quotation->lead->name }}<br>
												 {{ $quotation->lead->email }}<br>
												 {!! $quotation->lead->company_name != '' ? clean($quotation->lead->company_name).'<br>' : '' !!}
												 {!! $quotation->lead->address != '' ? clean($quotation->lead->address).'<br>' : '' !!}
												 {!! $quotation->lead->vat_id != '' ? _lang('VAT ID').': '.clean($quotation->lead->vat_id).'<br>' : '' !!}
												 {!! $quotation->lead->reg_no != '' ? _lang('REG NO').': '.clean($quotation->lead->reg_no).'<br>' : '' !!}
											 @endif                        
										</div>
														
										<div class="invoice-col-6 pt-3">
											<div class="d-inline-block float-md-right">		
												<h5><b>{{ _lang('Quotation Details') }}</b></h5>
												<b>{{ _lang('Quotation') }} #:</b> {{ $quotation->quotation_number }}<br>
												<b>{{ _lang('Quotation Date') }}:</b> {{ date($date_format, strtotime( $quotation->quotation_date)) }}<br>
												<b>{{ _lang('Vendedor') }} :</b> {{ $quotation->vendedor->name ?? '' }}<br>
											</div>
										</div>
									</div>
								</td>
							 </tr>
				</tbody>
			</table>
		 </div>
		 <!--End Quotation Information-->
		 <div class="clearfix"></div>
		 <!--Quotation Product-->
		 <div>
			<table class="table table-bordered mt-2" id="invoice-item-table">
				  <thead class="base_color">
								 <tr>
									<th>{{ _lang('Name') }}</th>
									<th>{{ _lang('Marca y modelo') }}</th>
									 <th>Nro interno Vehiculo</th>
									<th class="text-center wp-100">{{ _lang('Quantity') }}</th>
									<th class="text-right">{{ _lang('Unit Cost') }}</th>
									<th class="text-right wp-100">{{ _lang('Discount') }}</th>
									<th>{{ _lang('Tax') }}</th>
									<th class="text-right">{{ _lang('Sub Total') }}</th>
								 </tr>
					</thead>
				<tbody id="invoice">
								 @foreach($quotation->quotation_items as $item)
									 <tr id="product-{{ $item->item_id }}">
										 <td>
											<b>{{ $item->item->item_name }}</b><br>{{ $item->description }}
										 </td>
										 <td>
											{{-- <b>{{ $item->marca->item_name }}</b><br>{{ $item->description }} --}}
											<b>
													{{  ($car->marca_modelo->marca->marca  ?? '')	. " " . ($car->marca_modelo->modelo->modelo ?? '')  }}
											</b>
										</td>
										<td class="text-center">
											@isset($item->product->vehiculo)

											{{-- nroInternoAlias($item->product->vehiculo->company_id,$item->product->vehiculo->tipo_vehiculo,$item->product->vehiculo->id) 
											--}}
											
											{{ nroInternoAlias($quotation->company_id,$car->tipo_vehiculo,$car->id) 
											}}
													
												<!-- {{ $item->product->vehiculo->company_id == 1 ? 'PM-': 'PC-' }}{{ $item->product->vehiculo->id }} -->

													@else
														
													<!-- {{ $item->product->company_id == 1 ? 'PM-': 'PC-' }}{{ $item->product->nro_interno }} -->
													{{ nroInternoAlias($quotation->company_id,$car->tipo_vehiculo,$car->id) 
											}}  
												@endisset	
										</td>
										 <td class="text-center">{{ $item->quantity }}</td>
										 <td class="text-right">{!! decimalPlace($item->unit_cost, $currency) !!}</td>
										 <td class="text-right">{!! decimalPlace($item->discount, $currency) !!}</td>
										 <td>{!! clean(object_to_tax($item->taxes, 'name')) !!}</td>
										 <td class="text-right">{!! decimalPlace($item->sub_total, $currency) !!}</td>
									 </tr>
								 @endforeach
							 </tbody>
			</table>
		 </div>
		 <!--End Quotation Product-->
		 <!--Summary Table-->
					<div class="invoice-summary-right">
						<table class="table table-bordered" id="invoice-summary-table">
							<tbody>
								<tr>
									 <td>{{ _lang('Sub Total') }}</td>
									 <td class="text-right">
										<span>{!! decimalPlace($quotation->grand_total - $quotation->tax_total, $currency) !!}</span>
									 </td>
								</tr>
								@foreach($quotation_taxes as $tax)
									<tr>
										 <td>{{ $tax->name }}</td>
										  <td class="text-right">
											<span>{!! decimalPlace($tax->tax_amount, $currency) !!}</span>
										 </td>
									</tr>
								@endforeach
								<tr>
									<td><b>{{ _lang('Grand Total') }}</b></td>
									<td class="text-right">
										<b>{!! decimalPlace($quotation->grand_total, $currency) !!}</b>
										@if($quotation->related_to == 'contacts' && isset($quotation->client))
											@if($quotation->client->currency != $base_currency)
												<br><b>{!! decimalPlace($quotation->converted_total, currency($quotation->client->currency)) !!}</b>
											@endif
										@elseif($quotation->related_to == 'leads' && isset($quotation->lead))
											@if($quotation->lead->currency != $base_currency)
												<br><b>{!! decimalPlace($quotation->converted_total, currency($quotation->lead->currency)) !!}</b>
											@endif
										@endif
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<!--End Summary Table-->
					 
					 <div class="clearfix"></div>
					 <!--Related Transaction-->
			 @if( ! $transactions->isEmpty() )
			 <div class="col-md-12">
				 <div class="table-responsive">
					 <table class="table table-bordered mt-2">
						 <thead>
							 <tr>
								 <th>{{ _lang('Date') }}</th>
								 <th>{{ _lang('Account') }}</th>
								 <th class="text-right">{{ _lang('Amount') }}</th>
								 <th>{{ _lang('Payment Method') }}</th>
							 </tr>
						 </thead>
						 <tbody>	  
							@foreach($transactions as $transaction)
								 <tr id="transaction-{{ $transaction->id }}">
									 <td>{{ date($date_format,strtotime($transaction->trans_date)) }}</td>
									 <td>{{ $transaction->account->account_title.' - '.$transaction->account->account_currency }}</td>
									 <td class="text-right">{{ decimalPlace($transaction->amount, currency($transaction->account->account_currency)) }}</td>
									 <td>{{ $transaction->payment_method->name }}</td>
								 </tr>
							 @endforeach
						 </tbody>
					 </table>
				 </div>
			 </div> 
		  @endif
		  <!--END Related Transaction-->		
					 					 
					 <!--Quotation Note-->
					 @if($quotation->note  != '')
						<div class="invoice-note">{{ $quotation->note }}</div>
					 @endif
					 <!--End Quotation Note-->
					 
					 <!--Quotation Footer Text-->
					 @if(get_company_option('quotation_footer')  != '')
						<div class="invoice-note">{!! xss_clean(get_company_option('quotation_footer')) !!}</div>
					 @endif
					 <!--End Invoice Note-->
	</div>
</body>
</html>