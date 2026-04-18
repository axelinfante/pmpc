@extends('layouts.app')

@section('content')
<style type="text/css">
#invoice-table td:nth-child(5), #invoice-table td:nth-child(6){
	text-align: center !important;
}

</style>

<div class="row">
	<div class="col-12">
	
		<div class="card mt-2">
			<span class="panel-title d-none">{{ _lang('Comisiones por ventas') }}</span>

			<div class="card-body">
				@php $currency = currency() @endphp
				<div class="row">
					<div class="col-lg-3 mb-2">
                     	<label>{{ _lang('Invoice Number') }}</label>
                     	<input type="text" class="form-control select-filter" name="invoice_number" id="invoice-number">
                    </div>	
					@if(strtolower(auth()->user()->role->name) != 'vendedor')
					<div class="col-lg-3 mb-2">
                     	<label>{{ _lang('Vendedores') }}</label>
						<select class="form-control select2 select-filter" name="vendedor">
                            <option value="">{{ _lang('Vendedores') }}</option>
							{{ create_option('users','id','name','',array('role_id=' => $rol)) }}
                     	</select>
                    </div>
					@endif
						<div class="col-lg-3 mb-2">
							<label>{{ _lang('Status') }}</label>
							<select class="form-control select2 select-filter" name="status" id="status">
								<option value="0">{{ _lang('No pagados') }}</option>
								<option value="1">{{ _lang('Pagado') }}</option>


							</select>
						</div>
					
                    {{--<div class="col-lg-3 mb-2">--}}
                     	{{--<label>{{ _lang('Status') }}</label>--}}
                     	{{--<select class="form-control select2 select-filter" data-placeholder="{{ _lang('Invoice Status') }}" name="status" multiple="true">--}}
							{{--<option value="Unpaid">{{ _lang('Unpaid') }}</option>--}}
							{{--<option value="Paid">{{ _lang('Paid') }}</option>--}}
							{{--<option value="Partially_Paid">{{ _lang('Partially Paid') }}</option>--}}
							{{--<option value="Canceled">{{ _lang('Canceled') }}</option>--}}
                     	{{--</select>--}}
                    {{--</div>	--}}

                    {{--<div class="col-lg-3">--}}
                     	{{--<label>{{ _lang('Date Range') }}</label>--}}
                     	{{--<input type="text" class="form-control select-filter" id="date_range" autocomplete="off" name="date_range">--}}
                    {{--</div>	--}}
	
                </div>

                <hr>
				
				<table id="invoice-table" class="table table-bordered">
					<thead>
						<tr>
							<th>
                               <input type="checkbox" class id="allComi">
                            </th>
							<th>{{ _lang('Cotizacion') }}</th>
							<th>{{ _lang('FechaVenta') }}</th>
							<th>{{ _lang('Cliente') }}</th>
							<th><center>{{ _lang('Piezas / Marca Modelo / Importe') }}</center></th>
							<th>{{ _lang('Monto Neto') }}</th>
							<th><center>{{ _lang('Anulado Importe / Detalle / Fecha') }}</center></th>
							<th>{{ _lang('% comis') }}</th>
							<th>{{ _lang('Importe liquidado') }}</th>
							<th>{{ _lang('Importe pagado / Fecha pago') }}</th>
							<th>{{ _lang('Observaciones') }}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
					<tfoot>
						<tr style="text-align:right">
							<th colspan="5">Totales:</th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
					</tfoot>
				</table>

                <hr>
{{-- <div class="row">

	<div class="col-md-6" id="total_monto_pagado">
		<h4>Monto total pagado : {{decimalPlace($total_monto_pagado, currency())}}</h4>
	</div>

	<div class="col-md-6" id="total_monto">
		<h4>Monto total deudor : {{decimalPlace($total_monto, currency())}}</h4>
	</div>

</div> --}}

			</div>
		</div>
	</div>
</div>

@endsection


