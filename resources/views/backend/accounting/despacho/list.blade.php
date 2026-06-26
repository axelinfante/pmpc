@extends('layouts.app')
<link href="{{ asset('public/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css" />
@section('content')
    <div class="row">

        <div class="col-12">
            @csrf
            <div class="card mt-2">
                <span class="d-none panel-title">{{ _lang('Orden de despacho') }}</span>

                <div class="card-body">
									
				<div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
					<span class="text-muted small text-uppercase fw-bold me-1">Estados:</span>
					<span class="badge rounded-pill bg-warning text-dark px-2.5 py-1 fw-bold">A despachar</span>
					<span class="text-muted small">&rarr;</span>
					<span class="badge rounded-pill bg-success text-white px-2.5 py-1 fw-bold">Despachado</span>
					<span class="text-muted small">&rarr;</span>
					<span class="badge rounded-pill bg-danger text-white px-2.5 py-1 fw-bold">Devolución</span>
				</div>

                    @php $currency = currency() @endphp
                    <table id="orden-despacho-table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Acciones.') }}
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Nro.') }}
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Fecha de venta') }}
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Cotización') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Interno') }}</th>
								<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Deposito') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Ubicación') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Marca') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Modelo') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Pieza') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Cliente Datos de Entrega') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Vendedor') }}</th>
                                <th style="width: 250px; min-width: 250px" class="text-right">{{ _lang('Acciones Solicitadas por el Vendedor') }}</th>

                                <th style="width: 100px;min-width: 100px" class="text-right">
                                    {{ _lang('Estado Cotización') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('F.Desarme') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Estado la pieza') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">
                                    {{ _lang('F.envio otro dep.') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('F.envio dep.') }}
                                </th>
                                <!--<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Embalado el') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Lugar Embalado') }}
                                </th>-->
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('F.Entrega') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">
                                    {{ _lang('Forma de Entrega') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Despachado por') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Observaciones') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Foto Guía') }}</th>
                                {{-- <th style="width: 100px;min-width: 100px">{{ _lang('Prioridad') }}</th> --}}

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
	
    @include('backend.accounting.despacho.modal.confirmar-entrega')
    @include('backend.accounting.despacho.modal.confirmar-entrega-max')
@endsection

@section('js-script')
<script src="{{ asset('public/dropzone/dropzone.min.js') }}" defer></script>
    <script>
		var lugarentregas_tables = <?php echo json_encode($lugar_entregas); ?>;
        var adminDesarme =
            {{ strTolower(auth()->user()->role->name) == 'administrativo de despacho' || strTolower(auth()->user()->role->name) == 'gerencial' ? 'true' : 'false' }};
    </script>

    <script>
        function changeProcesar(s) {
            let select = $(s);
            let id = select.data('id');
            let procesar = select.val();
            $.ajax({
                url: '{{ url('orden-despacho/changeProcesar') }}/' + id + '/' + procesar,
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

            $('#orden-despacho-table thead tr')
                .clone(true)
                .addClass('filters')
                .appendTo('#orden-despacho-table thead');

            var table = $('#orden-despacho-table').DataTable({
                scrollX: true,
                serverSide: true,
                processing: true,
                searching: true,
                orderCellsTop: true,
                fixedHeader: true,
                stateSave: true,
                stateLoadCallback: function(settings) {
                    var state = JSON.parse(localStorage.getItem(
                        '{{ url('orden-despacho/get_table_data') }}'));
                    if (state) {
                        // Restaurar valores de filtros personalizados
                        state.columns.forEach(function(column, index) {
                            $('input[name="filtro_columna_' + index + '"]').val(column.search
                                .search);
                        });
                    }
                    return state;
                },
                ajax: {
                    url: '{{ url('orden-despacho/get_table_data') }}',
                    method: "POST",
                    data: function(d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.id = "{{ $id ?? null }}";
                    }
                },
                dom: 'Bfrtip',
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions'
                    },
                    {
                        data: 'nro',
                        name: 'nro'
                    },
                    {
                        data: 'fecha_venta',
                        name: 'fecha_venta'
                    }, // Fecha
                    {
                        data: 'cotizacion',
                        name: 'cotizacion'
                    }, // Cotización
                    {
                        data: 'interno',
                        name: 'interno'
                    }, // Interno
					{
                        data: 'deposito',
                        name: 'deposito'
                    }, // Ubicación
                    {
                        data: 'ubicacion',
                        name: 'ubicacion'
                    }, // Ubicación
                    {
                        data: 'marca',
                        name: 'marca'
                    }, // Marca
                    {
                        data: 'modelo',
                        name: 'modelo'
                    }, // Modelo
                    {
                        data: 'pieza',
                        name: 'pieza'
                    }, // Pieza
                    {
                        data: 'cliente',
                        name: 'cliente'
                    }, // Cliente
                    {
                        data: 'vendedor',
                        name: 'vendedor'
                    }, // Vendedor
                    {
                        data: 'acciones_cotizacion',
                        name: 'acciones_cotizacion',            
                    },
                    {
                        data: 'estado_cotizacion',
                        name: 'estado_cotizacion'
                    }, // Estado Cotización
                    {
                        data: 'fecha_desarme',
                        name: 'fecha_desarme'
                    },
                    {
                        data: 'estado_pieza',
                        name: 'estado_pieza'
                    },
                    {
                        data: 'envio_otro_deposito',
                        name: 'envio_otro_deposito'
                    },
                    {
                        data: 'envio_deposito',
                        name: 'envio_deposito'
                    },

                  /*  {
                        data: 'embalado_el',
                        name: 'embalado_el'
                    },
                    {
                        data: 'lugar_embalado',
                        name: 'lugar_embalado'
                    },*/
                    {
                        data: 'fecha_entrega',
                        name: 'fecha_entrega'
                    },
                    {
                        data: 'forma_entrega',
                        name: 'forma_entrega'
                    },
                    {
                        data: 'despachado_por',
                        name: 'despachado_por'
                    },
                    {
                        data: 'observaciones',
                        name: 'observaciones'
                    },
                    {
                        data: 'guia',
                        name: 'guia'
                    },
                ],

                buttons: [{
					extend: 'colvis',
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
								$('.filtros').val('');
								$('.select-filter').val('');
								// Limpiar la selección de un select2
								table.search('').columns().search('').draw();
								$('.select2').val(null).trigger('change');

                           }
				},
				{
                        extend: 'pdf',
                        text: 'Exportar a PDF',
                        exportOptions: {
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
                        }
                    },
                    {
                        extend: 'excel',
                        text: 'Exportar a Excel',
                        exportOptions: {
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
                        }

                    },
					
					{
                    text: 'Confirmacion de entrega',
			        className: 'btn btn-xs',
					attr: {
						title: "Confirmacion de entrega",
						id: "confirmacion-button",
						"data-title": "Confirmacion de entrega",
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
				}
                ],

                createdRow: function(row, data) {
                    var estado = data.estatus;
                    if (estado === 'devueltos') {
                        $(row).css('background-color', '#FF0000');
                    } else if (estado === 'despachado') {
                        $(row).css('background-color', '#98FB98');
                    } else if (!estado || estado === 'pendiente') {
                        $(row).css('background-color', '#FFFACD');
                        //$(row).css('background-color', '#F08080');
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

                    var columnasFecha = [3, 15, 17, 18,19];
                    var columnaFormaEntrega = 20;

                    api.columns().eq(0).each(function(colIdx) {

                        var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                        if (!cell.length) return;

                        var title = $(cell).text();

                        // Si es la columna "forma_entrega"
                        if (colIdx === columnaFormaEntrega) {
							 const selectHTML = {!! json_encode(formasEntrega('forma_entrega','',true)) !!};
							var select = $(selectHTML).appendTo(cell.empty())
								.on('change', function() {
									var val = $.fn.dataTable.util.escapeRegex($(this).val());
									 api.column(colIdx)
									 table.column( colIdx ).search(val ? val : '', false, false).draw();
									//table.column(colIdx).search(val ? '^' + val + '$' : '', true, false).draw();
								});
                        } else if (colIdx != 0 && colIdx != 1 && colIdx != 23) {
							
							if (colIdx == 6) {
								
								var select = $('<select id="' + title + '" multiple="true" class="form-control select2"></select>')
								.appendTo(cell.empty())
								.on( 'change', function () {
											var val = $(this).val();
											table.column( colIdx ).search(val ? val : '', false, false).draw();
								} );
								
								
							select.append( '<option value="-1">VACIOS</option>' );
							for (const row_xx of lugarentregas_tables) {
									select.append( '<option value="'+row_xx.id+'">'+row_xx.nombre+'</option>' )
							}
							
								$('.select2').select2({
									multiple: true,
									closeOnSelect: false//,
								  }); 
							
							
							}else{
							
                            var tipoInput = columnasFecha.includes(colIdx) ? 'date' : 'text';

                            $(cell).html(
                                '<input class="filtros" style="width:100%" type="' + tipoInput +
                                '" placeholder="' + title + '" />'
                            );

                            $('input', cell).each(function() {
                                let cursorPosition = 0;

                                $(this).off('change')
                                    .on('change', function(e) {
                                        $(this).attr('title', $(this).val());
                                        let regexr = '({search})';
                                        cursorPosition = this.selectionStart;

                                     //if (e.keyCode == 13) { 
									  api.column(colIdx).search(
											this.value
									  ).draw();
									  
									  
                                    });
                            });
							};
                        } else {
                            $(cell).html('');
                        }
                    });

                    api.columns.adjust();
                }

            });


$('#orden-despacho-table').on('processing.dt', function (e, settings, processing) {
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
			_table.search(this.value).draw();
		}
		
    });
	


            $(document).on('click', '#select-all', function() {
                var isChecked = $(this).is(':checked');
                $('#orden-despacho-table tbody input.row-checkbox').each(function() {
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
                $('#orden-despacho-table tbody input.row-checkbox').each(function() {
                    var id = $(this).data('id');
                    $(this).prop('checked', selectedRows.has(id));
                });
                var allChecked = $('#orden-despacho-table tbody input.row-checkbox').length > 0 &&
                    $('#orden-despacho-table tbody input.row-checkbox').length === $(
                        '#orden-despacho-table tbody input.row-checkbox:checked').length;
                $('#select-all').prop('checked', allChecked);
            });


			

        });
    </script>
    <script>
        $('#modalConfirmarEntrega').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            $('#modal_orden_id').val(button.data('id'));
            $('#modal_fecha_entrega').val(button.data('fecha') ?? '');
            $('#modal_forma_entrega').val(button.data('forma') ?? '');
            $('#modal_despachado_por').val(button.data('despachado') ?? '');
        });
		
	
	$('#main_modal').on('hidden.bs.modal', function () {
			$('#orden-despacho-table').DataTable().ajax.reload(null, false);
		});
		 
		 
		$('#modalConfirmarEntregaMax').on('hidden.bs.modal', function () {
			// Limpiar la validación al cerrar el modal
			$('#miFormulario').parsley().reset();
			// Limpiar los campos del formulario
			$('#modal_orden_id_max').val('');
			$('#orden-despacho-table').DataTable().ajax.reload(null, false);
		}); 
		 
		 
		        
$( document ).ready(function() {
		$('#miFormulario').submit(function(e) {
        e.preventDefault();
        var url = $(this).attr("action");
        let formData = new FormData(this);
    
        $.ajax({
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
           });
        
    });
});	
 
    </script>
@endsection
