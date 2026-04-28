<form method="post" class="validate ajax-submit" autocomplete="off" action="{{route('item.store')}}" >
	{{ csrf_field()}}
    <div class="col-12">
		<div class="row">
			
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Product Name') }}</label>						
					<input type="text" class="form-control" name="item_name" value="" required>
				</div>
			</div>
			
					<div class="col-lg-6 mb-3">	
						<div class="form-group">
									<label for="allcar">Predefinido <span class="text-danger"></span></label>
									<select class="form-control" name="allcar" id="allcar" required>
										<option value="1" {{ old('allcar' ?? '') == '1' ? 'selected' : '' }} >Si</option>
										<option value="0" {{ old('allcar' ?? '0') == '0' ? 'selected' : '' }} >No</option>
									</select>
						</div>
					</div>
					
			<div class="col-lg-6 mb-3">					
						<div class="form-group">
                                <label for="activo">Activo <span class="text-danger"></span></label>
                                <select class="form-control" name="activo" id="activo" required>
                                    <option value="Si" {{ old('activo' ?? '') == 'Si' ? 'selected' : '' }}>Activo</option>
                                    <option value="No" {{ old('activo' ?? '') == 'No' ? 'selected' : '' }}>Desactivo</option>
                                </select>
						</div>
                    </div>		
				<input type="hidden" name="item_type" value="product">
			<div class="col-md-12">
			  <div class="form-group">
				<button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>