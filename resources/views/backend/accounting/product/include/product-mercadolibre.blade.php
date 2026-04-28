					@if ($data->mercado_libre == 1)
						<button class="btn btn-sm btn-estado" data-campo="mercado_libre" data-valor="0" data-id="{{$data->id}}"><i class="fa fa-toggle-on fa-2x" aria-hidden="true"></i></button>
					@else
                    <button class="btn btn-sm btn-estado" data-campo="mercado_libre" data-valor="1" data-id="{{$data->id}}"><i class="fa fa-toggle-off fa-2x" aria-hidden="true"></i></button>
					@endif
				