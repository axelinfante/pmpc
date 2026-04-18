<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('StaffController@update', $id) }}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
	
	<div class="col-12">
		<div class="row">
			<div class="col-md-12">
			 	<div class="form-group">
					<label class="control-label">{{ _lang('Name') }}</label>						
					<input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
			 	</div>
			</div>

			<div class="col-md-6">
			 	<div class="form-group">
					<label class="control-label">{{ _lang('Email') }}</label>						
					<input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
			 	</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">{{ _lang('Password') }}</label>						
					<input type="password" class="form-control" name="password">
				</div>
			</div>
			
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">{{ _lang('Confirm Password') }}</label>						
					<input type="password" class="form-control" name="password_confirmation">
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
				  <label class="control-label">{{ _lang('Teléfono') }}</label>						
				  <input type="phone" class="form-control" name="phone_number" value="{{ $user->phone_number }}" required>
				</div>
			  </div>
			
			
			<div class="col-md-6">
			  	<div class="form-group">
					<label class="control-label">{{ _lang('Status') }}</label>						
					<select class="form-control select2 auto-select" data-selected="{{ $user->status }}" id="status" name="status" required>
					  <option value="1">{{ _lang('Active') }}</option>
					  <option value="0">{{ _lang('Inactive') }}</option>
					</select>
			  	</div>
			</div>

			<div class="col-md-12">
			  	<div class="form-group">
					<label class="control-label">{{ _lang('Role') }}</label>						
					<select class="form-control select2" id="role_id" name="role_id" required>
					  <option value="">{{ _lang('Select Role') }}</option>
					  {{ create_option('staff_roles','id','name', $user->role_id) }}
					</select>
			  	</div>
			</div>
			<div id="contLugares" class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Ubicación') }}</label>
					<select class="form-control " id="location" name="location" >
						<option value="">{{ _lang('Select One') }}</option>
						{{ create_option('lugar_entregas','id','nombre', $user->location) }}
					</select>
				</div>
			</div>

			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Company') }}</label>


					<select id="company" name="company" required class="form-control">
						<option value="">Seleccionar</option>
						{{ create_option('companies','id','business_name',$user->company_id, ['business_name ='=>
						'Pentacar','|| business_name = '=>'Paternal' ])}}
						{{--@foreach($cias as $cia)--}}
							{{--@if($cia->business_name == 'Pentacar' || $cia->business_name == 'Paternal')--}}
								{{--<option--}}
										{{--{{  auth()->user()->company_id == $cia->id ?--}}
										{{--'selected' : ''}}--}}
										{{--value="{{$cia->id}}">{{$cia->business_name}}</option>--}}
							{{--@endif--}}
						{{--@endforeach--}}
					</select>
				</div>
			</div>
			
			<div class="col-md-12">
			 	<div class="form-group">
					<label class="control-label">{{ _lang('Profile Picture') }} ( 300 X 300 {{ _lang('for better view') }} )</label>	
					<input type="file" class="dropify" name="profile_picture" data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG" data-default-file="{{ $user->profile_picture != "" ? asset('public/uploads/profile/'.$user->profile_picture) : '' }}" >
			 	</div>
			</div>

						
			<div class="form-group">
			 	<div class="col-md-12">
					<button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
			  	</div>
			</div>
		</div>
	</div>
</form>
@php
	$roles = get_table('staff_roles')
@endphp

<script>
    const contLu = $('#contLugares');
    // contLu.hide();
    const rol = $('#role_id');
    let objRoles = "{{ json_encode(get_table("staff_roles")) }}";
    let arrRoles = [];
	@forelse ($roles as $r)
    arrRoles.push(JSON.parse('{!!json_encode($r)!!}'));
	@empty


	@endforelse
    // showHidenLocation(rol.val());
    console.log(arrRoles)
    rol.change(function(e) {
        // showHidenLocation($(this).val())
        //console.log(filt);
    })

	function showHidenLocation(val) {
        let find = arrRoles.find( r => r.id == val && (r.name == 'Receptor' || r.name == 'Operario'));
        contLu.hide();
		if($("#location").val().length < 1) {
			$("#location").val('');
		}
       
        if(find != undefined) {
            contLu.show();
        }
    }
</script>

