@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <span class="panel-title d-none">{{ _lang('Add Product') }}</span>

                <div class="card-body">
                    <form method="post" class="validate" id="myForm" autocomplete="off" action="{{ url('products') }}"
                        enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-12">
                            <div class="alert alert-danger print-error-msg" style="display:none">
					                <ul></ul>
				            </div>
                            </div>
<div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Nº interno') }}</label>
                                    <select id="nro_interno" name="nro_interno" required class="form-control select2">
                                    <option value="0">Sin Interno</option>
                                        @foreach ($nro_interno_datos as $interno_row)
                                                        <option value="{{ $interno_row->id }}">{{ nroInternoAlias($interno_row->company_id,$interno_row->tipo_vehiculo,$interno_row->id) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
								<!--<a id="productLink" href="{{ route('item.create') }}" class="select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>-->
						<label class="control-label">{{ _lang('Producto en vehiculo') }}</label>
                                    <label class="control-label">Productos</label>
                                    <select id="item_id" name="item_id" required class="form-control select2">
                                        <option value="">Seleccionar</option>
                                         @forelse ($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                                            @empty
                                        @endforelse
                                    </select>
                                </div>
                            </div>

                            <div id="contNroMotor" class="col-md-12 d-none">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Nº motor') }}</label>
                                    <input type="text" class="form-control" id="nro_motor" name="nro_motor"
                                        value="{{ old('nro_motor') }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Deposito</label>
                                    <select id="idDeposito" name="idDeposito" required class="form-control select2">
                                        <option value="">Seleccionar</option>
                                        {{ create_option('lugar_entregas', 'id', 'nombre', old('idDeposito', auth()->user()->location)) }}
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Ubicación') }}</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion"
                                        value="{{ old('ubicacion') }}">
                                </div>
                            </div>

                            {{-- <div class="col-md-12">
					<div class="form-group">
					  <label class="control-label">{{ _lang('Product Name') }}</label>						
					  <input type="text" class="form-control" name="item_name" value="{{ old('item_name') }}" required>
					</div>
				  </div> --}}

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Company') }}</label>
                                    <select id="company" name="company" required class="form-control">
                                        <option value="">Seleccionar</option>
                                        @foreach ($cias as $cia)
                                            @if ($cia->business_name == 'Pentacar' || $cia->business_name == 'Paternal')
                                                @if (auth()->user()->role->name != 'Gerencial')
                                                    @if (auth()->user()->company_id == $cia->id)
                                                        <option value="{{ $cia->id }}" selected>{{ $cia->business_name }}
                                                        </option>
                                                    @endif
                                                @else
                                                    <option {{-- {{  auth()->user()->company_id == $cia->id ? --}} {{-- 'selected' : ''}} --}}
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
                                        value="{{ old('anio') }}">
                                </div>
                            </div>

                            <div class="col-md d-none">
                                <label for="estado_prod">Estado</label>
                                <div class="form-group">
                                    <select class="form-control" name="estado_prod" id="estado_prod">
                                        <option selected value="optimo">Óptimo</option>
                                        <option value="no funciona">No funciona</option>
                                        <option value="descompuesto">Descompuesto</option>
                                    </select>
                                </div>
                            </div>
                            

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Nº oblea') }}</label>
                                    <input type="text" class="form-control" name="nro_oblea"
                                        value="{{ old('nro_oblea') }}">
                                </div>
                            </div>

                            <div class="col-md-6 d-none">
                                <label for="car_or_stock">Tipo de producto</label>
                                <select name="car_or_stock" class="form-control" id="car_or_stock" required>
                                    <option value="">Selecciona</option>
                                    <option selected value="2">Producto en stock</option>
                                    {{-- <option value="1">Todos los vehiculos</option> --}}
                                </select>
                            </div>

                            {{-- <div class="col-md-6"> --}}
                            {{-- <div class="form-group"> --}}
                            {{-- <a href="{{ route('vehiculo.create') }}" data-reload="false" data-title="{{ _lang('Add Supplier') --}}
                            {{-- }}" --}}
                            {{-- class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a> --}}

                            {{-- <label class="control-label">{{ _lang('Vehiculo') }}</label> --}}
                            {{-- <select class="form-control select2-ajax" data-value="cars.id" data-display="marcas.marca" --}}
                            {{-- data-display2="modelos.modelo" data-display3="siniestro" --}}
                            {{-- data-table="cars" --}}
                            {{-- data-where="8" name="car_id"> --}}
                            {{-- <option value="">{{ _lang('- Select Car -') }}</option> --}}
                            {{-- </select> --}}
                            {{-- </div> --}}
                            {{-- </div> --}}

                            <div class="col-md-6">
                                <div class="form-group">
                                    <a href="{{ route('marcas.createLinea') }}" data-reload="false"
                                        data-select="vendedor_id" data-title="{{ _lang('Create Marca') }}" class="ajax-modal-2 select2-add"><i
                                            class="ti-plus"></i> {{ _lang('Add New') }}</a>
                                    <label class="control-label">{{ _lang('Marca') }}</label>
                                    <select class="form-control select2" data-value="id" data-display="marca"
                                        data-table="marca" data-where="" id="marca" name="marca">
                                        <option value="">{{ _lang('Select One') }}</option>
                                        {{ create_option('marcas', 'id', 'marca', old('marca'),array('activo=' => 'Si')) }}
                                    </select>
                                </div>
                            </div>
                           
						  <div class="col-md-6">
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

                            <div class="col-md-6 d-none">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Product Cost') . ' ' . currency() }}</label>
                                    <input type="hidden" class="form-control" name="product_cost"
                                        value="0{{-- old('product_cost') --}}">
                                </div>
                            </div>

                            <div class="col-md-6 d-none">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Product Price') . ' ' . currency() }}</label>
                                    <input type="text" class="form-control" name="product_price"
                                        value="{{ old('product_price') }}">
                                </div>
                            </div>

                            {{-- <div class="col-md-6"> --}}
                            {{-- <div class="form-group"> --}}
                            {{-- <a href="{{ route('product_units.create') }}" data-reload="false" data-title="{{ _lang('Add Product Unit') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a> --}}
                            {{-- <label class="control-label">{{ _lang('Product Unit') }}</label> --}}
                            {{-- <select class="form-control select2-ajax" data-value="unit_name" data-display="unit_name" data-table="product_units" data-where="1" name="product_unit" required> --}}
                            {{-- <option value="">{{ _lang('- Select Product Unit -') }}</option> --}}
                            {{-- </select> --}}
                            {{-- </div> --}}
                            {{-- </div> --}}

                            {{-- <div class="col-md-6">
					<div class="form-group">
						<a href="{{ route('categorias.create') }}" data-reload="false" data-title="{{ _lang
                                        ('Crear Categoria')
				}}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
						<label class="control-label">{{ _lang('Color') }}</label>
						<select multiple class="form-control select2-ajax" data-value="id" data-display="nombre"
								data-table="categorias" data-where="" name="categoria[]" id="categoria">
							<option value="">{{ _lang('Select One') }}</option>

						</select>
					</div>
				</div> --}}

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Description') }}</label>
                                    <textarea class="form-control" name="description">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <!--<div class="col-md py-4">
                                <div class="form-check">
                
                                    <input type="checkbox" id="mercado_libre" name="mercado_libre" class="form-check-input" value="1">
                                    <label class="form-check-label" for="mercado_libre">PUBLICADA EN MERCADO LIBRE</label>
                
                                </div>
                            </div>-->
							
							<div class="col-md-6 py-4">
                                    <div class="form-group">
                                        <div class="custom-switch-container">
                                            <label class="switch">
                                                <input type="checkbox" id="mercado_libre" name="mercado_libre" 
                                                     value="1">
                                                <span class="slider round"></span>
                                            </label>
                                            <span class="switch-label">PUBLICADA EN MERCADO LIBRE</span>
                                        </div>
                                    </div>
                             </div>

                           <!-- <div class="col-md-12 mb-3">
                                <label class="control-label">{{ _lang('Fotos') }}</label>
                                <input type="file" class="form-control" id="imagen[]" name="imagen[]"
                                    multiple="">
                            </div>-->
							
							<div class="col-md-12">
									<x-dropzone-input 
										id="dropzone-productos" 
										url="{{ url('products') }}"
										:serverFiles="$productosFiles ?? []"
									/>
							</div>


                            <div class="col-md-12 mt-3">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
                                </div>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title" id="exampleModalLabel">Imprimir</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
		  
		</div>
		<div class="modal-body">
          <div class="row">
            <div  id="printsinQR" class="col-md-12">
            </div>      
         </div>      
	
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>
  
  <div class="modal fade" id="itemCreateModal" tabindex="-1" aria-labelledby="itemCreateModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryCreateModalLabel">Crear Items</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
            </div>
            <form id="miFormulario" name="miFormulario" class="was-validated" action="{{ route('item.store') }}" method="post">
                @csrf
                <div class="modal-body">
				<div class="alert alert-danger print-error-msg" style="display:none">
					<ul></ul>
				</div>
				<div class="col-lg-12 mb-3">
						<label for="item_name" class="form-label">{{ _lang('Product Name') }}</label>
                        <input type="text" name="item_name" id="item_name" required class="form-control" value="{{old('item_name')}}">
                        @error('item_name')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
				</div>	
			  <input type="hidden" id="item_type" name="item_type" value="product" />
			  <input type="hidden" id="company_id" name="company_id" value="{{ company_id() }}" />
			  <input type="hidden" id="activo" name="activo" value="si" />
			</div>	
                <div class="modal-footer">
                    <button class="btn btn-primary"> Actualizar <i class="bi bi-check"></i></button>
                </div>
            </form>
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
            let nro_interno = $('#nro_interno');

            nro_interno.change(function(e) {
                    let select= $("#item_id");

                    $(':input[type="submit"]').prop('disabled', false);
                    //let item_id_actual=1;//select.val();
                    select.find("option").prop("disabled", false);
                    select.prop('selectedIndex', 0);
                    limpiarItems();
                    select.select2();
                    //MostrarModelo();
					
					if ($('#nro_interno').val() > 0){
						marca.prop("disabled", true);
						modelo.prop("disabled", true);
					}else{
						marca.prop("disabled", false);
						modelo.prop("disabled", false)
						marca.val('');
						marca_modelo.val('');
						//marca.select2();
						marca.trigger('change'); 

					}
                    
                 // Llamada AJAX para verificar pieza y obtener nro_motor
                if ($('#item_id').val() != '' && $('#nro_interno').val() > 0 ) {
                    $.ajax({
                        url: "{{ url('vehiculo/verifica-pieza') }}" + "/" + $('#item_id').val() +
                            "/" +
                            nro_interno.val(),
                        dataType: 'json',
                        success: function(res) {
                            if (($('#item_id option:selected').text() ==
                                    'Motor Semiarmado Con Accesorios' ||
                                    $('#item_id option:selected').text() ==
                                    'Motor Semiarmado Sin Acesorios')) {
                                $('#nro_motor').val(res.nro_motor);
                            }
                            if (res.existe_pieza) {
                                $('#myForm').find(".print-error-msg").find("ul").html('');
                                    $('#myForm').find(".print-error-msg").css('display','block');
                                    $('#myForm').find(".print-error-msg").find("ul").append('<li>ATENCION: El vehiculo ya posee esta pieza registrada</li>');
                                //alert('ATENCION: El vehiculo ya posee esta pieza registrada')
                                $(':input[type="submit"]').prop('disabled', true);  
                            }else{
                                   MostrarModelo();
                            }
                        }
                    });

                }else{
                     MostrarModelo();
                }

               /* 
                $.ajax({
                    url: "{{ url('vehiculo/getMarcaModeloByCar/') }}/" + nro_interno.val(),
                    dataType: 'json',
                    success: function(resMM) {
                        marca.val(resMM.marca_modelo.idMarca);

                        $('#marca_modelo').val(resMM.marca_modelo.id);
                        marca.select2()
                        $.ajax({
                            url: "{{ route('modelosByMarca') . '/' }}" + resMM
                                .marca_modelo.idMarca,
                            dataType: 'json',
                            success: function(res) {
                                let html =
                                    `<option value="">{{ _lang('Select One') }}</option>`;
                                res.map(r => {
                                    selected = '';
                                    if (resMM.marca_modelo.idModelo == r
                                        .idModelo) {
                                        selected = 'selected'
                                    }
                                    html +=
                                        `<option ${selected} value="${r.idModelo}">${r.modelo.modelo}</option>`;
                                })
                                result = res;

                                modelo.html(html);
                                // modelo.select2();

                            }

                        })



                    }

                })*/
               
            })

            item_id.change(function(e) {
                $(':input[type="submit"]').prop('disabled', false);  
               if ($('#item_id').val() != '' && $('#nro_interno').val() > 0 ) {
                    $.ajax({
                        url: "{{ url('vehiculo/verifica-pieza') }}" + "/" +
                            $('#item_id').val() + "/" +
                            nro_interno.val(),
                        dataType: 'json',
                        success: function(res) {
                            if (($('#item_id option:selected').text() ==
                                    'Motor Semiarmado Con Accesorios' ||
                                    $('#item_id option:selected').text() ==
                                    'Motor Semiarmado Sin Acesorios')) {
                                $('#nro_motor').val(res.nro_motor);
                            }
                            if (res.existe_pieza) {
                                    $('#myForm').find(".print-error-msg").find("ul").html('');
                                    $('#myForm').find(".print-error-msg").css('display','block');
                                    $('#myForm').find(".print-error-msg").find("ul").append('<li>ATENCION: El vehiculo ya posee esta pieza registrada</li>');
                                //alert(
                                  //  'ATENCION: El vehiculo ya posee esta pieza registrada')
                                      $(':input[type="submit"]').prop('disabled', true);
                            }else{
                                            //alert();
                                            MostrarNroMotor();
                                        }
                        }
                    });

                }else{
                                MostrarNroMotor();
                            }
                })
            

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
						//$btnModelo.css({ 'pointer-events': 'auto', 'opacity': '1' });
						//var urlBase = "{{ url('modelos/createLinea') }}"; // Ajusta a tu nombre de ruta real
						//$btnModelo.attr('href', urlBase + '?idMarca=' + marca.val());
						//modelo.prop('disabled', false).trigger('change');
                    }

                })
            })

            //modelo.select2();
            modelo.change(function() {
                marca_modelo.val('');

                //console.log(result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val()));
                result = result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val());
                //console.log(result);

                marca_modelo.val(result.id);

            })


            function MostrarNroMotor() {
                
                $.ajax({
                    url: "{{ url('products/item/') }}/" + item_id.val(),
                    dataType: 'json',
                    success: function(res) {
                        let contNroMotor = $('#contNroMotor');
                        if (res && (res.item.item_name == 'Motor Semiarmado Con Accesorios' ||
                                res.item.item_name == 'Motor Semiarmado Sin Acesorios')) {
                            contNroMotor.removeClass('d-none')
                        } else {
                            contNroMotor.addClass('d-none')
                            $('#nro_motor').val('');
                        }
                        result = res;
                    }
                })
            }

            function MostrarModelo() {
                if ($('#nro_interno').val() > 0){
					$.ajax({
                    url: "{{ url('vehiculo/getMarcaModeloByCar/') }}/" + nro_interno.val(),
                    dataType: 'json',
                    success: function(resMM) {
                        marca.val(resMM.marca_modelo.idMarca);

                        $('#marca_modelo').val(resMM.marca_modelo.id);
                        marca.select2()
                        $.ajax({
                            url: "{{ route('modelosByMarca') . '/' }}" + resMM
                                .marca_modelo.idMarca,
                            dataType: 'json',
                            success: function(res) {
                                let html =
                                    `<option value="">{{ _lang('Select One') }}</option>`;
                                res.map(r => {
                                    selected = '';
                                    if (resMM.marca_modelo.idModelo == r
                                        .idModelo) {
                                        selected = 'selected'
                                    }
                                    html +=
                                        `<option ${selected} value="${r.idModelo}">${r.modelo.modelo}</option>`;
                                })
                                result = res;

                                modelo.html(html);
                                // modelo.select2();

                            }

                        })



                    }

                })
			}
        }


            function limpiarItems() {
                let nro_interno = $('#nro_interno');
                 if (nro_interno.val() > 0) {
                    $.ajax({
                        url: "{{ url('vehiculo/utilizadas-pieza') }}" + "/" + nro_interno.val(),
                        dataType: 'json',
                        success: function(res) {
                            let selected =res.pieza_listas[0].seleccionados;
                            if (selected){
                                 selected =selected.split(',');
                                 for (var index in selected) {
                                    $('#item_id').find('option[value="' + selected[index] + '"]:not(:selected)').prop("disabled", true);
                                }
                            }
                           
                        }
                    });

                }
            }

