<form method="post" class="ajax-submit" autocomplete="off" action="{{route('tipocomprobante.store')}}" enctype="multipart/form-data">
{{ csrf_field() }}
	
	<div class="col-12">
		<div class="row">
			<div class="col-md-4">
			  <div class="form-group">
				<label class="control-label">Código</label>						
				<input type="text" class="form-control float-field" name="numero" value="{{ old('numero') }}" required>
			  </div>
			</div>

			<div class="col-md-8">
			  <div class="form-group">
				<label class="control-label">Desripción</label>						
				<input type="text" class="form-control" name="descripcion" value="{{ old('descripcion') }}">
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