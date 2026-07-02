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
			<div class="alert alert-danger print-error-msg col-md-12" style="display:none">
					<ul></ul>
				</div>
			<div class="col-md-12">
			 <div class="form-group">
                                    <label class="control-label">{{ _lang('Nº interno') }}</label>
                                    <select id="nro_interno" name="nro_interno" required class="form-control select2">
										<option value="">Seleccionar</option>
                                        @foreach ($nro_interno_datos as $interno_row)
                                                        <option value="{{ $interno_row->id }}">{{ nroInternoAlias($interno_row->company_id,$interno_row->tipo_vehiculo,$interno_row->id) }}</option>
                                        @endforeach
                                    </select>
              </div>
			  </div>
			    <div class="col-md-12">
                                <div class="form-group">
                                  <label class="control-label">Tipo Movimiento</label>
                                    <select required class="form-control" name="estado" id="estado">
                                        <option selected value="despacho">Enviar a stock</option>
                                        <option value="desarme-stock">Enviar Desarme -> Stock</option>
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
                                    <select disabled class="form-control select2" data-value="id" data-display="marca"
                                        data-table="marca" data-where="" id="marca" name="marca">
                                        <option value=""></option>
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
                                    <label  class="control-label">{{ _lang('Modelo') }}</label>
                                    <select disabled class="form-control select2" id="modelo">
                                        <option value=""></option>

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
											<th class="notexport">Accion <input type="checkbox" id="qr_master" class="ml-1"></th>
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
				//if($('select[name=filtrado]').val() != ''){
					d.filtrado = "predefinido";//$('select[name=filtrado]').val();
				//}
            },
            error: function (request, status, error) {
                // console.log(error);
            }
        }),
		visibleButtons: {
        reset: true,
        excel: true,
        print: false
		},
		customButtons: [
            {
                text: '<i class="fas fa-check-square"></i> Multiples Qr',
                className: 'btn-success', 
                attr: {
                    id: 'btn_obtener_datos'
                },
                action: function (e, dt, node, config) {
						var qr_seleccionados = [];
						_table.$('input.chk-accion:checked').each(function () {
							var rowData = _table.row($(this).closest('tr')).data();
							var idTarget = rowData.item_id || rowData[0]; 
							if (idTarget) {
								qr_seleccionados.push(rowData.product_id);
							}
						});	
						

						if (qr_seleccionados.length === 0) {
							alert('Debe seleccionar al menos un valor');
							return; 
						}
						//console.log("IDs listos para procesar:", qr_seleccionados);
						impresion_multiple(qr_seleccionados);
					/*var formData = new FormData();
					formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                    formData.append('idsSeleccionados', qr_seleccionados);
					$.ajax({
					url: "{{ route('print-qr-mult') }}",						
					type: "POST",
					data: formData,
					processData: false, 
					contentType: false, 
					beforeSend: function() {
                    if (typeof inicioLoading === 'function') inicioLoading();
					},
					success: function(response) {
                    if (typeof response === 'string' || response.html) {
                        var htmlContenido = response.html ? response.html : response;
						$('#main_modal .modal-body').html(htmlContenido);
                        $('#main_modal').modal('show'); 
                    } 
                },
                error: function(xhr) {
                    console.error("Error al procesar la impresión múltiple:", xhr);
                    alert('Ocurrió un error al intentar procesar los códigos QR.');
                },
                complete: function() {
                    if (typeof closeLoading === 'function') closeLoading();
                }

				});*/
				
				
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
		"columnDefs": [
    {
        "targets": 6, 
        "searchable": false,
        "orderable": false,
        "className": 'text-center',
        "render": function (data, type, row, meta) {
            
            if (data && data.trim() !== '') {
                
                var rowId = row.id || row[0] || meta.row;

                return '<div class="d-flex align-items-center justify-content-center">' +
                           '<input type="checkbox" class="chk-accion mr-2" value="' + rowId + '">' +
                           '<div>' + data + '</div>' +
                       '</div>';
            }
            
            return ''; 
        }
    }
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
		

			$('#seleccionar-todos').on('click', function() {
				$('.fila-seleccionada').prop('checked', $(this).prop('checked'));
			});

			$('#nro_interno').on('change', function() {
				$('#formulario_create').find(".print-error-msg").hide().find("ul").html('');
					marca.val('');
					marca_modelo.val('');
					modelo.html(`<option value=""></option>`);
					//modelo.val('');
					marca.trigger('change');
					modelo.trigger('change');
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
					$('#formulario_create').find(".print-error-msg").hide().find("ul").html('');
				  var selectedIds = [];
				   var ids = [];
				  $(_table.$('input[name="bank_check"]:checked').each(function () {
						var row = _table.row($(this).closest('tr')).data();
						ids.push(row.item_id);
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
						if(response.result == "success"){
									/*_table.draw();
									impresion_multiple(ids);*/
									setTimeout(function() {
										_table.draw();
										impresion_multiple(response.ids_creados);
									}, 2000); 
									
							}else{
								//$('#formulario_create').find(".print-error-msg").find("ul").html('');
								$('#formulario_create').find(".print-error-msg").css('display','block');
								$.each( response.message, function( key, value ) {
									$('#formulario_create').find(".print-error-msg").find("ul").append('<li>'+value+'</li>');
								});
							}
						},
						error: function(response){
							//$('#formulario_create').find(".print-error-msg").find("ul").html('');
							$('#formulario_create').find(".print-error-msg").css('display','block');
							$.each( response.responseJSON.errors, function( key, value ) {
								$('#formulario_create').find(".print-error-msg").find("ul").append('<li>'+value+'</li>');
							});
						}
					});
					
					
				});
				
				
				
				function MostrarModelo() {
                if ($('#nro_interno').val() > 0){
					$.ajax({
                    url: "{{ url('vehiculo/getMarcaModeloByCar/') }}/" + nro_interno.val(),
                    dataType: 'json',
                    success: function(resMM) {
						
						let marcaId = resMM.marca_modelo?.idMarca ?? "0";
                        if (marcaId=="0"){
							return;
						}
						
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
				
			
		
			$('#qr_master').on('click', function() {
				$('.chk-accion').prop('checked', $(this).prop('checked'));
			});
				
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
	
		function impresion_multiple(ids){
			if (ids.length==0)
								{
									alert('Debe seleccionar un valor');
								 return;
								}

				var formData = new FormData();
					formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                    formData.append('idsSeleccionados', ids);
					$.ajax({
					url: "{{ route('print-qr-mult') }}",						
					type: "POST",
					data: formData,
					processData: false, 
					contentType: false, 
					beforeSend: function() {
                    if (typeof inicioLoading === 'function') inicioLoading();
					},
					success: function(response) {
                    if (typeof response === 'string' || response.html) {
                        var htmlContenido = response.html ? response.html : response;
						$('#main_modal .modal-body').html(htmlContenido);
                        $('#main_modal').modal('show'); 
                    } 
                },
                error: function(xhr) {
                    console.error("Error al procesar la impresión múltiple:", xhr);
                    alert('Ocurrió un error al intentar procesar los códigos QR.');
                },
                complete: function() {
                    if (typeof closeLoading === 'function') closeLoading();
                }

				});								
								
			//alert(ids)
		}	
		

	</script>
@endsection