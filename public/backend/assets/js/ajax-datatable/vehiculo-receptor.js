(function($) {
	"use strict";
    // let temp = $("#btn1").clone();
    // $("#btn1").click(function(){
    //     $("#btn1").after(temp);
    // });

    // //Creamos una fila en el head de la tabla y lo clonamos para cada columna

    $('#vehiculos_table thead tr').clone(true).appendTo( '#vehiculos_table thead' );
    $('#vehiculos_table thead tr:eq(1) th').each( function (i) {
		//const array_const = [3,23];
			let inputArray = [
  			{ id: 2, name: "dominio_vacio"},
				{ id: 5, name: "marca_vacio"},
			];
		let ids = inputArray.map( (item) => item.id);
        var title = $(this).text(); //es el nombre de la columna
		if(i != 0) {
            $(this).html( '<input type="text" value="" class="form-control filtros" placeholder="Search...'+title+'" />' );

            $( '.filtros', this ).on( 'change', function () {
                if ( vehiculo_table.column(i).search() !== this.value ) {

                    vehiculo_table
                        .column(i)
                        .search( this.value )
                        .draw();
                }

            } );
			
				if (ids.includes(i)){
					const objectWithId1 = inputArray.find(item => item.id === i);
						if (objectWithId1) {
							$(this).html('<input type="checkbox" id="mostrar-todos-'+objectWithId1.name+'">vacios <input id="input-text-'+objectWithId1.name+'" style="width:100%;" type="text" placeholder="' + title + '" />');
								//	let nombreDinamico = "miCampoPersonalizado" + Math.random().toString(36).substring(2); // Genera un nombre único
								let nombreDinamico = 'input-text-'+objectWithId1.name; // Genera un nombre único
                    let campoInput = $('#'+nombreDinamico);
                    $('#mostrar-todos-'+objectWithId1.name).change(function () {
                          let buscar= ($(this).is(':checked')) ? "todos":"";
                            if ($(this).is(':checked')) {
                                campoInput.hide();
                            } else {
                               campoInput.val(''); 
                               campoInput.show();
                            }

                            vehiculo_table
                            .column(i)
                            .search(buscar)
                            .draw();
                        });


                    $('#input-text-'+objectWithId1.name, this).on('change', function() {
                        if (vehiculo_table.column(i).search() !== this.value) {
                            vehiculo_table
                            .column(i)
                            .search(this.value)
                            .draw();
                        }
                    });

						}
			}
			
				if (i == 9) {
				var select = $('<select id="' + title + '" multiple="true" class="form-control select2"></select>')
				.appendTo( $(this).empty() )
				.on( 'change', function () {
					  		var val = $(this).val();
							vehiculo_table.column( i ).search(val ? val : '', false, false).draw();
				} );
			select.append( '<option value="-1">VACIOS</option>' );
			for (const row_xx of lugarentregas_tables) {
					select.append( '<option value="'+row_xx.id+'">'+row_xx.nombre+'</option>' )
			}

			}
			
		}else{
            $(this).html( '' );
		}

    } );


    var vehiculo_table = $('#vehiculos_table').DataTable({
		processing: true,
		serverSide: true,
         scrollX: true,
        orderCellsTop: true,
        fixedHeader: true,
        ajax: ({
			url : _url + '/vehiculo/get_table_data',
			method: "POST",
			data: function (d) {
				d._token =  $('meta[name="csrf-token"]').attr('content');

                if($('select[name=client_id]').val() != ''){
	                d.client_id = $('select[name=client_id]').val();
	            }

                if($('select[name=status]').val() != null){
                	d.status = JSON.stringify($('select[name=status]').val());
                }

            },
			 error: function (request, status, error) {
				console.log(request.responseText);
			 }
		}),
		"columns" : [
            { data : "action", name : "action", searcheable : false },
			{  data : 'id', name : 'id' },
			//{  data : 'anulado', name : 'anulado' },
			// {  data : 'nro_interno', name : 'nro_interno' },
			//{  data : 'fecha_asignacion', name : 'fecha_asignacion' },
			// {  data : 'forma', name : 'forma' },
			//{  data : 'tramitador', name : 'tramitador' },
			//{  data : 'aseguradora', name : 'aseguradora' },
			//{  data : 'tramitador_compania', name : 'tramitador_compania' },
			//{  data : 'siniestro', name : 'siniestro' },
			{  data : 'dominio', name : 'dominio' },
			// {  data : 'marca_modelo', name : 'marca_modelo' },
			// {  data : 'motor', name : 'motor_nro' },
			// {  data : 'tipo_baja', name : 'tipo_baja' },
			// {  data : 'asegurado', name : 'asegurado' },
			// {  data : 'contacto', name : 'contacto' },
			// {  data : 'lugar_retiro', name : 'lugar_retiro' },
			// {  data : 'localidad', name : 'localidad' },
			// {  data : 'provincia', name : 'provincia' },
			// {  data : 'estado', name : 'estado', searcheable : false},
			//{  data : 'entregado_a', name : 'entregado_a' },
			// {  data : 'fecha_entrega', name : 'fecha_entrega' },
			//{  data : 'observacion_admin', name : 'observacion_admin' },
            //{  data : 'fecha_recepcion', name : 'fecha_recepcion' },
            // {  data : 'coordinar_retiro', name : 'coordinar_retiro' },
            // {  data : 'fecha_envio_doc', name : 'fecha_envio_doc' },
            // {  data : 'chasis', name : 'chasis' },
            // {  data : 'fecha_confirmacion_contacto', name : 'fecha_confirmacion_contacto' },
            // {  data : 'fecha_limite_retiro', name : 'fecha_limite_retiro' },
            {  data : 'responsable_retiro', name : 'responsable_retiro' },
			{  data : 'color', name : 'color' },
						{  data : 'marca', name : 'marca' },
			{  data : 'modelo', name : 'modelo' },

            // {  data : 'crp_nro', name : 'crp_nro' },
            {  data : 'kilometraje', name : 'kilometraje' },
            // {  data : 'retira', name : 'retira' },
            {  data : 'pieza_no_disponible', name : 'pieza_no_disponible' },
			{  data : 'lugar_entrega', name : 'lugar_entrega', searcheable : false},
            // {  data : 'control', name : 'control' },
            // {  data : 'observacion_retiro', name : 'observacion_retiro', },


		],
        "dom": 'Bfrtip',
				buttons: [
			{
					extend: 'colvis',
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
                    $('.filtros').val('');
										$('.select-filter').val('');
										vehiculo_table.search('').columns().search('').draw();

                           }
       },
			 {
				extend: 'excel',
				text : "<i class='fa fa-file-export'></i> Exportar a Excel",
				title: "Vehiculo",
				filename: function () {
					var tableName =  "vehiculo"+timeStamp()
					 return tableName;
					 },
				exportOptions: {
					columns: [ ':not(.act):visible' ]	,
					//columns: ':visible',
					modifier: {
						search: 'applied',
						order: 'applied',
						selected: true, 
						page: 'all'
					}
				}, action: newexportaction, 
			},
		],
		// responsive: true,
		"bStateSave": true,
		"bAutoWidth":false,	
		"ordering": false,
		"searching": true,
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
	}).on( 'init.dt', function () {
         $('[data-toggle="tooltip"]').tooltip();
    });

    $('.select-filter').on('change', function(e) {
        vehiculo_table.draw();
    });


	vehiculo_table.search('').columns().search('').draw();

		 function newexportaction(e, dt, button, config) {

this.processing( true );
var self = this;
var oldStart = dt.settings()[0]._iDisplayStart;
dt.one('preXhr', function (e, s, data) {
  // Just this once, load all data from the server...
  data.start = 0;
  //data.length = 2147483647;
  data.length = -1;
  dt.one('preDraw', function (e, settings) {
      // Call the original action function
      if (button[0].className.indexOf('buttons-copy') >= 0) {
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-excel') >= 0) {
          $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-csv') >= 0) {
          $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
          $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-print') >= 0) {
          $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
      }
      dt.one('preXhr', function (e, s, data) {
          // DataTables thinks the first item displayed is index 0, but we're not drawing that.
          // Set the property to what it was before exporting.
          settings._iDisplayStart = oldStart;
          data.start = oldStart;
      });
      // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
      setTimeout(dt.ajax.reload, 0);
      // Prevent rendering of the full data to the DOM
      return false;
  });
});
// Requery the server with the new one-time export settings
dt.ajax.reload();
this.processing( false );
}

function timeStamp() {
		// Create a date object with the current time
			let now = new Date();
		
		// Create an array with the current month, day and time
			let date = [ now.getMonth() + 1, now.getDate(), now.getFullYear() ].map(d=>d.toString().length === 1 ? "0"+d : d);
		
		// Create an array with the current hour, minute and second
			let time = [ now.getHours(), now.getMinutes(), now.getSeconds() ].map(d=>d.toString().length === 1 ? "0"+d : d);
		
		
		// Return the formatted string
			return time.join(":") + "_" + date.join(".");
		}

$('.select2').select2({
                multiple: true,
                closeOnSelect: false//,
                //placeholder: "Select a " + title
              });

})(jQuery);

