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
                <span class="d-none panel-title">{{ _lang('Estado y Seguimiento de Vehiculo') }}</span>
                <div class="card-body">
                    <div class="table-responsive" style="padding: 10px;">
                    <table id="orden-desarme-table" class="table table-bordered" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="text-center">{{ _lang('Interno') }}</th>
                                <th class="text-center">{{ _lang('Dominio') }}</th>
                                <th class="text-center">{{ _lang('Marca Modelo') }}</th>
                                <th class="text-center">{{ _lang('Motor') }}</th>
                                <th class="text-center">{{ _lang('Tipo baja') }}</th>
                                <th class="text-center">{{ _lang('Estado') }}</th>
                                <th class="text-center">{{ _lang('Ubicacion') }}</th>
                                <th class="text-center">{{ _lang('Observaciones Retiro') }}</th>
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
            ajax: '{{ route('list_estado_vehiculo') }}',
            dataType: "json",
            orderCellsTop: true,
            fixedHeader: true,
            columns : [
                     { data : "interno", name : "interno" },
                     { data : "dominio", name : "dominio" },
                     { data : "marcamodelo", name : "marcamodelo" },
                     { data : "motor_nro", name : "motor_nro" },
                     { data : "tipo_baja", name : "tipo_baja" },
                     { data : "estado", name : "estado" },
                     { data : "ubicacion", name : "ubicacion" },
                     { data : "observacion_retiro", name : "observacion_retiro" },
                ], 
                initComplete: function() {
                    var api = this.api();
                    api
                        .columns()
                        .eq(0)
                        .each(function(colIdx) {

                            if (colIdx !== 99 ) {
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
                                        /*var cursorPosition = this.selectionStart;*/
                                        // Search the column for that value
                                        api
                                            .column(colIdx)
                                            .search(searchTerm, true, false, true)
                                            .draw();
                                    })
                                    .on('keyup', function(e) {
                                        e.stopPropagation();

                                        var cursorPosition = this.selectionStart;

                                        $(this).trigger('change');
                                            $(this)
                                            .focus()[0]
                                            .setSelectionRange(cursorPosition, cursorPosition);
                                        
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
        });
  </script>
@endsection
