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
					
					<span class="badge rounded-pill bg-warning text-dark px-2.5 py-1 fw-bold">Pendiente / Sin Estado</span>
					<span class="text-muted small">&rarr;</span>
					
					<span class="badge rounded-pill bg-success text-white px-2.5 py-1 fw-bold">Completado</span>
					<span class="text-muted small">&rarr;</span>
					<span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background-color: #D2B48C; color: white;">Cancelado</span>
				</div>
                    @php $currency = currency() @endphp
                    <table id="orden-desarme-table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th  class="text-center notexport"><input type="checkbox" id="select-all" class="ml-1"></th>
                                {{-- <th style="width: 150px;min-width: 150px" class="text-center">{{ _lang('Procesar') }}</th> --}}
                                {{-- <th style="width: 100px;min-width: 100px" >{{ _lang('Id') }}</th> --}}
                                {{-- <th style="width: 100px;min-width: 100px" >{{ _lang('Pedido pasado') }}</th> --}}
                                <th style="width: 100px;min-width: 100px">{{ _lang('Puesto') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Fecha de venta') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('venta') }}</th>

                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Interno') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Reserva') }}</th>


                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Lugar de venta') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Marca y modelo') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('pieza') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">
                                    {{ _lang('Detalle de pieza') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">
                                    {{ _lang('Detalle de anulado') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Cliente') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Vendedor') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Ubicación') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Estado') }}</th>
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Autorizo') }}</th> --}}
                                <th style="width: 100px;min-width: 100px" class="text-right">
                                    {{ _lang('Fecha estimada de pieza disponible') }}</th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Esta la pieza') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Que Falta') }}</th>
                                {{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Quien informó que no esta') }}</th> --}}
                                <th style="min-width: 300px" class="text-right">{{ _lang('OBS. Al desarme o busqueda') }}
                                </th>
                                <th style="width: 100px;min-width: 100px" class="text-right">
                                    {{ _lang('Desarmado o anulado') }}</th>
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
@endsection

@section('js-script')
    <script>
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
            /*$.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
				
				var sourceData = [
    { id: 1, text: 'Si' }, // Objeto estructurado
    'No'                   // Valor plano
];

            });*/


			var table = $('#orden-desarme-table').appTable({
					title:"Historico de Orden de desarme",
					ajax: {
						url: "{{ url('orden-desarme/get_table_data') }}", // Substitua pela sua rota
						type: 'POST',
						data: function (d) {
							  d._token = $('meta[name="csrf-token"]').attr('content');
							  d.id = "{{$id ?? null}}";  // ID dinámico que puedes enviar
							  d.filtrado_activo = $('select[name="filtrado_activo"]').val() || "";  
						}
					},
					visibleButtons: {
					reset: true,
					excel: true,
					print: false
					},
				   columnFilters: ['none',
				    { 
					 type: 'select', 
					 data: @json($puestos) // Array simple
					}
					,'daterangepicker',,,'daterangepicker','input'],
					columnDefs: [
    {
        "targets": 0, 
        "searchable": false,
        "orderable": false,
        "className": 'text-center' ,
        "render": function (data, type, row, meta) {
            
            if (data && data.trim() !== '') {
                var rowId = row.id || row[0] || meta.row;
					return '<div class="d-flex align-items-center justify-content-center">' +
                           '<input type="checkbox" class="row-checkbox mr-2" value="' + rowId + '" data-product_id ="' + row.product_id + '">' +
                           '<div>' + data + '</div>' +
                       '</div>';								
            }
            return ''; 
        }
    }
],
					
					customButtons: [
					{
                text: '<i class="fas fa-check-square"></i> Multiples Qr',
                className: 'btn-success', 
                attr: {
                    id: 'btn_obtener_datos'
                },
                action: function (e, dt, node, config) {
						
							var qr_seleccionados = [];
                            $('.row-checkbox:checked').each(function() {
                                qr_seleccionados.push($(this).data('product_id'));
                            });

						if (qr_seleccionados.length === 0) {
							alert('Debe seleccionar al menos un valor');
							return; 
						}
						
						
						
						
						//console.log("IDs listos para procesar:", qr_seleccionados);
						impresion_multiple(qr_seleccionados);
			  }
			},
					{
							text: 'Filtrar por: ' +
							'<select id="filtrado_activo" name="filtrado_activo"  class="form-control-sm">' +
							'<option value="pendiente" selected >Pendiente</option>' +
							'<option value="completado">Completado</option>' +
							'<option value="anulada">Cancelado</option>' +
							'</select>',
					className: 'botones-custom',
					action: function ( e, dt, node, config ) {
						}
					}
					],
				   columns: [
				    { data: 'action', name: 'action', orderable: false},
					{ data : "puesto", name : "puesto" },
					{ data : "fecha_venta", name : "fecha_venta" },
					{ data: "venta", name: "venta" }, 
					{ data: "interno", name: "interno" }, 
					{ data: "cotizacion", name: "cotizacion" }, 
					{ data: "lugar_venta", name: "lugar_venta" }, 
					{ data: "marca_modelo", name: "marca_modelo" }, 
					{ data: "pieza", name: "pieza" }, 
					{ data: "detalle_pieza", name: "detalle_pieza" }, 
					{ data: "detalle_anulado", name: "detalle_anulado" }, 
					{ data: "cliente", name: "cliente" }, 
					{ data: "vendedor", name: "vendedor" }, 
					{ data: "ubicacion", name: "ubicacion" }, 
					{ data: "estado", name: "estado" }, 					
					{ data: "fecha_estimada_pieza_disponible", name: "fecha_estimada_pieza_disponible" },
					{ data: "existe", name: "existe" }, 
					{ data: "falta", name: "falta" },
					{ data: "obs_desarme_busqueda", name: "obs_desarme_busqueda" },
					{ data: "fecha_desarmado_anulado", name: "fecha_desarmado_anulado" }
					],
					createdRow: function(row, data) {
                    var estado = data.estado;
                    if (estado === 'parcial') {
                        $(row).css('background-color', '#FFFACD');
                    } else if (estado === 'completado') {
                        $(row).css('background-color', '#98FB98');
                    } else if (estado === 'anulada') {
                        $(row).css({
                            'background-color': '#D2B48C',
                            'color': 'white'
                        });
                    } else if (!estado || estado === '') {
                       $(row).css('background-color', '#FFFACD');
                      // $(row).css('background-color', '#F08080');
                    }
                }
				});
				
				$('#filtrado_activo').on('change', function(e) {
					e.preventDefault();
					table.draw();
           			return false; //for old browsers 
			});
				
				$('#filtrado').on('change', function(e) {
					e.preventDefault();
					table.draw();
           			return false; //for old browsers 
			});
			
			$('#select-all').on('click', function() {
				$('.row-checkbox').prop('checked', $(this).prop('checked'));
			});
			
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
                    alert('Ocurrió un error al intentar procesar los códigos QR.');
                },
                complete: function() {
                    if (typeof closeLoading === 'function') closeLoading();
                }

				});								
								
			//alert(ids)
		}
		
		
        });
    </script>
@endsection
