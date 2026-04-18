(function($) {
	"use strict";
	
	$('#expense-table').DataTable({
		processing: true,
		serverSide: true,
		scrollX: true,
		ajax: _url + '/expense/get_caja_table_data',
		"columns" : [
			{ data : "trans_date", name : "trans_date" },
			{ data : "payer.name", name : "payer.name" },
			{ data : "razon_social", name : "razon_social" },
			{ data : "tipo_comprobante.descripcion", name : "tipo_comprobante.descripcion" },
			{ data : "account.account_title", name : "account.account_title" },
			{ data : "cuenta_imputar.account_title", name : "cuenta_imputar.account_title" },
			{ data : "expense_type.name", name : "expense_type.name" },
			{ data : "detalle_rubro", name : "detalle_rubro" },
			{ data : "amount", name : "amount" },
			{ data : "payment_method.name", name : "payment_method.name" },
			{ data : "banco", name : "banco" },
			{ data : "cheque_nro", name : "cheque_nro" },
			{ data : "cheque_vencimiento", name : "cheque_vencimiento" },
			{ data : "cheque_entregado_a", name : "cheque_entregado_a" },
			{ data : "action", name : "action" },
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
		"bAutoWidth":false,	
		"ordering": false,
		"language": {
		   "decimal":        "",
		   "emptyTable":     $lang_no_data_found,
		   "info":           $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " + $lang_entries,
		   "infoEmpty":      $lang_showing_0_to_0_of_0_entries,
		   "infoFiltered":   "(filtered from _MAX_ total entries)",
		   "infoPostFix":    "",
		   "thousands":      ",",
		   "lengthMenu":     $lang_show + " _MENU_ " + $lang_entries,
		   "loadingRecords": $lang_loading,
		   "processing":     $lang_processing,
		   "search":         $lang_search,
		   "zeroRecords":    $lang_no_matching_records_found,
		   "paginate": {
			  "first":      $lang_first,
			  "last":       $lang_last,
			  "next":       $lang_next,
			  "previous":   $lang_previous
		   }
		}
	});

})(jQuery);

