<form method="post" class="validate ajax-submit" autocomplete="off" action="{{action('ProductController@update', $id)}}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
   
    <div class="col-12">
		<div class="row">
			
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Product Name') }}</label>						
					<input type="text" class="form-control" name="item_name" value="{{ $product->item->item_name }}" required>
				</div>
			</div>
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Año') }}</label>
					<input type="text" class="form-control" maxlength="4" name="anio" value="{{ old
						('anio', $product->anio) }}"
						   required>
				</div>
			</div>

			<div class="col-md">
				<label for="estado_prod">Estado</label>
				<div class="form-group">
					<select class="form-control" name="estado_prod" id="estado_prod">
						<option {{ $product->estado ==  'optimo' ? 'selected' : ''}} value="optimo">Óptimo</option>
						<option {{ $product->estado ==  'no funciona' ? 'selected' : ''}} value="no funciona">No funciona</option>
						<option {{ $product->estado ==  'descompuesto' ? 'selected' : ''}} value="descompuesto">Descompuesto</option>
					</select>
				</div>
			</div>

			{{-- <div class="col-md-6">
				<div class="form-group">
					<a href="{{ route('vehiculo.create') }}" data-reload="false" data-title="{{ _lang('Add Supplier')}}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>

					<label class="control-label">{{ _lang('Vehiculo') }}</label>
					<select class="form-control select2-ajax" data-value="cars.id" data-display="marcas.marca"
							data-display2="modelos.modelo" data-display3="siniestro"
							data-table="cars"
							data-where="8" name="car_id" id="car_id">
						<option value="">{{ _lang('- Select Car -') }}</option>

						<option selected value="{{$auto->id ?? ''}}">{{ ($auto->marca_modelo->marca->marca ?? '').' '.
						($auto->marca_modelo->modelo->modelo ?? '') .' '. $auto->siniestro}}</option>

					</select>
				</div>
			</div>

			<input type="hidden" name="marca_modelo" id="marca_modelo" value="{{$auto->marca_modelo->id}}">





			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Description') }}</label>						
					<textarea class="form-control" name="description">{{ $product->description }}</textarea>
				</div>
			</div> --}}

			<div class="col-md-12">
			  <div class="form-group">
				<button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>