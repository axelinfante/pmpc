(function ($) {
    "use strict";

    let idCar = $('#idCar');
    var table = $('#expense-mov-table').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: _url + '/vehiculo/expense_get_table_data?idCar=' + idCar.val(),
        "columns": [
            { data: "action", name: "action" },
            { data: "trans_date", name: "trans_date" },
            { data: "razon_social", name: "razon_social" },
            { data: "amount", name: "amount" },
            { data: "payer.name", name: "payer.name" },
            { data: "tipo_comprobante.descripcion", name: "tipo_comprobante.descripcion" },
            { data: "account.account_title", name: "account.account_title" },
            { data: "imputar_a", name: "imputar_a" },
            { data: "expense_type.name", name: "expense_type.name" },
            { data: "detalle_rubro", name: "detalle_rubro" },
            
            { data: "payment_method.name", name: "payment_method.name" },
            { data: "banco", name: "banco" },
            { data: "cheque_nro", name: "cheque_nro" },
            { data: "cheque_vencimiento", name: "cheque_vencimiento" },
            { data: "cheque_entregado_a", name: "cheque_entregado_a" },
            { data: "tasa", name: "tasa" },
          
        ],
        createdRow: function (row, data, dataIndex) {
            // Accediendo al valor de la columna "estado"
            var estado = data.status;

            if (estado == 'Resuelto') {
                $(row).css('background-color', '#98FB98 !important'); // verde pastel
            } else {
                $(row).css('background-color', '#F08080 !important'); // Rojo pastel
            }
        },

        dom: 'Bfrtip', 
        buttons: [{
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
                let params = table.ajax.params(); 

                $.ajax({
                    url: routes.exportExcel + '?idCar=' + idCar.val(), 
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
                            'expense.xlsx'; 
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
                let params = table.ajax.params(); 

                $.ajax({
                    url: routes.exportPDF+ '?idCar=' + idCar.val(),  
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
                        'expense.pdf'; 
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
        "bStateSave": true,
        "bAutoWidth": false,
        "ordering": false,
        "language": {
            "decimal": "",
            "emptyTable": $lang_no_data_found,
            "info": $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " + $lang_entries,
            "infoEmpty": $lang_showing_0_to_0_of_0_entries,
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": $lang_show + " _MENU_ " + $lang_entries,
            "loadingRecords": $lang_loading,
            "processing": $lang_processing,
            "search": $lang_search,
            "zeroRecords": $lang_no_matching_records_found,
            "paginate": {
                "first": $lang_first,
                "last": $lang_last,
                "next": $lang_next,
                "previous": $lang_previous
            }
        }
    });
    table.search('').columns().search('').draw();
})(jQuery);

