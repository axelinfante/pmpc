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

			 
			   <div class="col-md-6">
                                <div class="form-group">
                                    <!--<a href="{{ route('marcas.createLinea') }}" data-reload="false"
                                        data-select="vendedor_id" data-title="{{ _lang('Create Marca') }}" class="ajax-modal-2 select2-add"><i
                                            class="ti-plus"></i> {{ _lang('Add New') }}</a>-->
                                    <label class="control-label">{{ _lang('Marca') }}</label>
                                    <select class="form-control select2" data-value="id" data-display="marca"
                                        data-table="marca" data-where="" id="marca" name="marca">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        {{ create_option('marcas', 'id', 'marca', old('marca'),array('activo=' => 'Si')) }}
                                    </select>
                                </div>
                            </div>
                           
						  <div class="col-md-6">
                                <div class="form-group">
								<!--<a href="#" id="btn-add-modelo" data-reload="false" style="pointer-events: none; opacity: 0.5;"
										data-select="modelo" data-title="{{ _lang('Create Modelo') }}" class="ajax-modal-2 select2-add">
										<i class="ti-plus"></i> {{ _lang('Add New') }}
									</a>-->
                                    <label class="control-label">{{ _lang('Modelo') }}</label>
                                    <select class="form-control select2" id="modelo">
                                        <option value="">{{ _lang('Select One') }}</option>

                                    </select>
                                    <input type="hidden" name="marca_modelo" id="marca_modelo">
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
                                <table id="tabla_detalle_busqueda" name="tabla_detalle_busqueda" class="table table-bordered"> 
                                    <thead>
                                        <tr>
											<th><input class="notexport" type="checkbox" id="seleccionar-todos"></th>
                                            <th >Producto</th>
                                            <th>{{ _lang('nº oblea') }}</th>
                                            <th >Id_Producto</th>
                                            <th >Estado</th>
                                            <th >Stock</th>
											<th class="notexport">Accion</th>
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
	
	        let marca = $('#marca');
            let modelo = $('#modelo');
            let marca_modelo = $('#marca_modelo');
			let nro_interno = $('#nro_interno');
					
	
	var _table = $("#tabla_detalle_busqueda").appTable({
        title: "Items predefinidos",
        ajax: ({
            url : "{{ route('products.table_detalle') }}",
            method: "POST",
            data: function (d) {
                d._token = "{{ csrf_token() }}";    
                if($('select[name=nro_interno]').val() != ''){
                    d.nro_interno = $('select[name=nro_interno]').val();
                }
				if($('select[name=filtrado]').val() != ''){
					d.filtrado = $('select[name=filtrado]').val();
				}
            },
            error: function (request, status, error) {
                // console.log(error);
            }
        }),
		visibleButtons: {
        reset: true,
        excel: false,
        print: false
		},
		customButtons: [{
                   text: 'Filtrar por: ' +
                      '<select id="filtrado" name="filtrado"  class="form-control-sm select2">' +
                      '<option value="predefinido">Predefinidos</option>' +
                      '<option value="activos">Activos</option>' +
                      '</select>',
                className: 'botones-custom',
                action: function ( e, dt, node, config ) {
						}
					}
				],
		columnFilters: [
        'none', 
        'input',
        'input',
        'input',
        'input',
        'none'
		],  
        columns: [
            { data: 'selection', name: 'selection', orderable: false },
            { data: 'item_name', name: 'item_name' },
            { data: 'nro_oblea', name: 'nro_oblea' },
			{ data: 'id_producto', name: 'id_producto' },
			{ data: 'estado', name: 'estado' },
            { data: 'stock', name: 'stock' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
    });	
	
				/*var _table=$('#tabla_detalle_busqueda').DataTable({
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
            });*/
		

			$('#seleccionar-todos').on('click', function() {
				$('.fila-seleccionada').prop('checked', $(this).prop('checked'));
			});

			$('#nro_interno').on('change', function() {
				
					if ($('#nro_interno').val() > 0){
						marca.prop("disabled", true);
						modelo.prop("disabled", true);
					}else{
						marca.prop("disabled", false);
						modelo.prop("disabled", false)
						marca.val('');
						marca_modelo.val('');
						//marca.select2();
						marca.trigger('change'); 

					}
				
				
				    MostrarModelo();
					_table.draw();
			});	
			nro_interno.trigger('change');
			$('.dataTables_filter input').unbind();


			$(document).on('keydown', '.dataTables_filter input, .filtros', function (e) {
				var code = e.keyCode || e.which;
				var value = $(this).val().trim();

				if (code === 13) {
					e.preventDefault();   
					e.stopPropagation();  

					var api = $('#tabla_detalle_busqueda').DataTable();

					if (value.length >= 3 || value.length === 0) {
						if ($(this).closest('th, td').length) {
							var columnIndex = $(this).closest('th, td').index();
							api.column(columnIndex).search(value).draw();
						} else {
							api.search(value).draw();
						}
					}
				}
			});
	
	
		$( 'form#formulario_create' ).bind( 'keypress keydown keyup', function(e) {
					if (e.keyCode == 13) {
						e.preventDefault();
					}
				});
		
				 $('#formulario_create').on('submit', function(e) {
					e.preventDefault();
						 
				  var selectedIds = [];
				   var ids = [];
				  $(_table.$('input[name="bank_check"]:checked').each(function () {
						var row = _table.row($(this).closest('tr')).data();
						ids.push(row.id);
					}));	
				  
							if (ids.length==0)
								{
									alert('Debe seleccionar un valor');
								 return;
								}
				  
					 var formData = new FormData(this);
					 formData.append('idsSeleccionados', ids);
					$.ajax({
						type: 'POST',
						url: "{{ route('table.detalle.post') }}",
						data: formData,
						processData: false, 
						contentType: false, 
						success: function(response) {
							_table.draw();
							
						},
						error: function(error) {
							//console.error('Error:', error);
						}
					});
				});
				
				
				
				function MostrarModelo() {
                if ($('#nro_interno').val() > 0){
					$.ajax({
                    url: "{{ url('vehiculo/getMarcaModeloByCar/') }}/" + nro_interno.val(),
                    dataType: 'json',
                    success: function(resMM) {
                        marca.val(resMM.marca_modelo.idMarca);

                        $('#marca_modelo').val(resMM.marca_modelo.id);
                        marca.select2()
                        $.ajax({
                            url: "{{ route('modelosByMarca') . '/' }}" + resMM
                                .marca_modelo.idMarca,
                            dataType: 'json',
                            success: function(res) {
                                let html =
                                    `<option value="">{{ _lang('Select One') }}</option>`;
                                res.map(r => {
                                    selected = '';
                                    if (resMM.marca_modelo.idModelo == r
                                        .idModelo) {
                                        selected = 'selected'
                                    }
                                    html +=
                                        `<option ${selected} value="${r.idModelo}">${r.modelo.modelo}</option>`;
                                })
                                result = res;

                                modelo.html(html);
                                // modelo.select2();

                            }

                        })



                    }

                })
			}
			
				$('#filtrado').on('change', function(e) {
					e.preventDefault();
					_table.draw();
           			return false; //for old browsers 
			});
			
        }
				
				
				
				
				
				
				
				
				
			   }); 
			   
		async function ActualizarOblea(id) {
        inicioLoading();
		 const itemId = id;
		  const nro_oblea= $("#prod_id-"+itemId).val();
		 //alert(nro_oblea);
		 
		 const response = await fetch('{{ route("actualizaStockitems") }}',{
        headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
        method: 'POST',
        body: JSON.stringify({ 
                id: itemId, 
                nro_oblea: nro_oblea 
            })
    })

       
    const data = await response.json();
    closeLoading();
        
    }	   

	</script>
@endsection