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
        $date_format = get_company_option('date_format', 'Y-m-d');
    @endphp
    <div class="report-params">
        <form class="validate" method="post" action="{{ url('reports/report_by_day') }}">
            <div class="row">
                {{ csrf_field() }}

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Compañia</label>
						<select class="form-control select2" name="company[]" multiple required>
							@foreach(\App\Company::whereIn('business_name', ['Paternal', 'Pentacar'])->get() as $m)
								@php
									$selectedValues = \Illuminate\Support\Arr::wrap(old('company', isset($company) ? $company : []));
								@endphp
								<option value="{{ $m->id }}" {{ in_array($m->id, $selectedValues) ? 'selected' : '' }}>
									{{ $m->business_name }}
								</option>
							@endforeach
						</select>
                        <!--<select class="form-control select2" name="company[]" multiple required>
							{{ create_option('companies', 'id', 'business_name', isset($company) ? $company : old('company'), ['business_name=' => 'Paternal', 'or business_name=' => 'Pentacar']) }}
						</select>-->
                    </div>
                </div>
                <div class="col-md-3">
                    <label>Ubicación</label>
                    <select class="form-control  select-filter" name="idUbicacion">
                        <option value="">{{ _lang('Ubicación') }}</option>
                        {{ create_option('lugar_entregas', 'id', 'nombre', isset($idUbicacion) ? $idUbicacion : old('idUbicacion')) }}
                    </select>
                </div>
                <div class="col-md-3">
						<div class="form-group">
							<label class="control-label">{{ _lang('Account Currency') }}</label>
							<!-- Se agrega el atributo 'multiple' y los corchetes '[]' al name -->
							<select class="form-control select2" name="account_currency[]" id="account_currency" multiple required>
								@php
									// Consolidamos el valor previo (del formulario fallido o de la base de datos) como un array seguro
									$selectedCurrencies = \Illuminate\Support\Arr::wrap(old('account_currency', isset($account_currency) ? $account_currency : []));
								@endphp

								<!-- Removido el "Select One" ya que en un multiselect no tiene sentido lógico -->
								<option value="ARS" {{ in_array('ARS', $selectedCurrencies) ? 'selected' : '' }}>
									Argentine peso - ARS ($)
								</option>
								<option value="USD" {{ in_array('USD', $selectedCurrencies) ? 'selected' : '' }}>
									United States dollar - USD ($)
								</option>
							</select>
						</div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">Cuenta</label>
                        <select class="form-control " name="account">
                            <option value="">{{ _lang('Select One') }}</option>
                            {{ create_option('accounts', 'id', 'account_title', isset($account) ? $account : old('account')) }}
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="control-label">{{ _lang('From') }}</label>
                        <input type="text" class="form-control datepicker" name="date1" id="date1"
                            value="{{ isset($date1) ? $date1 : old('date1') }}" readOnly="true" required>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label class="control-label">{{ _lang('To') }}</label>
                        <input type="text" class="form-control datepicker" name="date2" id="date2"
                            value="{{ isset($date2) ? $date2 : old('date2') }}" readOnly="true" required>
                    </div>
                </div>

                <div class="col-md-2">
                    <button type="submit"
                        class="btn btn-primary btn-xs btn-block mt-26">{{ _lang('View Report') }}</button>
                </div>
            </div>
        </form>

    </div><!--End Report param-->
    <!--Start Card-->


    <div class="row my-3">
        <div class="col-md">
			<div class="report-header">
				<h4>{{ _lang('Account Statement') }} ({{ implode(', ', \Illuminate\Support\Arr::wrap($account_currency)) }})</h4>
				<h5>{{ isset($date1) ? date($date_format, strtotime($date1)).' '._lang('to').' '.date($date_format, strtotime($date2)) : '-------------  '._lang('to').'  -------------' }}</h5>
			 </div>
