@extends('layouts.app')

@section('content')
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">
<input type="hidden" id="idProd" value="{{ $idProduct ?? '' }}">
<div class="row">
	<div class="col-12">
		<div class="card">
			<span class="d-none panel-title">{{ _lang('Create Quotation') }}</span>

			<div class="card-body">
				<form method="post" class="validate" autocomplete="off" action="{{url('reservas')}}" enctype="multipart/form-data">
					{{ csrf_field() }}
					
					<div class="row">
						<div class="col-md-3">
						  <div class="form-group">
							<label class="control-label">{{ _lang('Quotation Number') }}</label>						
							<input type="text" class="form-control" name="quotation_number" value="{{ old('invoice_number',get_company_option('quotation_prefix').get_company_option('quotation_starting',1001)) }}" disabled>
							<input type="hidden" name="quotation_starting_number" value="{{ get_company_option('quotation_starting') }}"> 
						  </div>
						</div>
						
						<div class="col-md-3">
						  <div class="form-group">
							<label class="control-label">{{ _lang('Quotation Date') }}</label>	
							<input type="text" class="form-control datepicker" disabled value="{{ old('quotation_date') }}" required>					
							<input type="hidden" class="form-control datepicker" name="quotation_date" value="{{ old('quotation_date') }}" required>
						  </div>
						</div>

						<select class="form-control d-none" name="template">
							@foreach(get_quotation_templates() as $key => $value)
								<option value="{{ $key }}">{{ $value }}</option>
							@endforeach
						</select>

						<div class="col-md-6 d-none">
						  <div class="form-group">
							<label class="control-label">{{ _lang('Related To') }}</label>						
							<select class="form-control select2 " data-selected="{{ isset($_GET['related_to']) ? $_GET['related_to'] : 'contacts' }}" name="related_to" id="related_to">
								<option selected value="contacts">{{ _lang('Customer') }}</option>
								<option value="leads">{{ _lang('Lead') }}</option>
							</select>
						  </div>
						</div>

						{{-- <div class="col-md-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('¿Desarmar?') }}</label>
								<select class="form-control "  name="desarmar" id="desarmar">
									<option value="0">{{ _lang('No') }}</option>
									<option value="1">{{ _lang('Si') }}</option>
								</select>
							</div>
						</div> --}}

						{{-- <div class="col-md-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('Fecha de entrega') }}</label>
								<input type="date" class="form-control" name="fecha_entrega">
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('Retiro') }}</label>
								<select class="form-control select2 auto-select"  name="retiro" id="retiro">
									<option value="0">Seleccionar</option>
									<option value="1">{{ _lang('Retirado') }}</option>
									<option value="2">{{ _lang('Enviado') }}</option>
								</select>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('Entregado a') }}</label>
								<input type="text" name="entregado_a" class="form-control">
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">{{ _lang('Entregado por') }}</label>
								<select class="form-control select2 auto-select"  name="entregado_por"
										id="entregado_por">
									<option value="">{{ _lang('Seleccionar') }}</option>
									@foreach($users as $u)
									<option value="{{$u->id}}">{{ $u-> name}}</option>
									@endforeach
								</select>
							</div>
						</div> --}}

						<div class="col-md-6 d-none" id="contacts">
						  <div class="form-group">
							<a href="{{ route('contacts.create') }}" data-reload="false" data-title="{{ _lang('Add Client') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
							<label class="control-label">{{ _lang('Select Client') }}</label>						
							<select class="form-control select2-ajax" data-value="id" required data-display="contact_name" data-display2="dni_cuit" data-table="contacts" data-where="" name="client_id" id="client_id">
							   <option value="">{{ _lang('Select One') }}</option>
							</select>
						  </div>
						</div>
						
						<div class="col-md-6 d-none" id="leads">
						  <div class="form-group">
							<a href="{{ route('leads.create') }}" data-reload="false" data-title="{{ _lang('Add New lead') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
							<label class="control-label">{{ _lang('Select Lead') }}</label>						
							<select class="form-control select2-ajax" data-value="id" data-display="id" data-display2="name" data-table="leads" data-where="1" name="lead_id" id="lead_id">
							   <option value="">{{ _lang('Select One') }}</option>
							   @if(isset($_GET['lead_id']))
									{{ create_option("leads","id",array("id","name"), $_GET['lead_id'], array("company_id="=>company_id())) }}
							   @endif
							</select>
						  </div>
						</div>
						@if(!$idCar || !$idProduct)

							{{-- <div class="col-md-6">
								<div class="form-group select-product-container">

									<label class="control-label">{{ _lang('Productos en stock') }}</label>
									<select class="form-control select2-ajax" data-value="items.id"
											data-display="item_name"
											data-display2="marcas.marca"
											data-display3="modelos.modelo"
											data-table="items" data-where="9"  name="service" id="service">
										<option value="">{{ _lang('Select Product') }}</option>


									</select>
								</div>
							</div> --}}


						@endif
						
				<div class="col-md-6">
					<div class="form-group select-product-container">
						<a id="productLink" href="{{ route('products.create') }}?idCar={{$idCar}}" data-reload="false"
						   data-title="{{
								_lang
								('Add Product') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
						<label class="control-label">{{ _lang('Producto en vehiculo') }}</label>

						<select class="form-control" data-value="items.id" data-display="items.item_name" 
								data-table="items" data-where="100" data-option = '' name="product" id="product">
							<option value="">{{ _lang('Select Product') }}</option>
						</select>
					</div>
				</div>
				
						{{-- @if($idCar || !$idProduct) --}}
							{{-- <div class="col-md-6">
							<div class="form-group select-product-container">
								<a id="productLink" href="{{ route('products.create') }}?idCar={{$idCar}}" data-reload="false" data-title="{{
														_lang
														('Add Product') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
								<label class="control-label">{{ _lang('Producto en vehiculo') }}</label>
						
								<select class="form-control" data-value="products.id" data-display="items.item_name" data-table="products" data-where="9"
									data-option="{{isset($idCar) ? " products .car_id=$idCar": '' }}" name="product" id="product">
									<option value="">{{ _lang('Select Product') }}</option>
						
								</select>
							</div>
						</div> --}}
							{{-- @else
							<div class="col-md-6">
								<div class="form-group select-product-container">

									<label class="control-label">{{ _lang('Producto') }}</label>
									<select class="form-control" data-value="items.id" data-display="item_name"

											data-display2="marcas.marca"
											data-display3="modelos.modelo"
											data-table="items" data-where="2"  name="product" id="product">
										<option value="">{{ _lang('Select Product') }}</option>
										<option value="{{$item->product->id}}">{{ $item->item_name}}</option>

									</select>
								</div>
							</div> --}}
						{{-- @endif --}}

						@if($idCar || !$idProduct)
						<div class="col-md-6">
							<div class="form-group">
								{{--<a href="{{ route('vehiculo.create') }}" data-reload="false" data-title="{{ _lang('Add Supplier') --}}
								{{--}}" --}}
								{{--class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>--}}

								@php
									$in = '';
									if ($vehiculo->company_id == 1) {
										$in = 'PM-';
									} else if ($vehiculo->company_id == 2) {
										$in = 'PC-';
									}
								@endphp

								<label class="control-label">{{ _lang('Vehiculo') }}</label>
								<select class="form-control select2" data-value="cars.id" data-display="IF(cars.company_id = 1, CONCAT('PM-',cars.id), CONCAT('PC-',cars.id))" 
								data-display2="marcas.marca" data-display3="modelos.modelo"
										data-table="cars"
										data-where="10" name="car_id" id="car_id">
									<option value="">{{ _lang('- Select Car -') }}</option>


									<option {{old('car_id',$idCar ?? '') == $vehiculo->id? 'selected' :''}} value="{{
										$vehiculo->id}}">{{ $in.$vehiculo->id.' '.
										($vehiculo->marca_modelo->marca->marca ??
										 '').' '.
										($vehiculo->marca_modelo->modelo->modelo ?? '') .' '. $vehiculo->siniestro}}</option>
									
									{{-- @forelse($vehiculos as $v)
										<option {{old('car_id',$idCar ?? '') == $v->id? 'selected' :''}} value="{{
										$v->id}}">{{
										($v->marca_modelo->marca->marca ??
										 '').' '.
										($v->marca_modelo->modelo->modelo ?? '') .' '. $v->siniestro}}</option>
										@empty
									@endforelse --}}
								</select>
							</div>
						</div>
						@endif

						<!--Order table -->
						@php $currency = currency(); @endphp
						
						<div class="col-md-12">
							<div class="table-responsive">
								<table id="order-table" class="table table-bordered">
									<thead>
										<tr>
											<th>{{ _lang('Name') }}</th>
											<th>{{ _lang('Description') }}</th>
											<th class="text-center wp-100">{{ _lang('Quantity') }}</th>
											<th class="text-right">{{ _lang('Unit Cost').' '.$currency }}</th>
											<th class="text-right wp-100">{{ _lang('Discount').' '.$currency }}</th>
											<th>{{ _lang('Tax') }}</th>
											<th class="text-right">{{ _lang('Sub Total').' '.$currency }}</th>
											<th class="text-right">Id Vehiculo</th>
											<th class="text-center">{{ _lang('Action') }}</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
									<tfoot class="tfoot active">
										<tr>
											<th>{{ _lang('Total') }}</th>
											<th></th>
											<th class="text-center" id="total-qty">0</th>
											<th></th>
											<th class="text-right" id="total-discount">0.00</th>
											<th class="text-right" id="total-tax">0.00</th>
											<th class="text-right" id="total">0.00</th>
											<th class="text-center"></th>
											<input type="hidden" name="product_total" id="product_total" value="0">
											<input type="hidden" name="tax_total" id="tax_total" value="0">
										</tr>
									</tfoot>
								</table>
								
								<table class="table table-striped">
								   <thead class="thead-light">
									  <tr>
										 <th>
											{{ _lang('Converted Amount') }} ({{ _lang('Client Currency') }} - <span class="client_currency">{{ base_currency() }}</span>)
											&emsp;<span id="converted_amount">{{ $currency }} 0.00</span>
										 </th>
									  </tr>
								   </thead>
								</table>
								
							</div>
						</div>

						<!--End Order table -->

						<div class="col-md-12">
						  <div class="form-group">
							<label class="control-label">{{ _lang('Note') }}</label>						
							<textarea class="form-control" rows="4" name="note">{{ old('note') }}</textarea>
						  </div>
						</div>

						<div class="col-md-12">
						  <div class="form-group">
							<button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
						  </div>
						</div>
					</div>
				</form>
			</div>
	    </div>
	</div>
