@extends('layouts.app')

@section('content')

<div class="row">
	<div class="col-12">
	    <a class="btn btn-primary btn-xs ajax-modal" data-reload="true" data-title="{{ _lang('Add Product') }}" href="{{ route('item.create')
	    }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
	    <a class="btn btn-dark btn-xs" aria-disabled="true" href="{{ route('products.create') }}?predefinido=1"> {{ _lang('Actualizar productos y autos') }}</a>

		<div class="card mt-2">
			<span class="panel-title d-none">{{ _lang('List Product') }}</span>
			<div class="card-body">
			<div class="table-responsive">
				<table id="data-table" class="table table-bordered table-striped">
					<thead>
					  <tr>
							<th>{{ _lang('Product') }}</th>
							<th>{{ _lang('Predefinido') }}</th>
							<th>{{ _lang('Activo') }}</th>
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
</div>

@endsection

@section('js-script')
<script>
        $(function() {
			
		var table = $("#data-table").appTable({
		title:"Lista Productos",
        ajax: {
            url : "{{ route('productos_comunes') }}",
            method: "GET",
				data: function (d) {
						d._token =   "{{ csrf_token() }}";	
					if($('select[name=filtrado]').val() != ''){
						d.filtrado = $('select[name=filtrado]').val();
					}
			}
        },
		customButtons: [{
                   text: 'Filtrar por: ' +
                      '<select id="filtrado" name="filtrado"  class="form-control-sm select2">' +
                      '<option value="predefinido">Predefinidos</option>' +
                      '<option value="activos">Activos</option>' +
                      '<option value="inactivos">Inactivos</option>' +
                      '</select>',
                className: 'botones-custom',
                action: function ( e, dt, node, config ) {
						}
					}
				],
		columnFilters: ['input', { 
            type: 'select', 
            data: ['Si','No'] // Array simple
        },{ 
            type: 'select', 
            data: ['Si','No'] // Array simple
        }], 
        columns: [
            {data: 'item_name', name: 'item_name'},
            {data: 'allCar', name: 'allCar'},
            {data: 'activo', name: 'activo'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
    });	
	
    // Configurar jQuery para enviar token CSRF en todas las solicitudes AJAX
	   /* table = $('#data-table').DataTable({
                processing:true,
                serverSide:true,
				//scrollX: true,
				//responsive: true, // Enable responsiveness
				ajax: ({
				url : "{{ route('productos_comunes') }}",
				method: "GET",
				data: function (d) {
						d._token =   "{{ csrf_token() }}";	
					if($('select[name=filtrado]').val() != ''){
						d.filtrado = $('select[name=filtrado]').val();
					}
				},
				error: function (request, status, error) {
					//console.log(request.responseText);
					}
				}),
				dom: 'Bfrltip',
				buttons: [
					{ extend: 'excelHtml5',
					title: 'productos' // Sets the exported file name
					//'excel'
					},
					{
                // Botón personalizado que contiene el select
                text: 'Filtrar por: ' +
                      '<select id="filtrado" name="filtrado"  class="form-control-sm select2">' +
                      '<option value="predefinido">Predefinidos</option>' +
                      '<option value="activos">Activos</option>' +
                      '<option value="inactivos">Inactivos</option>' +
                      '</select>',
                className: 'botones-custom',
                action: function ( e, dt, node, config ) {
                    // Acción al hacer clic en el botón, no en el select
						}
					}
				],
				orderCellsTop: true,
				pageLength: 25,
				lengthMenu: [[ 25, 50, 500 ], [25,50, 500]],
				autoWidth: false,
			columns: [
                    { data: 'item_name', name: 'item_name' },
                    { data: 'allCar', name: 'allCar' },
                    { data: 'activo', name: 'activo' },
                    { data: 'action', name: 'action' },
                ],
			drawCallback : function(settings) {
          // $('.select2').select2({
                // Opciones de select2 aquí
              //  width: '100%'
            //});
			}
        });*/
		
		
		$('#data-table tbody').on('click', '.button-delete', function() {
			var row = $(this).closest('tr');
			var data = table.row(row).data();
			var recordId = data.id;
			
			if (confirm('¿Deseas eliminar esta fila?')) {
				
				$.ajax({
				//url: "/item/" + recordId,
				url: "{{ route('item.store') }}"+'/'+recordId,
				type: 'DELETE',
				data: {
					_token: $('meta[name="csrf-token"]').attr('content')
					},
				success: function(response) {
                // Reload the table data
                //table.ajax.reload(null, false); // false keeps current paging
				if (table) {
					table.ajax.reload(null, false);
				}
            }
        });

		}
			
	});
		
		$('#filtrado').on('change', function(e) {
					e.preventDefault();
					table.draw();
           			return false; //for old browsers 
			});
		
		
/*		$('.dataTables_filter input')
			.unbind('keypress keyup input')
			.bind('keyup input', function (e) {
				//console.log($(this).val());
				 var code = e.keyCode || e.which;
				 if ($(this).val().length >= 2 && code === 13) {
					table.search(this.value).draw();
				}
				
			});*/
		
		
	});	
	
	
	

		
		
		 
	
    </script>
@endsection





