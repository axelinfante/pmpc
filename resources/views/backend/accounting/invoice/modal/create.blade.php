<style>
	.hide-col {
  overflow: hidden;
  width: 0 !important;
  max-width: 0 !important;
  padding: 0 !important;
  border-width: 0 !important;
  font-size: 0 !important; /* Optionally hide text completely */
}
</style>

@php
	$ve = '';
	if (auth()->user()->role->name == 'Vendedor') {
		$ve = 'd-none';
	}
@endphp
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">
<input type="hidden" id="idProd" nameid="idProd" value="{{ $idProduct ?? '' }}">
<button type="button" id="actualizarButton" class="btn btn-primary"  style="display: none;" >Actualizar</button>

<div class="row">
	<div class="col-12">
	<div class="card">
	<span class="d-none panel-title">{{ _lang('Create Invoice') }}</span>

	<div class="card-body">
	  <form id="form_create" name="form_create" method="post" class="validate" autocomplete="off" action="{{ url('invoices') }}" enctype="multipart/form-data">
		{{ csrf_field() }}

		<div class="row">
			<div class="col-md-2">
			  <div class="form-group">
				<label class="control-label" style="font-size: 13px">{{ _lang('Invoice Number') }}</label>
				<input type="text" class="form-control" name="invoice_number" value="{{ old('invoice_number',get_company_option('invoice_prefix').get_company_option('invoice_starting',1001)) }}" required>
				<input type="hidden" name="invoice_starting_number" value="{{ get_company_option('invoice_starting') }}">
			  </div>
			</div>

			<div class="col-md-3">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Invoice Date') }}</label>
				<input type="text" class="form-control datepicker" name="invoice_date" value="{{ old('invoice_date') }}" required>
			  </div>
			</div>

			<div class="col-md-3 d-none">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Due Date') }}</label>
				<input type="text" class="form-control datepicker" name="due_date" value="{{ old('due_date') }}" required>
			  </div>
			</div>
			{{-- <div class="col-md py-4">
				<div class="form-check">

					<input type="checkbox" id="facturar" name="facturar" class="form-check-input" value="1">
					<label class="form-check-label" for="facturar">Facturar</label>

				</div>
			</div> --}}

			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">{{ _lang('Acciones') }}</label>
					<select class="form-control select2" multiple name="acciones[]" id="acciones">
						<option value="0">Seleccionar</option>
						<!--<option value="no_desarmar">{{ _lang('Desarmado') }}</option>-->
						<option value="retirar">{{ _lang('Retirar en el momento') }}</option>
						<option value="despacho">{{ _lang('Despacho') }}</option>
						<option value="retiro_programado">{{ _lang('Retiro Programado') }}</option>
						<option value="facturar">{{ _lang('Facturar') }}</option> 
						<option value="retiro_ventanita">{{ _lang('Retiro Ventanita') }}</option> 
						<option value="retiro_constituyentes">{{ _lang('Retiro Constituyentes') }}</option> 
						<option value="retiro_octubre">{{ _lang('Retiro Octubre') }}</option> 
						<option value="despacho">{{ _lang('Despacho') }}</option> 
					</select>
				</div>
			</div>

			@if($rol != auth()->user()->role_id)
			<div class="col-lg-3 mb-2">
				<label>{{ _lang('Vendedores') }}</label>
				<select class="form-control select2 select-filter" name="vendedor">
					<option value="">{{ _lang('Vendedores') }}</option>
					{{ create_option('users','id',['name','email'],'',array('role_id=' => $rol), " company_id in (".implode(",", $companias_global).")") }}
				</select>
			</div>
			@endif

			
			<div class="col-lg-3 mb-2">
				<label>{{ _lang('Tipo de venta') }}</label>
				<select class="form-control  " required name="comision">
					<option selected value="">Seleccione</option>
					<option value="Camiones">{{ _lang('Camiones'

								 ) }}</option>
					<option value="Motos enteras">{{ _lang('
								 Motos enteras'
								) }}</option>
					<option value="Ruedas">{{ _lang('ruedas') }}</option>
					<option value="Motores">{{ _lang('Motores') }}</option>
					<option value="Mercado Libre">{{ _lang('Mercado Libre') }}</option>
					<option value="Reventa">{{ _lang('Reventa') }}</option>
					<option value="Lote">{{ _lang('Lote') }}</option>
					<option value="Venta normal">{{ _lang('Venta normal') }}</option>

				<option value="Venta menos a 30000">{{ _lang('Venta menos a 30000') }}</option>


				</select>
			</div>

			<div class="col-md-4 d-none">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Related To') }}</label>
				<select class="form-control select2 " data-selected="{{ isset($_GET['related_to']) ? $_GET['related_to'] : 'contacts' }}" name="related_to" id="related_to">
				   <option selected value="contacts">{{ _lang('Customer') }}</option>
				   <option value="projects">{{ _lang('Project') }}</option>
				</select>
			  </div>
			</div>


			{{-- <div class="col-md-2 py-4">
				<div class="form-group">

					<div class="form-check">
						<input class="form-check-input" name="desarmar" type="checkbox" value="0" id="flexCheckDefault">
						<label class="form-check-label" for="flexCheckDefault">
						  No desarmar
						</label>
					  </div>
				</div>
			</div> --}}
			<div class="col-md-2 ">
				<div class="form-group">
					<label class="control-label">{{ _lang('Prioridad de desarme') }}</label>
					<select class="form-control "  name="prioridad_desarmar" id="prioridad_desarmar">
						
						<option value="alta">{{ _lang('alta') }}</option>
						<option selected value="normal">{{ _lang('normal') }}</option>
						<option value="baja">{{ _lang('baja') }}</option>
					</select>
					
				</div>
			</div>

			<div class="col-md {{ $ve }}">
				<div class="form-group">
					<label class="control-label">{{ _lang('Fecha de entrega') }}</label>
					<input type="date" class="form-control" name="fecha_entrega">
				</div>
			</div>

			<div class="col-md-6 {{ $ve }}">
				<div class="form-group">
					<label class="control-label">{{ _lang('Retiro') }}</label>
					<select class="form-control select2 auto-select"  name="retiro" id="retiro">
						<option value="0">Seleccionar</option>
						<option value="1">{{ _lang('Retirado') }}</option>
						<option value="2">{{ _lang('Enviado') }}</option>
					</select>
				</div>
			</div>

			<div class="col-md-4 {{ $ve }}">
				<div class="form-group">
					<label class="control-label">{{ _lang('Entregado a') }}</label>
					<input type="text" name="entregado_a" class="form-control">
				</div>
			</div>

			<div class="col-md-4 {{ $ve }}">
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
			</div>
			<div class="col-md-4 {{ $ve }}">
				<div class="form-group">
					<label class="control-label">{{ _lang('Ubicación') }}</label>
					<input type="text" name="ubicacion" class="form-control">
					
				</div>
			</div>

			<div class="col-md-4 d-none" id="contacts">
			  <div class="form-group">
				<a href="{{ route('contacts.create') }}" data-reload="false" data-title="{{ _lang('Add Client') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Select Client') }}</label>
				<select data-placeholder="{{ _lang('Buscar por Dni o nombre...') }}" class="form-control select2-ajax" data-value="id" data-display="contact_name" data-display2="dni_cuit"
						data-table="contacts" data-where="101" name="client_id" id="client_id" required>
						{{--  <option value="">{{ _lang('Escriba el nombre o dni del cliente...') }}</option> --}}
				</select>
			  </div>
			</div>
		<div class="col-md-12">
					<div class="form-group">
