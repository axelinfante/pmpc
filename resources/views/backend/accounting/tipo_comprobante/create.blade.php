@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-md-8">
	<div class="card">
	<span class="d-none panel-title">Agregar tipo de comprobante</span>

	<div class="card-body">
	  <form method="post" class="validate" autocomplete="off" action="{{ url('tipocomprobante') }}" enctype="multipart/form-data">
		{{ csrf_field() }}
		
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
	  </form>
	</div>
  </div>
 </div>
</div>
@endsection


