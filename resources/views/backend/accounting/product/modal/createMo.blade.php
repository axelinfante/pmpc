<form method="post" data-select="service" class="validate ajax-submit" autocomplete="off" action="{{url('products')}}" enctype="multipart/form-data">
	{{ csrf_field() }}
	<div class="col-12">


			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<label class="control-label">{{ _lang('Nº interno') }}</label>
						{{-- <input type="text" class="form-control"  name="nro_interno" value="{{ old('nro_interno')}}"
						> --}}

						<select id="nro_interno"  name="nro_interno"  class="form-control select2">
							<option value="">Seleccionar</option>
							  @foreach ($cars as $interno_row)
                                                        <option value="{{ $interno_row->id }}">{{ nroInternoAlias($interno_row->company_id,$interno_row->tipo_vehiculo,$interno_row->id) }}</option>
                            @endforeach
						</select>
					</div>
				</div>
				  <div class="col-md-12">
                                <div class="form-group">
								<!--<a id="productLink" href="{{ route('item.create') }}" class="select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>-->
						<label class="control-label">{{ _lang('Producto en vehiculo') }}</label>
                                    <label class="control-label">Productos (Listado Predefinido)</label>
									<select id="item_id" required name="item_id" class="form-control" style="width: 100%;">
							</select>
                            <!--        <select id="item_id" name="item_id" required class="form-control select2">
                                        <option value="">Seleccionar</option>
                                         @forelse ($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                                            @empty
                                        @endforelse
                                    </select>-->
                                </div>
                            </div>

				<div class="col-md-12">
					<div class="form-group">
						<label class="control-label">{{ _lang('Company') }}</label>


						<select id="company" name="company" required class="form-control">
							<option value="">Seleccionar</option>
							@foreach($cias as $cia)
								@if($cia->business_name == 'Pentacar' || $cia->business_name == 'Paternal')
									<option
											{{--{{  auth()->user()->company_id == $cia->id ?--}}
											{{--'selected' : ''}}--}}
											value="{{$cia->id}}">{{$cia->business_name}}</option>
								@endif
							@endforeach
						</select>
					</div>
				</div>

				<div class="col-md-12">
					<div class="form-group">
						<label class="control-label">{{ _lang('Año') }}</label>
						<input type="text" class="form-control" maxlength="4" name="anio" value="{{ old
						('anio') }}"
						>
					</div>
				</div>
				<div class="col-md">
					<label for="estado_prod">Estado</label>
					<div class="form-group">
						<select class="form-control" name="estado_prod" id="estado_prod">
							<option value="optimo">Óptimo</option>
							<option value="no funciona">No funciona</option>
							<option value="descompuesto">Descompuesto</option>
						</select>
					</div>
				</div>
				

				<div class="col-md-6">
					<label for="car_or_stock">Tipo de producto</label>
					<select name="car_or_stock" class="form-control" id="car_or_stock" required>
						<option value="">Selecciona</option>
						<option selected value="2">Producto en stock</option>
						{{--<option value="1">Todos los vehiculos</option>--}}
					</select>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<a href="{{ route('marcamodelo.create') }}" data-reload="false" data-title="{{ _lang('Create Marca')
				}}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
						<label class="control-label">{{ _lang('Marca') }}</label>
						<select class="form-control select2" data-value="id" data-display="marca"
								data-table="marcas" data-where="" id="marca"  >
							<option value="">{{ _lang('Select One') }}</option>
							{{ create_option('marcas','id','marca',old('marca')) }}
						</select>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<label class="control-label">{{ _lang('Modelo') }}</label>
						<select class="form-control select2" id="modelo" >
							<option value="">{{ _lang('Select One') }}</option>

						</select>
						<input type="hidden" name="marca_modelo" id="marca_modelo">
					</div>
				</div>

				<div class="col-md-6 d-none">
					<div class="form-group">
						<label class="control-label">{{ _lang('Product Cost').' '.currency() }}</label>
						<input type="hidden" class="form-control" name="product_cost" value="0{{-- old('product_cost') --}}"
						>
					</div>
				</div>

				<div class="col-md-6 d-none">
					<div class="form-group">
						<label class="control-label">{{ _lang('Product Price') .' '.currency() }}</label>
						<input type="text" class="form-control" name="product_price" value="{{ old('product_price') }}" >
					</div>
				</div>


				<div class="col-md-12">
					<div class="form-group">
						<label class="control-label">{{ _lang('Description') }}</label>
						<textarea class="form-control" name="description">{{ old('description') }}</textarea>
					</div>
				</div>


				<div class="col-md-12">
					<div class="form-group">
						<button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
						<button type="submit" id="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
						<input type="hidden" class="form-control" name="item_name" value="">
					</div>
				</div>
			</div>


	</div>