<br>
						<label class="control-label" style="color: black;"><strong>Productos en vehiculo</strong></label> </div></div>

			<div class="col-12 " style="background-color: #ffa5a5">
				<div class="row">


			{{-- @if($idCar || !$idProduct) --}}
				
<div class="col-md-6">
					<div class="form-group">
						{{--<a href="{{ route('vehiculo.create') }}" data-reload="false" data-title="{{ _lang('Add Supplier') --}}
						{{--}}" --}}
						{{--class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>--}}

						<label class="control-label">{{ _lang('Vehiculo') }}(Estados: BD / APTO / No Apto Autorizado / Compactado -> Debe colocar observacion)</label>
						<select class="form-control select2-ajax" data-value="cars.id" data-display="IF(cars.company_id = 1, CONCAT('PM',COALESCE(tipo_vehiculo,''),'-',LPAD(cars.id, 10, '0')), CONCAT('PC',COALESCE(tipo_vehiculo,''),'-',LPAD(cars.id, 10, '0') ))"
								data-display2="IF(cars.idMarca_modelo > 0, marcas.marca , 'Sin marca')" data-display3="IF(cars.idMarca_modelo > 0,modelos.modelo, 'Sin modelo')"
								data-table="cars"
								data-where="11" name="car_id" id="car_id">
							<option value="">{{ _lang('- Select Car -') }}</option>
							{{-- @forelse($vehiculos as $v)
							@if($v->idEstado !=1)
								<option {{old('car_id',$idCar ?? '') == $v->id? 'selected' :''}} value="{{
										$v->id}}">{{ $v->id.' '.
										($v->marca_modelo->marca->marca ??
										 '').' '.
										($v->marca_modelo->modelo->modelo ?? '') .' '. $v->siniestro}}</option>
							
							
							@endif
							@empty
							@endforelse --}}
						</select>
					</div>
				</div>
			{{-- @endif --}}

			{{-- @if($idCar || !$idProduct) --}}
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
			{{-- @else
				<div class="col-md-6">
					<div class="form-group select-product-container">

						<label class="control-label">{{ _lang('Producto') }}</label>
						<select class="form-control" data-value="products.id" data-display="items.item_name"
								data-table="products" data-where="9"  name="product" id="product">
							<option value="">{{ _lang('Select Product') }}</option>
							<option selected value="{{$item->id}}">{{ $item->item->item_name}}</option>

						</select>
					</div>
				</div> --}}
			{{-- @endif --}}

				</div>
			</div>
	@if(!$idCar || !$idProduct)
