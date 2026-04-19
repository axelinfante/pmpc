<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('aseguradora.store') }}"
	  enctype="multipart/form-data">
	{{ csrf_field() }}
	
    <div class="col-md-12">
		<div class="form-group">
			<label class="control-label">{{ _lang('Nombre') }}</label>
			<input type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" required>
		</div>
	</div>



	
	<div class="col-md-12">
	    <div class="form-group">
	        <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
		    <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
	    </div>
	</div>
</form>
