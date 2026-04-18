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
					<label class="control-label">{{ _lang('Año') }}</label>
					<input type="text" class="form-control" maxlength="4" name="anio" value="{{ old
						('anio') }}"
					>
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
					{{--<a href="{{ route('vehiculo.create') }}" data-reload="false" data-title="{{ _lang('Add Supplier') --}}
					{{--}}" --}}
					{{--class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>--}}

					<label class="control-label">{{ _lang('Vehiculo') }}</label>
					<select class="form-control select2-ajax" data-value="cars.id" data-display="IF(cars.company_id = 1, CONCAT('PM',COALESCE(tipo_vehiculo,''),'-',LPAD(cars.id, 7, '0')), CONCAT('PC-',COALESCE(tipo_vehiculo,''),'-',LPAD(cars.id, 7, '0')))"
								data-display2="IF(cars.idMarca_modelo > 0, marcas.marca , 'Sin marca')" data-display3="IF(cars.idMarca_modelo > 0,modelos.modelo, 'Sin modelo')"
							data-table="cars"
							data-where="8" name="car_id" id="car_id">
						<option value="">{{ _lang('- Select Car -') }}</option>

							<option selected value="{{$auto->id}}">{{  nroInternoAlias($auto->company_id,$auto->tipo_vehiculo,$auto->id) .' '. ($auto->marca_modelo->marca->marca ?? '').' '.($auto->marca_modelo->modelo->modelo ?? '') .' '. $auto->siniestro}}</option>

					</select>
				</div>
			</div>

			<input type="hidden" name="marca_modelo" id="marca_modelo" value="{{$auto->marca_modelo->id ?? ''}}">

			<div class="col-md-6 d-none">
				<div class="form-group">
					<label class="control-label">{{ _lang('Product Cost').' '.currency() }}</label>
					<input type="hidden" class="form-control" name="product_cost" value="0{{-- old('product_cost') --}}"
						   required>
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


			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Description') }}</label>
					<textarea class="form-control" name="description">{{ old('description') }}</textarea>
				</div>
			</div>

			<div class="col-md-12">
				<div class="form-group">
					<button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
					<button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
				</div>
			</div>
		</div>
	</div>
</form>