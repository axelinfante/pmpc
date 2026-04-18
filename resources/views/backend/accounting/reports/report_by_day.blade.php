@extends('layouts.app')

@section('content')

@if (\Session::has('paypal_success'))
  <div class="alert alert-success text-center">
	<b>{{ \Session::get('paypal_success') }}</b>
  </div>
  <br>
@endif

@php
$currency = currency();
$date_format = get_company_option('date_format','Y-m-d');
@endphp
<div class="report-params">
	<form class="validate" method="post" action="{{ url('reports/report_by_day') }}">
		<div class="row">
			  {{ csrf_field() }}
			
			<div class="col-md-3">
				<div class="form-group">
				<label class="control-label">Compañia</label>						
				  <select class="form-control select2" name="company" required>
						<option value="">{{ _lang('Select One') }}</option>
						{{ create_option("companies","id","business_name",isset($company) ? $company : old('company'),array("business_name="=>'Paternal',"or business_name="=>'Pentacar')) }}
				  </select>
				</div>
			</div>

			<div class="col-md-3">
				<div class="form-group">
					<label class="control-label">{{ _lang('From') }}</label>					
					<input type="text" class="form-control datepicker" name="date1" id="date1" value="{{ isset($date1) ? $date1 : old('date1') }}" readOnly="true" required>   
				</div>
			</div>

			<div class="col-md-3">
				<div class="form-group">
					<label class="control-label">{{ _lang('To') }}</label>
					<input type="text" class="form-control datepicker" name="date2" id="date2" value="{{ isset($date2) ? $date2 : old('date2') }}" readOnly="true" required>						
				</div>
			</div>
			
			<div class="col-md-3">
				<button type="submit" class="btn btn-primary btn-xs btn-block mt-26">{{ _lang('View Report') }}</button>
			</div>
		</form>

	</div>
</div><!--End Report param-->
<!--Start Card-->


<div class="row my-3">
	<div class="col-md">
		<h4>Movimientos Pesos</h4>
		<table class="table table-bordered report-table">
			<thead>
				<th>Cuenta</th>
				<th>Metodo de pago</th>
				<th class="text-right">Ingresos</th>    
			<th class="text-right">Gastos</th>      
			</thead>
			<tbody>
			 
			@if(isset($current_day_income_expense_by_account))
				@php
					// $currency = currency($acc->account_currency);
					 $ingreso = 0;
					 $egreso = 0;
				@endphp
		{{-- {{ dd($current_day_income_expense_by_account) }} --}}
			@foreach($current_day_income_expense_by_account as $report) 
				@if( $report['totalIngreso'] == 0 && $report['totalEgreso'] == 0 )					
				   @php continue; @endphp
				@endif
				<tr>
				   <td>{{ $report['account'] }}</td>
				   <td>{{ $report['payment_method'] }}</td>
				   <td class="text-right">{{ $report['totalIngreso'] != 0 ? decimalPlace($report['totalIngreso'], $currency) : "" }}</td>
				   <td class="text-right">{{ $report['totalEgreso'] != 0 ? decimalPlace($report['totalEgreso'], $currency) : "" }}</td>
				</tr>
			 @php $ingreso += (float)$report['totalIngreso']; $egreso += (float)$report['totalEgreso'];  @endphp
			 @endforeach
			 @php $a_dividirIngreso = (float)$a_dividir->totalIngreso / 2; $a_dividirEgreso = (float) $a_dividir->totalEgreso / 2;  @endphp
			 <tr>
				<td><b> {{ _lang('Total dividido') }} </b></td>
				<td class="text-right">
					<b>Todos</b>
				</td>
				
				<td class="text-right"><b>{{ decimalPlace($a_dividirIngreso, $currency) }}</b></td>
				<td class="text-right"><b>{{ decimalPlace($a_dividirEgreso , $currency) }}</b></td>
			</tr>
				<tr>
					<td></td>
					<td>{{ _lang('Total') }}</td>
					<td class="text-right"><b>{{ decimalPlace($ingreso + $a_dividirIngreso, $currency) }}</b></td>
					<td class="text-right"><b>{{ decimalPlace($egreso + $a_dividirEgreso, $currency) }}</b></td>
				</tr>

				
			@endif
			</tbody>
		</table>
	</div>

	<div class="col-md ml-md-3">
		<h4>Movimientos Usd</h4>
		<table class="table table-bordered report-table-2">
			<thead>
				<th>Cuenta</th>
				<th>Metodo de pago</th>
				<th class="text-right">Ingresos</th>    
			<th class="text-right">Gastos</th>      
			</thead>
			<tbody>
			 
			@if(isset($current_day_income_expense_by_account_usd))
				@php
					// $currency = currency($acc->account_currency);
					 $ingresoUSD = 0;
					 $egresoUSD = 0;
				@endphp
	
			@foreach($current_day_income_expense_by_account_usd as $report) 
				@if( $report['totalIngreso'] == 0 && $report['totalEgreso'] == 0 )					
				   @php continue; @endphp
				@endif
				<tr>
				   <td>{{ $report['account'] }}</td>
				   <td>{{ $report['payment_method'] }}</td>
				   <td class="text-right">{{ $report['totalIngreso'] != 0 ? decimalPlace($report['totalIngreso'], $currency) : "" }}</td>
				   <td class="text-right">{{ $report['totalEgreso'] != 0 ? decimalPlace($report['totalEgreso'], $currency) : "" }}</td>
				</tr>
			 @php $ingresoUSD += (float)$report['totalIngreso']; $egresoUSD += (float)$report['totalEgreso'];  @endphp
			 @endforeach
			 @php $a_dividirIngreso = (float)$a_dividir_usd->totalIngreso / 2; $a_dividirEgreso = (float) $a_dividir_usd->totalEgreso / 2;  @endphp
			 <tr>
				<td><b> {{ _lang('Total dividido') }} </b></td>
				<td class="text-right">
					<b>Todos</b>
				</td>
				
				<td class="text-right"><b>{{ decimalPlace($a_dividirIngreso, $currency) }}</b></td>
				<td class="text-right"><b>{{ decimalPlace($a_dividirEgreso , $currency) }}</b></td>
			</tr>
				<tr>
					<td></td>
					<td>{{ _lang('Total') }}</td>
					<td class="text-right"><b>{{ decimalPlace($ingresoUSD + $a_dividirIngreso, $currency) }}</b></td>
					<td class="text-right"><b>{{ decimalPlace($egresoUSD + $a_dividirEgreso, $currency) }}</b></td>
				</tr>
			@endif
			</tbody>
		</table>
		<div>

		</div>
	</div>