<div class="col-md-12">
					<div class="form-group">
<br>
						<label class="control-label" style="color: black;"><strong>Productos en stock</strong></label> </div></div>
				<div class="col-md-12" style="background-color: #faff7e;">
					<div class="form-group select-product-container">
						<a href="{{ route('products.create') }}?modalInStock=1" data-reload="false" data-title="{{
								_lang('Add Product') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
						<label class="control-label">{{ _lang('Productos') }}</label>
						<select data-placeholder="{{ _lang('Buscar por IdProducto, Interno, Items name o marca-modelo...') }}" class="form-control select2-ajax" data-value="products.id"
								data-display="items.item_name"
								data-display2="IF(products.marca_modelo > 0, marcas.marca , 'Sin marca')" data-display3="IF(products.marca_modelo > 0,modelos.modelo, 'Sin modelo')"
								data-table="products" data-where="9"  name="service" id="service">
								{{--<option value="">{{ _lang('Select Product') }}</option>--}}
							{{--<option value="{{$item->product->id}}">{{ $item->item_name}}</option>--}}
							@if ($idProduct)
							<option selected value="{{$item->id}}">{{ $item->item->item_name}}</option>
							@endif
							

						</select>
					</div>
				</div>


			@endif
			

			<div class="col-md-3">
				<label class="" for="companySelect">Empresa</label>
				<select id="company_id_s" disabled class="form-control">

					{{ list_company_entrar() }}
                
				</select>
				<input type="hidden" name="company_id" id="company_id">
				
			</div>

			<div class="col-md-3 py-4">
				<div class="form-check">

					<input type="checkbox" id="is_usd" name="is_usd" class="form-check-input" value="1">
					<label class="form-check-label" for="is_usd">Monto en usd</label>

				</div>

				
			</div>

			<div class="col-md-3 d-none">
				<div class="form-group">
					<label for="tasa_usd">Tasa Usd</label>
					<input type="number" class="form-control" step="0.01" id="tasa_usd" name="tasa">
				</div>
				
			</div>

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group select-product-container">--}}
					{{--<a href="{{ route('products.create') }}" data-reload="false" data-title="{{ _lang('Add Product') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>--}}
					{{--<label class="control-label">{{ _lang('Select Product') }}</label>--}}
					{{--<select class="form-control select2-ajax" data-value="id" data-display="item_name" data-table="items" data-where="2" name="product" id="product">--}}
						{{--<option value="">{{ _lang('Select Product') }}</option>--}}
					{{--</select>--}}
				{{--</div>--}}
			{{--</div>--}}

			<!--Order table -->
			@php $currency = currency(); @endphp

			<div class="col-md-12">
				<div class="table-responsive">
					<table id="order-table" class="table table-bordered">
						<thead>
							<tr>
								<th>Id_Producto / Nro Oblea</th>
								<th>{{ _lang('Name') }}</th>
								<th>{{ _lang('Description') }}</th>
								<th class="text-center wp-100">{{ _lang('Quantity') }}</th>
								<th class="text-right">{{ _lang('Unit Cost').' $/usd' }}</th>
								
								<th class="text-right">{{ _lang('Sub Total').' $/usd' }}</th>
								<th class="text-right">{{ _lang('USD convertidos').' '.$currency }}</th>
								<th class="text-right">Nro interno</th>
								<th class="text-center">{{ _lang('Action') }}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot class="tfoot active">
							<tr>
								<th>{{ _lang('Total') }}</th>
								<th></th>
								<th></th>
								<th class="text-center" id="total-qty">0</th>
								<th></th>
								
								<th class="text-right" id="total">0.00</th>
								<th class="text-right"></th>
								<th class="text-center"></th>
								<th class="text-center"></th>
								<input type="hidden" name="product_total" id="product_total" value="0">
								<input type="hidden" name="tax_total" id="tax_total" value="0">
							</tr>
						</tfoot>
					</table>

					<table class="table table-striped d-none">
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

			<div class="col-lg-3 mb-2">
				<label>{{ _lang('Revendedores') }}</label>
				<select class="form-control select2 select-filter" name="revendedor">
					<option value="">{{ _lang('Revendedores') }}</option>
					{{ create_option('users','id',['name','email'],'',array('role_id=' => $rol_revendedor)) }}
				</select>
			</div>

			<div class="col-md-12" id="saldo-cc-container" style="display:none;">
				<div class="form-check">
					<input type="checkbox" name="usar_saldo_cuenta_corriente" id="usar_saldo_cc" class="form-check-input" value="1">
					<label class="form-check-label" for="usar_saldo_cc">
						{{ _lang('Abonar con saldo de cuenta corriente') }}
						(Saldo disponible: <span id="saldo-cc-disponible">$0.00</span>)
					</label>
				</div>
			</div>

			<div class="col-md-12">
			  <div class="form-group">
				<button id="btn-submit" type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
				<input type="hidden" name="nro_interno_tmp" id="nro_interno_tmp" value="">
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




<script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/invoice/create.js?v=1.4') }}"></script>

<script>
    let car = $('#car_id');
    let product = $('#product');

	let is_usd = $('#is_usd');

	is_usd.change(function () {
		if(is_usd.is(':checked')){
            $('#tasa_usd').prop('disabled',false);
        }else{
            $('#tasa_usd').prop('disabled',true);
        }
		checkSaldoAFavor();
	})
	function checkSaldoAFavor() {
		let clientId = $('#client_id').val();
		if (!clientId) {
			$('#saldo-cc-container').hide();
			$('#usar_saldo_cc').prop('checked', false);
			return;
		}
		let isUsd = $('#is_usd').is(':checked');
		$.ajax({
			url: '{{ url("cuenta_corriente") }}' + '/' + clientId + '/resumen',
			dataType: 'json',
			success: function(data) {
				let saldo = isUsd ? data.saldo_actual_usd : data.saldo_actual_peso;
				if (saldo < 0) {
					let disponible = Math.abs(saldo);
					let currency = isUsd ? 'USD' : 'ARS';
					$('#saldo-cc-disponible').text(currency + ' ' + disponible.toFixed(2));
					$('#saldo-cc-container').show();
				} else {
					$('#saldo-cc-container').hide();
					$('#usar_saldo_cc').prop('checked', false);
				}
			}
		});
	}

	$('#client_id').change(checkSaldoAFavor);

	let interval = setInterval(habilitarBtn, 300);
	function habilitarBtn(){
		let btn = $('#btn-submit');
		let company = $('#company_id').val();
		if(company > 0) {
			btn.prop('disabled', false);
			clearInterval(interval);
			console.log('b')
		}else{
			btn.prop('disabled', true);
			// console.log('c')
		}
	}
    if(car.val() != '' ){

        $('#productLink').prop('href',"{{route('products.create')}}?idCar="+car.val());
        $('#product').prop('data-idCar',car.val());


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

		// console.log('a')
	}
    car.change(function() {

        product.prop('data-option','products.car_id = ' + $(this).val());
        //product.select2({});
		$('#productLink').removeClass('d-none')
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

		limpiarItems($(this).val());
		product.select2({
				placeholder: 'Buscar...', // ¡Aquí puedes meter el placeholder que querías!
				allowClear: true,
			ajax: {
				url: _url + '/ajax/get_table_data?table=' + product.data('table') + 
					  '&value=' + product.data('value') +
					  '&display=' + product.data('display') + display2 + display3 + 
					  '&where=' + product.data('where') +
					  '&car_id=' + $(this).val() +
					  '&option= products.car_id = ' + $(this).val(),
        delay: 250,
        dataType: 'json',
        processResults: function (data) {
			                 return {
                    results: data 
                };
        }
    }
});
        /*product.select2({
            ajax: {
                url: _url + '/ajax/get_table_data?table=' + product.data('table') + '&value=' + product.data('value') +
                '&display=' + product.data('display') + display2 + display3 + '&where=' +product.data('where')+
                '&option= products.car_id = ' + $(this).val(),
                processResults: function (data) {
					selected=$('#nro_interno_tmp').val();
					selected = selected.toString().split(",").map(function(t){return parseInt(t)})
 					var data_modified = $.map(data, function (obj) {
							 	obj.disabled = ($.inArray(obj.id, selected) != -1) ? true:false; // or use logical statement
          				return obj;
        				});

					   return { results: data_modified };	
                }
            }
        });*/



    })


	$('.select2-ajax').on('change',function (e) {
	
	if($(this).prop('id') == 'car_id') {
		$.ajax({
			url :_url + '/vehiculo/get-company/'+$(this).val(),
			async: false,
			dataType: 'json',
			success: function(data) {
				$('#service').data('company', data.company);
				$('#car_id').data('company', data.company);
				//console.log(car.data('company'));
				$('#company_id').val( data.company);
				$('#company_id_s').val( data.company);
			},
			error: function(error) {
				console.log('Error: Al cargar empresa' )
			}
		});
	}

	if($(this).prop('id') == 'service') {
		if ($(this).val()!= '') {
		$.ajax({
			url :_url + '/products/get-company/'+$(this).val(),
			async: false,
			dataType: 'json',
			success: function(data) {
				$('#service').data('company', data.company);
				$('#car_id').data('company', data.company);
				//console.log(car.data('company'));
				$('#company_id').val( data.company);
				$('#company_id_s').val( data.company);
			},
			error: function(error) {
				// console.log('Error: Al cargar empresa' )
			}
		});
	}
	}
})

/*if($(this).prop('id') == 'service') {
	if ($(this).val()!= '') {
		$.ajax({
			url :_url + '/products/get-company/'+$(this).val(),
			async: false,
			dataType: 'json',
			success: function(data) {
				console.log(data.company);
				$('#service').data('company', data.company);
				$('#car_id').data('company', data.company);
				//console.log(car.data('company'));
				$('#company_id').val( data.company);
				$('#company_id_s').val( data.company);
			},
			error: function(error) {
				// console.log('Error: Al cargar empresa' )
			}
		});
	}
	}

*/
			function limpiarItems(nro_interno) {
				return;
				$('#nro_interno_tmp').val("");
                 if (nro_interno > 0) {
                    $.ajax({
                        url: "{{ url('vehiculo/utilizadas-pieza') }}" + "/" + nro_interno,
                        dataType: 'json',
                        success: function(res) {
                            let selected =res.pieza_listas[0].seleccionados;
                            if (selected){
								$('#nro_interno_tmp').val(selected);
                            }
                           
                        }
                    });

                }
            }



			  $('#form_create').on('submit', function(e) {
				let product_total = parseFloat($("#product_total").val());
				const product_total_ = parseFloat(product_total); // Intenta convertir a número

				if (Number.isNaN(product_total_)) { // Verifica si NO es NaN
						e.preventDefault();
						alert("Precio de Producto no pueden tener un valor menor 0");
              			return false; //for old browsers 
				} 

				 if (validar_summary() === false) {
					e.preventDefault();
						alert("Producto no pueden tener valor 0");
              			return false; //for old browsers 
    			}
    //$(this).submit(); 
    //you don't have to submit manually if you didn't prevent the default event before*/

              });

function validar_summary() {
    let valido = true;
	let product_total=0;
    $("#order-table > tbody > tr").each(function (index, obj) {
        product_total = parseFloat($(this).find(".input-sub-total").val());
		if (product_total==0){
				valido = false;
		}
    });
	return valido;
    
}



$(document).on('change', '#product', function () {
        var product_id = $(this).val();
        if (product_id == '') {
            return;
        }


        let car_id = car.val();

        //alert($car_id);
        /*{{ route('products.create') }}?modalInStock=1
        item_id
        car_id*/
        //return;

		
		var link = "{{ url('products') }}";

		var myformData = new FormData();        
					myformData.append('_token', $('meta[name="csrf-token"]').attr('content'));
					myformData.append('car_id', car_id);
					myformData.append("item_id", product_id);

		 $.ajax({
			 method: "POST",
			 url: link,
			 data: myformData,
			 mimeType:"multipart/form-data",
			 contentType: false,
			 cache: false,
			 processData:false,
			 beforeSend: function(){
				$("#preloader").css("display","block");  
			 },success: function(data){
				$("#preloader").css("display","none"); 
				var json = JSON.parse(data);
				if(json['result'] == "success"){

						
						var select_value = json['data']["products.id"];
						var select_display = json['data']["items.item_name"];
						var newOption = new Option(select_display, select_value, true, true);
						$('#service').append(newOption).trigger('change');
					}
			 },
			 error: function (request, status, error) {
				console.log(request.responseText);
			 }
		 });


		
    });

	/*$('#idProd').on('change',function (e) {
		alert();
	} );*/
	
	$( "#actualizarButton" ).on( "click", function(e) {
	e.preventDefault();

    let idProd = $('#idProd').val();
	  if (idProd) {
				console.log(idProd); 
				let showCar = false;
				$.ajax({
				method: "GET",
                url: _url + "/products/productos-lote/"+idProd,
                beforeSend: function () {
                    $("#preloader").fadeIn(100);
                },
				success:function(datos){
						$("#preloader").fadeOut();
							 datos.forEach(function(item) {
								//console.log(item.id); // Acceder a una propiedad específica
								//console.log(item.item_id);
								
								let product_id=item.id;
								let item_type=item['item'].item_type;
								let item_name=item['item'].item_name;
								let interno = item.nro_interno;
								let product_price = item.product_price != null ? item.product_price : 0;
								let product_cost = item.product_cost != null ? item.product_cost : 0;
								let company_id = item.company_id;
					
								if ($("#order-table > tbody > #product-" + product_id).length > 0) {
									var line = $("#order-table > tbody > #product-" + product_id);
									var quantity = parseFloat($(line).find(".input-quantity").val());
									$(line).find(".input-quantity").val(quantity + 1).trigger('change');
									$("#product").val("").trigger('change');;
									return;
								}	
								
					
                    if (item_type == 'product') {
                        //var product_price = parseFloat(product['product_price']);
                        $('#service').data('company', company_id);
                        $('#car_id').data('company', company_id);
                        $('#company_id').val(company_id);
                        $('#company_id_s').val(company_id);
                    } else if (item['item_type'] == 'service') {
                        product_price = parseFloat(product_cost);
                    }

                    let company = ''
                    if (company_id == 1) {
                        company = 'PM-'
                    }

                    if (company_id == 2) {
                        company = 'PC-'
                    }
					 product_price = (product_price!=0) ? product_price:1.00;
					 
					 let marca = item.marca_modelo ? item.marca_modelo.marca.marca : 'Sin marca';
                    let modelo = item.marca_modelo ? item.marca_modelo.modelo.modelo : 'Sin marca';
					 
                    //Tax Value calculation
                    let unit_cost = product_price;
                    let sub_total = product_price;

                    let tax_selector = $("#tax-selector").html();
					//console.log(unit_cost);
					let product_row = `<tr id="product-${product_id}">
											<td><b>${item.id} / ${item.nro_oblea != null ? item.nro_oblea : ''}</b></td>
											<td><b>${item_name} ${marca} ${modelo}</b></td>
											<td class="description"><input type="text" name="product_description[]" class="form-control input-description" value="${item.description != null ? item.description : ''}"></td>
											<td class="text-center quantity"> 1 <input type="hidden" value="1" name="quantity[]" min="1" class="form-control input-quantity text-center"></td>
											<td class="text-right unit-cost"><input type="text" data-id="${product_id}" onChange="monto_en_usd(this,${product_id})" name="unit_cost[]" class="form-control input-unit-cost text-right" value="${unit_cost}"></td>
											
											
											<td class="text-right sub-total"><input type="text" name="sub_total[]" class="form-control input-sub-total text-right" value="${sub_total}" readonly></td>
											

                                             <td class="text-right usd"><input disabled id="usd_monto-${product_id}" type="text" class="form-control input-usd text-right" ></td>
											<td>
                                            ${company} ${interno}
                                            </td>  
											
                                            
											<input type="hidden" name="autos[]" value="${showCar ? $('#product').prop('data-idCar') ? $('#product').prop('data-idCar') : '' : ''}">
</td>

											<td class="text-center">
												<button type="button" class="btn btn-danger btn-xs remove-product"><i class='fa fa-trash'></i></button>
											</td>
											<input type="hidden" name="product_id[]" value="${product_id}">
											<input type="hidden" name="product_tax[]" class="input-product-tax" value="0">
											
									</tr>`;
                    $("#order-table > tbody").append(product_row);
                    update_summary();

                    $("#product").val("").trigger('change');
                    $("#service").val("").trigger('change');
                    $('.selectpicker').selectpicker('render');			
								
							});
							
							//console.log(data);
					}
				});
		  
		  //interval = setInterval(habilitarBtn, 300);
		    return false; //for old browsers 	
	  }
		
	});



</script>