<table id="reporteDiarioTable" class="table table-bordered table-striped display width-100">
    <thead>
        <tr>
            <th>Compañía</th>
            <th>Fecha</th>
            <th>Cuenta</th>
            <th>Método Pago</th>
            <th>Moneda</th>
            <th>Crédito ARS</th>
            <th>Débito ARS</th>
            <th>Saldo ARS</th>
            <th>Crédito USD</th>
            <th>Débito USD</th>
            <th>Saldo USD</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($current_day_income_expense_by_account) && count($current_day_income_expense_by_account) > 0)
            @php
                // Inicializamos los acumuladores generales
                $totCreditoARS = 0;
                $totDebitoARS  = 0;
                $totSaldoARS   = 0;
                
                $totCreditoUSD = 0;
                $totDebitoUSD  = 0;
                $totSaldoUSD   = 0;
            @endphp

            @foreach($current_day_income_expense_by_account as $row)
                @php
                    // Sumamos los valores a sus respectivos acumuladores
                    $totCreditoARS += (float) $row['creditoARS'];
                    $totDebitoARS  += (float) $row['debitoARS'];
                    $totSaldoARS   += (float) $row['saldoARS'];
                    
                    $totCreditoUSD += (float) $row['creditoUSD'];
                    $totDebitoUSD  += (float) $row['debitoUSD'];
                    $totSaldoUSD   += (float) $row['saldoUSD'];
                @endphp
                <tr>
                    <td>{{ $row['business_name'] }}</td> 
                    <td>{{ $row['trans_date'] }}</td>
                    <td>{{ $row['account_title'] }}</td>
                    <td>{{ $row['payment_method'] }}</td>
                    <td>
                        <span class="badge {{ $row['account_currency'] === 'USD' ? 'badge-success' : 'badge-primary' }}">
                            {{ $row['account_currency'] }}
                        </span>
                    </td>
                    <td class="text-right">{{ number_format($row['creditoARS'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['debitoARS'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['saldoARS'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['creditoUSD'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['debitoUSD'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['saldoUSD'], 2) }}</td>
                </tr>
            @endforeach
        @endif 
    </tbody>
    
    <!-- ESTRUCTURA TFOOT REQUERIDA POR DATATABLES PARA LA EXPORTACIÓN DE TOTALES -->
    @if(isset($current_day_income_expense_by_account) && count($current_day_income_expense_by_account) > 0)
    <tfoot>
        <tr style="font-weight: bold; background-color: #d1ecf1; color: #0c5460;">
            <td colspan="5" class="text-right">{{ _lang('Total General') }}:</td>
            <td class="text-right">{{ number_format($totCreditoARS, 2) }}</td>
            <td class="text-right">{{ number_format($totDebitoARS, 2) }}</td>
            <td class="text-right {{ $totSaldoARS >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totSaldoARS, 2) }}</td>
            <td class="text-right">{{ number_format($totCreditoUSD, 2) }}</td>
            <td class="text-right">{{ number_format($totDebitoUSD, 2) }}</td>
            <td class="text-right {{ $totSaldoUSD >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totSaldoUSD, 2) }}</td>
        </tr>
    </tfoot>
    @endif
</table>

        </div>

    </div>
@endsection

@section('js-script')
    <script>
	
	$('.select2').select2({
                multiple: true,
                closeOnSelect: false//,
                //placeholder: "Select a " + title
              });
			  
			  var table = $('#reporteDiarioTable').appTable({
					title:"Estado de Cuenta",
					serverSide:false,
					visibleButtonsFilter:false,
					visibleButtons: {
					reset: true,
					excel: false,
					print: false
					},
					customButtons: [
						{
							extend: 'excelHtml5',
							text: '<i class="fas fa-file-excel"></i> Excel',
							className: 'btn btn-sm',
							title: 'Reporte Diario de Caja',
							footer: true
						},
						]
				});
	/*
        report_table = $(".report-table-2").DataTable({
            responsive: true,
            "bAutoWidth": false,
            "ordering": false,
            "lengthChange": false,
            "language": {
                "decimal": "",
                "emptyTable": $lang_no_data_found,
                "info": $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " + $lang_entries,
                "infoEmpty": $lang_showing_0_to_0_of_0_entries,
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": $lang_show + " _MENU_ " + $lang_entries,
                "loadingRecords": $lang_loading,
                "processing": $lang_processing,
                "search": $lang_search,
                "zeroRecords": $lang_no_matching_records_found,
                "paginate": {
                    "first": $lang_first,
                    "last": $lang_last,
                    "next": $lang_next,
                    "previous": $lang_previous
                },
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                },
                "buttons": {
                    copy: $lang_copy,
                    excel: $lang_excel,
                    pdf: $lang_pdf,
                    print: $lang_print,
                }
            },
            //dom: 'Blfrtip',
            buttons: [
                'copy', 'excel', 'pdf',
                {
                    extend: 'print',
                    title: '',
                    customize: function(win) {
                        $(win.document.body)
                            .css('font-size', '10pt')
                            .prepend(
                                '<div class="text-center">' +
                                $(".report-header").html() +
                                '</div>'
                            );

                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');

                    }
                }
            ],
        });

        report_table.buttons().container().appendTo('#DataTables_Table_1_wrapper .col-md-6:eq(0)');*/
    </script>
@endsection
