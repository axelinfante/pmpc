(function ($) {
    "use strict";

    $('#vehiculos_table thead tr').clone(true).appendTo('#vehiculos_table thead');
    $('#vehiculos_table thead tr:eq(1) th').each(function (i) {
        var title = $(this).text();
        if (i != 0) {
            $(this).html('<input type="text" value="" class="form-control filtros" placeholder="Search..." />');
            $('.filtros', this).on('change', function () {
                var searchVal = this.value;
                // Verifica si la columna es una de las columnas de fecha
                if (title === 'F.Asignacion' || title === 'Fecha Inicio' || title === 'Fecha Finalizacion') {
                    var dateParts = searchVal.split('-');
                    if (dateParts.length === 3) {
                        // Convierte la fecha al formato yyyy-MM-dd
                        searchVal = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
                    }
                }
                if (vehiculo_table.column(i).search() !== searchVal) {
                    vehiculo_table
                        .column(i)
                        .search(searchVal)
                        .draw();
                }
            });
        } else {
            $(this).html('');
        }
    });


    var vehiculo_table = $('#vehiculos_table').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        orderCellsTop: true,
        fixedHeader: true,
        ajax: {
            url: _url + '/tramitador/get_table_data',
            method: "POST",
            data: function (d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                if ($('select[name=client_id]').val() != '') {
                    d.client_id = $('select[name=client_id]').val();
                }
                if ($('select[name=status]').val() != null) {
                    d.status = JSON.stringify($('select[name=status]').val());
                }
                if ($('select[name=estado_tramite]').val() != null) {
                    d.estado_tramite = JSON.stringify($('select[name=estado_tramite]').val());
                }

                
            },
            error: function (request, status, error) {  
                console.log(request.responseText);
            }
        },
        columns: [
            { data: "action", name: "action", searchable: true },
            { data: 'dominio', name: 'dominio', searchable: true },
            { data: 'id', name: 'id', searchable: true },
            { data: 'fecha_asignacion', name: 'fecha_asignacion', searchable: true },
            { data: 'tramitador', name: 'tramitador', searchable: true },
            { data: 'aseguradora', name: 'aseguradora', searchable: true },
            { data: 'company', name: 'company', searchable: true },
            { data: 'siniestro', name: 'siniestro', searchable: true },
            { data: 'marca_modelo', name: 'marca_modelo', searchable: true },
            { data: 'fecha_inicio', name: 'fecha_inicio', searchable: true },
            { data: 'fecha_finalizacion', name: 'fecha_finalizacion', searchable: true },
            { data: 'estado_tramite', name: 'estado_tramite', searchable: true, visible: false },
        ],
        columnDefs: [
            {
                targets: [11], // Índice de la columna que deseas ocultar
                visible: false,
                searchable: true
            }
        ],
        //dom: 'lfrtip', // Incluye el cuadro de búsqueda general
        dom: 'Bfrtip', 
        buttons: [
            {
					extend: 'colvis',
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
                    $('.filtros').val('');
                    $('.select-filter').val(null).trigger('change');
					//$('.select-filter').val('');
					vehiculo_table.search('').columns().search('').draw();

                           }
        },
            {
            extend: 'excel',
            text: 'Exportar a Excel',
            exportOptions: {
                columns: ':visible',
                modifier: {
                    search: 'applied',
                    order: 'applied',
                    page: 'all'
                }
            },
            action: function() {
                let params = vehiculo_table.ajax.params(); 

                $.ajax({
                    url: routes.exportExcel, 
                    type: 'POST', 
                    data: {
                        ...params,
                        _token: routes.csrfToken 
                    },
                    xhrFields: {
                        responseType: 'blob' 
                    },
                    success: function(response) {
                        
                        let blob = new Blob([response], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        });
                        let link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download =
                            'vehiculo.xlsx'; 
                        link.click();
                    },
                    error: function(xhr) {
                        
                        alert('Hubo un error al exportar el archivo.');
                    }
                });
            }

        },
        {
            extend: 'pdf',
            text: 'Exportar a PDF',
            exportOptions: {
                columns: ':visible',
                modifier: {
                    search: 'applied',
                    order: 'applied',
                    page: 'all'
                }
            },
            action: function() {
                let params = vehiculo_table.ajax.params(); 

                $.ajax({
                    url: routes.exportPDF, 
                    type: 'POST', 
                    data: {
                        ...params, 
                        _token: routes.csrfToken 
                    },
                    xhrFields: {
                        responseType: 'blob' 
                    },
                    success: function(response) {
                        // Descarga el archivo PDF
                        let blob = new Blob([response], {
                            type: 'application/pdf'
                        });
                        let link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download =
                        'vehiculos.pdf'; 
                        link.click();
                    },
                    error: function(xhr) {
                        // Manejo de errores
                        alert('Hubo un error al exportar el archivo.');
                    }
                    });
                }

            },

        ],
        bStateSave: true,
        bAutoWidth: false,
        ordering: false,
        searching: true,
        language: {
            decimal: "",
            emptyTable: $lang_no_data_found,
            info: $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " + $lang_entries,
            infoEmpty: $lang_showing_0_to_0_of_0_entries,
            infoFiltered: "(filtered from _MAX_ total entries)",
            infoPostFix: "",
            thousands: ",",
            lengthMenu: $lang_show + " _MENU_ " + $lang_entries,
            loadingRecords: $lang_loading,
            processing: $lang_processing,
            search: $lang_search,
            zeroRecords: $lang_no_matching_records_found,
            paginate: {
                first: $lang_first,
                last: $lang_last,
                next: $lang_next,
                previous: $lang_previous
            }
        },
        rowCallback: function(row, data) {
            // Obtener el valor de estado_tramite
            var estadoTramite = data.estado_tramite;
            
            // Aplicar color de fondo basado en estado_tramite
            if (estadoTramite === 'En Proceso') {
                $(row).css('background-color', '#33FFAC'); // Verde
            } else if (estadoTramite === 'En Gestoria') {
                $(row).css('background-color', '#33A8FF'); // Azul
            } else if (estadoTramite === 'Finalizado') {
                $(row).css('background-color', '#FFC433'); // Naranja
            } else {
                $(row).css('background-color', '#FFFFFF'); // Color por defecto
            }
        },
        createdRow: function(row, data, dataIndex) {
            $('td', row).eq(5).css('font-size', '12px'); 
            $('td', row).eq(8).css('font-size', '10px'); 
            $('td', row).eq(3).css('font-size', '12px'); 
            $('td', row).eq(9).css('font-size', '12px'); 
            $('td', row).eq(10).css('font-size', '12px'); 
        }
    }).on('init.dt', function () {
        $('[data-toggle="tooltip"]').tooltip();
    });


    $('.select-filter').on('change', function (e) {
        vehiculo_table.draw();
    });

    $(window).resize(function () {
        vehiculo_table.columns.adjust().draw();
    });

    vehiculo_table.search('').columns().search('').draw();

    $('.page-container').addClass('sbar_collapsed');
    
   

})(jQuery);