</div>

<select class="form-control d-none" id="tax-selector">
	@foreach(App\Tax::where("company_id",company_id())->get() as $tax)
		<option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}" data-tax-rate="{{ $tax->rate }}">{{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
	@endforeach
</select>

@endsection

@section('js-script')
<script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/quotation/create.js?v=1.2') }}"></script>
<script>
	let car = $('#car_id');
	let product = $('#product');

	if(car.val() != '' ){
		$('#productLink').prop('href',"{{route('products.create')}}?idCar="+car.val());
		
		$('#product').prop('data-idCar',car.val());
		car.prop('data-option',car.val());


		product.data('option','products.car_id = '+ car.val());

		var display2 = "";
		if( typeof  product.data('display2') !== "undefined" ){
			display2 = "&display2=" +  product.data('display2');
		}

		var display3 = "";
		if( typeof  product.data('display3') !== "undefined" ){
			display3 = "&display3=" +  product.data('display3');
		}

		product.select2({
			ajax: {
				url: _url + '/ajax/get_table_data?table=' + product.data('table') + '&value=' + product.data('value') +
				'&display=' + product.data('display') + display2 + display3 + '&where=' +product.data('where')+
				'&option=' +product.data('option'),
				processResults: function (data) {

					return {
						results: data
					};
				}
			}
		});
		}else{
		$('#productLink').addClass('d-none')
		}
		
		/*
	car.change(function() {

        product.prop('data-option','products.car_id = ' + $(this).val());
        console.log(product.prop('data-option'));
		car.prop('data-option',car.val());
        //product.select2({});

            var display2 = "";
            if( typeof  product.data('display2') !== "undefined" ){
                display2 = "&display2=" +  product.data('display2');
            }

            var display3 = "";
            if( typeof  product.data('display3') !== "undefined" ){
                display3 = "&display3=" +  product.data('display3');
            }

        	$('#productLink').prop('href',"{{route('products.create')}}?idCar="+car.val());
        	$('#product').prop('data-idCar',car.val());


        	product.select2({
                ajax: {
                    url: _url + '/ajax/get_table_data?table=' + product.data('table') + '&value=' + product.data('value') +
					'&display=' + product.data('display') + display2 + display3 + '&where=' +product.data('where')+
					'&option= products.car_id = ' + $(this).val(),
                    processResults: function (data) {

                        return {
                            results: data
                        };
                    }
                }
            });



	})*/
</script>
@endsection



