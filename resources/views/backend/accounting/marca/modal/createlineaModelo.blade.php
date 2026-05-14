<form method="post" class="" id="marca_modelo" autocomplete="off" action="{{ route('marcas.storeMarcaModeloLinea') }}" >
	{{ csrf_field() }}
	<div class="col-md-12">
		<div class="form-group">
			<label class="control-label">{{ _lang('Marca') }}</label>
			<input type="text" class="form-control" name="marca_datos" value="{{ $marca->marca }}" readonly>
			<input type="hidden" name="idMarca" value="{{ $marca->id }}">
		</div>
	</div>
    <div class="col-md-12">
		<div class="form-group">
			<label class="control-label">{{ _lang('Modelo') }}</label>
			<input type="text" class="form-control" name="modelo" value="{{ old('modelo') }}" required>
		</div>
	</div>
	<div class="col-md-12">
	    <div class="form-group">
	        <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
		    <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
	    </div>
	</div>
</form>
