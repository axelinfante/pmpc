@extends('layouts.app')

@section('content')
    {{-- <style type="text/css"> --}}
    {{-- #quotation-table td:nth-child(5){ --}}
    {{-- text-align: center !important; --}}
    {{-- } --}}
    {{-- </style> --}}
    <div class="row">
        {{-- <div class="col-lg-6 mb-2"> --}}
        {{-- <a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Add New Car') }}" --}}
        {{-- href="{{ route('orden-desarme.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a> --}}
        {{-- </div> --}}
        <div class="col-12">
            @csrf
            <div class="card mt-2">
                <span class="d-none panel-title">{{ _lang('Orden de desarme') }}</span>

                <div class="card-body">
				
				<div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
    <span class="text-muted small text-uppercase fw-bold me-1">Estados:</span>
    
    <span class="badge rounded-pill bg-warning text-dark px-2.5 py-1 fw-bold">Parcial</span>
    <span class="text-muted small">&rarr;</span>
    
    <span class="badge rounded-pill bg-success text-white px-2.5 py-1 fw-bold">Completado</span>
    <span class="text-muted small">&rarr;</span>
    
    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background-color: #D2B48C; color: white;">Cancelado</span>
    <span class="text-muted small">&rarr;</span>
    
    <span class="badge rounded-pill bg-warning text-dark px-2.5 py-1 fw-bold">Sin Estado</span>
</div>
				
                    @php $currency = currency() @endphp
                    <table id="orden-desarme-table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 10px;min-width: 10px">Lote</th>
                                <th style="width: 100px;min-width: 100px" class="text-center">{{ _lang('Action') }}</th>
                                {{-- <th style="width: 150px;min-width: 150px" class="text-center">{{ _lang('Procesar') }}</th> --}}
								{{-- @if (strTolower(auth()->user()->role->name) == 'administrativo de desarme' || strTolower(auth()->user()->role->name) == 'gerencial')
                                    <th style="width: 150px;min-width: 150px" class="text-center">{{ _lang('Puesto') }}</th>
                                    <th style="width: 150px;min-width: 150px" class="text-center">
                                        {{ _lang('Ingreso a Puesto') }}</th>
                                @endif --}}

                                {{-- <th style="width: 100px;min-width: 100px" >{{ _lang('Id') }}</th> --}}
                                 <th style="width: 100px;min-width: 100px" >{{ _lang('Fecha Ingreso a puesto') }}</th>
                                <th style="width: 100px;min-width: 100px">{{ _lang('Puesto') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Interno') }}</th>
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Reserva') }}</th> --}}
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('venta') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Fecha de venta') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Cliente') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Lugar de venta') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Marca y modelo') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('pieza') }}</th>
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Detalle de pieza') }}</th> --}}
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Detalle de anulado') }}</th> --}}
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Cliente') }}</th> --}}
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Vendedor') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Ubicación') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Estado') }}</th>
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Autorizo') }}</th> --}}
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Fecha estimada de pieza disponible') }}</th> --}}
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Esta la pieza') }}</th> --}}
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Que Falta') }}</th> --}}
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Quien informó que no esta') }}</th> --}}
                                 <th style="min-width: 300px" class="text-right">{{ _lang('OBS. Al desarme o busqueda') }}</th> 
                                 <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Desarmado o anulado') }}</th> 
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Cargado en camioneta el') }}</th> --}}
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Entregado') }}</th> --}}
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Embalado el') }}</th> --}}
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Aviso a vendedor') }}</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
	<div class="modal fade" id="puestosModal" tabindex="-1" role="dialog" aria-labelledby="puestosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
				<div class="modal-header">
						<h4 class="modal-title">Puestos</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
				</div>
                <div class="modal-body">
				  <form id="puestoForm" name="puestoForm" class="form-horizontal">
						<input type="hidden" name="puesto_id" id="puesto_id" value=0>
						@csrf
						<div class="alert alert-danger print-error-msg" style="display:none">
                        <ul></ul>
						</div>
				<div class="row">						
					<div class="col-lg-6 mb-3">	
                    <div class="form-group">
							<label for="puesto">Puesto<span class="text-danger"></span></label>
                            <input required type="text" class="form-control" id="puesto" name="puesto" placeholder="Puesto" value="" maxlength="50">
                    </div>
					</div>
						<div class="col-lg-6  mb-3">	
						<div class="form-group">
									<label for="company_id">Compañia <span class="text-danger"></span></label>
									<select {{ count($company) == 1  ? 'readonly':""; }} class="form-control" name="company_id" id="company_id">
								     @foreach ($company as $itemci)
										<option value="{{ $itemci->id }}">{{  $itemci->business_name }}</option>
									 @endforeach
									</select>
						</div>
					</div>
					
				</div>
				<div class="row">	
					<div class="col-lg-3 mb-3">					
						<div class="form-group">
                                <label for="activo">Activo <span class="text-danger"></span></label>
                                <select class="form-control" name="activo" id="activo" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Desactivo</option>
                                </select>
						</div>
                    </div>
					<div class="col-lg-3 mb-3">	
						<div class="form-group">
									<label for="predeterminada">Predeterminada <span class="text-danger"></span></label>
									<select class="form-control" name="predeterminada" id="predeterminada" required>
										<option value="1">Si</option>
										<option value="0">No</option>
									</select>
						</div>
					</div>
					<div class="col-lg-6 mb-3">	
						<div class="form-group">
						<label for="user_id">Usuario Asignado <span class="text-danger"></span></label>
								<select required class="form-control" name="user_id" id="user_id">
								{{-- @foreach ($usuario as $itemci)
										<option value="{{ $itemci->id }}">{{  $itemci->name. " ". $itemci->email }}</option>
								@endforeach --}}
								</select>
						</div>
					</div>
					
			</div>
                    <div class="col-sm-offset-2 col-sm-10 mb-3">
                     <button type="submit" class="btn btn-success mt-2" id="saveBtn" value="create"><i class="fa fa-save"></i> Confirmar
                     </button>
                    </div>
                </form>
				<div class="row">
					<div class="col-lg-12 mb-3">	
						<table id="myDataTable"  class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
						  <thead>
							<tr>
							  <th scope="col">Puesto</th>
							  <th scope="col">Asignado</th>
							  <th scope="col">Activo</th>
							  <th scope="col">Predeterminado</th>
							  <th scope="col">Compañia</th>
							</tr>
						  </thead>
						  <tbody>
						  </tbody>
						</table>
					</div>	
                </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        var adminDesarme = {{ (strTolower(auth()->user()->role->name) == 'administrativo de desarme' || strTolower(auth()->user()->role->name) == 'gerencial') ? 'true' : 'false' }};
		
    </script>

    <script>
			const usuario_json = JSON.parse('<?php echo $usuario; ?>');
				 function mostrarPuestos() {
			 	var url = "{{route('puestos.index')}}";
						$.ajax({
						url: url,
						type: "GET",
						dataType: "json",
						success: function(response) {
								let tableBody = $('#myDataTable tbody');
								//let puesto_filterBody = $('#puesto_filter_x');
								tableBody.empty(); // Clear existing data
								//puesto_filterBody.empty(); // Clear existing data
								//puesto_filterBody.append('<option>111</option>');
								opciones = [];
								$.each(response.data, function(index, item) {
								if($.inArray(item.puesto, opciones) === -1) { // No esta duplicado 
									opciones.push(item.puesto);
								}
								
								let btn = ' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'+item.id+'" data-original-title="Update" class="btn btn-warning btn-sm updatePuestos"><i class="ti-pencil"></i></a>';
								
								btn = btn + ' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'+item.id+'" data-original-title="Delete" class="btn btn-danger btn-sm deletePuesto"><i class="ti-eraser"></i></a>';
								
									let row = `<tr data-id="`+item.id +`" data-puesto="`+item.puesto +`" data-user_id="`+item.user_id +`" data-predeterminada="`+item.predeterminada +`" data-activo="`+item.activo +`" data-company_id="`+item.company_id +`">
												<td>${item.puesto}</td>
												<td>${item.asignado.email}</td>
												<td>${(item.activo==true ? 'Si':'No')}</td>
												<td>${(item.predeterminada==true ? 'Si':'No')}</td>
												<td>${item.company.business_name}</td>
												<td>${btn}</td>
											</tr>`;
									tableBody.append(row);
									//puesto_filterBody.append('<option>'+item.puesto+'</option>');
								});
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
				

		}
			mostrarPuestos();
			let opciones = [];
        function changeProcesar(s) {
            let select = $(s);
            let id = select.data('id');
            let procesar = select.val();
            $.ajax({
                url: '{{ url('orden-desarme/changeProcesar') }}/' + id + '/' + procesar,
                method: 'GET',
                success: function(data) {
                    if (data.result == 'success') {
                        select.addClass('border-success')
                    } else {
                        select.addClass('border-danger')
                    }
                }
            })
        }



        $(function() {
            var selectedRows = new Set();
			let exportFormatter = {
					rows: function ( idx, data, node ) {
						let selectedIds = $('.row-checkbox:checked').map(function() {
							return $(this).data('id');
						}).toArray();
						
						 if (selectedIds.length === 0) {
                            return true; 
                        }
						
						return $.inArray(data.id, selectedIds) !== -1;
					 },
				modifier: { selected: true },
				columns: ':visible:not(.not-export)',
				format: {
					body: function (data, row, column, node) {
						   if ($(node).find('select').length) {
                                        return $(node).find('select option:selected').text();
                                    }
                                    return $(node).text().trim();
					}
				}
			};

            $('#orden-desarme-table thead tr')
                .clone(true)
                .addClass('filters')
                .appendTo('#orden-desarme-table thead');

            var table = $('#orden-desarme-table').DataTable({
                scrollX: true,
                processing: true,
                serverSide: true,
                searching: true,
                orderCellsTop: true,
                fixedHeader: true,
				lengthMenu: [[25, 50, 100, 250], [25, 50, 100, 250]],
                ajax: {
                    url: '{{ url('orden-desarme/get_table_data') }}',
                    method: "POST",
                    data: function(d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.id = "{{ $id ?? null }}";
                    }
                },
                //dom: 'Bfrtip',
				dom: 'Bfrltip',
                buttons: [
				{
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
                        $('.filtrov').val('');
                        //$("#tmpEstado").select2('destroy').val("").select2();
							table.search('').columns().search('').draw();
                       	//$('.selectestado').val('').trigger('change');
                      }
                    },
				{
                        extend: 'pdf',
                        text: 'Exportar a PDF',
						orientation: 'landscape', // Cambia a horizontal
						pageSize: 'A4', // Combina bien con tamaño A4 o Carta
						exportOptions: exportFormatter,
                        /*exportOptions: {
                            columns: ':visible:not(.not-export)',
                            format: {
                                body: function(data, row, column, node) {
                                    // Si hay un select, obtiene el texto seleccionado
                                    if ($(node).find('select').length) {
                                        return $(node).find('select option:selected').text();
                                    }
                                    return $(node).text().trim();
                                }
                            }
                        }*/
                    },
                    {
                        extend: 'excel',
                        text: 'Exportar a Excel',
						//orientation: 'landscape', // Cambia a horizontal
						//pageSize: 'A4', // Combina bien con tamaño A4 o Carta
                        exportOptions: exportFormatter,
                    },
                    {
                        text: 'Generar Orden Desarme',
                        action: function(e, dt, node, config) {
                            var selectedIds = [];
                            $('.row-checkbox:checked').each(function() {
                                selectedIds.push($(this).data('id'));
                            });

                            if (selectedIds.length === 0) {
                                alert('Seleccione al menos una orden para imprimir.');
                                return;
                            }

                            // selectedIds.forEach(function(id) {
                            //     window.open('{{ url('orden-desarme/generar-pdf') }}/' + id,
                            //         '_blank');
                            // });
                            window.open('{{ url('orden-desarme/generar-pdf') }}/' + selectedIds,'_blank');
                            
                        }
                    },
					{
                    text: 'Puestos',
                    action: function (e, dt, node, config) {
						$("#puesto_id").val(0);						
						$("#puesto").val("");						
						$("#predeterminada").val(0);						
						$("#activo").val(1);						
						 $('#puestosModal').modal('show');
                        //alert('Button activated');
						
//						     $('#miModal').modal('show'); // Mostrar modal para ver/editar
                    //$('#miModal .modal-title').text('Detalle de Registro');
                    // Ejemplo: Cargar datos o una URL remota
                    //$('#miModal .modal-body').load('tu_url_detalle.html?id=' + rowData.id); // Cargar contenido remoto
                    // O simplemente mostrar los datos
                    // $('#miModal .modal-body').html('<p>ID: ' + rowData.id + '</p><p>Nombre: ' + rowData.nombre + '</p>');
						
                    }
					},
					/*{
                    text: 'Confirmacion de despacho',
			        className: 'btn btn-xs',
					attr: {
						title: "Confirmacion de despacho",
						id: "confirmacion-button",
						"data-title": "Confirmacion de despacho",
					},
					action: function (e, dt, node, config) {
						var selectedIds = [];
						$('.row-checkbox:checked').each(function() {
							selectedIds.push($(this).data('id'));
						});

						if (selectedIds.length === 0) {
							alert('Por favor, seleccione al menos un registro para continuar.');
							return;
						}
					  $('#modal_orden_id_max').val(selectedIds);
					  $('#modalConfirmarEntregaMax').modal('show');
					}
				},*/
				{
                    text: 'Confirmacion de desarme',
			        className: 'btn btn-xs ajax-modal',
					titleAttr: 'Confirmacion de desarme',
					//enabled: false,
					 attr: {
						title: "Confirmación de desarme",
						id: "desarme-button",
						"data-reload": "false" // <--- Agregado de forma fija aquí
					},
					init: function (dt, node, config) {
						 /*var selectedIds = [];
                            $('.row-checkbox:checked').each(function() {
                                selectedIds.push($(this).data('id'));
                            });

                            if (selectedIds.length === 0) {
                                alert('Seleccione al menos una orden.');
                                return;
                            }
						
						$(node).attr('href', '{{ url('orden-desarme/generar-ordenesdesarme') }}/' + selectedIds)*/
					},
					  action: function (e, dt, node, config) {
							e.preventDefault();
							
							var selectedIds = [];
							$('.row-checkbox:checked').each(function() {
								selectedIds.push($(this).data('id'));
							});

							if (selectedIds.length === 0) {
								alert('Seleccione al menos una orden.');
								$(node).attr('href', '#');
								e.stopImmediatePropagation(); 
								return false;
							}

							var baseUrl = "{{ url('orden-desarme/generar-ordenesdesarme') }}";
							var finalUrl = baseUrl + '/' + selectedIds.join(',');
							$(node).attr('href', finalUrl);
							if ($(node).hasClass('ajax-modal') && typeof $.fn.modal !== 'undefined') {
									$(node).trigger('click'); 
							}
						}
				}
                ],
                columns: (function() {
                    var columns = [{
                        data: null,
                        name: 'select',
                        orderable: false,
                        searchable: false,
                        className: 'text-center not-export',
                        render: function(data) {
                            return "<input type='checkbox' class='row-checkbox' data-id='" +
                                data.id + "'>";
                        }
                    }, {
                        data: "action",
                        name: "action",
                        className: 'not-export',
                    }];

                    /*if (adminDesarme) {
                        columns.push({
                            data: "puesto",
                            name: "puesto",
                            class: 'text-center'
                        });
                        columns.push({
                            data: "f_ingreso_puesto",
                            name: "f_ingreso_puesto"
                        });
                    }*/

                    columns.push({
                        //data: "fecha_desarmado_anulado",
                        //name: "fecha_desarmado_anulado"
						data: "f_ingreso_puesto",
                        name: "f_ingreso_puesto"
                    },{
                        data: "puesto",
                        name: "puesto"
                    },
					/*{
                        data: "prioridad",
                        name: "prioridad"
                    },*/
					{
                        data: "interno",
                        name: "interno"
                    }, {
                        data: "venta",
                        name: "venta"
                    }, {
                        data: "fecha_venta",
                        name: "fecha_venta"
                    }, {
                        data: "cliente",
                        name: "cliente"
                    }, {
                        data: "lugar_venta",
                        name: "lugar_venta"
                    }, {
                        data: "marca_modelo",
                        name: "marca_modelo"
                    }, {
                        data: "pieza",
                        name: "pieza"
                    }, {
                        data: "vendedor",
                        name: "vendedor"
                    }, {
                        data: "ubicacion",
                        name: "ubicacion"
                    }, {
                        data: "estado_veh",
                        name: "estado_veh"
                    }, {
                        data: "obs_desarme_busqueda",
                        name: "obs_desarme_busqueda"
                    },{
                        data: "fecha_desarmado_anulado",
                        name: "fecha_desarmado_anulado"
                    }
					
					);

                    return columns;
                })(),
                createdRow: function(row, data) {
                    var estado = data.estado;
                    if (estado === 'parcial') {
                        $(row).css('background-color', '#FFFACD');
                    } else if (estado === 'completado') {
                        $(row).css('background-color', '#98FB98');
                    } else if (estado === 'cancelado') {
                        $(row).css({
                            'background-color': '#D2B48C',
                            'color': 'white'
                        });
                    } else if (!estado || estado === '') {
                       $(row).css('background-color', '#FFFACD');
                      // $(row).css('background-color', '#F08080');
                    }
                },
                responsive: false,
                bStateSave: true,
                bAutoWidth: false,
                ordering: false,
                language: {
                    decimal: "",
                    emptyTable: "{{ _lang('No Data Found') }}",
                    info: "{{ _lang('Showing') }} _START_ {{ _lang('to') }} _END_ {{ _lang('of') }} _TOTAL_ {{ _lang('Entries') }}",
                    infoEmpty: "{{ _lang('Showing 0 To 0 Of 0 Entries') }}",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    thousands: ",",
                    lengthMenu: "{{ _lang('Show') }} _MENU_ {{ _lang('Entries') }}",
                    loadingRecords: "{{ _lang('Loading...') }}",
                    processing: "{{ _lang('Processing...') }}",
                    search: "{{ _lang('Search') }}",
                    zeroRecords: "{{ _lang('No matching records found') }}",
                    paginate: {
                        first: "{{ _lang('First') }}",
                        last: "{{ _lang('Last') }}",
                        next: "{{ _lang('Next') }}",
                        previous: "{{ _lang('Previous') }}"
                    }
                },
                initComplete: function() {
                    var api = this.api();
                    api.columns().eq(0).each(function(colIdx) {
                        var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                        if (colIdx !== 0 && colIdx !== 1) {
                            var title = $(cell).text();
                            if (colIdx == 3) {
                                let selectString = `<select id="puesto_filter_x" name="puesto_filter" style="width:100%" class="puesto_filter">`;
                                selectString += '<option value=""> </option>';
                                opciones.forEach(opcion => {
                                    selectString += `<option value="${opcion}" >${opcion}</option>`;
                                });
                                selectString += '</select>';
                                $(cell).html(selectString);

                                $('.puesto_filter').on('change', function() {
                                var puestoFilterValue = $(this).val();
								//alert(puestoFilterValue);
								
								  api.column(colIdx).search(this.value).draw();
								
                                // Usa una función de filtrado personalizada para manejar el select
                               /* $.fn.dataTable.ext.search.push(
                                    function(settings, data, dataIndex) {
                                        var puestoSelectValue =data[3];
                                        //var puestoSelectValue = $(table.cell(dataIndex, 2).node()).find('select').val(); // Obtiene el valor del select en la fila
                                        if (puestoFilterValue === "" || puestoSelectValue === puestoFilterValue) {
                                            return true;
                                        }
                                        return false;
                                    }
                                );
                                table.draw(); // Redibuja la tabla para aplicar el filtro
                                $.fn.dataTable.ext.search.pop(); // Remueve la función de filtrado para no interferir con otros filtros
								*/
                            });
                            } else if (colIdx == 6) {
								$(cell).html('<input type="text" id="fecha_venta" name="fecha_venta" class="form-control filtrov" placeholder="Rango de fechas..." autocomplete="off" />');

								var $inputFecha = $('#fecha_venta', cell);

								$inputFecha.daterangepicker({
									autoUpdateInput: false,
									opens: 'left',
									locale: {
										format: 'YYYY-MM-DD',
										cancelLabel: 'Limpiar',
										applyLabel: 'Aplicar',
										fromLabel: 'Desde',
										toLabel: 'Hasta',
										customRangeLabel: 'Personalizado',
										daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
										monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
									}
								});
							   $inputFecha.on('apply.daterangepicker', function(ev, picker) {
									let dateRango = picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD');
									$(this).val(dateRango);
									api.column(colIdx).search(dateRango).draw();
								});

								$inputFecha.on('cancel.daterangepicker', function(ev, picker) {
									$(this).val('');
									api.column(colIdx).search('').draw();
								});

								$inputFecha.on('keydown', function(e) {
									e.preventDefault();
								});
}else{
                                $(cell).html('<input class ="filtrov" style="width:100%" type="text" placeholder="' +
                                title + '" />');
                            }

                            $('input', cell).off('change').on('change', function(e) {
                                $(this).attr('title', $(this).val());
                                var regexr = '({search})';
                                var cursorPosition = this.selectionStart;
                                api.column(colIdx).search(this.value)
                                    .draw();
                            });/*.on('keyup', function(e) {
                                e.stopPropagation();
                                $(this).trigger('change');
                            });*/
                        } else {
                            if (colIdx == 0) {
                                $(cell).html('<input type="checkbox" id="select-all">');
                            } else {
                                $(cell).html('');
                            }
                        }
                    });
                }
            });


            table.search('').columns().search('').draw();

            $(document).on('click', '#select-all', function() {
                var isChecked = $(this).is(':checked');
                $('#orden-desarme-table tbody input.row-checkbox').each(function() {
                    var id = $(this).data('id');
                    $(this).prop('checked', isChecked);
                    if (isChecked) {
                        selectedRows.add(id);
                    } else {
                        selectedRows.delete(id);
                    }
                });
            });

            $(document).on('change', '.row-checkbox', function() {
                var id = $(this).data('id');
                if ($(this).is(':checked')) {
                    selectedRows.add(id);
                } else {
                    selectedRows.delete(id);
                }
            });

            table.on('draw', function() {
                $('#orden-desarme-table tbody input.row-checkbox').each(function() {
                    var id = $(this).data('id');
                    $(this).prop('checked', selectedRows.has(id));
                });
                var allChecked = $('#orden-desarme-table tbody input.row-checkbox').length > 0 &&
                    $('#orden-desarme-table tbody input.row-checkbox').length === $(
                        '#orden-desarme-table tbody input.row-checkbox:checked').length;
                $('#select-all').prop('checked', allChecked);
            });


            $(document).on('change', '.puesto-select', function(e) {
                e.stopPropagation();
                var puesto = $(this).val();
                var ordenId = $(this).data('id');
                //var operario = $(this).attr('data-operario');
                var operario = $(this).find('option:selected').data('operario');
                var compania = $(this).find('option:selected').data('compania');

                var f_ingreso_puesto = $(this).closest('tr').find('.f-ingreso-puesto-input').val();

                if (!f_ingreso_puesto) {
                    return false;
                }

                if (!puesto) {
                    return;
                }

/*                if (!puesto) {
                    alert('El campo Puesto no puede estar vacío.');
                    return;
                }

                if (!f_ingreso_puesto) {
                    alert('El campo Fecha de Ingreso no puede estar vacío.');
                    return;
                }*/
				//alert();
                //if (confirm('¿Estás seguro de que quieres cambiar el puesto a ' + puesto + '?')) {
                    $.ajax({
                        url: "{{ url('ordendesarme/update-puesto') }}",
                        method: 'POST',
                        data: {
                            puesto: puesto,
                            f_ingreso_puesto: f_ingreso_puesto,
                            ordenId: ordenId,
                            operario: operario,
                            compania: compania,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                //console.log(response);
                                $("#main_alert > span.msg").html("");
                                $("#main_alert").addClass("alert-success").removeClass(
                                    "alert-danger");
                                $("#main_alert > span.msg").html(response.success);
                                $("#main_alert").css('display', 'block');
								//table.draw(); // Redibuja la tabla para aplicar el filtro
                            } else if (response.error) {
                                //console.log(response);
                                $("#main_alert > span.msg").html("");
                                $("#main_alert").addClass("alert-danger").removeClass(
                                    "alert-success");
                                $("#main_alert > span.msg").html(response.error);
                                $("#main_alert").css('display', 'block');
                            }
                        },
                        error: function(request, status, error) {
                            //console.log(request.responseText);
                            $("#main_alert > span.msg").html("");
                            $("#main_alert").addClass("alert-danger").removeClass(
                                "alert-success");
                            $("#main_alert > span.msg").html(request.responseText);
                            $("#main_alert").css('display', 'block');
                        }
                    });
                //}


            });

            $(document).on('change', '.f-ingreso-puesto-input', function() {
                $(this).closest('tr').find('.puesto-select').trigger('change');
            });
			

	$('body').on('click', '.deletePuesto', function (event) {
		event.preventDefault();
        let puesto_id = $(this).data("id");
		const fila = this.closest('tr');
         if (confirm('Esta seguro de eliminar el registro')) {
			 
			 $.ajax({
					type: "DELETE",
					url: "{{ route('puestos.store') }}"+'/'+puesto_id,
					data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
					success: function (data) {
						 if (fila) {
							fila.remove();
							  mostrarPuestos();
							  mostrarPuestosBusqueda();
						}
                //table.draw();
				},
				error: function (data) {
                console.log('Error:', data);
				}
			});
			
        }
    });
	
	$('body').on('click', '.updatePuestos', function (event) {
		event.preventDefault();
		 var fila = $(this).closest('tr'); // Obtiene el elemento <tr> más cercano
		 var datosFila = fila.data(); // Obtiene los datos almacenados en la fila (por ejemplo, data-id-usuario="123")
		//const fila = this.closest('tr');
		
        let puesto_id = $(this).data("id");
		
//			let row = `<tr data-id="`+item.id +`" data-puesto="`+item.puesto +`" data-user_id="`+item.user_id +`" data-predeterminada="`+item.predeterminada +`" data-activo="`+item.activo +`" data-company_id="`+item.company_id +`">
		 $("#puesto_id").val(puesto_id);
		 $("#puesto").val(datosFila.puesto);
		 $("#company_id").val(datosFila.company_id).trigger('change');
		 $("#user_id").val(datosFila.user_id).trigger('change');
		 $("#predeterminada").val(datosFila.predeterminada).trigger('change');
		 $("#activo").val(datosFila.activo).trigger('change');
		 //$('#user_id').trigger('change');

    });
	
	
	  $('#puestoForm').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $('#saveBtn').html('Sending...');
		$('#puestoForm').find(".print-error-msg").find("ul").html('');
  
        $.ajax({
                type:'POST',
                url: "{{ route('puestos.store') }}",
                data: formData,
                contentType: false,
                processData: false,
                success: (response) => {
                      $('#saveBtn').html('Confirmar');
					  mostrarPuestos();
					  mostrarPuestosBusqueda();
                      //$('#puestoForm').trigger("reset");
                      //$('#puestoForm').modal('hide');
                      //table.draw();
                },
                error: function(response){
                    $('#saveBtn').html('Confirmar');
                    $('#puestoForm').find(".print-error-msg").css('display','block');
					//$('#error-messages').html(''); 
                    $.each( response.responseJSON.errors, function( key, value ) {
						//message
                        $('#puestoForm').find(".print-error-msg").find("ul").append('<li>'+value[0]+'</li>');
                    });
                }
           });
      
    });
	
		$(document).on('change', '#company_id', function() {
		     var selectedValue = $('#company_id option:selected').val(); //$(this).val();
			 //console.log(selectedValue);
			 var intValue = parseInt(selectedValue);
			 const usuarios_tmp = findUserByIdCompania(intValue);	
			 $('#user_id').empty();
			 $.each(usuarios_tmp, function(index, item_x) {
			  $('#user_id').append($('<option>', {
				value: item_x.id,
				text: item_x.name+ ' ' +item_x.email
			  }));
			});
         });
		 
		 
		 function findUserByIdCompania(id) {
				const user = usuario_json.filter(item => item.company_id === id);
			return user;
		}


			$('#company_id').val($('#company_id option:first').val()).trigger('change');
			
			 $("#company_id").select2({
				dropdownParent: $("#puestosModal")
			});
			
			$("#user_id").select2({
				dropdownParent: $("#puestosModal")
			});
			
			
			
			
	 function mostrarPuestosBusqueda() {
		setTimeout(function(){
		$('#puesto_filter_x').empty();
		$('#puesto_filter_x').append('<option value=""></option>');
		$.each(opciones, function(index, item) {
			$('#puesto_filter_x').append('<option value="'+item+'">'+item+'</option>');
		});			
		}, 100);
		//puesto_filterBody.trigger('change'); 
			
    };

	$('#myDataTable').DataTable({
		
	});		
	
	$('#orden-desarme-table').on('processing.dt', function (e, settings, processing) {
    if (processing) {
       	inicioLoading();
    } else {
        closeLoading();
    }
});

 $('.dataTables_filter input')
    .unbind('keypress keyup input')
    .bind('keyup input', function (e) {
		 var code = e.keyCode || e.which;
		 if ($(this).val().length >= 3 && code === 13) {
			table.search(this.value).draw();
		}
		
    });
	
		
	$('#main_modal').on('hidden.bs.modal', function () {
			$('#orden-desarme-table').DataTable().ajax.reload(null, false);
		});
		 
		 
		 	$('#modalConfirmarEntregaMax').on('hidden.bs.modal', function () {
			// Limpiar la validación al cerrar el modal
			$('#miFormulario').parsley().reset();
			// Limpiar los campos del formulario
			$('#modal_orden_id_max').val('');
			$('#orden-desarme-table').DataTable().ajax.reload(null, false);
		}); 
		 
		 
		        
$( document ).ready(function() {
		$('#miFormulario').submit(function(e) {
        e.preventDefault();
        var url = $(this).attr("action");
        let formData = new FormData(this);
    alert();
       /* $.ajax({
                type:'POST',
                url: url,
                data: formData,
                contentType: false,
                processData: false,
                success: (json) => {
				if(json['result'] == "success"){
						$('#modalConfirmarEntregaMax').modal('hide');
				}else{
					$('#miFormulario').find(".print-error-msg").find("ul").html('');
                    $('#miFormulario').find(".print-error-msg").css('display','block');
                    $.each( json['message'], function( key, value ) {
					//	console.log(value);
                        $('#miFormulario').find(".print-error-msg").find("ul").append('<li>'+value+'</li>');
                    });
				  }
				},
                error: function(response){
                    $('#ajax-form').find(".print-error-msg").find("ul").html('');
                    $('#ajax-form').find(".print-error-msg").css('display','block');
                    $.each( response.responseJSON.errors, function( key, value ) {
                        $('#ajax-form').find(".print-error-msg").find("ul").append('<li>'+value+'</li>');
                    });
                }
           });*/
        
    });
});	
 
	
	/*$('#fecha_venta').daterangepicker({
			autoUpdateInput: false,
			locale: {
				format: 'YYYY-MM-DD',
				cancelLabel: 'Clear'
			}
		});

		
		$('#fecha_venta').on('change', function(e) {
			let val = $(this).val();
			table.columns(6).search(val ? val : '', true, false );
			table.draw();
		});
	
	
		$('#fecha_venta').on('apply.daterangepicker', function(ev, picker) {
				let daterango =(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
					$(this).val(daterango);
					table.columns(6).search(daterango);
					table.draw();
			});

			$('#fecha_venta').on('cancel.daterangepicker', function(ev, picker) {
				//$(this).val('');
				$('#fecha_venta').val(null).trigger('change');	
		});
	*/
			

        });
    </script>
@endsection
