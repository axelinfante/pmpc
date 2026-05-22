@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="card mt-2">
                {{--  <span class="panel-title">{{ _lang('Tramitadores') }}</span> --}}
                <div class="card-body">

                    <form method="post" action="{{ route('vehiculo.store') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Company') }}</label>
                                    <select required id="company" name="company" class="form-control">
                                        <option value="">Seleccionar</option>
                                        @foreach ($cias as $cia)
                                            @if ($cia->business_name == 'Pentacar' || $cia->business_name == 'Paternal')
                                                <option {{ auth()->user()->company_id == $cia->id ? 'selected' : '' }}
                                                    value="{{ $cia->id }}">{{ $cia->business_name }}</option>
                                            @endif
                                        @endforeach

                                    </select>

                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group" style="opacity: 0.5;pointer-events: none;">
                                    <label class="control-label">{{ _lang('Nº interno') }}</label>
                                    <input disabled type="text" class="form-control" name="nro_interno">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Fecha Asignacion') }}</label>
                                    <input required type="date" class="form-control" name="fecha_asignacion"
                                        value="{{ old('fecha_asignacion') }}">
                                </div>

                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Tramitador') }}</label>
                                    <select required class="form-control select2" data-value="users.id"
                                        data-display="users.name" data-table="users" data-where="7" name="idTramitador">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        @forelse($tramitadores as $tramit)
                                            <option {{ old('idTramitador') == $tramit->id ? 'selected' : '' }}
                                                value="{{ $tramit->id }}">{{ $tramit->name }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Tramitador de compañia') }} </label>
                                    <input required type="text" class="form-control" name="tramitador_compania"
                                        value="{{ old('tramitador_compania') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">


                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Dominio') }} </label>
                                    <input required type="text" class="form-control" name="dominio"
                                        value="{{ old('dominio') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Siniestro') }} </label>
                                    <input required type="text" class="form-control" name="siniestro"
                                        value="{{ old('siniestro') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('aseguradora.create') }}" data-reload="false"
                                    data-title="{{ _lang('Create Aseguradora') }}" class="ajax-modal-2 select2-add"><i
                                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Aseguradora') }}</label>
                                    <select class="form-control select2-ajax" data-value="id" data-display="nombre"
                                        data-table="aseguradoras" data-where="" name="idAseguradora" required>
                                        <option value="">{{ _lang('Select One') }}</option>
                                        @forelse($aseguradoras as $aseguradora)
                                            <option {{ old('idAseguradora') == $aseguradora->id ? 'selected' : '' }}
                                                value="{{ $aseguradora->id }}">{{ $aseguradora->nombre }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Titular') }} </label>
                                    <input type="text" class="form-control" name="asegurado"
                                        value="{{ old('asegurado') }}">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Contacto') }} </label>
                                    <input type="text" class="form-control" name="contacto"
                                        value="{{ old('contacto') }}">
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                {{-- <div class="form-group">
                                    <label class="control-label">{{ _lang('Marca') }}</label>
                                    <select required class="form-control select2-ajax" data-value="id" data-display="marca"
                                        data-table="marcas" data-where="" id="marca" name = 'marca'>
                                        <option value="">{{ _lang('Select One') }}</option>
                                        @forelse($marcas as $marca)
                                            <option {{ old('marca') == $marca->id ? 'selected' : '' }}
                                                value="{{ $marca->id }}">{{ $marca->marca }}
                                            </option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div> --}}
                                <div class="form-group">
									<a href="{{ route('marcas.createLinea') }}" data-reload="false"
                                        data-title="{{ _lang('Create Marca') }}" class="ajax-modal-2 select2-add"><i
                                            class="ti-plus"></i>
                                        {{ _lang('Add New') }}</a>
                                    <label class="control-label">{{ _lang('Marca') }}</label>
                                    <select class="form-control select2-ajax" data-value="id" data-display="marca"
                                        data-table="marcas" data-where="" id="marca">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        {{-- @forelse($marcas as $marca_modelo) --}}
                                        {{-- <option value="{{$marca_modelo->id}}">{{$marca_modelo->marca}}</option> --}}
                                        {{-- @empty --}}
                                        {{-- @endforelse --}}
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-2">
                                <div class="form-group">
								<a href="#" id="btn-add-modelo" data-reload="false" style="pointer-events: none; opacity: 0.5;"
										data-select="modelo" data-title="{{ _lang('Create Modelo') }}" class="ajax-modal-2 select2-add">
										<i class="ti-plus"></i> {{ _lang('Add New') }}
									</a>
                                    <label class="control-label">{{ _lang('Modelo') }}</label>
                                    <select required class="form-control select2" id="modelo" name='modelo'>
                                        <option value="">{{ _lang('Select One') }}</option>

                                    </select>
                                    <input id="modelo_id" type="hidden" value="">
                                    <input type="hidden" name="marca_modelo" id="marca_modelo">


                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">Tipo vehiculo</label>
                                      <select required class="form-control" name="tipo_vehiculo">
                                        <option value="01" selected>01</option>
                                        <option value="02">02</option>
                                        <option value="03">03</option>
                                        <option value="04">04</option>
                                    </select>
                            </div></div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Tipo') }} </label>
                                    <input type="text" class="form-control" name="tipo"
                                        value="{{ old('tipo') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Marca Motor') }} </label>
                                    <input type="text" class="form-control" name="marca_motor"
                                        value="{{ old('marca_motor') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('No. Motor') }} </label>
                                    <input type="text" class="form-control" name="motor"
                                        value="{{ old('motor') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Marca Chasis') }} </label>
                                    <input type="text" class="form-control" name="marca_chasis"
                                        value="{{ old('marca_chasis') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('No.Chasis') }} </label>
                                    <input type="text" class="form-control" name="chasis"
                                        value="{{ old('chasis') }}">
                                </div>
                            </div>
                            <div class="col-md-2 ">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Tipo de baja') }} </label>

                                    <select required class="form-control" name="tipo_baja">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        @forelse($tipo_baja as $key => $value)
                                            <option {{ old('tipo_baja') == $key ? 'selected' : '' }}
                                                value="{{ $key }}">{{ $value }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>

                            </div>
                            <div class="col-md-2 border rounded">
                                <div class="form-group">
                                    <label for="imagen">Fotos 04D </label>
                                    <input {{-- @if (!$receptor) {{ $option }} @endif --}} type="file" class="form-control"
                                        id="imagen_recepcion[]" name="imagen_recepcion[]" multiple="multiple">
                                </div>

                            </div>
                            <div class="col-md-2">
                                <div class="form-group pt-3">

                                    <input {{ old('no_drnpa') == 1 ? 'checked' : '' }} type="checkbox" name="no_drnpa"
                                        value="1">
                                    <label class="control-label">No requiere enviar al DRNPA </label>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
                                </div>
                            </div>
                        </div>

                    </form>

                    <br>
                    <div class="col-md-2 ">
                        <a class="btn btn-primary btn-xs" data-title="{{ _lang('Volver') }}"
                            href="{{ route('tramitadores.index') }}"><i class="ti-back"></i>
                            {{ _lang('Volver') }}</a>
                    </div>
                    <div class="row">


                        <div class="col-md-12 ">
                            <table id="checkpoints_vehiculos_table" class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>{{ _lang('Nombre') }}</th>
                                        <th>{{ _lang('Fecha Inicio') }}</th>
                                        <th>{{ _lang('Estado Actual') }}</th>
                                        {{-- <th>{{ _lang('Observaciones') }}</th> --}}
                                        <th>{{ _lang('Usuario') }}</th>
                                        <th>{{ _lang('Actions') }}</th>
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
    {{-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js"></script> --}}
    {{-- <script src="{{ asset('public/backend/assets/js/ajax-datatable/tramitador.js') }}"></script> --}}
    <script>
        let ejecuting = false;
        let checkpoint_list_table = false;
        let vehiculo_id = $('#vehiculo_id').val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            let marca = $('#marca');
            let modelo = $('#modelo');
            let modelo_id = $('#modelo_id').val();
            let marca_modelo = $('#marca_modelo');
            let result;

            marca.change(function() {
				var idMarca = $(this).val();
				var $btnModelo = $('#btn-add-modelo');
                modelo.html(`<option value="">Seleccionar</option>`);
				
				if (marca.val()) {
					  $btnModelo.css({ 'pointer-events': 'auto', 'opacity': '1' });
				      var urlBase = "{{ route('marcas.createMarcaModeloLinea') }}"; // Ajusta a tu nombre de ruta real
					  $btnModelo.attr('href', urlBase + '?idMarca=' + idMarca);
					  
					} else {
						// Deshabilitar el acceso si no hay marca
						$btnModelo.css({ 'pointer-events': 'none', 'opacity': '0.5' }).attr('href', '#');
					} 
					
                $.ajax({
                    url: "{{ route('modelosByMarca') . '/' }}" + marca.val(),
                    dataType: 'json',
                    success: function(res) {
                        let html = `<option value="">Seleccionar</option>`;
                        res.map(r => {
                            html +=
                                `<option value="${r.idModelo}">${r.modelo.modelo}</option>`;
                        })
                        result = res;

                        modelo.html(html);

                    }

                })
            })

            //modelo.select2();
            modelo.change(function() {
                marca_modelo.val('');

                //console.log(result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val()));
                let modeloAjax = result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val());
                //console.log(result);

                marca_modelo.val(modeloAjax.id);

            })


$(document).on("submit", ".ajax-submitz", function(e) {	
    e.preventDefault();
    var elem = $(this);
    var link = $(this).attr("action");
    var current_modal = $(this).closest('.modal');
    
    $(elem).find("button[type=submit]").prop("disabled", true);
     
    $.ajax({
        method: "POST",
        url: link,
        data: new FormData(this),
        mimeType: "multipart/form-data",
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function() {
            $("#preloader").css("display", "block");  
        },
        success: function(response) {
            $(elem).find("button[type=submit]").prop("disabled", false);	
            $("#preloader").css("display", "none"); 

            if (response['result'] == "success") {
                $(current_modal).find(".alert-secondary").html(response['message']).removeClass('d-none');
                $(current_modal).find(".alert-danger").addClass('d-none');

                var nuevaOpcion = new Option(response['marca'], response['id'], true, true);
                $('#marca').append(nuevaOpcion).trigger('change');
                
                elem[0].reset(); 
                $(current_modal).modal('hide');
            } else {
                $("#main_modal .alert-danger").html("");
                $(current_modal).find(".alert-danger").html("");

                if (Array.isArray(response['message'])) {
                    if (typeof reload !== 'undefined' && reload != false) {
                        jQuery.each(response['message'], function(i, val) {
                           $("#main_modal .alert-danger").append("<p class='m-0'>" + val + "</p>");
                        });
                        $("#main_modal .alert-secondary").addClass('d-none');
                        $("#main_modal .alert-danger").removeClass('d-none');
                    } else {
                        jQuery.each(response['message'], function(i, val) {
                           $(current_modal).find(".alert-danger").append("<p class='m-0'>" + val + "</p>");
                        });
                        $(current_modal).find(".alert-secondary").addClass('d-none');
                        $(current_modal).find(".alert-danger").removeClass('d-none');
                    }
                } else {
                    if (typeof reload !== 'undefined' && reload != false) {
                        $("#main_modal .alert-danger").html("<p class='m-0'>" + response['message'] + "</p>");	
                        $("#main_modal .alert-secondary").addClass('d-none');
                        $("#main_modal .alert-danger").removeClass('d-none');
                    } else {
                        $(current_modal).find(".alert-danger").html("<p class='m-0'>" + response['message'] + "</p>");						
                        $(current_modal).find(".alert-secondary").addClass('d-none');
                        $(current_modal).find(".alert-danger").removeClass('d-none');
                    }
                }
            }
        },
        error: function(xhr) {
            $("#preloader").css("display", "none"); 
            $(elem).find("button[type=submit]").prop("disabled", false);	
            $(current_modal).find(".alert-secondary").addClass('d-none');
            
            var response = xhr.responseJSON;
            var $dangerAlert = $(current_modal).find(".alert-danger");
            $dangerAlert.html('').removeClass('d-none');

            if (response && response.result === "error" && response.message) {
                var mensajes = response.message; 

                if (Array.isArray(mensajes)) {
                    $.each(mensajes, function(index, msg) {
                        $dangerAlert.append("<p class='m-0'>" + msg + "</p>");
                    });
                } else {
                    $dangerAlert.html("<p class='m-0'>" + mensajes + "</p>");
                }
            } else {
                $dangerAlert.html("<p class='m-0'>Ocurrió un error inesperado en el servidor.</p>");
            }
        }
    });
    return false;
});


// Modelo
	$(document).on("submit", "#marca_modelo", function(e) {	
    e.preventDefault();
    var elem = $(this);
    var link = $(this).attr("action");
    var current_modal = $(this).closest('.modal');
    
    $(elem).find("button[type=submit]").prop("disabled", true);
     
    $.ajax({
        method: "POST",
        url: link,
        data: new FormData(this),
        mimeType: "multipart/form-data",
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function() {
            $("#preloader").css("display", "block");  
        },
        success: function(response) {
            $(elem).find("button[type=submit]").prop("disabled", false);	
            $("#preloader").css("display", "none"); 

            if (response['result'] == "success") {
                $(current_modal).find(".alert-secondary").html(response['message']).removeClass('d-none');
                $(current_modal).find(".alert-danger").addClass('d-none');
                
                 marca.trigger('change'); 
                elem[0].reset(); 
                $(current_modal).modal('hide');
            } else {
                $("#main_modal .alert-danger").html("");
                $(current_modal).find(".alert-danger").html("");

                if (Array.isArray(response['message'])) {
                    if (typeof reload !== 'undefined' && reload != false) {
                        jQuery.each(response['message'], function(i, val) {
                           $("#main_modal .alert-danger").append("<p class='m-0'>" + val + "</p>");
                        });
                        $("#main_modal .alert-secondary").addClass('d-none');
                        $("#main_modal .alert-danger").removeClass('d-none');
                    } else {
                        jQuery.each(response['message'], function(i, val) {
                           $(current_modal).find(".alert-danger").append("<p class='m-0'>" + val + "</p>");
                        });
                        $(current_modal).find(".alert-secondary").addClass('d-none');
                        $(current_modal).find(".alert-danger").removeClass('d-none');
                    }
                } else {
                    if (typeof reload !== 'undefined' && reload != false) {
                        $("#main_modal .alert-danger").html("<p class='m-0'>" + response['message'] + "</p>");	
                        $("#main_modal .alert-secondary").addClass('d-none');
                        $("#main_modal .alert-danger").removeClass('d-none');
                    } else {
                        $(current_modal).find(".alert-danger").html("<p class='m-0'>" + response['message'] + "</p>");						
                        $(current_modal).find(".alert-secondary").addClass('d-none');
                        $(current_modal).find(".alert-danger").removeClass('d-none');
                    }
                }
            }
        },
        error: function(xhr) {
            $("#preloader").css("display", "none"); 
            $(elem).find("button[type=submit]").prop("disabled", false);	
            $(current_modal).find(".alert-secondary").addClass('d-none');
            
            var response = xhr.responseJSON;
            var $dangerAlert = $(current_modal).find(".alert-danger");
            $dangerAlert.html('').removeClass('d-none');

            if (response && response.result === "error" && response.message) {
                var mensajes = response.message; 

                if (Array.isArray(mensajes)) {
                    $.each(mensajes, function(index, msg) {
                        $dangerAlert.append("<p class='m-0'>" + msg + "</p>");
                    });
                } else {
                    $dangerAlert.html("<p class='m-0'>" + mensajes + "</p>");
                }
            } else {
                $dangerAlert.html("<p class='m-0'>Ocurrió un error inesperado en el servidor.</p>");
            }
        }
    });

    return false;
});







        });
    </script>
@endsection
