@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <span class="panel-title d-none">{{ _lang('Add Product') }}</span>

                <div class="card-body">
                    <form method="post" class="validate" id="myForm" name="myForm" autocomplete="off" action="{{ url('products') }}"
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
                                    {{-- <input type="text" class="form-control"  name="nro_interno" value="{{ old('nro_interno')}}"
						> --}}
                                    <select id="nro_interno" name="nro_interno" class="form-control select2" required>
                                        <option value="">Seleccionar</option>
                                        {{--create_option('cars', 'id', 'id', old('nro_interno')) --}}
                                         @foreach ($cars as $interno_row)
                                                        <option value="{{ $interno_row->id }}">{{ nroInternoAlias($interno_row->company_id,$interno_row->tipo_vehiculo,$interno_row->id) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">

                                    <label class="control-label">{{ _lang('Marca') }}</label>
                                    <select class="form-control select2" data-value="id" data-display="marca" disabled
                                        data-table="marcas" data-where="" id="marca">
                                        <option value=""></option>
                                        {{ create_option('marcas', 'id', 'marca', old('marca')) }}
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Modelo') }}</label>
                                    <select class="form-control select2" id="modelo" disabled>
                                        <option value=""></option>

                                    </select>
                                    <input type="hidden" name="marca_modelo" id="marca_modelo">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Nº oblea') }}</label>
                                    <input type="text" class="form-control" name="nro_oblea"
                                        value="{{ old('nro_oblea') }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
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
                            <div id="contNroMotor" class="col-md-12 d-none">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Nº motor') }}</label>
                                    <input type="text" class="form-control" id="nro_motor" name="nro_motor"
                                        value="{{ old('nro_motor') }}">
                                </div>
                            </div>


                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Description') }}</label>
                                    <textarea class="form-control" name="description">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <input type="hidden" name="idDeposito" id="idDeposito" value="{{ Auth::user()->location }}">
                            <input type="hidden" name="carga_rapida" id="carga_rapida" value="1">



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

    <div class="row">
        <div class="col-12">

            <div class="card mt-2">
                <div class="card-body">
                        <table id="table-data-product" class="table-bordered" style="table-layout: auto;">  

                        <thead>
                            <tr>
                                <th style="width: 100px;">ID de producto</th>
                                <th style="width: 100px;">{{ _lang('Nro Interno') }}</th>
                                <th style="width: 200px;">{{ _lang('Product') }}</th>
                                <th style="width: 300px;">{{ _lang('Marca y modelo') }}</th>
                                <th style="width: 300px;">{{ _lang('nº oblea') }}</th>
                                <th style="width: 100px;">{{ _lang('nº motor') }}</th>
                                <th class="text-right" style="width: 100px;">{{ _lang('Deposito') }}</th>
                                <th class="text-right" style="width: 100px;">{{ _lang('Ubicacion') }}</th>
                                <th class="text-center" style="width: 200px;">{{ _lang('Descripcion') }}</th>
                                <th class="text-center" style="width: 100px;">{{ _lang('Fecha') }}</th>
                                <th class="text-center" style="width: 200px;">{{ _lang('Usuario') }}</th>
                                <th>{{ _lang('Accciones disponibles') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
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
                    select.find("option").prop("disabled", false);
                    select.prop('selectedIndex', 0);
                    limpiarItems();
                    select.select2();
                 
                    // Llamada AJAX para verificar pieza y obtener nro_motor
                if ($('#item_id').val() != '' && $('#nro_interno').val() > 0) {
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
                                alert('ATENCION: El vehiculo ya posee esta pieza registrada');
                                 $(':input[type="submit"]').prop('disabled', true);  
                            }else{
                                   MostrarModelo();
                            }
                        }
                    });

                } else{
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
                                console.log(res);
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
                                alert(
                                    'ATENCION: El vehiculo ya posee esta pieza registrada')
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

/*
 $.ajax({
                    url: "{{ url('products/item/') }}/" + item_id.val(),
                    dataType: 'json',
                    success: function(res) {
                        let contNroMotor = $('#contNroMotor');
                        if (res && (res.item.item_name == 'Motor Semiarmado Con Accesorios' ||
                                res.item.item_name == 'Motor Semiarmado Sin Acesorios')) {
                            contNroMotor.removeClass('d-none')
                            // Llamada AJAX para actualizar nro_motor cuando el item es de tipo motor
                            // Llamada AJAX para verificar pieza y obtener nro_motor

                        } else {
                            contNroMotor.addClass('d-none')
                            $('#nro_motor').val('');
                        }
                        result = res;



                    }

                });

*/



            })

               
               
               
                
                
            if ("{{ old('nro_interno') }}") {
                setTimeout(function() {
                    nro_interno.val("{{ old('nro_interno') }}").trigger('change');
                }, 500);
            }


            var table = $('#table-data-product').DataTable({
					processing: true,
					serverSide: true,
					ajax: "{{ url('products/carga-rapida') }}",
					 ordering: false,
			columns: [
            {data: 'producto_id', name: 'id'},
            {data: 'nro_interno', name: 'nro_interno'},
            {data: 'item_name', name: 'item_name'},
            {data: 'marcamodelo', name: 'marcamodelo'},
            {data: 'nro_oblea', name: 'nro_oblea'},
            {data: 'nro_motor', name: 'nro_motor'},
            {data: 'deposito', name: 'deposito'},
            {data: 'ubicacion', name: 'ubicacion'},
			{data: 'description', name: 'description'},
			
            {data: 'fecha_creacion', name: 'fecha_creacion'},
            {data: 'usuario', name: 'usuario'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
			],
			   columnDefs: [
				{ targets: [0, 2],  'className' : 'item_id'},
			]
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
                            // Llamada AJAX para actualizar nro_motor cuando el item es de tipo motor
                            // Llamada AJAX para verificar pieza y obtener nro_motor

                        } else {
                            contNroMotor.addClass('d-none')
                            $('#nro_motor').val('');
                        }
                        result = res;



                    }

                });
            }

            function MostrarModelo() {
                
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

            $('#myForm').on('submit', function(event) {
              event.preventDefault(); // Prevent the default action
                 $("#printsinQR").empty();
              const formData = $(this).serialize(); // Extract and serialize form data
               $(':input[type="submit"]').prop('disabled', true);
              $.ajax({
                  url: $(this).attr("action"), // Provide the URL to the forms backend
                  type: 'POST',
                  data: formData,
                  dataType: 'json',
                  success: function(response) {
                    if(response.result == "success"){
                        if (response.data.id!=""){
                            $( "#printsinQR" ).load("{{ url('product/print-qr') }}/"+response.data.id);
                            $('#myModal').modal({show:true});
                             }
                            nro_interno.trigger('change');
				}else{
                        $('#myForm').find(".print-error-msg").find("ul").html('');
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
              });

            

      //$('#myForm').on('submit', function(e){
        //e.preventDefault();
        /*  $( "#printsinQR" ).load( "http://axel.test/paternal_motor/product/printsin-qr/1324160" );
          //$( "#print-qr" ).load( "http://axel.test/paternal_motor/product/print-qr/1324160" );

         //$('.modal-body').load('http://axel.test/paternal_motor/product/print-qr/1324160',function(){
                             $('#myModal').modal({show:true});
//        });
*/
        //$('#myForm').submit();
      //return false;
    //});

  })
       
    </script>
    @if (!empty($contenidoEtiqueta))
            <script type="text/javascript">
                $(document).ready(function() {
                //window.onload = function() {
                    // Obtener el contenido HTML desde la variable de Blade
                    var contenidoEtiqueta = `{!! $contenidoEtiqueta !!}`;

                    // Crear una nueva ventana y escribir el contenido HTML
                    var nuevaVentana = window.open('', '_blank', 'width=800,height=600');
                    nuevaVentana.document.write(contenidoEtiqueta);
                    //nuevaVentana.document.close();
                });
            </script>
        @endif
@endsection
