<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('updatedModelo') }}"
	  enctype="multipart/form-data">
	{{ csrf_field() }}
	
   
	<div class="col-md-12">
		<div class="form-group">
			<label class="control-label">{{ _lang('Modelo') }}</label>
			<textarea class="form-control" name="modelo">{{ old('modelo', $modelo->modelo) }}</textarea>
		</div>
		<input type="hidden" name="id" value="{{ $id }}">
	</div>

	
	<div class="col-md-12">
	    <div class="form-group">
	        
		    <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
	    </div>
	</div>
</form>
