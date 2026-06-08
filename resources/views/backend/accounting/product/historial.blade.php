@extends('layouts.app')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/lozad/dist/lozad.min.js"></script>
<style>
table.dataTable {
            table-layout: fixed !important;
            width: 100% !important;
        }
        table.dataTable td {
            white-space: normal !important;
            overflow-wrap: break-word !important;
            word-wrap: break-word !important;
        }
</style>
@section('content')

<div class="row">
	<div class="col-12">
	    {{--<a class="btn btn-primary btn-xs" data-title="{{ _lang('Add Product') }}" href="{{ route('products.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
	    <a class="btn btn-dark btn-xs" href="{{ route('products.import') }}"><i class="ti-import"></i> {{ _lang('Import') }}</a>--}}

		<div class="card mt-2">
			<span class="panel-title d-none">{{ _lang('Historial productos') }}</span>
			
			
			<div class="card-body">
				<table id="table-data-historial" class="table table-bordered">
					<thead>
					  <tr>
						<th>ID de producto</th>
						<th>{{ _lang('Fecha Creacion') }}</th>
							<th>{{ _lang('Nro Interno') }}</th>
							<th>{{ _lang('Product') }}</th>
							
							<th class="text-right">{{ _lang('Marca Modelo') }}</th>
							{{-- <th class="text-right">{{ _lang('nº oblea') }}</th> --}}
							<th class="text-right">{{ _lang('Depósito') }}</th>
							<th class="text-right">{{ _lang('Ubicación') }}</th>
							<th class="text-right">{{ _lang('Descripción') }}</th>
							{{--<th class="text-right">{{ _lang('Product Price') }}</th>--}}
							{{--<th>{{ _lang('Product Unit') }}</th>--}}
							<th class="text-right">{{ _lang('Usuario') }}</th>
							<th style="width: 250px;min-width: 250px" class="text-center">{{ _lang('Informe') }}</th>
							 <th class="text-center notexport">{{ _lang('Action') }}</th> 
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
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


			var table = $('#table-data-historial').appTable({
					title:"Historial Producto",
					ajax: "{{ url('products/historial') }}",
					visibleButtonsFilter:false,
					visibleButtons: {
					reset: true,
					excel: true,
					print: false
					},
				   columns: [
                    { data: 'id', name: 'id'},
                    { data: 'created_at', name: 'created_at' },
                    { data: 'interno', name: 'interno' },
                    { data: 'productItem', name: 'productItem' },
                    { data: 'marcamodelo', name: 'marcamodelo' },
					{ data: 'deposito', name: 'deposito' },
					{ data: 'ubicacion', name: 'ubicacion' },
					{ data: 'description', name: 'description' },
					{ data: 'usuario', name: 'usuario' },
					{ data: 'informe', name: 'informe' },
                    { data: 'action', name: 'action', orderable: false}

                ],
				});


/*
            var table = $('#table-data-historial').DataTable({
                processing:true,
                serverSide:true,
                ajax: "{{ url('products/historial') }}",
                pageLength: 25, 
				lengthMenu: [10, 25, 50, 100],
                //width: "auto",
				//autoWidth: false,
				scrollX: true,
				orderCellsTop: true,
				fixedHeader: true,
                columns: [
                    { data: 'id', name: 'id'},
                    { data: 'created_at', name: 'created_at' },
                    { data: 'interno', name: 'interno' },
                    { data: 'productItem', name: 'productItem' },
                    { data: 'marcamodelo', name: 'marcamodelo' },
					{ data: 'deposito', name: 'deposito' },
					{ data: 'ubicacion', name: 'ubicacion' },
					{ data: 'description', name: 'description' },
					{ data: 'usuario', name: 'usuario' },
					{ data: 'informe', name: 'informe' },
                    { data: 'action', name: 'action', orderable: false}

                ],
            order: [
                [0, 'desc']
            ],
            dom: 'Blfrtip',
            orderCellsTop: true,
            buttons: [
                {
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
						table.search('').columns().search('').draw();
                    	$('#table-data-historial input').val('');
                           }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Exportar a Excel',
                    title: 'Historicos de Productos',
                    exportOptions: {
                        columns: ':visible'
                    }
                   ,action: newexportaction
                }
             ],
            });
*/

      /*      $('#table-data-historial thead tr').clone().prependTo('#table-data-historial thead');
                $('#table-data-historial thead tr:eq(0) th').each(function(i) {
                var title = $(this).text();
                if (title == 'Acción') {
                    $(this).hide();
                }

               if(i == 1) {

                    $(this).html( '<input type="text" id="fecha_ingreso" name="fecha_ingreso" value="" class="form-control select-filter" placeholder="Search...'+title+'" />' );
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
            table.columns(1).search(val ? val : '', true, false );
            table.draw();
        });
    
    
        $('#fecha_ingreso').on('apply.daterangepicker', function(ev, picker) {
                let daterango =(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                    $(this).val(daterango);
                    table.columns(1).search(daterango);
                    table.draw();
            });

            $('#fecha_ingreso').on('cancel.daterangepicker', function(ev, picker) {
                $('#fecha_ingreso').val(null).trigger('change');    
        });


        function newexportaction(e, dt, button, config) {

           this.processing( true );
         var self = this;
         var oldStart = dt.settings()[0]._iDisplayStart;
         dt.one('preXhr', function (e, s, data) {
             // Just this once, load all data from the server...
             data.start = 0;
             //data.length = 2147483647;
             data.length = -1;
             dt.one('preDraw', function (e, settings) {
                 // Call the original action function
                 if (button[0].className.indexOf('buttons-copy') >= 0) {
                     $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
                 } else if (button[0].className.indexOf('buttons-excel') >= 0) {
                     $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
                         $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
                         $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
                 } else if (button[0].className.indexOf('buttons-csv') >= 0) {
                     $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
                         $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
                         $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
                 } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
                     $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
                         $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
                         $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
                 } else if (button[0].className.indexOf('buttons-print') >= 0) {
                     $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
                 }
                 dt.one('preXhr', function (e, s, data) {
                     // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                     // Set the property to what it was before exporting.
                     settings._iDisplayStart = oldStart;
                     data.start = oldStart;
                 });
                 // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
                 setTimeout(dt.ajax.reload, 0);
                 // Prevent rendering of the full data to the DOM
                 return false;
             });
         });
         // Requery the server with the new one-time export settings
         dt.ajax.reload();
         this.processing( false );
     }
*/
           
        });
		


const observer = lozad('.lozad', {
    rootMargin: '10px 0px', 
    threshold: 0.1,         
    load: function(el) {
		if (el.nodeName.toLowerCase() === 'video') {
            if (el.dataset.src) {
                el.src = el.dataset.src;
            }
            const sources = el.querySelectorAll('source');
            if (sources.length > 0) {
                sources.forEach(source => {
                    source.src = source.dataset.src;
                });
            }
			  el.load(); 
		}else{
			  el.src = el.dataset.src;
		}
    },
    loaded: function(el) {
        el.classList.add('fade-in');
		
		
    }
});
	
	$("#main_modal").on('show.bs.modal', function () {
			observer.observe(); 
	 });
		
    </script>
       
@endsection


