<form method="post" class="validate ajax-submit" autocomplete="off" action="{{action('CategoryController@update', $id)}}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
   
    <div class="col-12">
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Nombre') }}</label>
					<input type="text" class="form-control" name="nombre" value="{{ $category->nombre }}"
						   required>
				</div>
			</div>
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Color') }}</label>
					<input type="color" class="form-control" name="color" value="{{ old
						('color', $category->color) }}"
						   required>
				</div>
			</div>




			<div class="col-md-12">
			  <div class="form-group">
				<button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>