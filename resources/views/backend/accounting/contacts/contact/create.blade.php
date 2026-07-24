@extends('layouts.app')

@section('content')
<div class="row">
<div class="col-12">
<form method="post" class="validate" autocomplete="off" action="{{ url('contacts') }}" enctype="multipart/form-data">
	<div class="row">
		<div class="col-md-8">
		<div class="card">
		<span class="d-none panel-title">{{ _lang('Add New Contact') }}</span>

		<div class="card-body">
			{{ csrf_field() }}

			<div class="row">
				<div class="col-md-6">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Profile Type') }}</label>						
					<select class="form-control select2" name="profile_type" required>
						<option value="Company" {{ old('profile_type')=="Company" ? "selected" : "" }}>{{ _lang('Company') }}</option>
						<option value="Individual" {{ old('profile_type')=="Individual" ? "selected" : "" }}>{{ _lang('Individual') }}</option>
					</select>
				  </div>
				</div>

				<div class="col-md-6">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Company Name') }}</label>						
					<input type="text" class="form-control" name="company_name" value="{{ old('company_name') }}">
				  </div>
				</div>

				<div class="col-md-6">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Contact Name') }}</label>						
					<input type="text" class="form-control" name="contact_name" value="{{ old('contact_name') }}" required>
				  </div>
				</div>

				<div class="col-md-6">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Contact Email') }}</label>						
					<input type="text" class="form-control" name="contact_email" value="{{ old('contact_email') }}" >
				  </div>
				</div>
				
				<div class="col-md-6">
				  <div class="form-group">
					<label class="control-label">{{ _lang('CUIT - DNI') }}</label>						
					<input type="text" class="form-control" required name="vat_id" value="{{ old('vat_id') }}">
				  </div>
				</div>

				{{--<div class="col-md-6">--}}
				  {{--<div class="form-group">--}}
					{{--<label class="control-label">{{ _lang('Reg No') }}</label>						--}}
					{{--<input type="text" class="form-control" name="reg_no" value="{{ old('reg_no') }}">--}}
				  {{--</div>--}}
				{{--</div>--}}

				<div class="col-md-6">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Contact Phone') }}</label>						
					<input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone') }}">
				  </div>
				</div>

				{{--<div class="col-md-6">--}}
				  {{--<div class="form-group">--}}
					{{--<label class="control-label">{{ _lang('Country') }}</label>						--}}
					{{--<select class="form-control select2" name="country">--}}
					    {{--<option value="">{{ _lang('Select Country') }}</option>--}}
						{{--{{ get_country_list( old('country') ) }}--}}
					{{--</select>--}}
				  {{--</div>--}}
				{{--</div>--}}
				
				<div class="col-md-6 d-none">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Currency') }}</label>						
					<select class="form-control select2 " data-selected="{{ get_company_option('base_currency')
					}}" name="currency" id="currency" required>
					   <option value="">{{ _lang('Select Currency') }}</option>
					   {{ get_currency_list('ARS') }}
					</select>
				  </div>
				</div>
				
				<div class="col-md-6">
				  <div class="form-group">
					<a href="{{ route('contact_groups.create') }}" data-reload="false" data-title="{{ _lang('Add Contact Group') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
					<label class="control-label">{{ _lang('Group') }}</label>						
					<select class="form-control select2-ajax" data-value="id" data-display="name" data-table="contact_groups" data-where="1" name="group_id" required>
						<option value="">{{ _lang('- Select Group -') }}</option>
						{{ create_option("contact_groups","id",'name',old('group_id',1)) }}
					</select>
				 </div>
				</div>

				<div class="col-md-4">
				  <div class="form-group">
					<label class="control-label">{{ _lang('City') }}</label>						
					<input type="text" class="form-control" name="city" value="{{ old('city') }}">
				  </div>
				</div>

				<div class="col-md-4">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Provincia') }}</label>
					<input type="text" class="form-control" name="state" value="{{ old('state') }}">
				  </div>
				</div>

				<div class="col-md-4">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Zip') }}</label>						
					<input type="text" class="form-control" name="zip" value="{{ old('zip') }}">
				  </div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
					  <label class="control-label">{{ _lang('Estado de Iva') }}</label>						
					  {{-- <input type="text" class="form-control" name="zip" value="{{ old('zip') }}"> --}}
					  <select name="estadoIva" class="form-control" id="estadoIva">
						<option value="">{{ _lang('Selecciona el Estado de Iva') }}</option>
						@foreach ($estadosIva as $iva )
							<option {{ (old('zip') == $iva) ? selected : ''   }} value="{{ $iva }}">{{ $iva }}</option>
						@endforeach
					  </select>
					</div>
				  </div>

				<div class="col-md-6">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Address') }}</label>						
					<textarea class="form-control" name="address">{{ old('address') }}</textarea>
				  </div>
				</div>
				
				<div class="col-md-6">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Remarks') }}</label>						
					<textarea class="form-control" name="remarks">{{ old('remarks') }}</textarea>
				  </div>
				</div>

				<div class="col-md-12">
                                    <h4 class="mt-4 mb-3">{{ _lang('Datos de envío') }}</h4>
                                    <hr>
                                </div>

                                <div class="col-md-4"><div class="form-group"><label class="control-label">{{ _lang('Nombre') }}</label><input type="text" class="form-control" name="nombre_env" value="{{ old('nombre_env') }}" ></div></div>
                                <div class="col-md-4"><div class="form-group"><label class="control-label">{{ _lang('Apellidos') }}</label><input type="text" class="form-control" name="apellidos_env" value="{{ old('apellidos_env') }}"></div></div>
                                <div class="col-md-4"><div class="form-group"><label class="control-label">{{ _lang('DNI Envío') }}</label><input type="text" class="form-control" name="dni_env" value="{{ old('dni_env') }}" ></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="control-label">{{ _lang('Calle Envío') }}</label><input type="text" class="form-control" name="calle_env" value="{{ old('calle_env') }}" ></div></div>
                                <div class="col-md-2"><div class="form-group"><label class="control-label">{{ _lang('Número') }}</label><input type="text" class="form-control" name="numero_env" value="{{ old('numero_env') }}"></div></div>
                                <div class="col-md-2"><div class="form-group"><label class="control-label">{{ _lang('Piso') }}</label><input type="text" class="form-control" name="piso_env" value="{{ old('piso_env') }}"></div></div>
                                <div class="col-md-2"><div class="form-group"><label class="control-label">{{ _lang('Depto') }}</label><input type="text" class="form-control" name="depto_env" value="{{ old('depto_env') }}"></div></div>
                                <div class="col-md-4"><div class="form-group"><label class="control-label">{{ _lang('CP Envío') }}</label><input type="text" class="form-control" name="cp_env" value="{{ old('cp_env') }}"></div></div>
                                <div class="col-md-4"><div class="form-group"><label class="control-label">{{ _lang('Localidad') }}</label><input type="text" class="form-control" name="localidad_env" value="{{ old('localidad_env') }}"></div></div>
                                <div class="col-md-4"><div class="form-group"><label class="control-label">{{ _lang('Provincia') }}</label><input type="text" class="form-control" name="pcia_env" value="{{ old('pcia_env') }}"></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="control-label">{{ _lang('Teléfono Envío') }}</label><input type="text" class="form-control" name="tel_env" value="{{ old('tel_env') }}"></div></div>

				{{--<div class="col-md-12">--}}
				  {{--<div class="form-group">--}}
					{{--<label class="control-label">{{ _lang('Facebook') }}</label>						--}}
					{{--<input type="text" class="form-control" name="facebook" value="{{ old('facebook') }}">--}}
				  {{--</div>--}}
				{{--</div>--}}

				{{--<div class="col-md-12">--}}
				  {{--<div class="form-group">--}}
					{{--<label class="control-label">{{ _lang('Twitter') }}</label>						--}}
					{{--<input type="text" class="form-control" name="twitter" value="{{ old('twitter') }}">--}}
				  {{--</div>--}}
				{{--</div>--}}

				{{--<div class="col-md-12">--}}
				  {{--<div class="form-group">--}}
					{{--<label class="control-label">{{ _lang('Linkedin') }}</label>						--}}
					{{--<input type="text" class="form-control" name="linkedin" value="{{ old('linkedin') }}">--}}
				  {{--</div>--}}
				{{--</div>--}}

				<div class="col-md-12">
				  <div class="form-group">
					<button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
					@can('contacts.create')
					<button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
					@endcan
				  </div>
				</div>
			</div>
		</div>
	  </div>
	 </div>
	 
	 <div class="col-md-4">

	 	{{--<div class="card">--}}
			{{--<h5 class="card-header bg-dark text-white mt-0 text-center">{{ _lang('Client Portal Access') }}</h5>--}}
			{{--<div class="card-body">--}}
			    {{----}}
			    {{--<div class="alert alert-info">--}}
			   	 	{{--<span>{{ _lang('If Client have already an account associated with Contact Email then client can login to his account using existing login details') }}.</span>--}}
			   	{{--</div> --}}
			   	{{----}}
			   	{{--<div class="alert alert-info">	--}}
			    	{{--<span>{{ _lang('If Client do not have any previous account associated with Contact Email then client need to create a new account using that contact email') }}.</span>--}}
			    {{--</div>--}}
			{{--</div>--}}
		{{--</div>--}}

		<div class="card">
			<div class="card-body">
			   <div class="col-md-12">
				  <div class="form-group">
					<label class="control-label">{{ _lang('Contact Image') }} 300px X 300px</label>						
					<input type="file" class="form-control dropify" name="contact_image"  data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG">
				  </div>
				</div>
			</div>
		</div>

	  </div>
    </div>
 </form>
</div>
</div>
@endsection


