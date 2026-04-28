<div class="card">
<div class="card-body">
    <table class="table table-bordered">
		<tr><td colspan="2" class="text-center"><img class="thumb-xl rounded" src="{{ MostrarImagen($user->profile_picture) }}"></td></tr>
		<tr><td>{{ _lang('Name') }}</td><td>{{ $user->name ?? '' }}</td></tr>
		<tr><td>{{ _lang('Email') }}</td><td>{{ $user->email ?? '' }}</td></tr>
		<tr><td>{{ _lang('User Type') }}</td><td>{{ ucwords($user->user_type ?? '') }}</td></tr>
		<tr><td>{{ _lang('Role') }}</td><td>{{ $user->role->name ?? ''}}</td></tr>
    </table>
</div>
</div>
