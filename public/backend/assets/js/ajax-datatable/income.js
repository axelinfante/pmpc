(function($) {
	"use strict";

    // //Creamos una fila en el head de la tabla y lo clonamos para cada columna

    $('#income-table thead tr').clone(true).appendTo( '#income-table thead' );
    $('#income-table thead tr:eq(1) th').each( function (i) {
        var title = $(this).text(); //es el nombre de la columna
        if(i != 15) {
            $(this).html( '<input type="text" value="" class="form-control filtros" placeholder="Search...'+title+'" />' );

            $( '.filtros', this ).on( 'change', function () {
                if ( table.column(i).search() !== this.value ) {

                    table
                        .column(i)
                        .search( this.value )
                        .draw();
                }

            } );
        }else{
            $(this).html( '' );
        }

    } );

    var table = $('#income-table').DataTable({
		processing: true,
		serverSide: true,
		scrollX: true,
		ajax: _url + '/income/get_table_data',
		"columns" : [
			{ data : "trans_date", name : "trans_date" },
			{ data : "payer.name", name : "payer.name" },
			{ data : "razon_social", name : "razon_social" },
			{ data : "tipo_comprobante.descripcion", name : "tipo_comprobante.descripcion" },
			{ data : "account.account_title", name : "account.account_title" },
			{ data : "imputar_a", name : "imputar_a" },
			{ data : "income_type.name", name : "income_type.name" },
			{ data : "detalle_rubro", name : "detalle_rubro" },
			{ data : "amount", name : "amount" },
			{ data : "payment_method.name", name : "payment_method.name" },
			{ data : "banco", name : "banco" },
			{ data : "cheque_nro", name : "cheque_nro" },
			{ data : "cheque_vencimiento", name : "cheque_vencimiento" },
			{ data : "cheque_entregado_a", name : "cheque_entregado_a" },
			{ data : "tasa", name : "tasa" },
			{ data : "action", name : "action" },
		],
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