/*            $('#myForm').on('submit', function(event) {
              event.preventDefault(); // Prevent the default action
                $("#printsinQR").empty();
				$('#myForm').find(".print-error-msg").find("ul").html('');
                $('#myForm').find(".print-error-msg").css('display','none');
              //const formData = $(this).serialize(); // Extract and serialize form data
               var formData = new FormData(this);
               $(':input[type="submit"]').prop('disabled', true);
              $.ajax({
                  url: $(this).attr("action"), // Provide the URL to the forms backend
                  type: 'POST',
                  data: formData,
                 enctype: 'multipart/form-data',
                  dataType: 'json',
                 cache: false,
                 contentType: false,
                 processData: false,
                  success: function(response) {
                      //console.log('Form submitted successfully');
                         //console.log(response.data.id);
                
						 

            if(response.result == "success"){
                                    if (response.data.id!=""){
                                        $( "#printsinQR" ).load("{{ url('product/printsin-qr') }}/"+response.data.id);
                                        $('#myModal').modal({show:true});
                                        }
                                        nro_interno.trigger('change');
                            }else{
                                    //$('#myForm').find(".print-error-msg").find("ul").html('');
                                    $('#myForm').find(".print-error-msg").css('display','block');
                                    $.each( response.message, function( key, value ) {
                                        $('#myForm').find(".print-error-msg").find("ul").append('<li>'+value+'</li>');
                                    });
                                
                            }

                            setTimeout(function(){  $(':input[type="submit"]').prop('disabled', false); }, 5000); // Habilitar después de 5 segundos
                  },
                  error: function() {
                      alert('Error submitting form');
                  }
              });
              });*/


