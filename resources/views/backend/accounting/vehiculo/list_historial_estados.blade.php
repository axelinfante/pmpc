@extends('layouts.app')

@section('content')
    <div class="row">

        <div class="col-12">
            @csrf
            <div class="card mt-2">
                <span class="d-none panel-title">{{ _lang('Historial de estados por fecha') }}</span>

                <div class="card-body">
                    <div class="report-params">
                        <div class="row">
                            {{ csrf_field() }}

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Seleccione un Estado') }}</label>
                                    <select id= 'estado' name = 'estado' class="form-control select2 select-filter" name="account">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        {{ create_option('estados', 'id', 'estado') }}
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Desde') }}</label>
                                    <input type="date" class="form-control select-filter" name="date1" id="date1"
                                        value="{{ old('date1') }}">
                                </div>
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Hasta') }}</label>
                                    <input type="date" class="form-control select-filter" name="date2" id="date2"
                                        value="{{ old('date2') }}">
                                </div>
                            </div>

                        </div>
                    </div><!--End Report param-->
                    <div class="report-header">
                        <h4> Reporte cambio de estados de vehiculos por fecha</h4>
                   
                    </div>

                    @php $currency = currency() @endphp
                    <table id="estados-fecha-table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 100px;min-width: 100px">{{ _lang('FECHA') }}</th>
                                <th style="width: 100px;min-width: 100px">{{ _lang('INTERNO') }}</th>
                                <th style="width: 100px;min-width: 100px">{{ _lang('DOMINIO') }}</th>
                                <th style="width: 100px;min-width: 100px">{{ _lang('ESTADO ANTERIOR') }}</th>
                                <th style="width: 100px;min-width: 100px">{{ _lang('NUEVO ESTADO') }}</th>
                                <th style="width: 100px;min-width: 100px">{{ _lang('USUARIO') }}</th>

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
        let date1 = $('#date1').val();
        let date2 = $('#date2').val();
        let estado = $('#estado').val();;

        $(function() {
            $('#estados-fecha-table thead tr')
                .clone(true)
                .addClass('filters')
                .appendTo('#orden-desarme-table thead');

            var table_estados = $('#estados-fecha-table').DataTable({
                scrollX: true,
                processing: true,
                serverSide: true,
                searching: true,
                orderCellsTop: true,
                fixedHeader: true,

                ajax: ({
                    url: '{{ url("get_estados_fecha_table_data") }}',
                    method: "POST",
                    data: function(d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');

                        if ($('select[name=estado]').val() != null) {
                            d.estado = $('select[name=estado]').val();
                        }

                        if ($('input[name=date1]').val() != null) {
                            d.date1 = $('input[name=date1]').val();
                        }
                        
                        if ($('input[name=date2]').val() != null) {
                            d.date2 = $('input[name=date2]').val();
                        }

                    },

                }),


                "columns": [

                    {
                        data: "fecha",
                        name: "fecha"
                    },
                    {
                        data: "interno",
                        name: "interno"
                    },
                    {
                        data: "dominio",
                        name: "dominio"
                    },
                    {
                        data: "state_ant",
                        name: "state_ant"
                    },
                    {
                        data: "state_new",
                        name: "state_new"
                    },
                    {
                        data: "usuario",
                        name: "usuario"
                    },

                ],
                responsive: false,
                "bStateSave": true,
                "bAutoWidth": false,
                "ordering": false,
                "language": {
                    "decimal": "",
                    "emptyTable": "{{ _lang('No Data Found') }}",
                    "info": "{{ _lang('Showing') }} _START_ {{ _lang('to') }} _END_ {{ _lang('of') }} _TOTAL_ {{ _lang('Entries') }}",
                    "infoEmpty": "{{ _lang('Showing 0 To 0 Of 0 Entries') }}",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "{{ _lang('Show') }} _MENU_ {{ _lang('Entries') }}",
                    "loadingRecords": "{{ _lang('Loading...') }}",
                    "processing": "{{ _lang('Processing...') }}",
                    "search": "{{ _lang('Search') }}",
                    "zeroRecords": "{{ _lang('No matching records found') }}",
                    "paginate": {
                        "first": "{{ _lang('First') }}",
                        "last": "{{ _lang('Last') }}",
                        "next": "{{ _lang('Next') }}",
                        "previous": "{{ _lang('Previous') }}"
                    }
                },

                initComplete: function() {
                    var api = this.api();

                    // For each column
                    api
                        .columns()
                        .eq(0)
                        .each(function(colIdx) {
                            // Set the header cell to contain the input element
                            var cell = $('.filters th').eq(
                                $(api.column(colIdx).header()).index()
                            );

                            var title = $(cell).text();
                            //console.log($(api.column(colIdx).header()).index());
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

                                    var cursorPosition = this.selectionStart;
                                    // Search the column for that value
                                    api
                                        .column(colIdx)
                                        .search(
                                            this.value != '' ?
                                            regexr.replace('{search}', '(((' + this.value +
                                                ')))') :
                                            '',
                                            this.value != '',
                                            this.value == ''
                                        )
                                        .draw();
                                })
                                .on('keyup', function(e) {
                                    e.stopPropagation();

                                    $(this).trigger('change');
                                    $(this)
                                        .focus()[0]
                                        .setSelectionRange(cursorPosition, cursorPosition);
                                });
                        });
                },
            });

            $('.select-filter').on('change', function(e) {
                table_estados.draw();
            });

            table_estados.search('').columns().search('').draw();

        });
    </script>
@endsection
