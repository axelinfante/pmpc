<div class="card">
	<div class="card-body">
		<table class="table table-bordered">
			<tr><td>{{ _lang('Product Name') }}</td><td>{{ $item->item_name }}</td></tr>
			<tr><td>{{ _lang('Marca y modelo') }}</td><td>{{ ($product->marcaModelo->marca->marca ?? '') .' ' .
							($product->marcaModelo->modelo->modelo ?? '')
							}}</td></tr>
			{{--<tr><td>{{ _lang('Product Cost') }}</td><td>{{ decimalPlace($item->product->product_cost, currency()) }}</td></tr>--}}
			{{--<tr><td>{{ _lang('Product Price') }}</td><td>{{ decimalPlace($item->product->product_price, currency()) }}</td></tr>--}}
			{{--<tr><td>{{ _lang('Product Unit') }}</td><td>{{ $item->product->product_unit }}</td></tr>--}}
			{{--<tr><td>{{ _lang('Availabel Quantity') }}</td><td>{{ $item->product_stock->quantity.' '.$item->product->product_unit }}</td></tr>--}}
			<tr><td>{{ _lang('Description') }}</td><td>{{ $product->description }}</td></tr>	

			<tr><td>{{ _lang('Mercado Libre') }}</td><td>{{ $product->mercado_libre==1? 'Si':'No' }}</td></tr>	

		</table>

		<div id="galeria">
			<h3 class="my-3">Fotos <a href="{{ route('pro_imag_zip',$product->id) }}" class="btn btn-info btn-xs mb-4"><i class="ti-zip"></i> Descargar Imagenes</a></h3>
			@forelse($product->img as $img)
				<div class="row">
						<div class="col" style="margin-top: 0.50rem !important;">
						<!--<img src="{{asset('public/uploads/products/'.$img->img)}}" alt="">-->
						<img class="card-img-top img-fluid" src="{{ marcaAgua(asset('public/uploads/products/'. $img->img),$img->company_id,'/products/'.$img->img) }}" alt="">
					</div>
				</div>
				@empty
				<div class="alert alert-danger">Sin imagenes</div>
			@endforelse
		</div>
	</div>
</div>				
