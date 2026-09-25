<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('lugarentrega.store') }}"
	  enctype="multipart/form-data">
	{{ csrf_field() }}
	
    <div class="col-md-8">
		<div class="form-group">
			<label class="control-label">{{ _lang('Deposito') }}</label>
			<input type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" required>
		</div>
	</div>

	<div class="col-md-4">					
						<div class="form-group">
                                <label for="activo">Activo <span class="text-danger"></span></label>
                                <select class="form-control" name="activo" id="activo" required>
                                    <option value="Si" {{ old('activo' ?? '') == 'Si' ? 'selected' : '' }}>Activo</option>
                                    <option value="No" {{ old('activo' ?? '') == 'No' ? 'selected' : '' }}>Desactivo</option>
                                </select>
						</div>
    </div>									



	
	<div class="col-md-12">
	    <div class="form-group">
	        <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
		    <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
	    </div>
	</div>
</form>
