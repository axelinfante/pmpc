					@if ($data->importado == "Si")
						<button class="btn btn-sm btn-activo" data-href="{{ route('items.actualizaimportado') }}" data-valor="No" data-id="{{$data->id}}"><i class="fa fa-toggle-on fa-2x" aria-hidden="true"></i></button>
					@else
                    <button class="btn btn-sm btn-activo" data-href="{{ route('items.actualizaimportado') }}" data-valor="Si" data-id="{{$data->id}}"><i class="fa fa-toggle-off fa-2x" aria-hidden="true"></i></button>
					@endif
				