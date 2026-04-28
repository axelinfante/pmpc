@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card mt-2">
            <span class="panel-title d-none">{{ _lang('List Product') }}</span>
            <div class="card-body">
                <div class="row">

                     <div class="col mb-2">
                     <a id="prueba" class="btn btn-primary btn-xs" data-title="{{ _lang('Add Product') }}" href="{{ route('products.create') }}"><i
                     class="ti-plus"></i> {{ _lang('Add New') }}</a>
                     </div>
                </div>

                <hr>
				    <div style="width: 100%; padding-left: -10px;">
					<div class="table-responsive dt-responsive"> 

                <table id="table-data-product" class="table-bordered"> 
                        <thead>
                            <tr>
                                <th>ID de producto</th>
                                <th class="text-right">{{ _lang('Fecha registro') }}</th>
                                <th class="text-right">{{ _lang('Fecha ingreso a stock') }}</th>
                                <th>{{ _lang('Nro Interno') }}</th>
                                <th>{{ _lang('Dominio') }}</th>
                                <th>{{ _lang('Product') }}</th>
                                <th>{{ _lang('Marca') }}</th>
                                <th>{{ _lang('Modelo') }}</th>
                                <th>{{ _lang('nº motor') }}</th>
                                <th>{{ _lang('nº oblea') }}</th>
                                <th>{{ _lang('Deposito') }}</th>
                                <th>{{ _lang('Ubicacion') }}</th>
                                <th>{{ _lang('Descripcion') }}</th>
                                <th>{{ _lang('Publicado ML') }}</th>
                                <th class="act">{{ _lang('Accciones disponibles') }}</th>
                                <th class="text-center act">Lote</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
					
					</div>
					</div>


            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')

