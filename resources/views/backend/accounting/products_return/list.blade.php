@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="card mt-2">
                <span class="panel-title d-none">{{ _lang('Listado Devolución de Productos') }}</span>
                <div class="card-body">
                    <div class="row">
                        <!--<div class="col mb-2">
                            <a class="btn btn-primary btn-xs" data-title="{{ _lang('Add New Return') }}"
                                href="{{ route('products_returns.create') }}"><i class="ti-plus"></i>
                                {{ _lang('Add New') }}</a>
                        </div>-->

                        <div class="col-lg-3">
                            <select class="form-control select2 select-filter" data-placeholder="{{ _lang('All Status') }}"
                                name="status" multiple="true">
                                <option selected value="pendiente">Pendientes</option>
                                <option value="procesada">Procesadas</option>
                                <option selected value="reparar">Defectuoso a reparar</option>
                                <option value="descompuesto">Defectuoso a destruir</option>
                                <option value="comercializable">Defectuoso comercializable</option>
                            </select>
                        </div>
                    </div>
                    <hr>

			<div class="table-responsive dt-responsive"> 
                    <table id="products_returns_table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ _lang('No. Devolución') }}</th>
                                <th>{{ _lang('Fecha') }}</th>
                                <th>{{ _lang('Numero de Venta') }}</th>
                                <th>{{ _lang('Client') }}</th>
                                <th>{{ _lang('Vendedor') }}</th>
                                <!-- <th>{{ _lang('Numero del Producto') }}</th> -->
                                 <th>{{ _lang('Numero Interno') }}</th> 
                                <th>{{ _lang('Nombre del Producto') }}</th>
                                {{-- <th class="text-right d-none">{{ _lang('Cantidad') }}</th> --}}
                                <th>{{ _lang('Motivo') }}</th>
                                <th>{{ _lang('Estatus') }}</th>
                                <th>{{ _lang('Ubicacion') }}</th>
                                <th class="text-center notexport">{{ _lang('Action') }}</th>
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
@endsection

