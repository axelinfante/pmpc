
    <div class="row">

        <div class="col-12">
            @csrf
            <div class="card mt-2">
                <span class="d-none panel-title">{{ _lang('Historial') }}</span>

                <div class="card-body">
                   
                  
                    @php $currency = currency() @endphp
                    <table id="historial" class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 100px;min-width: 100px">{{ _lang('Fecha') }}</th>
                                <th style="width: 100px;min-width: 100px">{{ _lang('Interno') }}</th>
                                <th style="width: 100px;min-width: 100px">{{ _lang('Dominio') }}</th>
                                <th style="width: 100px;min-width: 100px">{{ _lang('Datos viejos') }}</th>
                                <th style="width: 100px;min-width: 100px">{{ _lang('Datos nuevos ') }}</th>
                                <th style="width: 100px;min-width: 100px">{{ _lang('Usuario') }}</th>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



    <script>
        let date1 = $('#date1').val();
        let date2 = $('#date2').val();
        let estado = $('#estado').val();;

        $(function() {
            $('#historial thead tr')
                .clone(true)
                .addClass('filters')
                .appendTo('#orden-desarme-table thead');

            var historial = $('#historial').DataTable({
                scrollX: true,
                processing: true,
                serverSide: true,
                searching: true,
                orderCellsTop: true,
                fixedHeader: true,

                ajax: ({
                    url: '{{ url("vehiculo/list-historial?id=$id") }}',
                    // method: "POST",
                   

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
                        data: "valores_old",
                        name: "valores_old"
                    },
                    {
                        data: "new_values",
                        name: "new_values"
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
                "searching": false,
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
                historial.draw();
            });
            //historial.search('').columns().search('').draw();
        });
    </script>