<script>
	var lugarentregas_tables = <?php echo json_encode($lugar_entregas); ?>;
    var selectedRows = {};
	var rows_selected = [];
	let inputArray = [
				{ id: 4, name: "dominio_vacio"},
					{ id: 6, name: "marca_vacio"},
					{ id: 8, name: "motor_vacio"},
				];
			let ids = inputArray.map( (item) => item.id);	
    var table
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

             table = $('#table-data-product').DataTable({
                processing:true,
                serverSide:true,
				//scrollX: true,
				//responsive: true, // Enable responsiveness
                ajax: "{{ url('products') }}",
                width: "auto",
                columns: [
                    { data: 'id', name: 'id'},
                    { data: 'created_at', name: 'created_at' },
                    { data: 'fecha_ingreso_a_stock', name: 'fecha_ingreso_a_stock' },
                    { data: 'interno', name: 'nro_interno' },
                    { data: 'dominio', name: 'dominio' },
                    { data: 'productItem', name: 'productItem' },
                    { data: 'marca', name: 'marca' },
                    { data: 'modelo', name: 'modelo' },
                    { data: 'nro_motor', name: 'nro_motor' },
                    { data: 'nro_oblea', name: 'nro_oblea' },
                    { data: 'deposito', name: 'deposito' },
                    { data: 'ubicacion', name: 'ubicacion' },
                    { data: 'description', name: 'description' },
                    { data: 'mercado_libre', name: 'mercado_libre' },
                    { data: 'action', name: 'action',  searchable: false, orderable: false},
                     {
                        data: null,
                        name: 'select',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `<input type="checkbox" class="row-checkbox" data-id="${row.id}">`;
                        }
                    },

                ],
            order: [
                [0, 'desc']
            ],
            //dom: 'Bfrtip', //Bfrltip
            dom: 'Bfrltip',
            orderCellsTop: true,
			pageLength: 10,
			//lengthMenu: [10, 20, 50, 100, 200, 500],
			lengthMenu: [[ 10, 20, 50, 500, 1000 ], [10, 20,50, 500, 1000 ]],
			autoWidth: false,
            buttons: [
                {
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
                    //$('#table-data-product input').val('').change();
                    //$('#table-data-product select').val('').change();
                    table.search('').columns().search('').draw();
                    $('.filtros').val('');
					$('.select-filter').val('').trigger('change');
                                }
                },
                {
                    extend: 'excelHtml5',
                    text: "<i class='fa fa-file-export'></i>  Exportar a excel por lote ",
					title: "piezas",
				filename: function () {
					 var tableName =  "piezas";
					 return tableName;
					 },
				exportOptions: {
					rows: function(idx, data, node) {
						let count = $('.row-checkbox:checked').length;

						if (count > 0){
							console.log(rows_selected);
							if($.inArray(data.id, rows_selected) !== -1){
								return data;
							}
						}else{
							return data;
						}
						return false;
					},
					columns: [ ':not(.act):visible' ]	,
					modifier: {
						search: 'applied',
						order: 'applied',
						selected: true, 
						page: 'all'
					}
				}, action: newexportaction, 
                    /*action: function() {
                        let params = table.ajax.params();

                        $.ajax({
                            url: "{{ route('piezas.export.excel') }}",
                            type: 'POST',
                            data: {
                                ...params,
                                selected_ids: Object.keys(selectedRows).map(key => key.split('-')[1]) ,
                                _token: "{{ csrf_token() }}"
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
                                    'piezas.xlsx';
                                link.click();
                            },
                            error: function(xhr) {

                                alert('Hubo un error al exportar el archivo.');
                            }
                        });
                    }*/
                },
               /* {
                    extend: 'excelHtml5',
                    text: 'Cotizar por lote ',
                    action: function() {
                        let params = table.ajax.params();
                        const ids = Object.keys(selectedRows).map(key => key.split('-')[1])
                        let valorIdProd = ids.join(',');
                        //console.log(valorIdProd);
                        const url = `{{ url('invoices/create') }}?idProduct=${valorIdProd}`;

                        window.open(url, 'TheWindow');
						--$('<form method="get" action="{{ url('invoices/create') }}" target="TheWindow"><input type="hidden" name="something" value="something"></form>').appendTo('body').submit().remove();--
                        valorIdProd = undefined;

                        $('#table-data-product tbody tr .row-checkbox').each(function() {
                            const id = $(this).data('id');
                            delete selectedRows[id];
                            $(this).attr('checked',false);
                            //console.log( $(this));
                           
                        });
                        table.draw(false);
                    }
                },*/
				{
                    text: 'Cotizar por lote',
					//className: 'ajax-modal',
					action: function () {
							//alert('Custom button clicked!');
							//const ids = Object.keys(selectedRows).map(key => key.split('-')[1])
							//let valorIdProd = ids.join(',');
						//	$(node).attr('data-ids':valorIdProd); 
//							$('#main_modal').data('title', 'Cotizacioneszxxxxxxxxxxx');
//							$('#main_modal').modal();

						/*var selectedIds = [];
                            $('.row-checkbox:checked').each(function() {
                                selectedIds.push($(this).data('id'));
                            });

console.log(selectedIds);
                            if (selectedIds.length === 0) {
                                alert('Seleccione al menos una orden para imprimir.');
                                return;
                            }*/
							
							const ids = Object.keys(selectedRows).map(key => key.split('-')[1])
							let valorIdProd = ids.join(',');
							

							if (valorIdProd.length === 0) {
								alert('Por favor seleccione un valor')
								return
							}
							
		 target_select = $(this).parent().find(".select2-ajax");
		 
		 
	 	 $.ajax({
			 url: "{{ url('invoices/create') }}",
			 beforeSend: function(){
				$("#preloader").css("display","block"); 
			 },success: function(data){
				$("#preloader").css("display","none");
				$('#main_modal .modal-title').html("Cotizaciones");
				$('#main_modal .modal-body').html(data);
				$("#main_modal .alert-secondary").addClass('d-none');
				$("#main_modal .alert-danger").addClass('d-none');
				$("#main_modal").on("show.bs.modal", function (e) { 
						$(e.currentTarget).find('#idProd').val(valorIdProd);
						//sleep(2);
						$(e.currentTarget).find('#actualizarButton').trigger("click"); 
						/*var html="<div style=' position: absolute;z-index:1;' class='form-group'><input type='file' data-btnText='Select a file' id='file-to-upload' name='file-to-upload' accept='application/pdf' /></div>";
						$("#main_modal").append(html);*/						 
						alert();
						 
				}).modal('show');
				//$('#main_modal').modal('show'); 
				
				var modalDialog = $('#main_modal').find('.modal-dialog');
			    

				 modalDialog.removeClass('modal-lg');
				 modalDialog.addClass("fullscreen-modal modal-xl");
				$("#main_modal .ajax-submit").attr('data-reload',false);
				//Select2
				$("#main_modal select.select2").select2({
					dropdownParent: $("#main_modal .modal-content"),
				});
				
					init_editor();
				
				/** Init Datepicker **/
    			init_datepicker();

				/** Init DateTimepicker **/
				$('.datetimepicker').daterangepicker({
					timePicker: true,
					timePicker24Hour: true,
					singleDatePicker: true,
					showDropdowns: true,
					locale: {
					  format: 'YYYY-MM-DD HH:mm'
					}
				});
				
				//Ajax Select2
				if ($("#main_modal .select2-ajax").length) {
					$('#main_modal .select2-ajax').each(function(i, obj) {
						
						var display2 = "";
						if( typeof  $(this).data('display2') !== "undefined" ){
							display2 = "&display2=" +  $(this).data('display2');
						}

                        var display3 = "";
                        if( typeof  $(this).data('display3') !== "undefined" ){
                            display3 = "&display3=" +  $(this).data('display3');
                        }
				
				
						$(this).select2({
						  ajax: {
							url: _url + '/ajax/get_table_data?table=' + $(this).data('table') + '&value=' + $(this).data('value') + '&display=' + $(this).data('display') + display2 +display3+ '&where=' +$(this).data('where'),
							processResults: function (data) {
							  return {
								results: data
							  };
							}
						  },
						  dropdownParent: $("#main_modal .modal-content"),
						});
							
					});
				}
				
				//Auto Selected
				if ($(".auto-select").length) {
					$('.auto-select').each(function(i, obj) {
						$(this).val($(this).data('selected')).trigger('change');
					})	
				}

				$('.crm-scroll').slimscroll({
					railVisible: true,
					railColor: '#7f8c8d',
					height: '500px',
					alwaysVisible: true,
			    });
							
				$(".dropify").dropify();
				$("#main_modal .ajax-submit input:required, #main_modal .ajax-submit select:required, #main_modal .ajax-submit textarea:required").closest(".form-group").find('.control-label').append("<span class='required'> *</span>");
				$("#main_modal .ajax-screen-submit input:required, #main_modal .ajax-screen-submit select:required, #main_modal .ajax-screen-submit textarea:required").closest(".form-group").find('.control-label').append("<span class='required'> *</span>");
				
			 },
			  error: function (request, status, error) {
				console.log(request.responseText);
			  }
		 });
		 
		 return false;

						
						
						},
					/*attr: {
						//class: 'ajax-modal', // Setting a class attribute
					//	'data-title': 'Cotizaciones',
					//	'data-fullscreen':"true",
					//	'data-href':"{{ url('invoices/create') }}"
					}*/
                },
                // {
                //     extend: 'excelHtml5',
                //     text: 'Exportar a Excel',
                //     title: 'Lista de Productos',
                //     exportOptions: {
                //         columns: ':visible'
                //     }
                //    ,action: newexportaction
                // },
                {
                    extend: 'pdfHtml5',
                    text: 'Exportar a PDF',
                    title: 'Lista de Productos', // Título del archivo PDF
                    orientation: 'landscape', // Orientación horizontal
                    pageSize: 'LEGAL', // Tamaño del papel
                   /* exportOptions: {
                        columns: ':visible' // Exporta solo columnas visibles
                    },*/
					filename: function () {
					 var tableName =  "Lista de Productos";
					 return tableName;
					 },
				exportOptions: {
					rows: function(idx, data, node) {
						let count = $('.row-checkbox:checked').length;
						
						if (count > 0){
							//console.log(data.id);
							if($.inArray(data.id, rows_selected) !== -1){
								return data;
							}
						}else{
							return data;
						}
						return false;
					},
					columns: [ ':not(.act):visible' ]	,
					modifier: {
						search: 'applied',
						order: 'applied',
						selected: true, 
						page: 'all'
					}
				}, action: newexportaction,
		            /*action: newexportaction,
                    customize: function(doc) {
                        // Personaliza el diseño del PDF
                        doc.styles.tableHeader = {
                            bold: true,
                            fontSize: 10,
                            color: 'black',
                            fillColor: '#f2f2f2'
                        };
                        doc.defaultStyle.fontSize = 8; // Tamaño de fuente general
                    }*/
                }
             ],

            });

           


            $('#table-data-product thead tr').clone().prependTo('#table-data-product thead');

                $('#table-data-product thead tr:eq(0) th').each(function(i) {
                var title = $(this).text();
                //$(this).html('<input type="text" placeholder="Search" />');
				
				if(i < 14) {
						
						$(this).html( '<input class="filtros" style="width:100%;" type="text" placeholder="' + title + '" />' );
				 
						$( 'input', this ).on( 'change', function () {
							if ( table.column(i).search() !== this.value ) {
								table
									.column(i)
									.search( this.value )
									.draw();
							}
						} );
										
						if(i == 1) {
						$(this).html( '<input style="width:100%;" type="text" id="fecha_ingreso" name="fecha_ingreso" value="" class="form-control select-filter" placeholder="Search...'+title+'" />' );
						}
						
						if(i == 2) {
						$(this).html( '<input type="text" style="width:100%;" id="fecha_ingreso_stock" name="fecha_ingreso_stock" value="" class="form-control select-filter" placeholder="Search...'+title+'" />' );
						}
						
						
						if (ids.includes(i)){
						const objectWithId1 = inputArray.find(item => item.id === i);
						if (objectWithId1) {
							$(this).html('<input type="checkbox" id="mostrar-todos-'+objectWithId1.name+'">vacios <input id="input-text-'+objectWithId1.name+'" style="width:100%;" type="text" placeholder="' + title + '" />');
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

                            table
                            .column(i)
                            .search(buscar)
                            .draw();
                        });


                    $('#input-text-'+objectWithId1.name, this).on('change', function() {
                        if (table.column(i).search() !== this.value) {
                            table
                            .column(i)
                            .search(this.value)
                            .draw();
                        }
                    });

						}
						}
						
						if (i == 13) {
				$(this).html('<select style="width:100%;" class="form-control filtros"><option value="">Todas</option> <option value="Si">Si</option> <option value="No">No</option></select>');
				$('.filtros', this).on('change', function () {
					  if (table.column(i).search() !== this.value) {
	
						table
							.column(i)
							.search(this.value)
							.draw();
					}
	
				});
	
			}
			
			
			
			if (i == 10) {
				
//$(this).html('<input type="checkbox" id="mostrar-todos-input">vacios <input id="input-text" style="width:100%;" type="text" placeholder="' + title + '" />');
                    //let campoInput = $('#input-text');
					//                    $("#mostrar-todos-input").change(function () {

				var select = $('<input style="width:100%;" type="checkbox" id="mostrar-todos-deposito">vacios <select id="Deposito_file" multiple="true" class="form-control select2"></select>')
				.appendTo( $(this).empty() )
				.on( 'change', function () {
					  		var val = $(this).val();
							table.column( i ).search(val ? val : '', false, false).draw();
				} );
			//	select.append( '<option value="-1">VACIOS</option>' );
				for (const row_x of lugarentregas_tables) {
					select.append( '<option value="'+row_x.id+'">'+row_x.nombre+'</option>' )
				}
				let campoInput = $('#Deposito_file');
				
				$('#mostrar-todos-deposito').change(function () {
                          let buscar= ($(this).is(':checked')) ? "-1":"";
                            if ($(this).is(':checked')) {
                                campoInput.next().hide();
                            } else {
                               campoInput.next().show();
                            }

                            table
                            .column(i)
                            .search(buscar)
                            .draw();
                        });
			}
					
					
				}else{
					if (i == 15) {
                        $(this).html('<input type="checkbox" id="select-all">');
                    }else{
						$(this).html('');
					}
					
				}
                

              /* 
			}else
						
						
*/						
						
						



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
            table.columns(1).search(val ? val : '', true, false );
            table.draw();
        });
    
    
        $('#fecha_ingreso').on('apply.daterangepicker', function(ev, picker) {
                let daterango =(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                    $(this).val(daterango);
                    table.columns(1).search(daterango);
                    table.draw();
            });

            $('#fecha_ingreso').on('cancel.daterangepicker', function(ev, picker) {
                $('#fecha_ingreso').val(null).trigger('change');    
        });


	$('#fecha_ingreso_stock').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            }
        });

         $('#fecha_ingreso_stock').on('change', function(e) {
            let val = $(this).val();
            table.columns(2).search(val ? val : '', true, false );
            table.draw();
        });
    
    
        $('#fecha_ingreso_stock').on('apply.daterangepicker', function(ev, picker) {
                let daterango =(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                    $(this).val(daterango);
                    table.columns(2).search(daterango);
                    table.draw();
            });

            $('#fecha_ingreso_stock').on('cancel.daterangepicker', function(ev, picker) {
                $('#fecha_ingreso_stock').val(null).trigger('change');    
        });


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


     $('.dataTables_filter input')
    .unbind('keypress keyup input')
    .bind('change input', function (e) {
        if ($(this).val().length >= 3 && e.keyCode == 13) {
            table.search(this.value).draw();
        }
    });

  });

  $(document).ready(function() {
     ////---------------ne-e-e-e-e

            $('#select-all').on('change', function() {
                const isChecked = $(this).is(':checked');
              //  console.log('a')
                $('.row-checkbox').prop('checked', isChecked);

                // Actualizar el objeto `selectedRows` basado en el estado del checkbox general
                $('#table-data-product tbody tr').each(function() {
                    const id = $(this).find('.row-checkbox').data('id');
                    if (isChecked) {
                        selectedRows[id] = true;
						$(this).attr('checked','checked');
                    } else {
                        delete selectedRows[id];
						$(this).attr('checked',false);
                    }
					clear_array(id,isChecked);	
                });
            });


            $('#table-data-product').on('change', '.row-checkbox', function() {
                const id = $(this).data('id');
				const isChecked = $(this).is(':checked');	   
                if ($(this).is(':checked')) {
                    selectedRows[id] = true; // Marcar como seleccionado
					$(this).attr('checked','checked');
                } else {
					$(this).attr('checked',false);
                    delete selectedRows[id]; // Quitar de la selección
                }

                // Desmarcar el select-all si se desmarca alguna fila
                const allChecked = $('.row-checkbox:checked').length === $('.row-checkbox').length;
				clear_array(id,isChecked);
                //$('#select-all').prop('checked', allChecked);
				
				if ($('.row-checkbox').length > 0 ) {
					$('#select-all').prop('checked', allChecked)
				}else{
					$('#select-all').prop('checked', false)	
				}
            });


            table.on('draw', function() {
                $('.row-checkbox').each(function() {
                    const id = $(this).data('id');
                    if (selectedRows[id]) {
                        $(this).prop('checked', true);
						$(this).attr('checked','checked');
                    }
                });

                // Actualizar el estado del checkbox select-all
                const allChecked = $('.row-checkbox:checked').length === $('.row-checkbox').length;
				if ($('.row-checkbox').length > 0) {
					$('#select-all').prop('checked', allChecked)
				}else{
					$('#select-all').prop('checked', false)	
				}
            });
            //////---------------------------
			
			$('.select2').select2({
                multiple: true,
                closeOnSelect: false//,
                //placeholder: "Select a " + title
              });
			
		function clear_array(rowId, ischecked){		
			// Determine whether row ID is in the list of selected row IDs
				  var index = $.inArray(rowId, rows_selected);

				  // If checkbox is checked and row ID is not in list of selected row IDs
				  if(ischecked && index === -1){
					 rows_selected.push(rowId);

				  // Otherwise, if checkbox is not checked and row ID is in list of selected row IDs
				  } else if (!ischecked && index !== -1){
						rows_selected.splice(index, 1);
				  }
		}										
  })
  async function toggleStock(btn) {
    const itemId = btn.dataset.id;
    const response = await fetch('{{ route("toggleStock") }}',{
        headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
        method: 'POST',
        body: JSON.stringify({ 
                id: itemId 
            })
    })

    
    const data = await response.json();

    if (typeof table !== 'undefined' && table !== null) {
            table.ajax.reload(null, false); // El 'false' evita que vuelva a la primera página
            console.log("DataTable recargado.");
        }
  }
  
  
   /*$('#main_modal').on('show.bs.modal', function(e) {
        // Obtiene el ID del botón que activó el modal
		const ids = Object.keys(selectedRows).map(key => key.split('-')[1])
		let valorIdProd = ids.join(',');
       // var id = $(e.relatedTarget).data('id');
        //console.log(e.currentTarget);
        // Asigna el ID al campo oculto dentro del modal
        $(e.currentTarget).find('#idProd').val(valorIdProd);
		$(e.currentTarget).actualizar();
    });*/
  
    </script>
       
@endsection
