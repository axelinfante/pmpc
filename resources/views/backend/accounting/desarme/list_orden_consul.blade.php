@extends('layouts.app')

@section('content')
    {{-- <style type="text/css"> --}}
    {{-- #quotation-table td:nth-child(5){ --}}
    {{-- text-align: center !important; --}}
    {{-- } --}}
    {{-- </style> --}}
    <div class="row">
        <div class="col-12">
            @csrf
            <div class="card mt-2">
                <span class="d-none panel-title">{{ _lang('Consulta de Orden de desarme') }}</span>
                <div class="card-body">
				<div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
    <span class="text-muted small text-uppercase fw-bold me-1">Estados:</span>
    
    <span class="badge rounded-pill text-dark px-2.5 py-1 fw-bold" style="background-color: #FFFACD;">Parcial</span>
    <span class="text-muted small">&rarr;</span>
    
    <span class="badge rounded-pill text-dark px-2.5 py-1 fw-bold" style="background-color: #98FB98;">Completado</span>
    <span class="text-muted small">&rarr;</span>
    
    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background-color: #D2B48C; color: #ffffff;">Cancelado</span>
    <span class="text-muted small">&rarr;</span>
    
    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background-color: #F08080; color: #ffffff;">Sin Estado</span>
</div>
                    <div class="table-responsive" style="padding: 10px;">
                    <table id="orden-desarme-table" class="table table-bordered" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="text-center">{{ _lang('id') }}</th>
                                <th class="text-center">{{ _lang('Cotizacion') }}</th>
                                <th class="text-center">{{ _lang('Interno') }}</th>
                                <th class="text-center">{{ _lang('FechaVenta') }}</th>
                                <th class="text-center">{{ _lang('Marca Modelo') }}</th>
                                <th class="text-center">{{ _lang('Pieza') }}</th>
                                <th class="text-center">{{ _lang('Cliente') }}</th>
                                <th class="text-center">{{ _lang('Vendedor') }}</th>
                                <th class="text-center">{{ _lang('Ubicacion') }}</th>
                                <th class="text-center">{{ _lang('Estado') }}</th>
                                <th class="text-center">{{ _lang('Fecha Asignación puesto') }}</th>
                                <th class="text-center">{{ _lang('Puesto Asignado') }}</th>
                                <th class="text-center">{{ _lang('Quien informo que falta') }}</th>
                                <th class="text-center">{{ _lang('Obs al desarmaddo o busqueda') }}</th>
                                <th class="text-center">{{ _lang('Desarmado o anulado el') }}</th>
                                <th class="text-center">{{ _lang('Puesto desarme') }}</th>
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
            /*$.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });*/


			var table = $('#orden-desarme-table').appTable({
					title:"Consulta de Orden de desarme",
					ajax: {
						url: "{{ route('list_consu_orden') }}", // Substitua pela sua rota
						type: 'GET'
						/*data: function (d) {
							// Aqui você envia o valor do select/input como o parâmetro 'filtrado'
							//d.filtrado = $('#filtrado').val();
							  d._token = $('meta[name="csrf-token"]').attr('content');
							  d.id = "{{$id ?? null}}";  // ID dinámico que puedes enviar
							  d.estado = "completado";    // Estado enviado
							  d.isHistorial = 1;          // Historial
						}*/
					},
					//ajax: "{{ route('transfers.index') }}",
					//visibleButtonsFilter:false,
					visibleButtons: {
					reset: true,
					excel: true,
					print: false
					},
				   columnFilters: [,,,'daterangepicker',,,,,{ 
					 type: 'select', 
					 data: @json($lugar_entregas)
					},,'daterangepicker',{ 
					 type: 'select', 
					 data: @json($puestos)
					},,,'daterangepicker',{ 
					 type: 'select', 
					 data: @json($puestos)
					}],
				   columns: [
                     { data : "id", name : "id", visible:false , orderable: false, searchable: false},
                     { data : "invoice_number", name : "invoice_number" },
                     { data : "interno", name : "interno" },
                     { data : "fecha_venta", name : "fecha_venta" },
                     { data : "marcamodelo", name : "marcamodelo" },
                     { data : "item_pieza", name : "item_pieza" },
                     { data : "cliente", name : "cliente" },
                     { data : "vendedor", name : "vendedor" },
                     { data : "ubicacion", name : "ubicacion" },
                     { data : "estado", name : "estado" },
                     { data : "f_ingreso_puesto", name : "f_ingreso_puesto" },
                     { data : "puesto", name : "puesto" },
                     { data : "informo_ausencia", name : "informo_ausencia" },
                     { data : "obs_desarme_busqueda", name : "obs_desarme_busqueda" },
                     { data : "fecha_desarmado_anulado", name : "fecha_desarmado_anulado" },
                     { data : "puesto_final", name : "puesto_final" }
					], createdRow: function(row, data) {
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
                        $(row).css('background-color', '#F08080');
                    }
					},	
				});
				
				$('#filtrado').on('change', function(e) {
					e.preventDefault();
					table.draw();
           			return false; //for old browsers 
			});
        });


   /* $(function($) {
        $.noConflict();
        $('#orden-desarme-table thead tr')
                .clone(true)
                .addClass('filters')
                .appendTo('#orden-desarme-table thead');

        $('#orden-desarme-table').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            destroy: true,
            ajax: '{{ route('list_consu_orden') }}',
            dataType: "json",
            orderCellsTop: true,
            fixedHeader: true,
            columns : [
                     { data : "id", name : "id", visible:false , orderable: false, searchable: false},
                     { data : "invoice_number", name : "invoice_number" },
                     { data : "interno", name : "interno" },
                     { data : "fecha_venta", name : "fecha_venta" },
                     { data : "marcamodelo", name : "marcamodelo" },
                     { data : "item_pieza", name : "item_pieza" },
                     { data : "cliente", name : "cliente" },
                     { data : "vendedor", name : "vendedor" },
                     { data : "ubicacion", name : "ubicacion" },
                     { data : "estado", name : "estado" },
                     { data : "f_ingreso_puesto", name : "f_ingreso_puesto" },
                     { data : "puesto", name : "puesto" },
                     { data : "informo_ausencia", name : "informo_ausencia" },
                     { data : "obs_desarme_busqueda", name : "obs_desarme_busqueda" },
                     { data : "fecha_desarmado_anulado", name : "fecha_desarmado_anulado" },
                     { data : "puesto_final", name : "puesto_final" },
                ],
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
                        $(row).css('background-color', '#F08080');
                    }
                },				
                initComplete: function() {
                    var api = this.api();
                    api
                        .columns()
                        .eq(0)
                        .each(function(colIdx) {

                            if (colIdx !== 0 ) {
                                // Set the header cell to contain the input element
                                var cell = $('.filters th').eq(
                                    $(api.column(colIdx).header()).index()
                                );

                                var title = $(cell).text();
                                $(cell).html('<input style="width:100%" type="text" placeholder="' +
                                    title + '" />');

                                // On every keypress in this input
                                $(
                                        'input',
                                        $('.filters th').eq($(api.column(colIdx).header()).index())
                                    )
                                    .off('keyup change')
                                    .on('change', function(e) {
                                        // Get the search value
                                        $(this).attr('title', $(this).val());
                                        var regexr =
                                        '({search})'; //$(this).parents('th').find('select').val();
                                      
                                        var searchTerm = $(this).val();


                                
                                        // Search the column for that value
                                        api
                                            .column(colIdx)
                                            .search(searchTerm, true, false, true)
                                            .draw();
                                      

            
                                    });
                    
                            } else {
                                var cell = $('.filters th').eq(
                                    $(api.column(colIdx).header()).index()
                                );
                                $(cell).html('');
                            }
                        });
                },

        });
        });*/
  </script>
@endsection