@section('js-script')
<script>
	(function($) {
		let dtButtons = [];
		@if (auth()->user()->role->name == 'Gerencial' || auth()->user()->role->name == null)
		dtButtons.push({
			text: '<i class="ti-list-ol"></i> Pagar Seleccionadas', 
    			url: "#",
    			className: 'btn-danger',
				attr: {
                          	id: 'btn_seleccion'
            		},
				action: function (e, dt, node, config) {
				if ($('select[name=status]').val()==1){
					return;
				};	

				var ids = $(invoice_table.$('.form-check:checked').map(function (i, chk) {
				return $(chk).val();
				} ) ).get();

				if (ids.length === 0) {
					alert("Debe seleccionar un valor.");
				return;
				}
					if(confirm("Esta seguro de continuar con el proceso?"))
					{
					var myformData = new FormData();        
					myformData.append('_token', $('meta[name="csrf-token"]').attr('content'));
					myformData.append('ids', ids);
					$.ajax({
					url:"{{ route('invoices.comisiones_multiples') }}",
					method: 'post',
					processData: false,
					contentType: false,
					cache: false,
					data: myformData,
					enctype: 'multipart/form-data',
					success:function(data)
					{
						$('#invoice-table').DataTable().ajax.reload();
					}
					});
					}
				}
      } );

		dtButtons.push({
			text: '<i class="ti-align-justify"></i> Pagar Todas',
    			url: "#",
    			className: 'btn-primary',
				attr: {
                          	id: 'btn_todos'
            		},
				action: function (e, dt, node, config) {

					if ($('select[name=status]').val()==1){
						return;
					};	

				if(confirm("Esta seguro de continuar con el proceso?"))
				{
					var myformData = new FormData();        
					myformData.append('_token', $('meta[name="csrf-token"]').attr('content'));
					myformData.append('todas', true);
					
					if($('input[name=invoice_number]').val() != ''){
						myformData.append('invoice_number', $('input[name=invoice_number]').val());
					}

					if($('select[name=vendedor]').val() != ''){
						myformData.append('vendedor', $('select[name=vendedor]').val());
					}
					if($('select[name=status]').val() != ''){
						myformData.append('status', $('select[name=status]').val());
					}

					$.ajax({
                    url:"{{ route('invoices.comisiones_multiples') }}",
					method: 'post',
    				processData: false,
    				contentType: false,
    				cache: false,
					data: myformData,
					enctype: 'multipart/form-data',
                    success:function(data)
                    {
                        $('#invoice-table').DataTable().ajax.reload();
                    }
               	 });
				}
			}	

		});
		@endif
        var invoice_table = $('#invoice-table').DataTable({
            processing: true,
            serverSide: true,
			ajax: ({
				url: '{{ url('invoices/comisiones/table') }}',
				method: "POST",
				data: function (d) {

					d._token =  $('meta[name="csrf-token"]').attr('content');
					
					if($('input[name=invoice_number]').val() != ''){
						d.invoice_number = $('input[name=invoice_number]').val();
					}

					if($('select[name=vendedor]').val() != ''){
						d.vendedor = $('select[name=vendedor]').val();
					}
					if($('select[name=status]').val() != ''){
						d.status = $('select[name=status]').val();
					}
				},
				 error: function (request, status, error) {
					console.log(request.responseText);
				 }
			}),
			dom: 'Bfrtip',
			buttons: {
       			buttons:  dtButtons
    		},
			 drawCallback: function (settings) {
		        var api = this.api();
                    $(api.column(8).footer()).html(settings.json.total_importe);
					$(api.column(9).footer()).html(settings.json.total_pagado);
    		},
			"columns" : [
				{ data : "checkbox", name : "checkbox" },
				{ data : "invoice_number", name : "invoice_number" },
				{ data : "invoice_venta", name : "invoice_venta" },
				{ data : "cliente", name : "cliente" },
				{ data : "resumen_pieza", name : "resumen_pieza" },
				{ data : "venta_neta", name : "venta_neta" },
				{ data : "anulado", name : "anulado" },
				{ data : "comision", name : "comision" },//6
				{ data : "importe_liq", name : "importe_liq" },
				{ data : "importe_pag", name : "importe_pag" },
				{ data : "observaciones", name : "observaciones" },
			],
			"bStateSave": true,
			"bAutoWidth":false,	
			"ordering": false,
			"searching": false,
			"language": {
				"decimal":        "",
				"emptyTable":     "{{ _lang('No Data Found') }}",
				"info":           "{{ _lang('Showing') }} _START_ {{ _lang('to') }} _END_ {{ _lang('of') }} _TOTAL_ {{ _lang('Entries') }}",
				"infoEmpty":      "{{ _lang('Showing 0 To 0 Of 0 Entries') }}",
				"infoFiltered":   "(filtered from _MAX_ total entries)",
				"infoPostFix":    "",
				"thousands":      ",",
				"lengthMenu":     "{{ _lang('Show') }} _MENU_ {{ _lang('Entries') }}",
				"loadingRecords": "{{ _lang('Loading...') }}",
				"processing":     "{{ _lang('Processing...') }}",
				"search":         "{{ _lang('Search') }}",
				"zeroRecords":    "{{ _lang('No matching records found') }}",
				"paginate": {
					"first":      "{{ _lang('First') }}",
					"last":       "{{ _lang('Last') }}",
					"next":       "{{ _lang('Next') }}",
					"previous":   "{{ _lang('Previous') }}"
				}
			} 
        });
		

		$('#invoice-number').on('change', function(e) {
			invoice_table.draw();
		});
		
		$('.select-filter').on('change', function(e) {
			invoice_table.draw();
		});
		
		$('#date_range').daterangepicker({
			autoUpdateInput: false,
			locale: {
			  format: 'DD-MM-YYYY',
			  cancelLabel: 'Clear'
			}
		});

		$('#date_range').on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
			invoice_table.draw();
		});

		$('#date_range').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
			invoice_table.draw();
		});

		$('#status').on('change', function(e) {
			if ($('select[name=status]').val()==0){
				$( "#btn_todos" ).prop( "disabled", false )
				$( "#btn_seleccion" ).prop( "disabled", false )
			}else{
				$( "#btn_todos" ).prop( "disabled", true );
				$( "#btn_seleccion" ).prop( "disabled", true );
			}
			
		});
		
		$('#allComi').click(function(e) {
                let isCheked = $(this).is(':checked');
                if (isCheked) {
                    $('input[type="checkbox"]').prop('checked', 'true');
                } else {
                    $('input[type="checkbox"]').removeAttr('checked');
                }
            });
		
    })(jQuery);
</script>
@endsection


