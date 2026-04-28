(function ($) {
    "use strict";

    $('#expense-table thead tr').clone(true).appendTo('#expense-table thead');
    $('#expense-table thead tr:eq(1) th').each(function (i) {
        var title = $(this).text(); //es el nombre de la columna
        if (i != 17) {

            
            $(this).html('<input type="text" value="" class="form-control filtros" placeholder="Search...' + title + '" />');

            $('.filtros', this).on('change', function () {
                if (table.column(i).search() !== this.value) {

                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }

            });
            if (i == 19) {

                $(this).html('<select class="form-control filtros"><option value="">Todas</option> <option value="-1">Normal</option> <option value="urgente">Urgente</option> <option value="muy_urgente">Muy Urgente</option> <option value="no_pagar">No Pagar</option></select>');

                $('.filtros', this).on('change', function () {
                    if (table.column(i).search() !== this.value) {

                        table
                            .column(i)
                            .search(this.value)
                            .draw();
                    }

                });

            }
        } else {
            // $(this).html( '' );
            $(this).html('<select class="form-control filtros"> <option value="">Seleccione</option> <option value="1">pendiente</option> <option value="2">resuelto</option> </select>');

            $('.filtros', this).on('change', function () {
                if (table.column(i).search() !== this.value) {

                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }

            });
        }

    });

    let verVehiculo = $('#verVehiculo');

    var table = $('#expense-table').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: ({ 
            url: _url + '/expense/get_table_data',
            method: "GET",
            data: function (d) {
                
                if(verVehiculo.is(':checked')){
                    d.verVehiculo = true;
                }else{
                    d.verVehiculo = false;
                }
                    
            }

        }),
        "columns": [
            { data: "action", name: "action" },
            { data: "trans_date", name: "trans_date" },
            { data: "razon_social", name: "razon_social" },
            { data: "dominio", name: "dominio" },
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
            { data: "status", name: "status" },
            { data: "pagos_car", name: "pagos_car" },
           
            { data: "payment_priority", name: "payment_priority" },
            { data: "note", name: "note" },
            
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
        //responsive: true,
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

    $('.select-filter').on('change', function(e) {
        table.draw();
    });

    let idCar = $('#idCar');
    $('#expense-mov-table').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: _url + '/expense/get_table_data?idCar=' + idCar.val(),
        "columns": [
            { data: "trans_date", name: "trans_date" },
            { data: "payer.name", name: "payer.name" },
            { data: "razon_social", name: "razon_social" },
            { data: "tipo_comprobante.descripcion", name: "tipo_comprobante.descripcion" },
            { data: "account.account_title", name: "account.account_title" },
            { data: "imputar_a", name: "imputar_a" },
            { data: "expense_type.name", name: "expense_type.name" },
            { data: "detalle_rubro", name: "detalle_rubro" },
            { data: "amount", name: "amount" },
            { data: "payment_method.name", name: "payment_method.name" },
            { data: "banco", name: "banco" },
            { data: "cheque_nro", name: "cheque_nro" },
            { data: "cheque_vencimiento", name: "cheque_vencimiento" },
            { data: "cheque_entregado_a", name: "cheque_entregado_a" },
            { data: "tasa", name: "tasa" },
            { data: "action", name: "action" },
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


        //responsive: true,
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