$('#myForm').on('submit', function(e) {			  
    e.preventDefault();
    $("#printsinQR").empty();
	$('#myForm').find(".print-error-msg").find("ul").html('');
    $('#myForm').find(".print-error-msg").css('display','none');
    $(':input[type="submit"]').prop('disabled', true);
	
	let masterFormData = new FormData(this);
	  //let formData = new FormData(this);
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
                                    if (response.data.id!=""){
                                        $( "#printsinQR" ).load("{{ url('product/printsin-qr') }}/"+response.data.id);
                                        $('#myModal').modal({show:true});
                                        }
                                        nro_interno.trigger('change');
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


$("#productLink").click(function(e){
  e.preventDefault();
	$('#itemCreateModal').modal({show:true});
	return false;
  });			  
        
$('#miFormulario').submit(function(e) {
        e.preventDefault();
         
        var url = $(this).attr("action");
        let formData = new FormData(this);
		let select_display = $('#item_name').val();
    
        $.ajax({
                type:'POST',
                url: url,
                data: formData,
                contentType: false,
                processData: false,
                success: (json) => {
				if(json['result'] == "success"){
						var select_value = json['data'];
						var newOption = new Option(select_display, select_value, true, true);
						$('#item_id').append(newOption).trigger('change');
						$('#itemCreateModal').modal('hide');
					
				}else{
					$('#miFormulario').find(".print-error-msg").find("ul").html('');
                    $('#miFormulario').find(".print-error-msg").css('display','block');
                    $.each( json['message'], function( key, value ) {
					//	console.log(value);
                        $('#miFormulario').find(".print-error-msg").find("ul").append('<li>'+value+'</li>');
                    });
				  }
				},
                error: function(response){
                    $('#ajax-form').find(".print-error-msg").find("ul").html('');
                    $('#ajax-form').find(".print-error-msg").css('display','block');
                    $.each( response.responseJSON.errors, function( key, value ) {
                        $('#ajax-form').find(".print-error-msg").find("ul").append('<li>'+value+'</li>');
                    });
                }
           });
        
    });

  $('#itemCreateModal').on('hidden.bs.modal', function () {
    // Limpiar la validación al cerrar el modal
    $('#miFormulario').parsley().reset();
    // Limpiar los campos del formulario
    $('#item_name').val('');
  });
  
  
    // Procesar el formulario del modal
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

            // CORRECCIÓN: Se usa 'response' en lugar de 'json'
            if (response['result'] == "success") {
                // Limpiar alertas
                $(current_modal).find(".alert-secondary").html(response['message']).removeClass('d-none');
                $(current_modal).find(".alert-danger").addClass('d-none');
                
                // CORRECCIÓN: Usar '#idMarca' para que coincida con tu HTML
                var nuevaOpcion = new Option(response['marca'], response['id'], true, true);
                $('#marca').append(nuevaOpcion).trigger('change');
                
                // Resetear formulario y cerrar modal
                elem[0].reset(); 
                $(current_modal).modal('hide');
            } else {
                // Limpiar errores previos en los contenedores de alerta antes de iterar
                $("#main_modal .alert-danger").html("");
                $(current_modal).find(".alert-danger").html("");

                if (Array.isArray(response['message'])) {
                    if (typeof reload !== 'undefined' && reload != false) {
                        // Main Modal
                        jQuery.each(response['message'], function(i, val) {
                           $("#main_modal .alert-danger").append("<p class='m-0'>" + val + "</p>");
                        });
                        $("#main_modal .alert-secondary").addClass('d-none');
                        $("#main_modal .alert-danger").removeClass('d-none');
                    } else {
                        // Secondary Modal
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



 // Procesar el formulario del modal
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

            // CORRECCIÓN: Se usa 'response' en lugar de 'json'
            if (response['result'] == "success") {
                // Limpiar alertas
                $(current_modal).find(".alert-secondary").html(response['message']).removeClass('d-none');
                $(current_modal).find(".alert-danger").addClass('d-none');
                
                // CORRECCIÓN: Usar '#idMarca' para que coincida con tu HTML
                //var nuevaOpcion = new Option(response['modelo'], response['id'], true, true);
                //$('#modelo').append(nuevaOpcion).trigger('change');
                 marca.trigger('change'); 
                // Resetear formulario y cerrar modal
                elem[0].reset(); 
                $(current_modal).modal('hide');
            } else {
                // Limpiar errores previos en los contenedores de alerta antes de iterar
                $("#main_modal .alert-danger").html("");
                $(current_modal).find(".alert-danger").html("");

                if (Array.isArray(response['message'])) {
                    if (typeof reload !== 'undefined' && reload != false) {
                        // Main Modal
                        jQuery.each(response['message'], function(i, val) {
                           $("#main_modal .alert-danger").append("<p class='m-0'>" + val + "</p>");
                        });
                        $("#main_modal .alert-secondary").addClass('d-none');
                        $("#main_modal .alert-danger").removeClass('d-none');
                    } else {
                        // Secondary Modal
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


 $('#select-modelos').select2({
              //  theme: 'bootstrap4',
                placeholder: "Buscar modelos...",
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('modelos.buscar.ajax') }}",
                    dataType: 'json',
                    delay: 300, // Espera para no saturar el servidor
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });

     })
    </script>
@endsection