</form>

<script>
 // Usamos una función autoejecutable para aislar las variables y correr al instante
(function () {
    // 1. Definimos las variables de inmediato
    var current_nro_interno = $('#nro_interno');
    var marca = $('#marca');
    var modelo = $('#modelo');
    var marca_modelo = $('#marca_modelo');
    var result;

    // 1. Destruimos cualquier inicialización previa que la plantilla haya hecho automáticamente
if ($('#item_id').hasClass("select2-hidden-accessible")) {
    $('#item_id').select2('destroy');
}


	$('#item_id').select2({
				placeholder: 'Buscar ...',
				allowClear: true,
				width: '100%',
				dropdownParent: $('#item_id').closest('.modal').length ? $('#item_id').closest('.modal') : $('.modal.show'), 
				  ajax: {
				url: "{{ route('products.buscar') }}",
				 data: function (params) {
					return {
						q: params.term,          
						nro_interno: current_nro_interno.val()
					};
				},
				delay: 250,
				dataType: 'json',
				processResults: function (data) {
			    return {
                    results: data
                };
			}
		},
});

/*$('#item_id').select2({
    placeholder: 'Escribe el modelo o producto...',
    minimumInputLength: 0,
    allowClear: true,
    width: '100%',
    dropdownParent: $('#item_id').closest('.modal').length ? $('#item_id').closest('.modal') : $('.modal.show'), 
    ajax: {
        url: "{{ route('products.buscar') }}",
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                q: params.term,          
                page: params.page || 1,  
                nro_interno: current_nro_interno.val()
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
        noResults: function() { return "No se encontraron resultados"; },
        searching: function() { return "Buscando..."; }
    }
});*/

    current_nro_interno.change(function(e) {
        let select = $("#item_id");
        $(':input[type="submit"]').prop('disabled', false);
        select.find("option").prop("disabled", false);
        
        
        select.val(null).trigger('change.select2');
        limpiarItems();

        $.ajax({
            url: "{{url('vehiculo/getMarcaModeloByCar/')}}/" + current_nro_interno.val(),
            dataType: 'json',
            success: function (resMM) {
                marca.val(resMM.marca_modelo.idMarca);
                $('#marca_modelo').val(resMM.marca_modelo.id);
                marca.select2();
                
                $.ajax({
                    url: "{{route('modelosByMarca') .'/'}}" + resMM.marca_modelo.idMarca,
                    dataType: 'json',
                    success: function (res) {
                        let html = `<option value="">{{ _lang('Select One') }}</option>`;
                        res.map(r => {
                            let selected = (resMM.marca_modelo.idModelo == r.idModelo) ? 'selected' : '';
                            html += `<option ${selected} value="${r.idModelo}">${r.modelo.modelo}</option>`;
                        });
                        result = res;
                        modelo.html(html);
                    }
                });
            }
        });
    });

    function limpiarItems() {
        return; 
    }

    
    marca.change(function () {
        modelo.html(`<option value="">{{ _lang('Select One') }}</option>`);
        $.ajax({
            url: "{{route('modelosByMarca') .'/'}}" + marca.val(),
            dataType: 'json',
            success: function (res) {
                let html = `<option value="">{{ _lang('Select One') }}</option>`;
                res.map(r => {
                    html += `<option value="${r.idModelo}">${r.modelo.modelo}</option>`;
                });
                result = res;
                modelo.html(html);
            }
        });
    });

    // 5. Evento change de modelo
    modelo.change(function (){
        marca_modelo.val('');
        let encontrado = result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val());
        if(encontrado) {
            marca_modelo.val(encontrado.id);
        }
    });

    $('#item_id').on('select2:select', function (e) {
        let datosProducto = e.params.data; 
        if (!datosProducto.id) return; 
    });

})(); // Cierre de la función autoejecutable
</script>
