@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-12">
	<div class="card panel-default">
	<span class="d-none panel-title">{{ _lang('Add Staff') }}</span>

	<div class="card-body">
		<form method="post" class="validate" autocomplete="off" action="{{ url('staffs') }}" enctype="multipart/form-data">
			<div class="row">
				<div class="col-md-6">
					{{ csrf_field() }}
					
					<div class="col-md-12">
					  <div class="form-group">
						<label class="control-label">{{ _lang('Name') }}</label>						
						<input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
					  </div>
					</div>

					<div class="col-md-12">
					  <div class="form-group">
						<label class="control-label">{{ _lang('Email') }}</label>						
						<input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
					  </div>
					</div>

					

					<div class="col-md-12">
					  <div class="form-group">
						<label class="control-label">{{ _lang('Password') }}</label>						
						<input type="password" class="form-control" name="password" value="{{ old('password') }}" required>
					  </div>
					</div>
					
					<div class="col-md-12">
					 <div class="form-group">
						<label class="control-label">{{ _lang('Confirm Password') }}</label>						
						<input type="password" class="form-control" name="password_confirmation" required>
					 </div>
					</div>

					<div class="col-md-12">
						<div class="form-group">
						  <label class="control-label">{{ _lang('Teléfono') }}</label>						
						  <input type="phone" class="form-control" name="phone_number" value="{{ old('phone_number') }}" required>
						</div>
					  </div>

					
					<div class="col-md-12">
					  <div class="form-group">
						<label class="control-label">{{ _lang('Status') }}</label>						
						<select class="form-control select2 auto-select" data-selected="{{ old('user_type') }}" id="status" name="status" required>
						  <option value="1">{{ _lang('Active') }}</option>
						  <option value="0">{{ _lang('Inactive') }}</option>
						</select>
					  </div>
					</div>

					<div class="col-md-12">
					  	<div class="form-group">
							<label class="control-label">{{ _lang('Role') }}</label>						
							<select class="form-control select2" id="role_id" name="role_id">
							  <option value="">{{ _lang('Select Role') }}</option>
							  {{ create_option('staff_roles','id','name', old('role_id')) }}
							</select>
					  	</div>
					</div>

					<div id="contLugares" class="col-md-12">
						<div class="form-group">
							<label class="control-label">{{ _lang('Ubicación') }}</label>
							<select class="form-control " id="location" name="location" >
								<option value="">{{ _lang('Select One') }}</option>
								@forelse($lugar_entregas as $res)
									<option value="{{$res->id}}">{{$res->nombre}}</option>
								@empty
								@endforelse
							</select>
						</div>
					</div>
					<div id="contCompany" class="col-md-12">
						<div class="form-group">
							<label class="control-label">{{ _lang('Company') }}</label>


							<select id="company" name="company"  class="form-control">
								<option value="">Seleccionar</option>
								@foreach($cias as $cia)
									@if($cia->business_name == 'Pentacar' || $cia->business_name == 'Paternal')
										<option
												{{--{{  auth()->user()->company_id == $cia->id ?--}}
												{{--'selected' : ''}}--}}
												value="{{$cia->id}}">{{$cia->business_name}}</option>
									@endif
								@endforeach
							</select>
						</div>
					</div>
					
					<div class="form-group">
					  <div class="col-md-12">
						<button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
						<button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
					  </div>
					</div>
					
				</div>
				
				<div class="col-md-6">		
					<div class="col-md-12">
					 <div class="form-group">
						<label class="control-label">{{ _lang('Profile Picture') }} ( 300 X 300 {{ _lang('for better view') }} )</label>
						<input type="file" class="dropify" name="profile_picture" data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG" data-default-file="">
					 </div>
					</div>
				</div>	
			</div>		
		</form>
	  
	</div>
  </div>
 </div>
</div>
{{--{{dd(json_encode(get_table('staff_roles')))}}--}}

	@php
		$roles = get_table('staff_roles')
			@endphp
@endsection

@section('js-script')
	<script>
		const contLu = $('#contLugares');
		const contCompany = $('#contCompany');
        // contLu.hide();
        // contCompany.hide();
		const rol = $('#role_id');
        let objRoles = "{{ json_encode(get_table("staff_roles")) }}";
        let arrRoles = [];
        @forelse ($roles as $r)
            arrRoles.push(JSON.parse('{!!json_encode($r)!!}'));
			@empty


		@endforelse

		console.log(arrRoles)
        rol.change(function(e) {
            let find = arrRoles.find( r => r.id == $(this).val() && (r.name == 'Receptor' || r.name == 'Operario' ));
            let operGerent = arrRoles.find( r => r.id == $(this).val() && (r.name == 'Gerente de operarios' || r.name=='Operario'|| r.name == 'Administrativo de Desarme' ));
            // contLu.hide();
            // contCompany.hide();
            $("#location").val('');
            if(find != undefined) {
                // contLu.show();
			}
			if(operGerent != undefined) {
                // contCompany.show();
			}
            //console.log(filt);
		})
	</script>
@endsection


