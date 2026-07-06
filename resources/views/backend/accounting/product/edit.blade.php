@extends('layouts.app')
<style>


</style>
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <span class="d-none panel-title">{{ _lang('Update Product') }}</span>

                <div class="card-body">
                    <form method="post"  id="myForm" class="validate" autocomplete="off"
                        action="{{ action('ProductController@update', $id) }}" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input name="_method" type="hidden" value="PATCH">
                        <div class="row">
                            {{-- <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Product Name') }}</label>
                                    <input type="text" class="form-control" name="item_name"
                                           value="{{ $product->item->item_name }}" >
                                </div>
                            </div> --}}

                             <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Nº interno') }}</label>
                                    <select id="nro_interno" name="nro_interno" required class="form-control select2">
                                        <option value="0"  {{$product->nro_interno == 0 ? 'selected' : '' }}>Sin Interno</option>
                                        @foreach ($nro_interno_datos as $interno_row)
                                                        <option value="{{ $interno_row->id }}" {{$product->nro_interno == $interno_row->id ? 'selected' : '' }}>{{ nroInternoAlias($interno_row->company_id,$interno_row->tipo_vehiculo,$interno_row->id) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Productos</label>
                                    <!--<select id="item_id" name="item_id" required class="form-control">
                                        <option value="">Seleccionar</option>
                                          @forelse ($items as $item)
                                            <option value="{{ $item->id }}" {{$product->item_id == $item->id ? 'selected' : '' }}>{{ $item->item_name }}</option>
                                            @empty
                                        @endforelse
                                    </select>-->
								<select id="item_id" name="item_id" style="width: 100%;">
										@if(isset($product->item_id))
											<option value="{{ $product->item_id }}" selected>
												{{ $product->item->item_name }}
											</option>
										@endif
								</select>
                                </div>
                            </div>
                            <div id="contNroMotor" class="col-md-12 d-none">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Nº motor') }}</label>
                                    <input type="text" class="form-control" id="nro_motor" name="nro_motor"
                                        value="{{ old('nro_motor', $product->nro_motor) }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Deposito</label>
                                    <select id="idDeposito" name="idDeposito" required class="form-control select2">
                                        <option value="">Seleccionar</option>
                                        {{ create_option('lugar_entregas', 'id', 'nombre', old('idDeposito',$product->idDeposito)) }}
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Ubicación') }}</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion"
                                        value="{{ old('ubicacion',$product->ubicacion) }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Company') }}</label>

                                   {{--  <select id="company" name="company" required class="form-control">
                                        @foreach ($cias as $cia)
                                            @if ($cia->business_name == 'Pentacar' || $cia->business_name == 'Paternal')
                                                <option
                                                    {{ $product->item->company_id == $cia->id ? 'selected' : '' }}
                                                    value="{{ $cia->id }}">{{ $cia->business_name }}</option>
                                            @endif
                                        @endforeach
                                    </select> --}}


                                    <select id="company" name="company" required class="form-control">
                                        @foreach ($cias as $cia)
                                            @if ($cia->business_name == 'Pentacar' || $cia->business_name == 'Paternal')
                                                @if (auth()->user()->role->name != 'Gerencial')
                                                    @if (auth()->user()->company_id == $cia->id)
                                                        <option value="{{ $cia->id }}"  {{ $product->company_id == $cia->id ? 'selected' : '' }} >{{ $cia->business_name }}
                                                        </option>
                                                    @endif
                                                @else
                                                    <option  {{ $product->company_id == $cia->id ? 'selected' : '' }}
                                                        value="{{ $cia->id }}">{{ $cia->business_name }}</option>
                                                @endif
                                            @endif
                                        @endforeach
                                    </select>


                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Año') }}</label>
                                    <input type="text" class="form-control" maxlength="4" name="anio"
                                        value="{{ old('anio', $product->anio) }}">
                                </div>
                            </div>

                            <div class="col-md">
                                <label for="estado_prod">Estado</label>
                                <div class="form-group">
                                    <select class="form-control" name="estado_prod" id="estado_prod">
                                        <option {{ $product->estado == 'optimo' ? 'selected' : '' }} value="optimo">Óptimo
                                        </option>
                                        <option {{ $product->estado == 'no funciona' ? 'selected' : '' }}
                                            value="no funciona">No funciona</option>
                                        <option {{ $product->estado == 'descompuesto' ? 'selected' : '' }}
                                            value="descompuesto">Descompuesto</option>
                                    </select>
                                </div>
                            </div>



                           

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Nº oblea') }}</label>
                                    <input type="text" class="form-control" name="nro_oblea"
                                        value="{{ old('nro_oblea', $product->nro_oblea ?? null) }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Fecha último giro') }}</label>
                                    @php
                                        $fechaUltimoGiro = $product->fecha_ultimogiro ? \Carbon\Carbon::parse($product->fecha_ultimogiro)->format('Y-m-d') : '';
                                    @endphp
                                    <input type="date" class="form-control" name="fecha_ultimogiro"
                                        value="{{ old('fecha_ultimogiro', $fechaUltimoGiro) }}">
                                </div>
                            </div>

                            <div class="col-md-6 d-none">
                                <label for="car_or_stock">Tipo de producto</label>
                                <select name="car_or_stock" class="form-control" id="car_or_stock" disable>
                                    <option value="">Selecciona</option>
                                    <option {{ $product->allCar != 1 ? 'selected' : '' }} value="2">Producto en
                                        stock
                                    </option>
                                    {{-- <option {{$product->item->allCar == 1 ? 'selected' : ''}} value="1">Todos los --}}
                                    {{-- vehiculos --}}
                                    {{-- </option> --}}
                                </select>
                            </div>


                            @if ($product->allCar != 1)
                                <div class="col-md-6">
                                    <div class="form-group">
                                       <!-- <a href="{{ route('marcamodelo.create') }}" data-reload="false"
                                            data-title="{{ _lang('Create Marca') }}" class="ajax-modal-2 select2-add"><i
                                                class="ti-plus"></i> {{ _lang('Add New') }}</a>-->
                                        <label class="control-label">{{ _lang('Marca') }}</label>
                                        <select class="form-control select2-ajax" data-value="id" data-display="marca"
                                            data-table="marcas" data-where="" id="marca">
                                            <option value="">{{ _lang('Select One') }}</option>
                                            @forelse($marcas as $marca_modelo)
                                            <option  {{ $marca_modelo->id == ($product->marcaModelo->marca->id ?? '') ? 'selected' : '' }}
                                            value="{{$marca_modelo->id}}">{{$marca_modelo->marca}}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
								
	

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">{{ _lang('Modelo') }}</label>
                                        <select class="form-control select2" id="modelo">
                                            @if ($product->marcaModelo)
                                            <option
                                                value="{{ $product->marcaModelo->modelo->id }}">{{ $product->marcaModelo->modelo->modelo }}
                                            </option>
                                            @endif

                                        </select>
                                        <input type="hidden" name="marca_modelo" id="marca_modelo"
                                            value="{{ $product->marca_modelo }}">
                                    </div>
                                </div>


                                <div class="col-md-6 d-none">
                                    <div class="form-group">
                                        <label class="control-label">{{ _lang('Product Cost') . ' ' . currency() }}</label>
                                        <input type="hidden" class="form-control" name="product_cost"
                                            value="0{{-- old('product_cost') --}}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <a href="{{ route('categorias.create') }}" data-reload="false"
                                            data-title="{{ _lang('Create Marca') }}"
                                            class="ajax-modal-2 select2-add"><i class="ti-plus"></i>
                                            {{ _lang('Add New') }}</a>
                                        <label class="control-label">{{ _lang('Categorias') }}</label>
                                        @php
                                            $idsCat = [];
                                            $cat = $product->category ?? [];

                                            foreach ($cat as $c) {
                                                $idsCat[] = $c->categoria_id;
                                            }

                                        @endphp

                                        <select multiple class="form-control select2-ajax" data-value="id"
                                            data-display="nombre" data-table="categorias" data-where=""
                                            name="categoria[]" id="categoria">
                                            <option value="">{{ _lang('Select One') }}</option>
                                            @forelse ($categorias as $c)
                                                <option {{ in_array($c->id, $idsCat) }}
                                                    {{ in_array($c->id, $idsCat) ? 'selected' : '' }}
                                                    value="{{ $c->id }}">{{ $c->nombre }}
                                                </option>
                                            @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group d-none">
                                        <label class="control-label">{{ _lang('Product Price') . ' ' . currency() }}</label>
                                        <input type="text" class="form-control" name="product_price"
                                            value="{{ old('product_price') }}">
                                    </div>
                                </div>

                               


                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ _lang('Description') }}</label>
                                        <textarea class="form-control" name="description">{{ $product->description }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-6 py-4">
                                    <div class="form-group">
                                        <div class="custom-switch-container">
                                            <label class="switch">
                                                <input type="checkbox" id="mercado_libre" name="mercado_libre" 
                                                    {{ $product->mercado_libre == 1 ? 'checked' : '' }} value="1">
                                                <span class="slider round"></span>
                                            </label>
                                            <span class="switch-label">PUBLICADA EN MERCADO LIBRE</span>
                                        </div>
                                    </div>
                                </div>
								
								<div class="col-md-12">
									<x-dropzone-input 
										id="dropzone-productos" 
										url="{{ action('ProductController@update', $id) }}"
										:serverFiles="$galeriaFiles ?? []"
									/>
								</div>

                                <!--<div class="col-md-12 mb-5">
                                    <label class="control-label">{{ _lang('Fotos') }}</label>
                                   <input type="file" class="form-control" id="imagen[]" name="imagen[]"
                                        multiple="">
                                </div>-->
							
                            @endif

                            <div class="col-md-12 mt-3">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js-script')
    <script>
        $(document).ready(function() {

            let marca = $('#marca');
            let modelo = $('#modelo');
           
            let marca_modelo = $('#marca_modelo');
            let result;
            let item_id = $('#item_id');
			let item_seleccionado=item_id.val();

            /*if (item_id.val() != '') {
                $.ajax({
                    url: "{{ url('products/item/') }}/" + item_id.val(),
                    dataType: 'json',
                    success: function(res) {
                        console.log(res);
                        let contNroMotor = $('#contNroMotor');
                        if (res && (res.item.item_name == 'Motor Semiarmado Con Accesorios' || res.item
                                .item_name == 'Motor Semiarmado Sin Acesorios')) {
                            contNroMotor.removeClass('d-none')
                        } else {
                            contNroMotor.addClass('d-none')
                            $('#nro_motor').val('');
                        }
                        //result = res;



                    }

                })
            }*/

			if (item_id.val() != '') {
				item_id.change();
			}
	
	
         
            item_id.change(function(e) {
			//	console.log(item_seleccionado);
                $.ajax({
                    url: "{{ url('products/item/') }}/" + item_id.val(),
                    dataType: 'json',
                    success: function(res) {
                        //console.log(res);
                        let contNroMotor = $('#contNroMotor');
                        if (res && (res.item_name == 'Motor Semiarmado Con Accesorios' || res
                                .item_name == 'Motor Semiarmado Sin Acesorios')) {
                            contNroMotor.removeClass('d-none')
                        } else {
                            contNroMotor.addClass('d-none')
                            $('#nro_motor').val('');
                        }
                        //result = res;



                    }

                })
            })
     
				marca.change(function() {
                let modelo_id= modelo.val();
                modelo.html(`<option selected value="">{{ _lang('Select One') }}</option>`);
                //console.log(marca.val())


                $.ajax({
                    url: "{{ route('modelosByMarca') . '/' }}" + marca.val(),
                    dataType: 'json',
                    success: function(res) {
                        //console.log(res);
                        let html = `<option value="">{{ _lang('Select One') }}</option>`;
                        res.map(r => {
                            
                        if (modelo_id == r.idModelo) {

                            html +=
                                `<option selected value="${r.idModelo}">${r.modelo.modelo}</option>`;
                        } else {
                            html += `<option value="${r.idModelo}">${r.modelo.modelo}</option>`;
                        }

                    })
                    result = res;

                        modelo.html(html);
                       // result = res;
                        //console.log(result);

                        let modeloAjax = result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val());
                    if (modeloAjax)
                        marca_modelo.val(modeloAjax.id);
                    }

                })
            })

            //modelo.select2();
            modelo.change(function() {
                marca_modelo.val('');

                //console.log(result);

                //console.log(result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val()));
                let modeloAjax = result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val());
                //console.log(result);
                if (modeloAjax)
                marca_modelo.val(modeloAjax.id);

            })

            marca.change();
			
			
			
	  $('#item_id').select2({
        placeholder: 'Escribe el modelo o producto...',
        minimumInputLength: 2,
        allowClear: true,
        width: '100%',
        ajax: {
            url: "{{ route('products.buscar') }}",
            dataType: 'json',
            delay: 400, 
            data: function (params) {
                return {
                    q: params.term,          
                    page: params.page || 1,  
                    nro_interno:  $('#nro_interno').val(),
                    currentId:  item_seleccionado
					
                };
            },
            processResults: function (data, params) {
                return {
                    results: data.items 
                };
            },
            cache: true
        },
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            },
            searching: function() {
                return "Buscando..."; 
            }
        }
    });


    $('#item_id').on('select2:select', function (e) {
        let datosProducto = e.params.data; 
        if (!datosProducto.id) return; 
    });
			
			
 $('#myForm').on('submit', function(e) {			  
    e.preventDefault();
	$('#myForm').find(".print-error-msg").find("ul").html('');
    $('#myForm').find(".print-error-msg").css('display','none');
    $(':input[type="submit"]').prop('disabled', true);
	
	let masterFormData = new FormData(this);
    const formMethod = $(this).find('input[name="_method"]').val() || 'POST';
    masterFormData.append("_method", formMethod);
	
	$('.dropzone-drag-area').each(function() {
        const elementId = $(this).attr('id');
        const paramName = $(this).data('name');
        const type = $(this).data('type');
        const dz = document.getElementById(elementId).dropzoneInstance;
        if (dz) {
            let queuedFiles = dz.getQueuedFiles();
            
            // SOLO adjuntar si realmente hay archivos nuevos esperando en cola
            if (queuedFiles.length > 0) {
                if (type === 'video') {
                    masterFormData.append(paramName, queuedFiles[0]); // Envía el archivo individual directo
                } else {
                    queuedFiles.forEach(file => {
                        masterFormData.append(`${paramName}[]`, file);
                    });
                }
            }
        }
    });

	 $.ajax({
        url: $(this).attr("action"),
        method: 'POST',
        data: masterFormData,
         processData: false,
            contentType: false,
			dataType: 'json',
            cache: false,
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        success: function(response) {
		   
					if(response.result == "success"){
                              window.location.href = "{{ route('products.index') }}";
							  	//window.setTimeout(function(){window.location.reload()}, 500);
                     }else{
                                    //$('#myForm').find(".print-error-msg").find("ul").html('');
                                    $('#myForm').find(".print-error-msg").css('display','block');
                                    $.each( response.message, function( key, value ) {
                                        $('#myForm').find(".print-error-msg").find("ul").append('<li>'+value+'</li>');
                                    });
                                
                     }

                            setTimeout(function(){  $(':input[type="submit"]').prop('disabled', false); }, 5000); // Habilitar después de 5 segundos
				
		   
		   
        },
        error: function(xhr) {
            alert("Error al procesar los componentes multimedia.");
        }
    });
  });	


			
			
        })
    </script>
@endsection
