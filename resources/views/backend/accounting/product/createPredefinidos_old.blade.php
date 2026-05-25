@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-12">
	<div class="card">
	<span class="panel-title d-none">{{ _lang('Add Product') }}</span>

	<div class="card-body">
	  <form name="formulario_create" id="formulario_create" method="post" class="validate" autocomplete="off" action="{{ route('table.detalle.post') }}">
		{{ csrf_field() }}
		    <div class="row">
			
			<div class="col-md-12">
			 <div class="form-group">
                                    <label class="control-label">{{ _lang('Nº interno') }}</label>
                                    <select id="nro_interno" name="nro_interno" required class="form-control select2">
                                        @foreach ($nro_interno_datos as $interno_row)
                                                        <option value="{{ $interno_row->id }}">{{ nroInternoAlias($interno_row->company_id,$interno_row->tipo_vehiculo,$interno_row->id) }}</option>
                                        @endforeach
                                    </select>
              </div>
			  </div>
			  
			  			  <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Deposito</label>
                                    <select id="idDeposito" name="idDeposito" required class="form-control select2">
                                        <option value="">Seleccionar</option>
                                        {{ create_option('lugar_entregas', 'id', 'nombre', old('idDeposito', auth()->user()->location)) }}
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Ubicación') }}</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion"
                                        value="{{ old('ubicacion') }}">
                                </div>
                            </div>

			 
			  

				<div class="col-md-12">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Description') }}</label>						
					<textarea class="form-control" name="description">{{ old('description') }}</textarea>
				  </div>
				</div>
			
			
			 <div class="col-12">
                <div class="text-white bg-primary p-1 text-center">Detalles Productos</div>
                <div class="p-3 border border-3 border-primary">
                    <div class="row g-4">
                    <!-----Tabla para el detalle de la compra--->
                        <div class="col-12">
                            <div class="table-responsive">
                                <table id="tabla_detalle_busqueda" name="tabla_detalle_busqueda" class="table table-bordered data-table"> 
                                    <thead>
                                        <tr>
											<th><input type="checkbox" id="seleccionar-todos"></th>
                                            <th >Producto</th>
                                            <th >Id_Producto</th>
                                            <th >Stock</th>
											<th >Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		
		 <!--Botones--->
				<div class="col-md-12 mt-4">
				  <div class="form-group">
					<button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
					<button type="submit" id="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
				  </div>
				</div>
			</div>

	    </form>

	</div>
  </div>
 </div>
</div>

@endsection
@section('js-script')
<script>

$(document).ready(function() {
	
	
		$('#tabla_detalle_busqueda thead tr').clone(true).appendTo('#tabla_detalle_busqueda thead');
            $('#tabla_detalle_busqueda thead tr:eq(1) th').each(function(i) {
                var title = $(this).text(); //es el nombre de la columna
							$(this).html(
								'<input style="width:100%;" type="text" value="" class="form-control filtros" placeholder="Search...' +
								title + '" />');

							$('.filtros', this).on('change', function() {
								if (_table.column(i).search() !== this.value) {

									_table
										.column(i)
										.search(this.value)
										.draw();
								}

							});
            });
	
				var _table=$('#tabla_detalle_busqueda').DataTable({
                processing: true,
                serverSide: true,
				destroy: true,
				width: "auto",
				autoWidth: false,
				 ordering: false,
				//dom: 'Bfrtip',
				orderCellsTop: true,
				lengthMenu: [[ 25, 50, 200, 500 ], [25, 50, 200, 500]],
				///modifier: { selected: true },
				ajax: ({
				url : '{{ route('products.table_detalle') }}',
				method: "POST",
				data: function (d) {
						d._token =   "{{ csrf_token() }}";	
					
					if($('select[name=nro_interno]').val() != ''){
						d.nro_interno = $('select[name=nro_interno]').val();
					}
                
				},
			 error: function (request, status, error) {
				 console.log(error);
			 }
		}),
                columns: [
                    { data: 'selection', name: 'selection',  orderable: false   },
                    { data: 'item_name', name: 'item_name' },
                    { data: 'id_producto', name: 'id_producto' },
                    { data: 'stock', name: 'stock' },
                    { data: 'action', name: 'action' }
                ]
            });
		

			$('#seleccionar-todos').on('click', function() {
				$('.fila-seleccionada').prop('checked', $(this).prop('checked'));
			});

			$('#nro_interno').on('change', function() {
					_table.draw();
			});	
			
			$('#tabla_detalle_busqueda').on('processing.dt', function (e, settings, processing) {
    if (processing) {
       	inicioLoading();
    } else {
        closeLoading();
    }
});
			
	
	/*  $('.dataTables_filter input')
    .unbind('keypress keyup input')
    .bind('keyup input', function (e) {
		 var code = e.keyCode || e.which;
		 if ($(this).val().length >= 3 && code === 13) {
			_table.search(this.value).draw();
		}
		
    }); */
	
	// --- BÚSQUEDA PERSONALIZADA POR ENTER (GLOBAL Y FILTROS) ---
// Selecciona el buscador de DataTables Y cualquier elemento con la clase .filtros
$(document).on('keydown', '.dataTables_filter input, .filtros', function (e) {
    var code = e.keyCode || e.which;
    var value = $(this).val().trim();

    // Si presionan Enter
    if (code === 13) {
        e.preventDefault();   // Detiene el submit del formulario en seco
        e.stopPropagation();  // Evita que el evento suba al formulario

        // Validamos el mínimo de 3 caracteres (o vacío para limpiar el filtro)
        if (value.length >= 3 || value.length === 0) {
            
            // Si el input pertenece a un filtro de columna específico (dentro de una celda th/td)
            if ($(this).closest('th, td').length) {
                var columnIndex = $(this).closest('th, td').index();
                _table.column(columnIndex).search(value).draw();
            } else {
                // Si es el buscador global general
                _table.search(value).draw();
            }
        }
    }
});
	
$( 'form#formulario_create' ).bind( 'keydown', function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
            }
        });
		
		
	 $('#formulario_create').on('submit', function(e) {
        e.preventDefault();
			 
		  //var data = table.rows('.selected').data()
      //var seleccionados = data.length +' row(s) seleccionado(s)';
	  var selectedIds = [];
	   var ids = [];
	  $(_table.$('input[name="bank_check"]:checked').each(function () {
			var row = _table.row($(this).closest('tr')).data();
			//selectedIds.push(row);
			ids.push(row.id);
		}));	
	  
				if (ids.length==0)
					{
						alert('Debe seleccionar un valor');
					 return;
					}
	  

	//return;

		 var formData = new FormData(this);
		 formData.append('idsSeleccionados', ids);
		 //formData.append('tableData', JSON.stringify(selectedIds));
		$.ajax({
            type: 'POST',
            url: "{{ route('table.detalle.post') }}",
            data: formData,
            processData: false, // Requerido para FormData
            contentType: false, // Requerido para FormData
            success: function(response) {
                //console.log('Success:', response);
				_table.draw();
				
            },
            error: function(error) {
                //console.error('Error:', error);
            }
        });
    });
   }); 

	</script>
@endsection






