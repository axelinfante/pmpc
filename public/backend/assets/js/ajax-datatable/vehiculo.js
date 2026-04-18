(function($) {
	"use strict";
    // let temp = $("#btn1").clone();
    // $("#btn1").click(function(){
    //     $("#btn1").after(temp);
    // });

    // //Creamos una fila en el head de la tabla y lo clonamos para cada columna
    $('#vehiculos_table thead tr').clone(true).appendTo( '#vehiculos_table thead' );
		const array_const = [3,23];
			let inputArray = [
  			{ id: 2, name: "dominio_vacio"},
				{ id: 9, name: "marca_vacio"},
				{ id: 11, name: "motor_vacio"},
			];
		let ids = inputArray.map( (item) => item.id);
/*console.log(ids);

const objectWithId1 = inputArray.find(item => item.id === 4);
if (objectWithId1) {
  console.log(objectWithId1.name); // Using dot notation
  console.log(objectWithId1["value"]); // Using bracket notation
}*/


    $('#vehiculos_table thead tr:eq(1) th').each( function (i) {
        var title = $(this).text(); //es el nombre de la columna
		if(i != 0) {

				if (i == 32) {
					//if ($.trim(title)=="Fecha de ingreso"){
						$(this).html( '<input type="text" id="fecha_ingreso" name="fecha_ingreso" value="" class="form-control filtros" placeholder="Search...'+title+'" />' );
					}else{
						$(this).html( '<input type="text" value="" class="form-control filtros" placeholder="Search...'+title+'" />' );
					};
           
					
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
							$(this).html('<input type="checkbox" id="mostrar-todos-'+objectWithId1.name+'">vacios <input id="input-text-'+objectWithId1.name+'" class="form-control filtros" placeholder="Search...'+title+'" style="width:100%;" type="text" placeholder="' + title + '" />');
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

			if (i == 18) {
				//$(this).html('<select class="form-control filtros"><option value="">Todas</option> <option value="Si">Si</option> <option value="No">No</option></select>');

				var select = $('<select id="' + title + '" multiple="true" class="form-control select2"></select>')
				.appendTo( $(this).empty() )
				.on( 'change', function () {
					  		var val = $(this).val();
							vehiculo_table.column( i ).search(val ? val : '', false, false).draw();
				} );
				select.append( '<option value="-1">VACIOS</option>' );
				for (const row_x of Estados_tables) {
					//console.log(row_x.id+"-"+row_x.estado);
					select.append( '<option value="'+row_x.id+'">'+row_x.estado+'</option>' )
				}
  			
			}

			if (i == 30) {
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

			if(i == 29){

                $(this).html('<input type="checkbox" id="mostrar-todos-motorventa">vacios <input id="input-text-motor" style="width:100%;" type="text" placeholder="' + title + '" />');

                    let campoInput = $('#input-text-motor');
                    
                    $("#mostrar-todos-motorventa").change(function () {
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


                    $('#input-text-motor', this).on('change', function() {
                        if (vehiculo_table.column(i).search() !== this.value) {
                            vehiculo_table
                            .column(i)
                            .search(this.value)
                            .draw();
                        }
                    });

            }
			
			if(i == 21){

                $(this).html('<input type="checkbox" id="mostrar-todos-obs_administrativa">no vacias <input id="input-text-obs_administrativa" class="form-control filtros" placeholder="Search...'+title+'" style="width:100%;" type="text" placeholder="' + title + '" />');

                    let campoInput = $('#input-text-obs_administrativa');
                    
                    $("#mostrar-todos-obs_administrativa").change(function () {
                          let buscar= ($(this).is(':checked')) ? "novacios":"";
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


                    $('#input-text-obs_administrativa', this).on('change', function() {
                        if (vehiculo_table.column(i).search() !== this.value) {
                            vehiculo_table
                            .column(i)
                            .search(this.value)
                            .draw();
                        }
                    });

            }

			if (i == 12) {

				$(this).html('<select class="form-control filtros"><option value="">Todas</option> <option value="1">04 D</option> <option value="2">04 C</option> <option value="3">Moto c/alta motor</option> <option value="4">Moto baja definitiva</option> <option value="5">BD</option><option value="6">Alta de Motor</option></select>');
				$('.filtros', this).on('change', function () {
					if (vehiculo_table.column(i).search() !== this.value) {
	
						vehiculo_table
							.column(i)
							.search(this.value)
							.draw();
					}
	
				});
	
			}

//			if(jQuery.inArray("test", myarray)==0)
			//if($.inArray(i, [3,4])==0) {
			if (array_const.includes(i)) {	
			//if (i == 3) {
				$(this).html('<select class="form-control filtros"><option value="">Todas</option> <option value="Si">Si</option> <option value="No">No</option></select>');
				$('.filtros', this).on('change', function () {
					if (vehiculo_table.column(i).search() !== this.value) {
	
						vehiculo_table
							.column(i)
							.search(this.value)
							.draw();
					}
	
				});
	
			}


			if (i == 24) {
						$(this).html( '<input type="text" id="fecha_documentos" name="fecha_documentos" value="" class="form-control filtros" placeholder="Search...'+title+'" />' );
			}

		}
		
		else{
            $(this).html( '' );
		}

    } );
	// vehiculo_table.fnFilter('');

	let c_visible = [0, 1, 2,9,10,11,12,18,25,29,30];
// Reduce to create union
	let mapa_visible = c_visible.concat([...Array(35)].map((_,i) => ++i)).reduce((acc, value) => {
			if (!acc.includes(value)) acc.push(value);
			return acc;
	}, []);
		
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

								d.regitro_activo = $("#regitro_activo").prop('checked') == true ? 1 : 0;

            },
			// success: function(d) {
			// 	// $('#vehiculos_table').dataTable().fnFilter('');
				
			// },
			 error: function (request, status, error) {

			}
		}),
		"columns" : [
            { data : "action", name : "action", searcheable : false },
			{  data : 'id', name : 'id' },
			{  data : 'dominio', name : 'dominio' },
			{  data : 'anulado', name : 'anulado' },
			// {  data : 'nro_interno', name : 'nro_interno' },
			{  data : 'fecha_asignacion', name : 'fecha_asignacion' },
			// {  data : 'forma', name : 'forma' },
			{  data : 'tramitador', name : 'tramitador' },
			{  data : 'aseguradora', name : 'aseguradora' },
			{  data : 'tramitador_compania', name : 'tramitador_compania' },
			{  data : 'siniestro', name : 'siniestro' },
			{  data : 'marca', name : 'marca' },
			{  data : 'modelo', name : 'modelo' },
			{  data : 'motor', name : 'motor_nro' },
			{  data : 'tipo_baja', name : 'tipo_baja' },
			{  data : 'asegurado', name : 'asegurado' },
			{  data : 'contacto', name : 'contacto' },
			{  data : 'lugar_retiro', name : 'lugar_retiro' },
			{  data : 'localidad', name : 'localidad' },
			{  data : 'provincia', name : 'provincia' },
			{  data : 'estado', name : 'estado', searcheable : false},
			{  data : 'entregado_a', name : 'entregado_a' },
			{  data : 'fecha_entrega', name : 'fecha_entrega' },
			{  data : 'observacion_admin', name : 'observacion_admin' },
            {  data : 'fecha_recepcion', name : 'fecha_recepcion' },
            {  data : 'coordinar_retiro', name : 'coordinar_retiro' },//22
            {  data : 'fecha_envio_doc', name : 'fecha_envio_doc' },
            {  data : 'chasis', name : 'chasis' },
            {  data : 'fecha_confirmacion_contacto', name : 'fecha_confirmacion_contacto' },
            {  data : 'fecha_limite_retiro', name : 'fecha_limite_retiro' },
            {  data : 'responsable_retiro', name : 'responsable_retiro' },
            {  data : 'crp_nro', name : 'crp_nro' },
            {  data : 'entregar_en', name : 'entregar_en' },
            {  data : 'fecha_retiro', name : 'fecha_retiro' },
            {  data : 'fecha_ingreso', name : 'fecha_ingreso' },
            // {  data : 'control', name : 'control' },
			{  data : 'observacion_gerente_operario', name : 'observacion_gerente_operario' },
            {  data : 'observacion_retiro', name : 'observacion_retiro', },
						{  data : 'fecha_de_pago_cia', name : 'fecha_de_pago_cia', },


		],
		dom: 'Bfrtip',
		buttons: [
			{
					extend: 'colvis',
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
										vehiculo_table.search('').columns().search('').draw();
                    $('.filtros').val('');
										$('.select2').val('').trigger('change');
										//$('.select2').val(null).trigger('change');

                           }
       },
			{
				extend: 'colvis',
				text: "<i class='glyphicon glyphicon-eye-open' style='line-height: 1'></i><span> Columns</span>",
				columns: mapa_visible,
/*								columns: ':not(.noToggle)',
				columns: 'th:nth-child(n+2)',*/
				name: 'columnVisibility',
				prefixButtons: [
						{
								init: function (dt, btn) {
										btn.children().css('font-weight', 'bold');
										this.active(false);
								},
								action: function (e, dt, btn) {
										dt.columns().visible(true);
										//actionTrackTable.buttons('hideAllColumns:name').active(false);
										this.active(true);
								},
								text: 'Mostrar Todos',
								name: 'showAllColumns'
						},
						{
								init: function (dt, btn) {
										btn.children().css('font-weight', 'bold');
										this.active(false);
								},
								action: function (e, dt, btn) {
										var all_column = c_visible;
										dt.columns(':gt(0)').visible(false);
										dt.columns(all_column).visible(true);
										//actionTrackTable.buttons('showAllColumns:name').active(false);
										this.active(true);
								},
								text: 'Ocultar Todos',
								name: 'hideAllColumns'
						}
				]
		},
			 {
				extend: 'excel',
				text : "<i class='fa fa-file-export'></i> Exportar a Excel",
				title: "Vehiculo",
				filename: function () {
					var tableName =   //how catch attribute table.name??? new Date().format("yyyyMMddHHmmss")

														//if i have many table on page!!!
														//$('table.dataTable').DataTable(); //-- my selectid
														"vehiculo"+timeStamp()
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
				/*action: function (e, dt, button, config)
				{
					this.processing( true );
					var self = this;
					var oldStart = dt.settings()[0]._iDisplayStart;
					dt.one('preXhr', function (e, s, data) {

							// Just this once, load all data from the server...
							data.start = 0;
							data.length = -1;
							//data.length = 2147483647;
							//dt.columns(0).visible(false);
							dt.one('preDraw', function (e, settings) {

								--/var visibleColumns = vehiculo_table.columns(':visible').indexes().toArray();
								var columnData = [];
							$.each(visibleColumns, function (key, value) {
											if (value > 0) {
											columnData.push(vehiculo_table.settings()[0].aoColumns[value].data);
									}
							});

								var data = dt.buttons.exportData({
									columns: columnData
								});--/--

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
					//dt.columns(0).visible(true);
					this.processing( false );

				},*/



				/*action: function() {
					let params = vehiculo_table.ajax.params(); 
					var visibleColumns = vehiculo_table.columns(':visible').indexes().toArray();
					var columnData = [];

					$.each(visibleColumns, function (key, value) {
						//console.log(key + ": " + value );
            if (value > 0) {
								columnData.push(vehiculo_table.settings()[0].aoColumns[value].data);
            }
        });

					params.visible_columns = columnData;
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
				}*/
			},
			{
				extend: 'pdf',
			  text : "<i class='fa fa-file-pdf'></i> Exportar a PDF",
				/*text: 'Exportar a PDF',*/
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


		//responsive: true,
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


		$('#fecha_ingreso').daterangepicker({
			autoUpdateInput: false,
			locale: {
				format: 'YYYY-MM-DD',
				cancelLabel: 'Clear'
			}
		});

		
		$('#fecha_ingreso').on('change', function(e) {
			let val = $(this).val();
			vehiculo_table.columns(32).search(val ? val : '', true, false );
			vehiculo_table.draw();
		});
	
	
		$('#fecha_ingreso').on('apply.daterangepicker', function(ev, picker) {
				let daterango =(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
					$(this).val(daterango);
					vehiculo_table.columns(32).search(daterango);
					vehiculo_table.draw();
			});

			$('#fecha_ingreso').on('cancel.daterangepicker', function(ev, picker) {
				//$(this).val('');
				$('#fecha_ingreso').val(null).trigger('change');	
		});


		
			$('#fecha_documentos').daterangepicker({
			autoUpdateInput: false,
			locale: {
				format: 'YYYY-MM-DD',
				cancelLabel: 'Clear'
			}
		});

		
		$('#fecha_documentos').on('change', function(e) {
			let val = $(this).val();
			vehiculo_table.columns(24).search(val ? val : '', true, false );
			vehiculo_table.draw();
		});
	
	
		$('#fecha_documentos').on('apply.daterangepicker', function(ev, picker) {
				let daterango =(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
					$(this).val(daterango);
					vehiculo_table.columns(24).search(daterango);
					vehiculo_table.draw();
			});

			$('#fecha_documentos').on('cancel.daterangepicker', function(ev, picker) {
				//$(this).val('');
				$('#fecha_documentos').val(null).trigger('change');	
		});

		$('#regitro_activo').change(function() {
			vehiculo_table.draw();
			//var status = $(this).prop('checked') == true ? 1 : 0;
			//var id = $(this).data('id');
		})
/*
		 //initially clear select otherwise first option is selected
		 //$('#fecha_ingreso').val(null).trigger('change');	
	/*
		*/
		

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

		//$('.select2').val(null).trigger('change');

		$('.select2').select2({
                multiple: true,
                closeOnSelect: false//,
                //placeholder: "Select a " + title
              });


})(jQuery);

