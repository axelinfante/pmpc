var products_returns_table;
(function ($) {
	"use strict";

	$('#products_returns_table thead tr').clone(true).appendTo('#products_returns_table thead');
	$('#products_returns_table thead tr:eq(1) th').each(function (i) {
		var title = $(this).text(); //es el nombre de la columna
		 if(i != 7) {
		$(this).html('<input type="text" value="" class="form-control filtros" placeholder="Search...' + title + '" />');

		$('.filtros', this).on('change', function () {
			if (products_returns_table.column(i).search() !== this.value) {

				products_returns_table
					.column(i)
					.search(this.value)
					.draw();
			}

		});
		}else{
		    $(this).html( '' );
		}

	});



	products_returns_table = $('#products_returns_table').DataTable({
		// scrollX: true,
         processing: true,
         serverSide: true,
         searching: true,
         orderCellsTop: true,
         fixedHeader: true,
		 lengthMenu: [[ 10, 25, 50, 200 ], [10, 25,50, 200]],
		ajax: {
			url: _url + '/products_returns/get_table_data',
			method: "POST",
			data: function (d) {
				d._token = $('meta[name="csrf-token"]').attr('content');
				if ($('select[name=status]').val() != null) {
					d.status = $('select[name=status]').val(); // No necesitas stringify
				}
			},
		},
		columns: [
			{ data: 'return_number', name: 'return_number' },
			{ data: 'return_date', name: 'return_date' },
			{ data: 'invoice_id', name: 'invoice_id' },
			{ data: 'client', name: 'client' },
			// { data: 'product_id', name: 'product_id' },
			{ data: 'product_name', name: 'product_name' },
			//{ data: 'quantity', name: 'quantity', className: 'd-none' },
			{ data: 'note', name: 'note' },
			{ data: 'status', name: 'status' },
			{ data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false },
		],
		dom: 'Bfrltip',
		//dom: 'Bfrtip', // Incluye los botones en la interfaz
            buttons: [{
                    extend: 'excelHtml5',
                    text: 'Exportar a Excel',
                    title: 'Devoluciones', // Título del archivo Excel
                    exportOptions: {
                        columns: ':visible:not(:last-child)' ,
						rows: function(idx, data, node) {
							return true; // Incluye todas las filas sin importar la paginación
						}
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Exportar a PDF',
                    title: 'Devoluciones', // Título del archivo PDF
                    orientation: 'landscape', // Orientación horizontal
                    pageSize: 'A4', // Tamaño del papel
                    exportOptions: {
						columns: ':visible:not(:last-child)',
						rows: function(idx, data, node) {
							return true; // Incluye todas las filas sin importar la paginación
						}
                    },
                    customize: function(doc) {
                        // Personaliza el diseño del PDF
                        doc.styles.tableHeader = {
                            bold: true,
                            fontSize: 10,
                            color: 'black',
                            fillColor: '#f2f2f2'
                        };
                        doc.defaultStyle.fontSize = 8; // Tamaño de fuente general
                    }
                }
            ],
		bStateSave: true,
		bAutoWidth: false,
		ordering: false,
		searching: true,
		language: {
			// Aquí tus traducciones
		}
	});

	$('.select-filter').on('change', function (e) {
		products_returns_table.draw();
	});

	//products_returns_table.search('').columns().search('').draw();


})(jQuery);

