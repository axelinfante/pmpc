<form method="post" class="validate ajax-submit" autocomplete="off" action="{{action('ProductController@update_item', $id)}}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
   
    <div class="col-12">
		<div class="row">
			
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Product Name') }}</label>						
					<input type="text" class="form-control" name="item_name" value="{{ $item->item_name }}" required>
				</div>
			</div>
			
					<div class="col-lg-6 mb-3">	
						<div class="form-group">
									<label for="allcar">Predefinido <span class="text-danger"></span></label>
									<select class="form-control" name="allcar" id="allcar" required>
										<option value="1" {{ old('allcar', $item->allCar ?? '') == '1' ? 'selected' : '' }} >Si</option>
										<option value="0" {{ old('allcar', $item->allCar ?? '0') == '0' ? 'selected' : '' }} >No</option>
									</select>
						</div>
					</div>
					
			<div class="col-lg-6 mb-3">					
						<div class="form-group">
                                <label for="activo">Activo <span class="text-danger"></span></label>
                                <select class="form-control" name="activo" id="activo" required>
                                    <option value="Si" {{ old('activo', $item->activo ?? '') == 'Si' ? 'selected' : '' }}>Activo</option>
                                    <option value="No" {{ old('activo', $item->activo ?? '') == 'No' ? 'selected' : '' }}>Desactivo</option>
                                </select>
						</div>
                    </div>		
			



			{{-- <div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Description') }}</label>						
					<textarea class="form-control" name="description">{{ $item->description }}</textarea>
				</div>
			</div>  --}}

			<div class="col-md-12">
			  <div class="form-group">
				<button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>