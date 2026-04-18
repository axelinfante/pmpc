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
    $(function($) {
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

                                        /*var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                        column.search(val ? val : '', true, false).draw();*/

                                       
                                        /*if (typeof(textarea.selectionStart) == 'number') {
	                                    	pos = textarea.selectionStart;
	                                    }*/
                                        var searchTerm = $(this).val();


                                        /*var cursorPosition = this.selectionStart;*/
                                        // Search the column for that value
                                        api
                                            .column(colIdx)
                                            .search(searchTerm, true, false, true)
                                            .draw();
                                        /*api
                                            .column(colIdx)
                                            .search(
                                                this.value != '' ?
                                                regexr.replace('{search}', this.value) :                                                '',
                                                this.value != '',
                                                this.value == ''
                                            )
                                            .draw();*/

              /*                              .column(colIdx)
    .search("^" + searchTerm + "$", true, false, true)
    .draw();*/
                                    });
                                    /*.on('keyup', function(e) {
                                        e.stopPropagation();

                                        var cursorPosition = this.selectionStart;

                                        $(this).trigger('change');
                                            $(this)
                                            .focus()[0]
                                            .setSelectionRange(cursorPosition, cursorPosition);
                                        
                                    });*/
                            } else {
                                var cell = $('.filters th').eq(
                                    $(api.column(colIdx).header()).index()
                                );
                                $(cell).html('');
                            }
                        });
                },

        });
        });
  </script>
@endsection
