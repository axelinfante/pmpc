
    <div class="row">

        <div class="col-12">
            <div class="card mt-2">
                <span class="d-none panel-title">{{ _lang('Historial de Invoice') }}</span>
                <div class="card-body">
                     <table id="historial" class="table table-striped">
          <thead class="thead-dark">
            <tr>
              <th scope="col">Model</th>
              <th scope="col">Accion</th>
              <th scope="col">Usuario</th>
              <th scope="col">Fecha</th>
              <th scope="col">Valores Anteriores</th>
              <th scope="col">Nuevos Valores</th>
            </tr>
          </thead>
          <tbody >
            
          </tbody>
        </table>
                </div>
            </div>
        </div>
    </div>



    <script>
			$(function() {
			var table = $('#historial').DataTable({
			processing: true,
            serverSide: true,
            searchDelay: 2000,
            paging: true,
            orderCellsTop: true,
            fixedHeader: true,
			width: "auto",
			autoWidth: false,
            ///url: '{{ route("products.auditoriaProducto",$id) }}',
			ajax: ({
                    url: '{{ route("quotations.auditoriaQuotation",$id) }}',
                    // method: "POST",
                   

                }),
            iDisplayLength: "25",
            dom: "<'row'<'col-md-3'l><'col-md-5 mb-2'B><'col-md-4 justify-content-end'f>>tr<'row'<'col-md-5'i><'col-md-7 mt-2'p>>",
            "buttons": [
                {extend: 'excel',text: '<i class="bi bi-file-earmark-excel-fill"></i> Excel',
				exportOptions: {columns: ':visible:not(.notexport)'}},
                {extend: 'csv',text: '<i class="bi bi-file-earmark-excel-fill"></i> CSV', exportOptions: {columns: ':visible:not(.notexport)'}},
                {extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> Print',
                    title: "Bancos",
					exportOptions: {columns: ':visible:not(.notexport)'},
                    customize: function (win) {
                        $(win.document.body).find('h1').css('font-size', '15pt');
                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('h1').css('margin-bottom', '20px');
                        $(win.document.body).css('margin', '35px 25px');
                    }
                },
            ],
            ordering: false,
			columns: [
                    { data: 'model', name: 'model' },
                    { data: 'event', name: 'event' },
					{ data: 'usuario', name: 'usuario' },
                   { data: 'created_at', name: 'created_at' },
                    { data: 'valores_ant', name: 'valores_ant' },
                    { data: 'valores_nue', name: 'valores_nue' },
                ],
                lengthMenu: [25, 50, 100]
        });
		
		 //table.draw();
		});
	
      /*  $(function() {
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
                    url: '{{ route("products.auditoriaProducto",$id) }}',
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
        });*/
    </script>

