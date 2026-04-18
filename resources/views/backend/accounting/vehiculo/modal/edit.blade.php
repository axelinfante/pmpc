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
<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('VehiculoController@update', $id) }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}
    <input name="_method" type="hidden" value="PATCH">

    <div class="row">
    <div class="col-md-12 bg-warning"  >ASIGNACION DE VEHICULO</div>
        {{-- <div class="col-md-4"> --}}
        {{-- <div class="form-group"> --}}
        {{-- <label class="control-label">{{ _lang('Nº interno') }}</label> --}}
        {{-- <input type="text" class="form-control"  name="nro_interno" value="{{ old --}}
        {{-- ('nro_interno',($car->nro_interno ?? ($interno + 1)) ) }}" --}}
        {{-- > --}}
        {{-- </div> --}}
        {{-- </div> --}}
        @if ($option != 'disabled')
        @endif

        @if ($option != 'disabled')

        <div class="col-md-3 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Company') }}</label>
                <select {{ $option }} id="company" name="company" required class="form-control">
                    @foreach ($cias as $cia)
                        <option {{ $car->company_id == $cia->id ? 'selected' : '' }} value="{{ $cia->id }}">
                            {{ $cia->business_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

            <div class="col-md-3 {{ $class }}">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Fecha Asignacion') }}</label>
                    <input {{ $option }} type="date" class="form-control" name="fecha_asignacion"
                        value="{{ old('fecha_asignacion', $car->fecha_asignacion) }}" required>
                </div>
            </div>
           

            <div class="col-md-3 {{ $class }}">
                <div class="form-group">
                    <a href="{{ route('staffs.create') }}" data-reload="false"
                        data-title="{{ _lang('Create Tramitador') }}" class="ajax-modal-2 select2-add"><i
                            class="ti-plus"></i> {{ _lang('Add New') }}</a>
                    <label class="control-label">{{ _lang('Tramitador') }}</label>
                    <select {{ $option }} {{ $option }} class="form-control select2"
                        data-value="users.id" data-display="users.name" data-table="users" data-where="7"
                        name="idTramitador" required>
                        <option value="">{{ _lang('Select One') }}</option>
                        @forelse($tramitadoresAll as $tramit)
                            <option {{ $tramit->id == $car->idTramitador ? 'selected' : '' }}
                                value="{{ $tramit->id }}">{{ $tramit->name }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>
 <div class="col-md-3 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Siniestro') }} </label>
                <input {{ $option }} type="text" class="form-control" name="siniestro"
                    value="{{ old('siniestro', $car->siniestro) }}">
            </div>
        </div>

        @endif
        @if ($option != 'disabled')
        <div class="col-md-3 {{ $class }}">
            <a href="{{ route('aseguradora.create') }}" data-reload="false"
                data-title="{{ _lang('Create Aseguradora') }}" class="ajax-modal-2 select2-add"><i
                    class="ti-plus"></i> {{ _lang('Add New') }}</a>
            <div class="form-group">
                <label class="control-label">{{ _lang('Aseguradora') }}</label>
                <select {{ $option }} {{ $option }} class="form-control select2-ajax" data-value="id"
                    data-display="nombre" data-table="aseguradoras" data-where="" name="idAseguradora" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($aseguradoras as $aseguradora)
                        <option {{ $aseguradora->id == $car->idAseguradora ? 'selected' : '' }}
                            value="{{ $aseguradora->id }}">{{ $aseguradora->nombre }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
    @endif
        <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Tramitador de compañia') }} </label>
                <input {{ $option }} type="text" class="form-control" name="tramitador_compania"
                    value="{{ old('tramitador_compania', $car->tramitador_compania) }}">
            </div>
        </div>
        <div class="col-md-3 {{ $class }}">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Fecha de pago Cia') }}</label>
                    <input {{ $option }} type="date" class="form-control" name="fecha_pago_cia"
                        value="{{ ($car->properties['fecha_pago_cia'] ?? '') ?  \Carbon\Carbon::parse($car->properties['fecha_pago_cia'])->format('Y-m-d'):''}}">
                </div>
            </div>
      
        <div class="col-md-2 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Tipo Vehiculo') }}</label>
                
                <select {{ $option }}  class="form-control" name="tipo_vehiculo">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($tipo_vehiculo as $key => $value)
                        <option {{ old('tipo_vehiculo', $car->tipo_vehiculo) == $key ? 'selected' : '' }}
                            value="{{ $value }}">{{ $value }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>

       

        <div class="col-md-3 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Dominio') }} </label>
                <input {{ $option }} type="text" class="form-control" name="dominio"
                    value="{{ old('dominio', $car->dominio) }}">
            </div>
        </div>
     

        <div class="col-md-3 {{ $class }}">
            <div class="form-group">
                <a href="{{ route('marcamodelo.create') }}" data-reload="false"
                    data-title="{{ _lang('Create Marca') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i>
                    {{ _lang('Add New') }}</a>
                <label class="control-label">{{ _lang('Marca') }}</label>
                <select {{ $option }} {{ $option }} class="form-control select2-ajax" data-value="id"
                    data-display="marca" data-table="marcas" data-where="" id="marca">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($marcas as $marca)
                        <option {{ $marca->id == ($car->marca_modelo->marca->id ?? '') ? 'selected' : '' }}
                            value="{{ $marca->id }}">{{ $marca->marca }}
                        </option>
                    @empty
                    @endforelse
                    {{--                    {{ create_option("marcas","id",array("marca"),($car->marca_modelo->marca->id ?? '')) }} --}}
                </select>
            </div>
        </div>
        <div class="col-md-3 {{ $class }}">
            <div class="form-group">
                 <a href="" id="editarModelo" data-reload="false"
                                        data-title="{{ _lang('Update Modelo') }}" class="ajax-modal-2 select2-add"><i
                                            class="ti-plus"></i> {{ _lang('Editar') }}</a>
                <label class="control-label">{{ _lang('Modelo') }}</label>
                <select {{ $option }} {{ $option }} class="form-control select2" id="modelo">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($modelos as $modelo)
                        <option {{ $modelo->id == ($car->marca_modelo->modelo->id ?? '') ? 'selected' : '' }}
                            value="{{ $modelo->id }}">{{ $modelo->modelo }}
                        </option>
                    @empty
                    @endforelse
                </select>
                <input {{ $option }} type="hidden" name="marca_modelo" id="marca_modelo"
                    value="{{ $car->idMarca_modelo }}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Estado') }}</label>
                <select class="form-control" name="estado">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($estados as $estado)
                        <option {{ $estado->id == $car->idEstado ? 'selected' : '' }} value="{{ $estado->id }}">
                            {{ $estado->estado }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
        <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Tipo') }} </label>
                <input {{ $option }} type="text" class="form-control" name="tipo"
                    value="{{ old('tipo', $car->tipo) }}">
            </div>
        </div>

        <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Marca Motor') }} </label>
                <input {{ $option }} type="text" class="form-control" name="marca_motor"
                    value="{{ old('marca_motor', $car->marca_motor) }}">
            </div>
        </div>


        <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Motor') }} </label>
                <input {{ $option }} type="text" class="form-control" name="motor"
                    value="{{ old('motor', $car->motor_nro) }}">
            </div>
        </div>

        <div class="col-md-3 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Marca Chasis') }} </label>
                <input {{ $option }} type="text" class="form-control" name="marca_chasis"
                    value="{{ old('marca_chasis', $car->marca_chasis) }}">
            </div>
        </div>

        <div class="col-md-3 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Chasis') }} </label>
                <input {{ $option }} type="text" class="form-control" name="chasis"
                    value="{{ old('chasis', $car->chasis) }}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Color') }}</label>
                    <input  @if (!$receptor) {{ $option }} @endif type="text" class="form-control" name="color"
                        value="{{ old('color', $car->color) }}">
                </div>
            </div>
        </div>

        <div class="col-md-3 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Tipo de baja') }} </label>

                <select {{ $option }}  class="form-control" name="tipo_baja">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($tipo_baja as $key => $value)
                        <option {{ old('tipo_baja', $car->tipo_baja) == $key ? 'selected' : '' }}
                            value="{{ $key }}">{{ $value }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>

    </div> <!--FINAL row-->
     <div class="row" >
    <div class="col-md-12 bg-warning"  >DATOS ASEGURADOS</div>

     <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Asegurado') }} </label>
                <input {{ $option }} type="text" class="form-control" name="asegurado"
                    value="{{ old('asegurado', $car->asegurado) }}">
            </div>
        </div>


        <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Contacto') }} </label>
                <input {{ $option }} type="text" class="form-control" name="contacto"
                    value="{{ old('contacto', $car->contacto) }}">
            </div>
        </div>

        <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha confirmacion') }} </label>
                <input @if ($receptor||$retiros) {{ $option }} @endif type="date"
                    class="form-control" name="fecha_confirmacion_contacto"
                    value="{{ old('fecha_confirmacion_contacto', $car->fecha_confirmacion_contacto) }}">
            </div>
        </div>

         </div> <!--FINAL row-->

    <div class="row">
        <div class="col-md-12 bg-warning"  >COORDINACION DE RETIRO</div>

         <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha solicitud de retiro') }} </label>
                <input @if ($receptor||$retiros) {{ $option }} @endif type="date"
                    class="form-control" name="fecha_limite_retiro"
                    value="{{ old('fecha_limite_retiro', $car->fecha_limite_retiro) }}">
            </div>
        </div>

 <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Coordinar retiro') }} <input {{ $option }}
                        type="checkbox" class="" name="coordinar_retiro" value="1" {{$car->coordinar_retiro ? 'checked' : ''}} ></label>

            </div>
            @if ($option != 'disabled')
                <div class="form-group">
                    <label class="control-label">Avisar a tramitador <input type="checkbox" name="avisar_tramitador"
                            value="1"></label>

                </div>
            @endif

            <div class="form-group">
                <label class="control-label">Retiro anticipado <input type="checkbox" name="retiro_anticipado"
                        value="1"></label>

            </div>
        </div>

         <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <a href="{{ route('staffs.create') }}" data-reload="false"
                    data-title="{{ _lang('Create Transportista') }}" class="ajax-modal-2 select2-add"><i
                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                <label class="control-label">{{ _lang('Transportista') }}</label>
                <select {{-- @if (!$retiros && !$gerencial) {{'readonly'}} @endif --}}
                    class="form-control select2-ajax" data-value="users.id" data-display="users.name"
                    data-table="users" data-where="6" data-option="id = {{ $car->idResponsable_retiro }}" name="retira" id="retira">
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("users","id","name",$car->idResponsable_retiro,array( "role_id=" => 3 )) }}
                </select>
            </div>
        </div>
         <div class="col-md-4 {{-- $class --}}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Depósito') }}</label>
                <select class="form-control"
                    name="lugar_entregas">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($lugar_entregas as $res)
                        <option {{ $res->id == $car->idLugar_entrega ? 'selected' : '' }}
                            value="{{ $res->id }}">{{ $res->nombre }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>

        <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha de retiro') }} </label>
                <input @if (!$retiros && !$gerencial) {{'disabled'}} @endif type="date"
                    class="form-control" name="fecha_retiro" value="{{ old('fecha_retiro', $car->fecha_retiro) }}">
            </div>
        </div>

 <div class="col-md-12 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Lugar de retiro') }} </label>
                <input @if ($receptor) {{ $option }} @endif type="text"
                    class="form-control" name="lugar_retiro" value="{{ old('lugar_retiro', $car->lugar_retiro) }}">
            </div>
        </div>

        <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Localidad') }} </label>
                <input @if ($receptor) {{ $option }} @endif type="text"
                    class="form-control" name="localidad" value="{{ old('localidad', $car->localidad) }}">
            </div>
        </div>

        <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <a href="{{ route('provincia.create') }}" data-reload="false"
                    data-title="{{ _lang('Create Provincia') }}" class="ajax-modal-2 select2-add"><i
                        class="ti-plus"></i> {{ _lang('Add New') }}</a>
                <label class="control-label">{{ _lang('Provincia') }}</label>
                <select @if ($receptor) {{ $option }} @endif
                    class="form-control select2-ajax" data-value="id" data-display="provincia"
                    data-table="provincias" data-where="" name="provincia">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($provincias as $provincia)
                        <option {{ $provincia->id == $car->provincia ? 'selected' : '' }}
                            value="{{ $provincia->id }}">{{ $provincia->provincia }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>

         <div class="col-md-12 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Observaciones retiro') }}</label>
                <textarea @if ($receptor) {{ $option }} @endif class="form-control summernote"
                    name="observacion_retiro">{!! old('observacion_retiro', strip_tags($car->observacion_retiro)) !!}</textarea>
            </div>
        </div>

</div> <!--FINAL row-->

    <div class="row">
    <div class="col-md-12 bg-warning"  >DOCUMENTACION</div>

      <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('04 Entregado a') }}</label>
                <select {{ $option }} class="form-control" name="entregado_a">
                    <option value="">{{ _lang('Select One') }}</option>
                    @forelse($responsable_entregas as $key => $value)
                        <option {{ $key == $car->entregado_a ? 'selected' : '' }} value="{{ $key }}">
                            {{ $value }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>


<div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha entrega 04') }} </label>
                <input {{ $option }} type="date" class="form-control" name="fecha_entrega"
                    value="{{ old('fecha_entrega', $car->fecha_entrega_asegurado_cia) }}">
            </div>
        </div>

          <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <div class="ui-widget">
                    <label class="control-label">Gestor</label>
                    <input {{ $option }} type="text" class="form-control" name="gestor" id="gestor"
                        value="{{ old('gestor', $car->gestor) }}">
                </div>

            </div>
        </div>

        @include('backend.accounting.tramitador.modal.imagenes04D');

         <div class="col-md-12 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Observacion administrativas') }}</label>
                <textarea {{ $option }} class="form-control summernote" name="observacion">{!! old('observacion', strip_tags($car->observaciones_admin)) !!}</textarea>
            </div>
        </div>

        <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha recepcion de documentacion') }} </label>
                <input {{ $option }} type="date" class="form-control" name="fecha_documento"
                    value="{{ old('fecha_documento', $car->fecha_recepcion) }}">
            </div>

        </div>
          <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha envio doc') }} </label>
                <input {{ $option }} type="date" class="form-control" name="fecha_envio_doc"
                    value="{{ old('fecha_envio_doc', $car->fecha_envio_doc) }}">
            </div>
        </div>

       
        <div class="col-md-4 {{ $class }}">
            <div class="form-group pt-3">

                <input {{ $option }} {{ $car->no_drnpa == 1 ? 'checked' : '' }} type="checkbox" name="no_drnpa" value="1">
                <label class="control-label">No requiere enviar al DRNPA </label>
            </div>
        </div>
<div class="col-md-8">
            <div class="form-group">
                <label class="control-label">{{ _lang('') }} </label>
            </div>
        </div> 


         </div> <!--FINAL row-->

    <div class="row">
    <div class="col-md-12 bg-warning"  >INGRESO DE VEHICULO</div>

     <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha de  ingreso') }} </label>
                <input @if (!$receptor) {{ $option }} @endif  type="date" class="form-control" name="fecha_ingreso"
                    value="{{ old('fecha_ingreso', $car->fecha_ingreso) }}">
            </div>
        </div>


        <div class="col-md-8">
            <div class="form-group">
                <label class="control-label">{{ _lang('') }} </label>
            </div>
        </div> 

        <div class="col-md-4">
            <label class="control-label">Piezas ausentes</label>
            @php
                foreach ($car->pieza_ausente as $p) {
                    $itemPiezas[] = $p->name;
                }
            @endphp

            <div>
                <select @if (!$receptor) {{ $option }} @endif class="select2 form-control"
                    multiple name="piezasAu[]">
                    @forelse ($items as $item)
                        <option
                            @if (isset($itemPiezas)) {{ in_array($item->item_name, $itemPiezas) ? 'selected' : '' }} @endif
                            value="{{ $item->id }}">{{ $item->item_name }}</option>


                    @empty
                    @endforelse
                </select>
            </div>

        </div>

        <!--<div class="col-md-4">
            <div class=" form-group pb-2">
                <label class="control-label">Otra pieza</label>
                <input @if (!$receptor) {{ $option }} @endif type="text"
                    class="form-control" name="otraPieza" value="" />
                {{-- <a href="javascript:void(0);" class="add_button" title="Add field"><i class="fa fa-plus"></i></a> --}}
            </div></div>-->

         <div class="col-md-4">
            <label class="control-label">Piezas en mal estado</label>
            <textarea name="piezas_defectuosa" id="piezas_defectuosa" class="form-control" cols="30" rows="10" >
                 {!! old('piezas_defectuosa', trim($car->piezas_defectuosas)) !!}
            </textarea>
        </div>
  <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">Motor en marcha <input {{ $car->motor_en_marcha == 1 ? 'checked' : '' }}
                        type="checkbox" name="motor_en_marcha" value="1"></label>

            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">{{ _lang('Kilometraje') }} </label>
                <input @if (!$receptor) {{ $option }} @endif type="number"
                    class="form-control" name="kilometraje" value="{{ old('kilometraje', $car->kilometraje) }}">
            </div>
        </div>
 <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Video') }}</label>
                <input @if (!$receptor) {{ $option }} @endif type="file"
                    class="form-control" accept="video/*" name="video[]" multiple="multiple" />
            </div>
        </div>
      
            <div class="col-md-12 mb-3">
                <label for="imagen">Fotos</label>
                <input {{-- @if (!$receptor) {{ $option }} @endif --}} type="file" class="form-control" id="imagen[]" name="imagen[]"
                    multiple="multiple">
            </div>

            <div class="col-md-12 mb-3">
            <div class="form-group">
                <label class="control-label">Notificar carga de imágenes <input type="checkbox" id="cargImg"
                        name="carga_de_imagen" value="1"></label>
            </div>

            @forelse($car->img as $img)
                <div class="card mx-3" style="width: 18rem;">
                    <img class="card-img-top img-fluid" src="{{ marcaAgua(asset('public/uploads/vehiculos/'. $img->img),$car->company_id,'/vehiculos/'.$img->img) }}"
                        alt="Card image cap">
                    <div class="card-body">
                        {{-- <h5 class="card-title">Card title</h5> --}}
                        {{-- <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p> --}}
                        {{-- <a href="#" class="btn btn-primary">Go somewhere</a> --}}
                    </div>

                    <div class="card-footer">
                        <div class="form-check">
                            <input {{-- @if (!$receptor) {{ $option }} @endif --}} type="checkbox" class="form-check-input" name="imgDelete[]"
                                value="{{ $img->id }}">
                            <label class="form-check-label">
                                Eliminar
                            </label>
                        </div>
                    </div>
                </div>
            @empty
                <p>No hay imágenes disponibles.</p>
            @endforelse
        </div>

    </div> <!--FINAL row-->
    <div class="row">

        @if ($option != 'disabled')
            <div class="col-md-4 {{ $class }} d-none">
                <div class="form-group ">
                    <label class="control-label">{{ _lang('Forma') }}</label>
                    <input {{ $option }} type="text" class="form-control" name="forma"
                        value="{{ old('forma', $car->forma) }}">
                </div>
            </div>
        @endif
        @if ($option != 'disabled')
        @endif
 @if (strtolower(auth()->user()->role->name) == 'gerencial' ||
                strtolower(auth()->user()->role->name) == 'gerente de operarios'
                ||
                strtolower(auth()->user()->role->name) == 'operario')
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Observaciones de taller') }}</label>
                    <textarea class="form-control summernote" name="observacion_gerente_operario">{!! old('observacion_gerente_operario', $car->observacion_gerente_operario) !!}</textarea>
                </div>
            </div>
        @endif


        {{-- {{dd($car->marca_modelo->marca->id)}} --}}

        {{-- <div class="col-md-4 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Datos del Titular') }} </label>
                
                <textarea class="form-control" name="datos-titular" >{{ $car->titular }}</textarea>

            </div>
        </div> --}}

       
         <div class="col-md-12 {{ $class }}"> 
         <div class="form-group">
         <label class="control-label">{{ _lang('Motor Vendido') }} </label>
         <input type="text" class="form-control" name="crp" value="{{ old('crp',$car->crp_nro)}}"> 
         </div> 
         </div> 
		 
		 <div class="col-md-3 {{ $class }}">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha de recibo carpeta') }} </label>
                <input {{ $option }} type="date" class="form-control" name="fecha_recibo_carpeta"
                    value="{{$car->fecha_recibo_carpeta}}" >
            </div>
        </div>
		<div class="col-md-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha Env. DRNPA') }} </label>
                <input {{ $option }} type="date" class="form-control" name="fecha_envio_drnpa"
                    value="{{$car->fecha_envio_drnpa}}" >
            </div>
        </div>
		<div class="col-md-3">
        <div class="form-group">
                <label class="control-label">Fecha de Envio mail a DRNPA</label>
                <input type="date" class="form-control" name="fecha_envio_mail_drnpa"  value="{{$car->fecha_envio_mail_drnpa}}">
            </div>
         </div>
		 <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha de finalización') }} </label>
                <input {{ $option }} type="date" class="form-control" name="fecha_finalizacion"
                    value="{{$car->fecha_finalizacion}}" >
            </div>
        </div>
	</div>
        

       
        

        

        {{-- <div class="col-md-4"> --}}
        {{-- <div class="form-group"> --}}
        {{-- <label class="control-label">{{ _lang('Control fuera de programacion') }}</label> --}}
        {{-- <select {{$option}} class="form-control" name="control"> --}}
        {{-- <option value="">{{ _lang('Select One') }}</option> --}}
        {{-- <option {{$car->control == 0 ? 'selected' : '' }} value="0">{{ _lang('Explicar') }}</option> --}}
        {{-- <option {{$car->control == 1 ? 'selected' : '' }} value="1">{{ _lang('En fecha') }}</option> --}}

        {{-- </select> --}}
        {{-- </div> --}}
        {{-- </div> --}}


        <div class="col-12"></div>


        {{-- <div class="field_wrapper col-md-4 mb-2"> --}}


        {{-- <div class="d-flex justify-content-center align-items-center pb-2"> --}}
        {{-- <input type="text" class="form-control" name="piezasAu[]" value=""/> --}}
        {{-- <a href="javascript:void(0);" class="add_button" title="Add field"><i class="fa fa-plus"></i></a> --}}
        {{-- </div> --}}
        {{-- </div> --}}
       

    </div>
    <div class="col-md-12 mt-2">
        <div class="form-group">
            {{-- <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button> --}}
            <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
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
        const editModeloBtn = $('#editarModelo');
        //let modeloAjax;
        let file = $('input[type="file"]');

        
        editModeloBtn.attr('href', "{{ url('marcamodelo/edit-modelo') }}"+ '/'+marca_modelo.val());

        const modalSecundario = $('#secondary_modal');

        modalSecundario.on('hidden.bs.modal', function (e) {
            $.ajax({
            url: "{{ route('modelosByMarca') . '/' }}" + marca.val(),
            dataType: 'json',
            success: function(res) {
                //console.log(res);
                let html = `<option value="">{{ _lang('Select One') }}</option>`;
                res.map(r => {
                    if (modelo.val() == r.idModelo) {

                        html +=
                            `<option selected value="${r.idModelo}">${r.modelo.modelo}</option>`;
                    } else {
                        html += `<option value="${r.idModelo}">${r.modelo.modelo}</option>`;
                    }

                })
                result = res;



                // let modeloAjax = result.find(r => r.idModelo == modelo.val() && r.idMarca == marca
                //     .val());
                // console.log(result);

                modelo.html(html);

                // marca_modelo.val(modeloAjax.id);

            }


        })
        });


        file.each(function(i) {
            $(this).change(function(e) {
               // console.log($(this).val() != '');
                if ($(this).val() != '') {

                    $('#cargImg').prop('checked', true);

                }
            })

        })

        $.ajax({
            url: "{{ route('modelosByMarca') . '/' }}" + marca.val(),
            dataType: 'json',
            success: function(res) {
               // console.log(res);
                let html = `<option value="">{{ _lang('Select One') }}</option>`;
                res.map(r => {
                    if (modelo.val() == r.idModelo) {

                        html +=
                            `<option selected value="${r.idModelo}">${r.modelo.modelo}</option>`;
                    } else {
                        html += `<option value="${r.idModelo}">${r.modelo.modelo}</option>`;
                    }

                })
                result = res;



                let modeloAjax = result.find(r => r.idModelo == modelo.val() && r.idMarca == marca
                    .val());
                //console.log(result);

                modelo.html(html);

                marca_modelo.val(modeloAjax.id);

            }


        })

        marca.change(function() {
            modelo.html(`<option value="">{{ _lang('Select One') }}</option>`);
            $.ajax({
                url: "{{ route('modelosByMarca') . '/' }}" + marca.val(),
                dataType: 'json',
                success: function(res) {
                   // console.log(res);
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

        $('.select2').select2();

        // var maxField = 10; // Numero maximo de campos
        // var addButton = $('.add_button');
        // var wrapper = $('.field_wrapper');
        // var fieldHTML = '<div class="d-flex justify-content-center align-items-center pb-2"><input type="text" ' +
        //     'class="form-control" ' +
        //     'name="piezasAu[]" ' +
        //     'value=""/><a ' +
        //     'href="javascript:void(0);" ' +
        //     'class="remove_button" title="Remove field"><i class="fa fa-ban"></i></a></div>'; //New input field html
        // var x = 1;
        // $(addButton).click(function () {
        //     if (x < maxField) {
        //         x++;
        //         $(wrapper).append(fieldHTML);
        //     }
        // });
        // $(wrapper).on('click', '.remove_button', function (e) { // Una vez se ha hecho clic en el boton de eliminar
        //     e.preventDefault();
        //     $(this).parent('div').remove(); //Eliminamos el div
        //     x--; // Reducimos el contador a 1
        // });
    })
</script>
