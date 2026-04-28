@extends('layouts.app')

@section('content')
<style type="text/css">
#quotation-table td:nth-child(5){
	text-align: center !important;
}
</style>
<div class="row">
	<div class="col-12">
	
		<div class="card mt-2">
			<span class="d-none panel-title">{{ _lang('Quotation List') }}</span>

			<div class="card-body">
			  @php $currency = currency() @endphp
			  <table id="quotation-table" class="table table-bordered">
				<thead>
				  <tr>
					<th>{{ _lang('Nro reserva') }}</th>
					<th>{{ _lang('Quotation Date') }}</th>
					<th>{{ _lang('Quotation To') }}</th>
					<th class="text-right">{{ _lang('Grand Total') }}</th>
					<th class="text-right">{{ _lang('Interno') }}</th>
					<th class="text-right">{{ _lang('Modelo') }}</th>
					<th class="text-right">{{ _lang('Status') }}</th>
					<th class="text-center">{{ _lang('Action') }}</th>
				  </tr>
				</thead>
				<tbody>
				</tbody>
			  </table>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js-script')
<script>
	$(function() {
		
		
		$('#quotation-table thead tr').clone(true).appendTo('#quotation-table thead');
            $('#quotation-table thead tr:eq(1) th').each(function(i) {
                var title = $(this).text(); //es el nombre de la columna
                if (i != 0 && i != 3 && i != 7) {
					
					if (i == 6) {
							$(this).html('<select style="width:100%;" class="form-control filtros"><option value="">Todas</option> <option value="pendiente">Pendiente</option> <option value="anulada">Anulada</option><option value="convertida">Convertida</option></select>');
							$('.filtros', this).on('change', function () {
								  if (quotation_table.column(i).search() !== this.value) {
				
									quotation_table
										.column(i)
										.search(this.value)
										.draw();
								}
				
							});
					}else if(i == 1){
						$(this).html( '<input style="width:100%;" type="text" id="fecha_ingreso" name="fecha_ingreso" value="" class="form-control select-filter" placeholder="Search...'+title+'" />' );
					}else{
							$(this).html(
								'<input style="width:100%;" type="text" value="" class="form-control filtros" placeholder="Search...' +
								title + '" />');

							$('.filtros', this).on('change', function() {
								if (quotation_table.column(i).search() !== this.value) {

									quotation_table
										.column(i)
										.search(this.value)
										.draw();
								}

							});
					}
					
                } else {
						$(this).html('');
                }

            });
		
        var quotation_table = $('#quotation-table').DataTable({
			processing: true,
            serverSide: true,
            orderCellsTop: true,
            fixedHeader: true,
			bStateSave: true,
            bAutoWidth: false,
            ordering: false,
            searching: true,
			ajax: '{{ url('reservas/get_table_data') }}',
			"columns" : [
				{ data : "quotation_number", name : "quotation_number" },
				{ data : "quotation_date", name : "quotation_date" },
				{ data : "contact_name", name : "contact_name" },				
				{ data : "grand_total", name : "grand_total" },
				{ data : "car_id", name : "car_id" },
				{ data : "modelo", name : "modelo" },
				{ data : "status", name : "status" },
				{ data : "action", name : "action" },
			],
			dom: 'Bfrltip',
                buttons: [
				{
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
                        $('.filtros').val('');
						$('.select-filter').val(null).trigger('change');
							quotation_table.search('').columns().search('').draw();
                      }
                }],
			lengthMenu: [[ 20, 50, 500, 1000 ], [20,50, 500, 1000 ]],	
			responsive: true,
			"bStateSave": true,
			"bAutoWidth":false,	
			"ordering": false,
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
		
		 $('#fecha_ingreso').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            }
        });

         $('#fecha_ingreso').on('change', function(e) {
            let val = $(this).val();
            quotation_table.columns(1).search(val ? val : '', true, false );
            quotation_table.draw();
        });
    
    
        $('#fecha_ingreso').on('apply.daterangepicker', function(ev, picker) {
                let daterango =(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                    $(this).val(daterango);
                    quotation_table.columns(1).search(daterango);
                    quotation_table.draw();
            });

            $('#fecha_ingreso').on('cancel.daterangepicker', function(ev, picker) {
                $('#fecha_ingreso').val(null).trigger('change');    
        });
		
		    $('.dataTables_filter input')
    .unbind('keypress keyup input')
    .bind('change input', function (e) {
        if ($(this).val().length >= 3 && e.keyCode == 13) {
            table.search(this.value).draw();
        }
    });
  
  $('#quotation-table').on('processing.dt', function (e, settings, processing) {
    if (processing) {
       	inicioLoading();
    } else {
        closeLoading();
    }
});
		
		
    });
</script>
@endsection