</div>




@endsection

@section('js-script')
	<script>
		report_table = $(".report-table-2").DataTable({
		responsive: true,
		"bAutoWidth":false,
		"ordering": false,
		"lengthChange": false,
		"language": {
		   "decimal":        "",
		   "emptyTable":     $lang_no_data_found,
		   "info":           $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " + $lang_entries,
		   "infoEmpty":      $lang_showing_0_to_0_of_0_entries,
		   "infoFiltered":   "(filtered from _MAX_ total entries)",
		   "infoPostFix":    "",
		   "thousands":      ",",
		   "lengthMenu":     $lang_show + " _MENU_ " + $lang_entries,
		   "loadingRecords": $lang_loading,
		   "processing":     $lang_processing,
		   "search":         $lang_search,
		   "zeroRecords":    $lang_no_matching_records_found,
		   "paginate": {
			  "first":      $lang_first,
			  "last":       $lang_last,
			  "next":       $lang_next,
			  "previous":   $lang_previous
		  },
		  "aria": {
			  "sortAscending":  ": activate to sort column ascending",
			  "sortDescending": ": activate to sort column descending"
		  },
		  "buttons": {
			  copy: $lang_copy,
			  excel: $lang_excel,
			  pdf:   $lang_pdf,
			  print: $lang_print,
		  }
	  },
	  //dom: 'Blfrtip',
	  buttons: [
	  'copy', 'excel', 'pdf',
	  {
			extend: 'print',
			title: '',
			customize: function ( win ) {
				$(win.document.body)
					.css( 'font-size', '10pt' )
					.prepend(
						'<div class="text-center">'+
						$(".report-header").html()+
						'</div>'
					);

				$(win.document.body).find( 'table' )
					.addClass( 'compact' )
					.css( 'font-size', 'inherit' );
					 
			}
		}
	  ],
	});
			
	report_table.buttons().container().appendTo('#DataTables_Table_1_wrapper .col-md-6:eq(0)');
	</script>
@endsection