@section('js-script')
    <script src="{{-- asset('public/backend/assets/js/ajax-datatable/products_returns.js') --}}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        (function($) {
            "use strict";

			var products_returns_table = $("#products_returns_table").appTable({
							title:"marcas",
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
					columnFilters: ['input','daterangepicker',,,,,,'none'],
					columns: [
						{ data: 'return_number', name: 'return_number' },
						{ data: 'return_date', name: 'return_date' },
						{ data: 'invoice_id', name: 'invoice_id' },
						{ data: 'client', name: 'client' },
						{ data: 'vendedor', name: 'vendedor' },
						// { data: 'product_id', name: 'product_id' },
                        { data: 'internal_reference', name: 'internal_reference' },
						{ data: 'product_name', name: 'product_name' },
						//{ data: 'quantity', name: 'quantity', className: 'd-none' },
						{ data: 'note', name: 'note' },
						{ data: 'status', name: 'status' },
						{ data: 'ubicacion', name: 'ubicacion' },
						{ data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false },
						],
				});



	$('.select-filter').on('change', function (e) {
		products_returns_table.draw();
	});
	
	
	
         /*   $(document).on('click', '.procesar-devolucion', function(event) {
                event.preventDefault();
                let id = $(this).data('id');

                Swal.fire({
                    title: $lang_alert_title,
                    text: '¿Realmente desea procesar esta devolución? Observaciónes',
					input: "text", 
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: $lang_confirm_button_text_cancelar_venta,
                    cancelButtonText: $lang_cancel_button_text
                }).then((result) => {
					
					if (result.dismiss === Swal.DismissReason.cancel) {
						// Code to run if the 'Cancel' button was clicked
						//Swal.fire("Cancelled", "Your imaginary file is safe :)", "error");
					}else{
						    process_returns(id,result.value)
					}
					
                   // if (result.value) {
                    //    process_returns(id,result.value)
                    //}
                });
            });

            $(document).on('click', '.anular-devolucion', function(event) {
                event.preventDefault();
                let id = $(this).data('id');

                Swal.fire({
                    title: $lang_alert_title,
                    text: '¿Desea continuar con esta devolución? Observaciónes',
					input: "text", 
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: $lang_confirm_button_text_cancelar_venta,
                    cancelButtonText: $lang_cancel_button_text
                }).then((result) => {
					
					if (result.dismiss === Swal.DismissReason.cancel) {
						// Code to run if the 'Cancel' button was clicked
						//Swal.fire("Cancelled", "Your imaginary file is safe :)", "error");
					}else{
						cancel_returns(id,result.value)
					}
					
					//console.log(result);
                   // if (result.value) {
                       // cancel_returns(id,result.value)
                    //}
                });
            });

            $(document).on('click', '.anular-devolucion', function(event) {
                event.preventDefault();
            });


$(document).on('click', '.reparar-devolucion', function(event) {
    event.preventDefault();
    let id = $(this).data('id');

    Swal.fire({
        title: $lang_alert_title,
        text: '¿Desea enviar esta devolución a reparación? Observaciones',
        input: "text", 
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Enviar a Reparación',
        cancelButtonText: $lang_cancel_button_text
    }).then((result) => {
        if (result.dismiss !== Swal.DismissReason.cancel) {
            repair_returns(id, result.value);
        }
    });
});

function repair_returns(id, observacion) {
    $.ajax({
        url: "{{ route('products_returns.repair') }}",
        type: 'POST',
        dataType: 'json',
        data: {
            id: id,
            'observacion': observacion
        },
        beforeSend: function() {
            $("#preloader").fadeIn();
        },
        success: function(res) {
            var json = JSON.parse(JSON.stringify(res));
            if (json['result'] == 'success') {
                $("#preloader").fadeOut();
                $("#main_alert > span.msg").html("");
                $("#main_alert").addClass("alert-success").removeClass("alert-danger");
                $("#main_alert > span.msg").html("<p>" + json['message'] + "</p>");
                $("#main_alert").css('display', 'block');
            }
            products_returns_table.ajax.reload(null, false);
        },
        error: function(request, status, error) {
            $("#preloader").fadeOut();
            try {
                var json = JSON.parse(request.responseText);
                errorMessage = json.message;
            } catch (e) {
                errorMessage = request.responseText;
            }
            $("#main_alert > span.msg").html("");
            $("#main_alert").addClass("alert-danger").removeClass("alert-success");
            $("#main_alert > span.msg").html("<p>" + errorMessage + "</p>");
            $("#main_alert").css('display', 'block');

            products_returns_table.ajax.reload(null, false);
        }
    });
}*/

        })(jQuery);

        /*function process_returns(id,observacion) {
            $.ajax({
                url: "{{ route('products_returns.procesar') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    'id': id,
                    'observacion': observacion
                },
                beforeSend: function() {
                    $("#preloader").fadeIn();
                },
                success: function(res) {
                    var json = JSON.parse(JSON.stringify(res));
                    if (json['result'] == 'success') {
                        $("#preloader").fadeOut();
                        $("#main_alert > span.msg").html("");
                        $("#main_alert").addClass("alert-success").removeClass("alert-danger");
                        $("#main_alert > span.msg").html("<p>" + json['message'] + "</p>");
                        $("#main_alert").css('display', 'block');
                    }
                    products_returns_table.ajax.reload(null, false);
                },
                error: function(request, status, error) {
                    $("#preloader").fadeOut();

                     try {
                        var json = JSON.parse(request.responseText);
                        errorMessage = json.message;
                    } catch (e) {
                        errorMessage = request.responseText ;
                    }

                    $("#main_alert > span.msg").html("");
                    $("#main_alert").addClass("alert-danger").removeClass("alert-success");
                    $("#main_alert > span.msg").html("<p>" + errorMessage + "</p>");
                    $("#main_alert").css('display', 'block');

                    products_returns_table.ajax.reload(null, false);
                }
            });

        }


        function cancel_returns(id,observacion) {
            $.ajax({
                url: "{{ route('products_returns.cancel') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    id: id,
					'observacion': observacion
                },
                beforeSend: function() {
                    $("#preloader").fadeIn();
                },
                success: function(res) {
                    var json = JSON.parse(JSON.stringify(res));
                    if (json['result'] == 'success') {
                        $("#preloader").fadeOut();
                        $("#main_alert > span.msg").html("");
                        $("#main_alert").addClass("alert-success").removeClass("alert-danger");
                        $("#main_alert > span.msg").html("<p>" + json['message'] + "</p>");
                        $("#main_alert").css('display', 'block');
                    }
                    products_returns_table.ajax.reload(null, false);

                },
                error: function(request, status, error) {
                    $("#preloader").fadeOut();

                    try {
                        var json = JSON.parse(request.responseText);
                        errorMessage = json.message;
                    } catch (e) {
                        errorMessage = request.responseText ;
                    }

                    // console.log(request.responseText);

                    $("#main_alert > span.msg").html("");
                    $("#main_alert").addClass("alert-danger").removeClass("alert-success");
                    $("#main_alert > span.msg").html("<p>" + errorMessage + "</p>");
                    $("#main_alert").css('display', 'block');

                    products_returns_table.ajax.reload(null, false);
                }
            });
        }*/
    </script>
@endsection
