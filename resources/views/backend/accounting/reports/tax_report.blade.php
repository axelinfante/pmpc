@extends('layouts.app')

@section('content')
<style>
  .btn{margin-bottom: 2px !important;}
</style>
<div class="row">
	<div class="col-12">
		<div class="card">	
			<span class="d-none panel-title">{{ _lang('Tax Report') }}</span>
			<div class="card-body">
				<div class="report-params">
					<form class="validate" method="post" action="{{ url('reports/tax_report/view') }}">
						<div class="row">
              				{{ csrf_field() }}

						  	<div class="col-lg-3">
								<div class="form-group">
								<label class="control-label">{{ _lang('From') }}</label>						
									<input type="text" class="form-control datepicker" name="date1" id="date1" value="{{ isset($date1) ? $date1 : old('date1') }}" readOnly="true" required>
								</div>
							</div>

							<div class="col-lg-3">
								<div class="form-group">
									<label class="control-label">{{ _lang('To') }}</label>						
									<input type="text" class="form-control datepicker" name="date2" id="date2" value="{{ isset($date2) ? $date2 : old('date2') }}" readOnly="true" required>
								</div>
							</div>

							<div class="col-lg-2">
								<button type="submit" class="btn btn-primary btn-block btn-xs mt-28">{{ _lang('View Report') }}</button>
							</div>

							<div class="col-lg-2">
								<button type="button" class="btn btn-dark print btn-block btn-xs mt-28" data-print="report"><i class="ti-printer"></i> {{ _lang('Print') }}</button>
							</div>

						</form>
					</div>
				</div><!--End Report param-->
                
				@php 
				$date_format = get_company_option('date_format','Y-m-d');
				$currency = currency(); 
				$total_sales_tax = 0;
				$total_purcahse_tax = 0;
				$total_sales_return_tax = 0;
				$total_purchase_return_tax = 0;
				@endphp
				
				<div id="report">
					<div class="report-header">
						<h4>{{ _lang('Tax Report') }}</h4>
						<h5>{{ isset($date1) ? date($date_format, strtotime($date1)).' '._lang('to').' '.date($date_format, strtotime($date2)) : '-------------  '._lang('to').'  -------------' }}</h5>
					</div>

					<h5 class="mt-4 text-center">{{ _lang('SALES & SALES RETURN') }}</h5>
					<table class="table mt-2">
						<thead>
							<th>{{ _lang('Tax') }}</th>
							<th class="text-right">{{ _lang('Sales Subject to Tax') }}</th>
							<th class="text-right">{{ _lang('Tax Amount on Sales') }}</th>    
							<th class="text-right">{{ _lang('Sales Return Subject to Tax') }}</th>       
							<th class="text-right">{{ _lang('Tax Amount on Sales Return') }}</th>       
							<th class="text-right">{{ _lang('Net Tax Owing') }}</th>       
						</thead>
						<tbody>
							@if(isset($sales_taxes))
							@foreach($sales_taxes as $sales_tax)
							<tr>
								<td><b class="text-primary">{{ $sales_tax->tax_name }} ({{ $sales_tax->rate }} {{ $sales_tax->type == 'percent' ? '%' : '' }})</b></td>
								<td class="text-right">{{ decimalPlace($sales_tax->sales_amount, $currency) }}</td>
								<td class="text-right">{{ decimalPlace($sales_tax->sales_tax, $currency) }}</td>  
								<td class="text-right">{{ decimalPlace($sales_return_taxes[$loop->index]->sales_return_amount, $currency) }}</td>
								<td class="text-right">{{ decimalPlace($sales_return_taxes[$loop->index]->sales_return_tax, $currency) }}</td>
								<td class="text-right">{{ decimalPlace($sales_tax->sales_tax - $sales_return_taxes[$loop->index]->sales_return_tax, $currency)  }}</td> 
							</tr> 
							@php $total_sales_tax += $sales_tax->sales_tax; @endphp
							@php $total_sales_return_tax += $sales_return_taxes[$loop->index]->sales_return_tax; @endphp
							@endforeach
							<tr>
								<td><b>{{ _lang('Total') }}</b></td>
								<td class="text-right"></td>
								<td class="text-right"><b>{{ decimalPlace($total_sales_tax, $currency) }}</b></td>  
								<td class="text-right"></td>  
								<td class="text-right"><b>{{ decimalPlace($total_sales_return_tax, $currency) }}</b</td> 
								<td class="text-right"><b>{{ decimalPlace($total_sales_tax - $total_sales_return_tax, $currency) }}</b</td> 
							</tr> 
							@endif
						</tbody>
					</table>

					<h5 class="mt-5 text-center">{{ _lang('PURCHASES & PURCHASES RETURN') }}</h5>
					<table class="table mt-2">
						<thead>
							<th>{{ _lang('Tax') }}</th>
							<th class="text-right">{{ _lang('Purcahse Subject to Tax') }}</th>
							<th class="text-right">{{ _lang('Tax Amount on Purcahse') }}</th>    
							<th class="text-right">{{ _lang('Purchase Return Subject to Tax') }}</th>       
							<th class="text-right">{{ _lang('Tax Amount on Purchase Return') }}</th>       
							<th class="text-right">{{ _lang('Net Tax Paying') }}</th>       
						</thead>
						<tbody>
						@if(isset($purchase_taxes))
							@foreach($purchase_taxes as $purchase_tax)
							<tr>
								<td><b class="text-primary">{{ $sales_tax->tax_name }} ({{ $purchase_tax->rate }} {{ $purchase_tax->type == 'percent' ? '%' : '' }})</b></td>
								<td class="text-right">{{ decimalPlace($purchase_tax->purchase_amount, $currency) }}</td>
								<td class="text-right">{{ decimalPlace($purchase_tax->purchase_tax, $currency) }}</td>  
								<td class="text-right">{{ decimalPlace($purchase_return_taxes[$loop->index]->purchase_return_amount, $currency) }}</td>  
								<td class="text-right">{{ decimalPlace($purchase_return_taxes[$loop->index]->purchase_return_tax, $currency) }}</td> 
								<td class="text-right">{{ decimalPlace($purchase_tax->purchase_tax - $purchase_return_taxes[$loop->index]->purchase_return_tax, $currency)  }}</td> 
							</tr> 
							@php $total_purcahse_tax += $purchase_tax->purchase_tax; @endphp
							@php $total_purchase_return_tax += $purchase_return_taxes[$loop->index]->purchase_return_tax; @endphp
							@endforeach
							<tr>
								<td><b>{{ _lang('Total') }}</b></td>
								<td class="text-right"></td>
								<td class="text-right"><b>{{ decimalPlace($total_purcahse_tax, $currency) }}</b></td>  
								<td class="text-right"></td>  
								<td class="text-right"><b>{{ decimalPlace($total_purchase_return_tax, $currency) }}</b></td> 
								<td class="text-right"><b>{{ decimalPlace($total_purcahse_tax - $total_purchase_return_tax, $currency) }}</b></td> 
							</tr> 
							@endif
						</tbody>
					</table>
				</div><!--end Report DIV-->
			</div>
		</div>
	</div>
</div>

@endsection

@section('js-script')
<script>
(function($) {
	"use strict";

	document.title = $(".panel-title").html();
	$("#trans_type").val("{{ isset($dr_cr) ? $dr_cr : 'all' }}");
	
})(jQuery);
</script>
@endsection


