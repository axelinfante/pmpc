@extends('layouts.app')

@section('content')

<div class="row">
	<div class="col-12">
	    <a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Crear categoria') }}" href="{{ route
	    ('categorias.create')
	    }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
	    {{--<a class="btn btn-dark btn-xs" href="{{ route('products.import') }}"><i class="ti-import"></i> {{ _lang('Import') }}</a>--}}

		<div class="card mt-2">
			<span class="panel-title d-none">{{ _lang('Categorias') }}</span>

			
			<div class="card-body">
				<table class="table table-bordered data-table">
					<thead>
					  <tr>
						  <th>{{ _lang('Id') }}</th>
							<th>{{ _lang('Categoria') }}</th>
							<th class="text-right">{{ _lang('Color') }}</th>
							
							<th class="text-center">{{ _lang('Action') }}</th>
					  </tr>
					</thead>
					<tbody>
						

					  @foreach($categories as $category)
					  <tr id="row_{{ $category->id }}">
							<td class='id'>{{ $category->id }}</td>

							<td class='product_stock text-center'>{{ $category->nombre }}</td>
							<td class='product_stock text-center' style="background-color:{{ $category->color }}">{{
							$category->color }}</td>

							<td class="text-center">
								<form action="{{action('CategoryController@destroy', $category['id'])}}" method="post">
								<a href="{{action('CategoryController@edit', $category['id'])}}" data-title="{{ _lang
								('Editar categoría') }}" class="btn btn-warning btn-xs
										ajax-modal "><i
											class="ti-pencil"></i></a>
								{{--<a href="{{action('CategoryController@show', $category['id'])}}" data-title="{{ _lang('View Product') }}" class="btn btn-primary btn-xs ajax-modal"><i class="ti-eye"></i></a>--}}
								{{ csrf_field() }}
								<input name="_method" type="hidden" value="DELETE">
								<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>
								</form>
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


