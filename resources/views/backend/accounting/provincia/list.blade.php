@extends('layouts.app')

@section('content')

<div class="row">
	<div class="col-lg-12">

		<a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Create') }}" href="{{ route('provincia.create')
		}}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>

		<div class="card mt-2">

			<div class="card-body">
				<table id="roles_table" class="table table-bordered data-table">
					<thead>
					    <tr>
						    <th>{{ _lang('Id') }}</th>
						    <th>{{ _lang('Provincia') }}</th>

							<th class="text-center">{{ _lang('Action') }}</th>
					    </tr>
					</thead>
					<tbody>
					    @foreach($provincias as $m_m)
					    <tr data-id="row_{{ $m_m->id }}">
							<td class='name'>{{ $m_m->id }}</td>
							<td class='name'>{{ $m_m->provincia }}</td>

							
							<td class="text-center">
								<div class="dropdown">
								  <button class="btn btn-primary dropdown-toggle btn-xs" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								  {{ _lang('Action') }}
								  <i class="fas fa-angle-down"></i>
								  </button>
								  <form action="{{ action('ProvinciaController@destroy', $m_m['id']) }}" method="post">
									{{ csrf_field() }}
									<input name="_method" type="hidden" value="DELETE">
									
									<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
										<a href="{{ action('ProvinciaController@edit', $m_m['id']) }}" data-title="{{ _lang
										('Update') }}" class="dropdown-item ajax-modal"><i class="mdi
										mdi-pencil"></i> {{ _lang('Edit') }}</a>
										<a href="{{ action('ProvinciaController@show', $m_m['id']) }}" data-title="{{ _lang
										('View') }}" class="dropdown-item ajax-modal"><i class="mdi
										mdi-eye"></i>
											{{ _lang('View') }}</a>
										<button class="btn-remove dropdown-item" type="submit"><i class="mdi mdi-delete"></i> {{ _lang('Delete') }}</button>
									</div>
								  </form>
								</div>
							</td>
					    </tr>
					    @endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

@endsection