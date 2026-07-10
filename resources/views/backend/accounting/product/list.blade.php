@extends('layouts.app')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/lozad/dist/lozad.min.js"></script>
<style>
	
 </style>
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
                                <th style="width: 200px; min-width: 200px;">{{ _lang('Deposito') }}</th>
                                <th style="width: 200px; min-width: 200px;">{{ _lang('Ubicacion') }}</th>
                                <th>{{ _lang('Descripcion') }}</th>
                                <th>{{ _lang('Publicado ML') }}</th>
								<th >{{ _lang('Reparaciones') }}</th>
                                <th>{{ _lang('Fecha último giro') }}</th>
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

<div class="modal fade" id="modalUpdateFechaUltimoGiro" tabindex="-1" role="dialog" aria-labelledby="modalUpdateFechaUltimoGiroLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUpdateFechaUltimoGiroLabel">Actualizar campos masivos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input bulk-field-toggle" id="bulk_update_fecha_ultimogiro">
                        <label class="custom-control-label" for="bulk_update_fecha_ultimogiro">Modificar fecha último giro</label>
                    </div>
                </div>
                <div class="form-group" id="bulk_fecha_ultimogiro_group" style="display:none;">
                    <label for="bulk_fecha_ultimogiro">Fecha último giro</label>
                    <input type="text" id="bulk_fecha_ultimogiro" class="form-control datepicker" autocomplete="off" readonly />
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input bulk-field-toggle" id="bulk_update_ubicacion">
                        <label class="custom-control-label" for="bulk_update_ubicacion">Modificar ubicación</label>
                    </div>
                </div>
                <div class="form-group" id="bulk_ubicacion_group" style="display:none;">
                    <label for="bulk_ubicacion">Ubicación</label>
                    <input type="text" id="bulk_ubicacion" class="form-control" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmar-actualizar-fecha-ultimogiro">Guardar</button>
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

            function toggleBulkFieldSection() {
                $('#bulk_fecha_ultimogiro_group').toggle($('#bulk_update_fecha_ultimogiro').is(':checked'));
                $('#bulk_ubicacion_group').toggle($('#bulk_update_ubicacion').is(':checked'));
            }

            $('.bulk-field-toggle').on('change', toggleBulkFieldSection);
            toggleBulkFieldSection();
            init_datepicker();

            $('#confirmar-actualizar-fecha-ultimogiro').on('click', function() {
                const ids = $('.row-checkbox:checked').map(function() {
                    return $(this).data('id');
                }).get();

                if (ids.length === 0) {
                    if (typeof $.toast !== 'undefined') {$.toast({ position: 'top-right', text: 'Seleccione al menos un producto.', icon: 'error' });}
                    //alert('Seleccione al menos un producto.');
                    return;
                }

                const updateFecha = $('#bulk_update_fecha_ultimogiro').is(':checked');
                const updateUbicacion = $('#bulk_update_ubicacion').is(':checked');

                if (!updateFecha && !updateUbicacion) {
                    if(typeof $.toast !== 'undefined') $.toast({ position: "top-right", text: 'Seleccione al menos un campo para actualizar.', icon: 'error' });
                    //alert('Seleccione al menos un campo para actualizar.');
                    return;
                }

                const payload = {
                    ids: ids.join(','),
                    update_fecha_ultimogiro: updateFecha ? 1 : 0,
                    update_ubicacion: updateUbicacion ? 1 : 0
                };

                if (updateFecha) {
                    payload.fecha_ultimogiro = $('#bulk_fecha_ultimogiro').val();
                }

                if (updateUbicacion) {
                    payload.ubicacion = $('#bulk_ubicacion').val();
                }

                $.ajax({
                    url: "{{ route('products.update_fecha_ultimogiro') }}",
                    type: 'POST',
                    data: payload,
                    success: function(response) {
                        $('#modalUpdateFechaUltimoGiro').modal('hide');
                        if (response.result === 'success') {
                            if(typeof $.toast !== 'undefined') $.toast({ position: "top-right", text: response.updated + ' productos actualizados.', icon: 'success' });
                            table.draw(false);
                        } else {
                            if(typeof $.toast !== 'undefined') $.toast({ position: "top-right", text: response.message || 'No se pudo actualizar.', icon: 'error' });
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'No se pudo actualizar.';
                        if(typeof $.toast !== 'undefined') $.toast({ position: "top-right", text: message, icon: 'error' });
                    }
                });
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
                    { data: 'motor_nro', name: 'motor_nro' },
                    { data: 'nro_oblea', name: 'nro_oblea' },
                    { data: 'deposito', name: 'deposito' },
                    {
                    data: 'ubicacion',
                    name: 'ubicacion',
                    render: function(data, type, row) {
                        return `
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm edit-ubicacion" value="${data || ''}" id="input-ubicacion-${row.id}">
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-warning save-ubicacion" data-id="${row.id}">
                                        <i class="ti-check"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    }
                },
                    { data: 'description', name: 'description' },
                    { data: 'mercado_libre', name: 'mercado_libre' },
                    { data: 'reparaciones', name: 'reparaciones' },
                    {
                        data: 'fecha_ultimogiro',
                        name: 'fecha_ultimogiro',
                        render: function(data) {
                            if (!data) {
                                return '';
                            }

                            if (data.includes('-')) {
                                const [year, month, day] = data.split('-');
                                return `${day}/${month}/${year}`;
                            }

                            return data;
                        }
                    },
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
			lengthMenu: [[ 10, 20, 50, 500], [10, 20,50, 500]],
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
                    text: 'Actualizar campos masivos',
                    action: function(e, dt, node, config) {
                        const ids = $('.row-checkbox:checked').map(function() {
                            return $(this).data('id');
                        }).get();

                        if (ids.length === 0) {
                            if(typeof $.toast !== 'undefined') $.toast({ position: 'top-right', text: 'Seleccione al menos un producto.', icon: 'error' });
                            return;
                        }

                        $('#bulk_fecha_ultimogiro').val('');
                        $('#bulk_ubicacion').val('');
                        $('.bulk-field-toggle').prop('checked', false);
                        toggleBulkFieldSection();
                        $('#modalUpdateFechaUltimoGiro').modal('show');
                    }
                },
                {
                        extend: 'excel',
                        text : "<i class='fa fa-file-export'></i> Exportar a Excel",
						title: "invoices",
						filename: "invoices",
                        exportOptions: {
                            columns: [ ':not(.act):visible' ],	
                            modifier: {
                                search: 'applied',
                                order: 'applied',
                                page: 'all'
                            }
						}, action: newexportaction, 
                    },
				{   text: 'Cotizar por lote',
                    action: function(e, dt, node, config) {
							const ids = Object.keys(selectedRows).map(key => key.split('-')[1])
							let valorIdProd = ids.join(',');
							

							if (valorIdProd.length === 0) {
								if (typeof $.toast !== 'undefined') {
									$.toast({ position: 'top-right', text: 'Por favor seleccione un valor.', icon: 'error' });
								}
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
				$("#main_modal").find('#idProd').val(valorIdProd);
				$("#main_modal").find('#actualizarButton').trigger("click"); 
				$('#main_modal').modal('show'); 
				var modalDialog = $('#main_modal').find('.modal-dialog');
				 modalDialog.removeClass('modal-lg');
				 modalDialog.addClass("fullscreen-modal modal-xl");
				$("#main_modal .ajax-submit").attr('data-reload',false);
				//Select2
				$("#main_modal select.select2").select2({
					dropdownParent: $("#main_modal .modal-content"),
				});
				
					init_editor();
				
				/// Init Datepicker 
    			init_datepicker();

				/// Init DateTimepicker 
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
							
					//alert();
					}
				},				
				/*{
                    text: 'Cotizar por lote',
					action: function () {
							
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
						$(e.currentTarget).find('#actualizarButton').trigger("click"); 
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
				
				/// Init Datepicker 
    			init_datepicker();

				/// Init DateTimepicker 
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
		
                },*/
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
                }
             ],

            });


            $('#table-data-product thead tr').clone().prependTo('#table-data-product thead');

                $('#table-data-product thead tr:eq(0) th').each(function(i) {
                var title = $(this).text();
                //$(this).html('<input type="text" placeholder="Search" />');
				
				if(i < 15) {
						
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
						$(this).html( '<input style="width:100%;" type="date" id="fecha_ingreso" name="fecha_ingreso" value="" class="form-control select-filter" />' );
						$('#fecha_ingreso').on('change', function () {
							let val = $(this).val();
							table.column(i).search(val ? val : '', true, false).draw();
						});
						}
						
						if(i == 2) {
						let name_ingreso = "fechaingreso_vacio";
							$(this).html( '<input type="checkbox" id="mostrar-todos-'+name_ingreso+'">vacios <input type="date" style="width:100%;" id="fecha_ingreso_stock" name="fecha_ingreso_stock" value="" class="form-control select-filter" />' );
								let campoInput = $('#fecha_ingreso_stock');
								$('#mostrar-todos-'+name_ingreso).change(function () {
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
								campoInput.on('change', function () {
									let val = $(this).val();
									table.column(i).search(val ? val : '', true, false).draw();
								});
						
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
			
			if (i == 9) {
				$(this).html('<input class="filtros" style="width:150px;"  type="text" placeholder="' + title + '" />');
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
					if (i == 16) {
                        $(this).html('<input type="checkbox" id="select-all">');
                    }else{
						$(this).html('');
					}
					
				}
                

              /* 
			}else
						
						
*/						
						
						



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
             data.exportar = 1;
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

            $('#confirmar-actualizar-fecha-ultimogiro').on('click', function() {
                const ids = $('.row-checkbox:checked').map(function() {
                    return $(this).data('id');
                }).get();

                if (ids.length === 0) {
                    alert('Seleccione al menos un producto.');
                    return;
                }

                const updateFecha = $('#bulk_update_fecha_ultimogiro').is(':checked');
                const updateUbicacion = $('#bulk_update_ubicacion').is(':checked');

                if (!updateFecha && !updateUbicacion) {
                    alert('Seleccione al menos un campo para actualizar.');
                    return;
                }

                if (updateFecha && !$('#bulk_fecha_ultimogiro').val()) {
                    alert('Ingrese una fecha.');
                    return;
                }

                const payload = {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    ids: ids.join(','),
                    update_fecha_ultimogiro: updateFecha ? 1 : 0,
                    update_ubicacion: updateUbicacion ? 1 : 0
                };

                if (updateFecha) {
                    payload.fecha_ultimogiro = $('#bulk_fecha_ultimogiro').val();
                }

                if (updateUbicacion) {
                    payload.ubicacion = $('#bulk_ubicacion').val();
                }

                $.ajax({
                    url: "{{ route('products.update_fecha_ultimogiro') }}",
                    method: 'POST',
                    data: payload,
                    success: function(response) {
                        $('#modalUpdateFechaUltimoGiro').modal('hide');
                        if (response.result === 'success') {
                            table.ajax.reload(null, false);
                        } else {
                            alert(response.message || 'No se pudo actualizar.');
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'No se pudo actualizar.');
                    }
                });
            });

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
	
	
	async function ActualizarOblea(id) {
        inicioLoading();
		 const itemId = id;
		  const nro_oblea= $("#prod_id-"+itemId).val();
		 //alert(nro_oblea);
		 
		 const response = await fetch('{{ route("actualizaStockitems") }}',{
        headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
        method: 'POST',
        body: JSON.stringify({ 
                id: itemId, 
                nro_oblea: nro_oblea 
            })
    })

       
    const data = await response.json();
    closeLoading();
	//console.log(data);

    /*if (typeof table !== 'undefined' && table !== null) {
            table.ajax.reload(null, false); // El 'false' evita que vuelva a la primera página
        }
	*/	
        
    }
	
	
	async function ActualizarDeposito(id) {
        inicioLoading();
		 const itemId = id;
		  const deposito= $("#prod_depo_id-"+itemId).val();
		 
		 const response = await fetch('{{ route("actualizaStockitems") }}',{
        headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
        method: 'POST',
        body: JSON.stringify({ 
                id: itemId, 
                campo: 'idDeposito', 
                valor: deposito 
            })
    })

       
    const data = await response.json();
    closeLoading();
	//console.log(data);

    /*if (typeof table !== 'undefined' && table !== null) {
            table.ajax.reload(null, false); // El 'false' evita que vuelva a la primera página
        }
	*/	
        
    }

    $(document).on('click', '.save-ubicacion', function(e) {
    e.preventDefault();
    let id = $(this).data('id');
    let nuevaUbicacion = $('#input-ubicacion-' + id).val();
	
	
			const payload = {
                    ids: id,
                    update_ubicacion: 1,
					ubicacion: nuevaUbicacion
                };
	
	
	 $.ajax({
                    url: "{{ route('products.update_fecha_ultimogiro') }}",
                    type: 'POST',
                    data: payload,
					beforeSend: function() {
						inicioLoading();
					},
                    success: function(response) {
                        if (response.result === 'success') {
                            if(typeof $.toast !== 'undefined') $.toast({ position: "top-right", text: response.updated + ' productos actualizados.', icon: 'success' });
                            table.draw(false);
                        } else {
                            if(typeof $.toast !== 'undefined') $.toast({ position: "top-right", text: response.message || 'No se pudo actualizar.', icon: 'error' });
                        }
						closeLoading();	
                    },
                    error: function(xhr) {
						closeLoading();
                        const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'No se pudo actualizar.';
                        if(typeof $.toast !== 'undefined') $.toast({ position: "top-right", text: message, icon: 'error' });
                    }
                });
			});
	
	
	$(document).on('click', '.btn-estado', function() {
		var itemId = $(this).data('id');
		var valor = $(this).data('valor');
		var campo = $(this).data('campo');
		var btn = $(this);
		event.preventDefault(); 
		btn.disabled=true;
			$.ajax({
                url: "{{ route('actualizaStockitems') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    id: itemId,
					campo : campo,
					valor : valor
                },
                beforeSend: function() {
                   inicioLoading();
                },
                success: function(res) {
                    table.ajax.reload(null, false);
					btn.disabled=false;
					closeLoading();
                },
                error: function(request, status, error) {
					closeLoading();
                    table.ajax.reload(null, false);
                }
            });
	});
	

const observer = lozad('.lozad', {
    rootMargin: '10px 0px', // margin around the root
    threshold: 0.1,         // ratio of element visibility before loading
    load: function(el) {
        //console.log('Loading element:', el);
        // Custom loading logic here
      
		
		if (el.nodeName.toLowerCase() === 'video') {
            // Si tiene data-src directo
            if (el.dataset.src) {
                el.src = el.dataset.src;
            }
            // Si tiene fuentes internas
            const sources = el.querySelectorAll('source');
            if (sources.length > 0) {
                sources.forEach(source => {
                    source.src = source.dataset.src;
                });
            }
			  el.load(); // ¡Importante! Esto fuerza al navegador a leer el nuevo src
		}else{
			  el.src = el.dataset.src;
		}
    },
    loaded: function(el) {
        // Run after element is loaded
        el.classList.add('fade-in');
		
		
    }
});
	
	/*const observer = lozad('.lozad', {
    loaded: function(el) {
        //console.log('Elemento cargado:', el.src);
    }
	});*/
	
	$("#main_modal").on('show.bs.modal', function () {
			observer.observe(); 
	 });
	    	
  
    </script>
       
@endsection
