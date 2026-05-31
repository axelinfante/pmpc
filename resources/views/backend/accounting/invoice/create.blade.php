@extends('layouts.app')

@section('content')
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
<input type="hidden" id="idProd" value="{{ $idProduct ?? '' }}">
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

			<!--/*<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">{{ _lang('Acciones') }}</label>
					<select class="form-control select2" multiple name="acciones[]" id="acciones">
						<option value="0">Seleccionar</option>
						<!--<option value="no_desarmar">{{ _lang('Desarmado') }}</option>
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
			</div>-->

			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label">{{ _lang('Acciones') }}</label>
					<select class="form-control select2" multiple name="acciones[]" id="acciones" data-placeholder="{{ _lang('Seleccionar acciones') }}">
						<option value=""></option> 
						
						<option value="flete">{{ _lang('Flete') }}</option>
						<option value="Despacho con guía">{{ _lang('Despacho con guía') }}</option>
						
						<option value="Retira en Penta">{{ _lang('Retira en Penta') }}</option>
						<option value="Retira en Octubre">{{ _lang('Retira en Octubre') }}</option>
						<option value="Retira en Tucson">{{ _lang('Retira en Tucson') }}</option>
						<option value="Retira en Constituyentes">{{ _lang('Retira en Constituyentes') }}</option>
						<option value="Retira Ventanita">{{ _lang('Retira Ventanita') }}</option>
						
						<option value="Enviar a Penta por Jumper">{{ _lang('Enviar a Penta por Jumper') }}</option>
						<option value="Enviar a Rosario">{{ _lang('Enviar a Rosario') }}</option>
						<option value="Mercado envío (c/Etiq) Jumper a correo">{{ _lang('Mercado envío (c/Etiq) Jumper a correo') }}</option>
						<option value="ML vía cargo (Jumper)">{{ _lang('ML vía cargo (Jumper)') }}</option>
						<option value="Moto flex. ML">{{ _lang('Moto flex. ML') }}</option>
						<option value="Colecta ziping">{{ _lang('Colecta ziping') }}</option>
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
				<select class="form-control select2-ajax" data-value="id" data-display="contact_name" data-display2="dni_cuit"
						data-table="contacts" data-where="101" name="client_id" id="client_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
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
						<!--<a id="productLink" href="{{ route('products.create') }}?idCar={{$idCar}}" data-reload="false"
						   data-title="{{
								_lang
								('Add Product') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>-->
			<a id="productLink_" href="{{ route('item.create') }}" class="select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>								
								<input type="hidden" name="desamar_item" id="desamar_item" value"">
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
						<select class="form-control select2-ajax" data-value="products.id"
								data-display="items.item_name"
								data-display2="IF(products.marca_modelo > 0, marcas.marca , 'Sin marca')" data-display3="IF(products.marca_modelo > 0,modelos.modelo, 'Sin modelo')"
								data-table="products" data-where="9"  name="service" id="service">
							<option value="">{{ _lang('Select Product') }}</option>
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

			<div class="col-md-3">
				<div class="form-group">
					<label for="tasa_usd">Tasa Usd</label>
					<input type="number" class="form-control" step="0.01" id="tasa_usd" name="tasa">
				</div>
				
			</div>

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group select-product-container">--}}
					{{--<a href="{{ route('products.create') }}" data-reload="false" data-title="{{ _lang('Add Product') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>--}}
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


 <div class="modal fade" id="itemCreateModal" tabindex="-1" aria-labelledby="itemCreateModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryCreateModalLabel">Crear Items</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
            </div>
            <form id="miFormulario" name="miFormulario" class="was-validated" action="{{ route('item.store') }}" method="post">
                @csrf
                <div class="modal-body">
				<div class="alert alert-danger print-error-msg" style="display:none">
					<ul></ul>
				</div>
				<div class="col-lg-12 mb-3">
						<label for="item_name" class="form-label">{{ _lang('Product Name') }}</label>
                        <input type="text" name="item_name" id="item_name" required class="form-control" value="{{old('item_name')}}">
                        @error('item_name')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
				</div>	
			  <input type="hidden" id="item_type" name="item_type" value="product" />
			  <input type="hidden" id="company_id" name="company_id" value="{{ company_id() }}" />
			  <input type="hidden" id="activo" name="activo" value="si" />
			</div>	
                <div class="modal-footer">
                    <button class="btn btn-primary"> Actualizar <i class="bi bi-check"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js-script')
<script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/invoice/create.js?v=1.4') }}"></script>

<script>
    let car = $('#car_id');
    let product = $('#product');

	let is_usd = $('#is_usd');

	$(document).ready(function() {
        $('#acciones').select2({
            allowClear: true,
            placeholder: $('#acciones').data('placeholder') 
        });
    });

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
		
		setTimeout(function() {
			$('#car_id').trigger('change');
		}, 2000); // Executes after 2 seconds
		
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

                    /*return {
                        results: data
                    };*/
                }
            }
        });



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
					myformData.append("estado_prod", 'desarme');

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

						$('#desamar_item').val("si");
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
	
	
	
$("#productLink_").click(function(e){
  e.preventDefault();
	$('#itemCreateModal').modal({show:true});
	return false;
  });			  
        
$('#miFormulario').submit(function(e) {
        e.preventDefault();
         
        var url = $(this).attr("action");
        let formData = new FormData(this);
		let select_display = $('#item_name_m').val();
    
        $.ajax({
                type:'POST',
                url: url,
                data: formData,
                contentType: false,
                processData: false,
                success: (json) => {
				if(json['result'] == "success"){
						var select_value = json['data'];
						var newOption = new Option(select_display, select_value, true, true);
						$('#item_id').append(newOption).trigger('change');
						$('#itemCreateModal').modal('hide');
					
				}else{
					$('#miFormulario').find(".print-error-msg").find("ul").html('');
                    $('#miFormulario').find(".print-error-msg").css('display','block');
                    $.each( json['message'], function( key, value ) {
					//	console.log(value);
                        $('#miFormulario').find(".print-error-msg").find("ul").append('<li>'+value+'</li>');
                    });
				  }
				},
                error: function(response){
                    $('#ajax-form').find(".print-error-msg").find("ul").html('');
                    $('#ajax-form').find(".print-error-msg").css('display','block');
                    $.each( response.responseJSON.errors, function( key, value ) {
                        $('#ajax-form').find(".print-error-msg").find("ul").append('<li>'+value+'</li>');
                    });
                }
           });
        
    });

  $('#itemCreateModal').on('hidden.bs.modal', function () {
    // Limpiar la validación al cerrar el modal
    $('#miFormulario').parsley().reset();
    // Limpiar los campos del formulario
    $('#item_name_m').val('');
  });




</script>
@endsection