<style>
    #main_modal .modal-lg {
        max-width: 800px;
    }

    #main_modal .modal-body {
        overflow: visible !important;
    }

    ul.ui-autocomplete {
        z-index: 1100;
    }
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">

@php

    $option = '';
    $class = '';
    $receptor = false;
    $retiros = false;
    $gerencial = false;

    if (strtolower(auth()->user()->role->name) == 'retiros') {
        $option = 'disabled';
        $retiros = true;
    }
    if (strtolower(auth()->user()->role->name) == 'tramitadores con retiros') {
        $retiros = true;
    }
    if (strtolower(auth()->user()->role->name) == 'receptor') {
        $optionRecep = 'disabled';
        $option = 'disabled';
        $receptor = true;
        $class = 'd-none';
    }
    if (strtolower(auth()->user()->role->name) == 'gerencial') {
       
        $gerencial = true;
    }

 

   // dd(!$retiros && !$gerencial);
@endphp


<form method="post" class="ajax-submit" action="{{ route('vehiculo.store') }}" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="row">
    <div class="col-md-12 bg-warning"  >ASIGNACION DE VEHICULO</div>
    <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Company') }}</label>
                <select id="company" name="company" required class="form-control">
                    <option value="">Seleccionar</option>
                    @foreach ($cias as $cia)
                        @if ($cia->business_name == 'Pentacar' || $cia->business_name == 'Paternal')
                            <option {{-- {{  auth()->user()->company_id == $cia->id ? --}} {{-- 'selected' : ''}} --}} value="{{ $cia->id }}">
                                {{ $cia->business_name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha Asignacion') }}</label>
                <input type="date" class="form-control" name="fecha_asignacion" value="{{ old('fecha_asignacion') }}"
                    required>
            </div>
        </div>
        <div class="col-md-3">
            <a href="{{ route('staffs.create') }}" data-reload="false" data-title="{{ _lang('Create Tramitador') }}"
                class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
            <div class="form-group">
                <label class="control-label">{{ _lang('Tramitador') }}</label>
                <select class="form-control select2" data-value="users.id" data-display="users.name"
                    data-table="users" data-where="7" name="idTramitador" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($tramitadores as $tramit)
                        <option value="{{ $tramit->id }}">{{ $tramit->name }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Siniestro') }} </label>
                <input type="text" class="form-control" name="siniestro" required
                    value="{{ old('siniestro') }}">
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <a href="{{ route('aseguradora.create') }}" data-reload="false"
                    data-title="{{ _lang('Create Aseguradora') }}" class="ajax-modal-2 select2-add"><i
                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                <label class="control-label">{{ _lang('Aseguradora') }}</label>
                <select class="form-control select2-ajax" data-value="id" data-display="nombre"
                    data-table="aseguradoras" data-where="" name="idAseguradora" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    {{-- @forelse($aseguradoras as $aseguradora) --}}
                    {{-- <option value="{{$aseguradora->id}}">{{$aseguradora->nombre}}</option> --}}
                    {{-- @empty --}}
                    {{-- @endforelse --}}
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Tramitador de compañia') }} </label>
                <input type="text" class="form-control" name="tramitador_compania"
                    value="{{ old('tramitador_compania') }}">
            </div>
        </div>
        <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Tipo Vehiculo') }}</label>
                
                <select {{ $option }}  class="form-control" name="tipo_vehiculo">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($tipo_vehiculo as $key => $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Dominio') }} </label>
                <input type="text" class="form-control" name="dominio" value="{{ old('dominio') }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <a href="{{ route('marcas.createLinea') }}" data-reload="false"
                    data-title="{{ _lang('Create Marca') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i>
                    {{ _lang('Add New') }}</a>
                <label class="control-label">{{ _lang('Marca') }}</label>
                <select class="form-control select2-ajax" data-value="id" data-display="marca" data-table="marcas"
                    data-where="" id="marca">
                    <option value="">{{ _lang('Select One') }}</option>
                    {{-- @forelse($marcas as $marca_modelo) --}}
                    {{-- <option value="{{$marca_modelo->id}}">{{$marca_modelo->marca}}</option> --}}
                    {{-- @empty --}}
                    {{-- @endforelse --}}
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
			<a href="#" id="btn-add-modelo" data-reload="false" style="pointer-events: none; opacity: 0.5;"
										data-select="modelo" data-title="{{ _lang('Create Modelo') }}" class="ajax-modal-2 select2-add">
										<i class="ti-plus"></i> {{ _lang('Add New') }}
									</a>
                <label class="control-label">{{ _lang('Modelo') }}</label>
                <select class="form-control select2" id="modelo">
                    <option value="">{{ _lang('Select One') }}</option>

                </select>
                <input type="hidden" name="marca_modelo" id="marca_modelo">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">

                <label class="control-label">{{ _lang('Estado') }}</label>
                <select class="form-control select2" name="estado">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($estados as $estado)
                        <option value="{{ $estado->id }}">{{ $estado->estado }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Tipo') }} </label>
                <input type="text" class="form-control" name="tipo" value="{{ old('tipo') }}">
            </div>
        </div>


        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Marca del Motor') }} </label>
                <input type="text" class="form-control" name="marca_motor" value="{{ old('marca_motor') }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Motor') }} </label>
                <input type="text" class="form-control" name="motor" value="{{ old('motor') }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Marca del Chasis') }} </label>
                <input type="text" class="form-control" name="marca_chasis" value="{{ old('marca_chasis') }}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Chasis') }} </label>
                <input type="text" class="form-control" name="chasis" value="{{ old('chasis') }}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Color') }}</label>
                    <input type="text" class="form-control" name="color" value="{{ old('color') }}">
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Tipo de baja') }} </label>

                <select class="form-control" name="tipo_baja">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($tipo_baja as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>

    </div> <!--FINAL row-->

    <div class="row" >
    <div class="col-md-12 bg-warning"  >DATOS ASEGURADOS</div>

    <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Asegurado') }} </label>
                <input type="text" class="form-control" name="asegurado" value="{{ old('asegurado') }}">
            </div>
        </div>


        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Contacto') }} </label>
                <input type="text" class="form-control" name="contacto" value="{{ old('contacto') }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha confirmacion') }} </label>
                <input type="date" class="form-control" name="fecha_confirmacion_contacto"
                    value="{{ old('fecha_confirmacion_contacto') }}">
            </div>
        </div>
        


    </div> <!--FINAL row-->

    <div class="row">
        <div class="col-md-12 bg-warning"  >COORDINACION DE RETIRO</div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha Solicitud de retiro') }} </label>
                <input type="date" class="form-control" name="fecha_limite_retiro"
                    value="{{ old('fecha_limite_retiro') }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Coordinar retiro') }} <input type="checkbox" class=""
                        name="coordinar_retiro" value="1"></label>

            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">Retiro anticipado <input type="checkbox" name="retiro_anticipado"
                        value="1"></label>

            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <a href="{{ route('staffs.create') }}" data-reload="false"
                    data-title="{{ _lang('Create Transportista') }}" class="ajax-modal-2 select2-add"><i
                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                <label class="control-label">{{ _lang('Transportista') }}</label>
                <select @if (!$retiros && !$gerencial) {{'disabled'}} @endif class="form-control select2-ajax" data-value="users.id" data-display="users.name"
                    data-table="users" data-where="6" name="retira">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($responsable_retiros as $res)
                        <option value="{{ $res->id }}">{{ $res->name }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Depósito') }}</label>
                <select class="form-control select2" name="lugar_entregas">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($lugar_entregas as $res)
                        <option value="{{ $res->id }}">{{ $res->nombre }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha de retiro') }} </label>
                <input @if (!$retiros && !$gerencial) {{'disabled'}} @endif type="date" class="form-control" name="fecha_retiro" value="{{ old('fecha_retiro') }}">
            </div>
        </div>
        
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Lugar de retiro') }} </label>
                <input type="text" class="form-control" name="lugar_retiro"
                    value="{{ old('lugar_retiro') }}">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Localidad') }} </label>
                <input type="text" class="form-control" name="localidad" value="{{ old('localidad') }}">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <a href="{{ route('provincia.create') }}" data-reload="false"
                    data-title="{{ _lang('Create Provincia') }}" class="ajax-modal-2 select2-add"><i
                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                <label class="control-label">{{ _lang('Provincia') }}</label>
                <select class="form-control select2-ajax" data-value="id" data-display="provincia"
                    data-table="provincias" data-where="" name="provincia">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($provincias as $provincia)
                        <option value="{{ $provincia->id }}">{{ $provincia->provincia }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Observaciones retiro') }}</label>
                <textarea class="form-control summernote" name="observacion_retiro">{{ old('observacion_retiro') }}</textarea>
            </div>
        </div>


    </div> <!--FINAL row-->

    <div class="row">
    <div class="col-md-12 bg-warning"  >DOCUMENTACION</div>
    <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('04 Entregado a') }}</label>
                <select class="form-control select2" name="entregado_a">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($responsable_entregas as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha entrega 04') }} </label>
                <input type="date" class="form-control" name="fecha_entrega" value="{{ old('fecha_entrega') }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">

                    <label class="control-label">Gestor</label>
                    <input type="text" class="form-control" name="gestor" id="gestor"
                        value="{{ old('gestor') }}">
     

            </div>
        </div>

        <div class="col-md-12 border rounded">
            <div class="form-group">
                <label for="imagen">Fotos 04D </label>
                <input {{-- @if (!$receptor) {{ $option }} @endif --}} type="file" class="form-control" id="imagen_recepcion[]"
                    name="imagen_recepcion[]" multiple="multiple">
            </div>

       
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Observacion administrativas') }}</label>
                <textarea class="form-control summernote" name="observacion">{{ old('observacion') }}</textarea>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha recepcion de documentacion') }} </label>
                <input type="date" class="form-control" name="fecha_documento"
                    value="{{ old('fecha_documento') }}">
            </div>

        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha envio doc') }} </label>
                <input type="date" class="form-control" name="fecha_envio_doc"
                    value="{{ old('fecha_envio_doc') }}">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group pt-3">

                <input type="checkbox" name="no_drnpa" value="1">
                <label class="control-label">No requiere enviar al DRNPA </label>
            </div>
        </div>

    </div> <!--FINAL row-->

    <div class="row">
    <div class="col-md-12 bg-warning"  >INGRESO DE VEHICULO</div>
    <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha de  ingreso') }} </label>
                <input type="date" class="form-control" name="fecha_ingreso"
                    value="{{ old('fecha_ingreso') }}">
            </div>
        </div>

        <div class="col-md-8">
            <div class="form-group">
                <label class="control-label">{{ _lang('') }} </label>
            </div>
        </div> 

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Piezas ausentes') }} </label>
                <select class="select2 form-control" multiple name="piezasAu[]">
                    @forelse ($items as $item)
                        <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>    

        <!--<div class="col-md-4">
            <div class="form-group">
                <label for="">Otra pieza</label>
                <input type="hidden" class="form-control" name="otraPieza" value="" />
            </div>
        </div>-->
        <div class="col-md-4">
            <label for="">Piezas en mal estado</label>
            <textarea name="piezas_defectuosa" id="piezas_defectuosa" class="form-control" cols="30" rows="10">
                 {!! old('piezas_defectuosa') !!}
            </textarea>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">Motor en marcha <input type="checkbox" name="motor_en_marcha"
                        value="1"></label>

            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Kilometraje') }} </label>
                <input type="number" class="form-control" name="kilometraje" value="{{ old('kilometraje') }}">
            </div>
        </div>
         <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Motor Vendido') }} </label>
                <input type="text" class="form-control" name="crp_nro" value="{{ old('crp_nro') }}">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Video') }}</label>
                <input type="file" class="form-control" accept="video/*" name="video[]" multiple="multiple" />
            </div>
        </div>

        <div class="col-md-12 mb-3">
            <label for="imagen">Fotos</label>
            <input type="file" class="form-control" accept="image/*" id="imagen[]" name="imagen[]"
                multiple="">
        </div>
        <div class="form-group">
            <label class="control-label">Notificar carga de imagenes <input type="checkbox" name="carga_de_imagen"
                    value="1"></label>

        </div>
    </div> <!--FINAL row-->
    <div class="row">
        <div class="col-md-4 d-none">
            <div class="form-group">
                <label class="control-label">{{ _lang('Forma') }}</label>
                <input type="text" class="form-control" name="forma" value="{{ old('forma') }}">
            </div>
        </div>
        @if (strtolower(auth()->user()->role->name) == 'gerencial' ||
                strtolower(auth()->user()->role->name) == 'gerente de operarios'
                || strtolower(auth()->user()->role->name) == 'operario')
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Observaciones de taller') }}</label>
                    <textarea class="form-control summernote" name="observacion_gerente_operario">{{ old('observacion_gerente_operario') }}</textarea>
                </div>
            </div>
        @endif
       

        <div class="col-md-12">
            <div class="form-group">
                {{-- <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button> --}}
                <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
            </div>
        </div>
    </div>
</form>

<script>
    $(function() {
        var availableTags = [
            "CARIGLINO",
            "BODIGLIO",
            "BARRANDEGUY",
            "MEREGALLI",
            "MEDINA",
            "PINOTTI",
            "PRIETO",
            "MAJO",
            "FLAVIA",
            "PM",
            "PC",
        ];
        $("#gestor").autocomplete({
            source: availableTags
        });
    });
    $(document).ready(function() {
        let marca = $('#marca');
        let modelo = $('#modelo');
        let marca_modelo = $('#marca_modelo');
        let result;



        marca.change(function() {
			var idMarca = $(this).val();
			var $btnModelo = $('#btn-add-modelo');
            modelo.html(`<option value="">{{ _lang('Select One') }}</option>`);
			
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
                    let html = `<option value="">{{ _lang('Select One') }}</option>`;
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


        // var maxField = 10; // Numero maximo de campos
        // var addButton = $('.add_button');
        // var wrapper = $('.field_wrapper');
        // var fieldHTML = '<div class="d-flex justify-content-center align-items-center pb-2"><input type="text" ' +
        // 'class="form-control" ' +
        // 'name="piezasAu[]" ' +
        // 'value=""/><a ' +
        // 'href="javascript:void(0);" ' +
        // 'class="remove_button" title="Remove field"><i class="fa fa-ban"></i></a></div>'; //New input field html
        // var x = 1;
        // $(addButton).click(function(){
        //     if(x < maxField){
        //         x++;
        //         $(wrapper).append(fieldHTML);
        //     }
        // });
        // $(wrapper).on('click', '.remove_button', function(e){ // Una vez se ha hecho clic en el boton de eliminar
        //     e.preventDefault();
        //     $(this).parent('div').remove(); //Eliminamos el div
        //     x--; // Reducimos el contador a 1
        // });
		
  // Crear marca
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
		
		
    })
</script>
