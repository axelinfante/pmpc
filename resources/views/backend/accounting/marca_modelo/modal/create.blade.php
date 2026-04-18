<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('marcamodelo.store') }}"
	  enctype="multipart/form-data">
	{{ csrf_field() }}
	
    <div class="col-md-12">
		<div class="form-group">
			<label class="control-label">{{ _lang('Marca') }}</label>
			<input type="text" class="form-control" name="marca" value="{{ old('marca') }}" required>
		</div>
	</div>

	<div class="col-md-12">
		<div class="form-group">
			<label class="control-label">{{ _lang('Modelo') }}</label>
			<textarea class="form-control" name="modelo">{{ old('modelo') }}</textarea>
		</div>
	</div>

	
	<div class="col-md-12">
	    <div class="form-group">
	        <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
		    <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
	    </div>
	</div>
</form>
