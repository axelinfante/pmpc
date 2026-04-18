<form method="post" class="validate ajax-submit" autocomplete="off" action="{{url('products')}}" enctype="multipart/form-data">
	{{ csrf_field() }}
	<div class="col-12">


			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<label class="control-label">{{ _lang('Product Name') }}</label>
						<input type="text" class="form-control" name="item_name" value="{{ old('item_name') }}" required>
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
				<div class="col-md-12">
					<div class="form-group">
						<label class="control-label">{{ _lang('Nº interno') }}</label>
						{{-- <input type="text" class="form-control"  name="nro_interno" value="{{ old('nro_interno')}}"
						> --}}

						<select id="nro_interno"  name="nro_interno"  class="form-control select2">
							<option value="">Seleccionar</option>
							{{ create_option('cars','id','id',old('nro_interno')) }}
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

				{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
				{{--<a href="{{ route('vehiculo.create') }}" data-reload="false" data-title="{{ _lang('Add Supplier') --}}
				{{--}}" --}}
				{{--class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>--}}

				{{--<label class="control-label">{{ _lang('Vehiculo') }}</label>--}}
				{{--<select class="form-control select2-ajax" data-value="cars.id" data-display="marcas.marca"--}}
				{{--data-display2="modelos.modelo" data-display3="siniestro"--}}
				{{--data-table="cars"--}}
				{{--data-where="8" name="car_id">--}}
				{{--<option value="">{{ _lang('- Select Car -') }}</option>--}}
				{{--</select>--}}
				{{--</div>--}}
				{{--</div>--}}

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

				{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
				{{--<a href="{{ route('product_units.create') }}" data-reload="false" data-title="{{ _lang('Add Product Unit') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>--}}
				{{--<label class="control-label">{{ _lang('Product Unit') }}</label>--}}
				{{--<select class="form-control select2-ajax" data-value="unit_name" data-display="unit_name" data-table="product_units" data-where="1" name="product_unit" required>--}}
				{{--<option value="">{{ _lang('- Select Product Unit -') }}</option>--}}
				{{--</select>--}}
				{{--</div>--}}
				{{--</div>--}}

				{{-- <div class="col-md-6">
					<div class="form-group">
						<a href="{{ route('categorias.create') }}" data-reload="false" data-title="{{ _lang
                                        ('Create Marca')
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
				{{--<div class="col-md-12 mb-3">--}}
					{{--<label class="control-label">{{ _lang('Fotos') }}</label>--}}
					{{--<input type="file" class="form-control" id="imagen[]" name="imagen[]" multiple="">--}}
				{{--</div>--}}


				<div class="col-md-12">
					<div class="form-group">
						<button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
						<button type="submit" id="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
					</div>
				</div>
			</div>


	</div>
</form>

<script>
    // $(document).ready(function () {
    //     let marca = $('#marca');
    //     let modelo = $('#modelo');
    //     let marca_modelo = $('#marca_modelo');
    //     let result;
    //     marca.change(function () {
    //         modelo.html(`<option value="">{{ _lang('Select One') }}</option>`);
    //         $.ajax({
    //             url: "{{route('modelosByMarca') .'/'}}"+marca.val(),
    //             dataType: 'json',
    //             success: function (res) {
    //                 console.log(res);
    //                 let html = `<option value="">{{ _lang('Select One') }}</option>`;
    //                 res.map(r => {
    //                     html += `<option value="${r.idModelo}">${r.modelo.modelo}</option>`;
    //                 })
    //                 result = res;

    //                 modelo.html(html);

    //             }

    //         })
    //     })

    //     //modelo.select2();
    //     modelo.change(function (){
    //         marca_modelo.val('');

    //         //console.log(result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val()));
    //         result = result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val());
    //         //console.log(result);

    //         marca_modelo.val(result.id);

    //     })
    // })


	$(document).ready(function () {
            let marca = $('#marca');
            let modelo = $('#modelo');
            let marca_modelo = $('#marca_modelo');
            let result;
			// let item_id = $('#item_id');
			let nro_interno = $('#nro_interno');

			nro_interno.change(function(e) {
				
				$.ajax({
                    url: "{{url('vehiculo/getMarcaModeloByCar/')}}/"+nro_interno.val(),
                    dataType: 'json',
                    success: function (resMM) {
						marca.val(resMM.marca_modelo.idMarca);
						
						$('#marca_modelo').val(resMM.marca_modelo.id);
						marca.select2()
                        $.ajax({
							url: "{{route('modelosByMarca') .'/'}}"+resMM.marca_modelo.idMarca,
							dataType: 'json',
							success: function (res) {
								console.log(res);
								let html = `<option value="">{{ _lang('Select One') }}</option>`;
								res.map(r => {
									selected = '';
									if(resMM.marca_modelo.idModelo == r.idModelo) {
										selected = 'selected'
									}
									html += `<option ${selected} value="${r.idModelo}">${r.modelo.modelo}</option>`;
								})
								result = res;

								modelo.html(html);
								// modelo.select2();

							}

						})

                        

                    }

                })
			})
			
			// item_id.change(function(e) {
			// 	$.ajax({
            //         url: "{{url('products/item/')}}/"+item_id.val(),
            //         dataType: 'json',
            //         success: function (res) {
            //             console.log(res);
			// 			let contNroMotor = $('#contNroMotor');
            //             if (res && (res.item.item_name == 'Motor Semiarmado Con Accesorios' ||  res.item.item_name == 'Motor Semiarmado Sin Acesorios')) {
			// 				contNroMotor.removeClass('d-none')
			// 			}else{
			// 				contNroMotor.addClass('d-none')
			// 				$('#nro_motor').val('');
			// 			}
            //             result = res;

                        

            //         }

            //     })
			// })
			
            marca.change(function () {
                modelo.html(`<option value="">{{ _lang('Select One') }}</option>`);
                $.ajax({
                    url: "{{route('modelosByMarca') .'/'}}"+marca.val(),
                    dataType: 'json',
                    success: function (res) {
                        console.log(res);
                        let html = `<option value="">{{ _lang('Select One') }}</option>`;
                        res.map(r => {
                            html += `<option value="${r.idModelo}">${r.modelo.modelo}</option>`;
                        })
                        result = res;

                        modelo.html(html);

                    }

                })
            })

            //modelo.select2();
            modelo.change(function (){
                marca_modelo.val('');

                //console.log(result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val()));
                result = result.find(r => r.idModelo == modelo.val() && r.idMarca == marca.val());
                //console.log(result);

                marca_modelo.val(result.id);

            })
        })
</script>