@extends('layouts.app')

@section('content')

<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">
<style>
.row-disabled {
   background-color: rgba(236, 240, 241, 0.5);
   pointer-events: none;
   width: 100%;
}
</style>
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
    $class = '';
	$rol = '';
    if(strtolower(auth()->user()->role->name) == 'despacho') {
        $class = 'd-none';
		$rol = 'despacho';
    }
	
	$anulada = '';
	if  ($invoice->status == 'Canceled'){
		$anulada = 'disabled';
	}
		
	
@endphp
<div class="row">
	<div class="col-12">
		<div class="card">
			<span class="d-none panel-title">{{ _lang('Update Invoice') }}</span>

			<div class="card-body">
				<form method="post" id="formId" class="validate" autocomplete="off" action="{{ action('InvoiceController@update', $id) }}" enctype="multipart/form-data">
					{{ csrf_field()}}
					<input name="_method" type="hidden" value="PATCH">				
					
					<div class="row">
						<div class="col-md-4 {{$class}}">
							<div class="form-group">
								<label class="control-label">{{ _lang('Invoice Number') }}</label>						
								<input type="text" class="form-control" name="invoice_number" value="{{ $invoice->invoice_number }}" required>
							</div>
						</div>
						{{-- <div class="col-md-3 {{$class}}">
							<label class="" for="companySelect">Empresa</label>
							<select name="company_id" class="form-control">

								{{ list_company_entrar($invoice->company_id) }}
							</select>
						</div> --}}
						{{-- <input type="hidden" name="company_id" id="company_id" value="{{ $invoice->company_id }}"> --}}


						{{-- @if($rol != auth()->user()->role_id) // se comenta a peticion de axel.. 
							<div class="col-lg-3 mb-2 {{$class}}">
								<label>{{ _lang('Vendedores') }}</label>
								<select class="form-control select2 select-filter" name="vendedor">
									<option value="">{{ _lang('Vendedores') }}</option>
									{{ create_option('users','id',['name','email'],$invoice->user_id,array('role_id=' => $rol)) }}
								</select>
							</div>
						@endif --}} 
						

						<div class="col-lg-3 mb-2 {{$class}}">
							<label>{{ _lang('Tipo de venta') }}</label>
							<select class="form-control  " required name="comision">
								<option  value="">Seleccione</option>
								<option {{ ($invoice->comision->tipo ?? null) == 'Camiones' ? 'selected' : ''  }}
										value="Camiones">{{ _lang
								('Camiones')
								}}</option>

								<option {{ ($invoice->comision->tipo ?? null) == 'Motos enteras' ? 'selected' : ''  }} value="Motos enteras">{{ _lang
								('Motos enteras')
								}}</option>

								<option {{ ($invoice->comision->tipo ?? null) == 'Ruedas' ? 'selected' : ''  }}
										value="Ruedas">{{ _lang
								('Ruedas')
								}}</option>

								<option {{ ($invoice->comision->tipo ?? null) == 'Motores'  ? 'selected' : '' }}
										value="Motores">{{ _lang
								('Motores')
								}}</option>

								<option {{ ($invoice->comision->tipo ?? null) == 'Mercado Libre'  ? 'selected' : '' }} value="Mercado Libre">{{ _lang('Mercado Libre') }}</option>

								<option {{ ($invoice->comision->tipo ?? null) == 'Reventa'  ? 'selected' : '' }}
									value="Reventa">{{ _lang
								('Reventa')
								}}</option>

								<option {{ ($invoice->comision->tipo ?? null) == 'Lote'  ? 'selected' : '' }}
									value="Lote">{{ _lang
								('Lote')
								}}</option>

								<option {{ ($invoice->comision->tipo ?? null) == 'Venta normal'  ? 'selected' : '' }}
										value="Venta normal">{{ _lang
								('Venta normal')
								}}</option>

<option {{ ($invoice->comision->tipo ?? null) == 'Venta menos a 30000
Venta menos a 30000'  ? 'selected' : '' }}
	value="Venta menos a 30000">{{ _lang('Venta menos a 30000')
}}</option>

								{{--<option {{ ($invoice->comision->porcentaje ?? null) == 7  ? 'selected' : '' }} value="7">{{ _lang('venta--}}
								{{--mayor--}}
								{{--30000')--}}
								{{--}}</option>--}}
								{{----}}
								{{--<option {{ isset($invoice->comision->isAdicional) ? 'selected' : ''   }} value="70">{{--}}
								{{--_lang--}}
								{{--('venta--}}
								{{--menor--}}
								{{--30000')--}}
								{{--}}</option>--}}

							</select>
						</div>

						<div class="col-md-3 py-4 {{$class}}">
							<div class="form-check">

								<input type="checkbox" id="facturar" name="facturar" {{$invoice->facturar == 1 ?
								'checked' : ''}}
								class="form-check-input"
									   value="1">
								<label class="form-check-label" for="facturar">Facturar</label>

							</div>
						</div>


						<div class="col-md-4 d-none">
							<div class="form-group">
								<label class="control-label">{{ _lang('Related To') }}</label>						
								<select class="form-control " data-selected="{{ $invoice->related_to }}" name="related_to" id="related_to">
								   <option selected value="contacts">{{ _lang('Customer') }}</option>
								   <option value="projects">{{ _lang('Project') }}</option>
								</select>
							</div>
						</div>

						<div class="col-md-4 {{$class}}  {{ $invoice->related_to == 'contacts' ? '' : 'd-none' }}" >
							<div class="form-group">
								<a href="{{ route('contacts.create') }}" data-reload="false" data-title="{{ _lang('Add Client') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
								<label class="control-label">{{ _lang('Select Client') }}</label>						
								<select class="form-control select2-ajax" data-value="id" data-display="contact_name" data-display2="dni_cuit"
										data-table="contacts" data-where="" name="client_id" id="client_id" required>
									<option value="">{{ _lang('Select One') }}</option>
									{{ create_option("contacts","id","contact_name", $invoice->related_id) }}
								</select>
							</div>
						</div>


						<div class="col-md-4 {{ $invoice->related_to == 'projects' ? '' : 'd-none' }}" id="projects">
							<div class="form-group">
								<label class="control-label">{{ _lang('Select Project') }}</label>						
								<select class="form-control select2" id="project_id" name="project_id">
								   <option value="">{{ _lang('Select One') }}</option>
								   {{ create_option('projects','id','name', $invoice->related_id, array('company_id=' => company_id())) }}
								</select>
							</div>
						</div>
				
						<div class="col-md-4 {{$class}}">
							<div class="form-group">
								<label class="control-label">{{ _lang('Invoice Date') }}</label>						
								<input type="text" class="form-control datepicker" name="invoice_date" value="{{ $invoice->invoice_date }}" required>
							</div>
						</div>
				
						<div class="col-md-4 d-none {{$class}}">
							<div class="form-group">
								<label class="control-label">{{ _lang('Due Date') }}</label>						
								<input type="text" class="form-control datepicker" name="due_date" value="{{ $invoice->due_date }}" required>
							</div>
						</div>
						
						<div class="col-md-4 d-none {{$class}}">
							<div class="form-group">
								<label class="control-label">{{ _lang('Invoice Template') }}</label>						
								<select class="form-control select2" name="template">
								   @foreach(get_invoice_templates() as $key => $value)
										<option value="{{ $key }}" {{ $invoice->template == $key ? 'selected' : '' }}>{{ $value }}</option>
								   @endforeach
								</select>
							</div>
						</div>

						@if(!$idCar || !$idProduct || true)

							<div class="col-md-6 {{$class}}">
								<div class="form-group select-product-container">

									<label class="control-label">{{ _lang('Productos en stock') }}</label>
									<select class="form-control select2-ajax" data-value="products.id"
											data-display="items.item_name"
											{{-- data-display2="marcas.marca"
											data-display3="modelos.modelo" --}}
											data-display2="IF(products.marca_modelo > 0, marcas.marca , 'Sin marca')" 
											data-display3="IF(products.marca_modelo > 0,modelos.modelo, 'Sin modelo')"
											data-table="products" data-where="9"  name="service" id="service">
										<option value="">{{ _lang('Select Product') }}</option>
										{{--<option value="{{$item->product->id}}">{{ $item->item_name}}</option>--}}

									</select>
								</div>
							</div>


						@endif

						<div class="col-md-6 py-4 {{$class}}">
							<div class="form-group">
								{{-- <label class="control-label">{{ _lang('¿Desarmar?') }}</label> --}}
								{{-- <select class="form-control "  name="desarmar" id="desarmar">
									<option {{$invoice->desarmar == 0 ? 'selected' : ''}} value="0">{{ _lang('No')
									}}</option>
									<option {{$invoice->desarmar == 1 ? 'selected' : ''}} value="1">{{ _lang('Desarme
									 completo')
									}}</option><option {{$invoice->desarmar == 2 ? 'selected' : ''}} value="2">{{
									_lang('Desarme parcial')
									}}</option>
								</select> --}}

								<div class="form-check">
									<input class="form-check-input" {{$invoice->desarmar == 0 ? 'checked' : ''}} name="desarmar" type="checkbox" value="0" id="flexCheckDefault">
									<label class="form-check-label" for="flexCheckDefault">
									  No desarmar
									</label>
								  </div>
							</div>
						</div>
						
						<div class="col-12 " style="background-color: #f1f5fa">
							<div class="row">
						{{-- @if($idCar || !$idProduct) --}}
							<div class="col-md-6 {{$class}}">
								<div class="form-group">
									{{--<a href="{{ route('vehiculo.create') }}" data-reload="false" data-title="{{ _lang('Add Supplier') --}}
									{{--}}" --}}
									{{--class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>--}}

									<label class="control-label">{{ _lang('Vehiculo') }}(Estados: BD / APTO / No Apto Autorizado)</label>
									<select class="form-control select2-ajax" data-value="cars.id" data-display="IF(cars.company_id = 1, CONCAT('PM-',cars.id), CONCAT('PC-',cars.id))"
											{{-- data-display2="marcas.marca" data-display3="modelos.modelo" --}}
											data-display2="IF(cars.idMarca_modelo > 0, marcas.marca , 'Sin marca')" data-display3="IF(cars.idMarca_modelo > 0,modelos.modelo, 'Sin modelo')"
											data-table="cars"
											data-where="11" name="car_id" id="car_id">
										<option value="">{{ _lang('- Select Car -') }}</option>
										@forelse($vehiculos as $v)
											<option {{old('car_id',$idCar ?? '') == $v->id? 'selected' :''}} value="{{
										$v->id}}">{{
										($v->marca_modelo->marca->marca ??
										 '').' '.
										($v->marca_modelo->modelo->modelo ?? '') .' '. $v->siniestro}}</option>
										@empty
										@endforelse
									</select>
								</div>
							</div>
						{{-- @endif --}}

						{{-- @if($idCar || !$idProduct) --}}
							<div class="col-md-6 {{$class}}">
								<div class="form-group select-product-container">
									<a id="productLink" href="{{ route('products.create') }}?idCar={{$idCar}}"
									   data-reload="false"
									   data-title="{{
								_lang
								('Add Product') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
									<label class="control-label">{{ _lang('Producto en vehiculo') }}</label>

									<select class="form-control" data-value="products.id" data-display="items.item_name"
											data-table="products" data-where="9" data-option = "{{isset($idCar) ? "products
										.car_id = $idCar": ''}}" name="product" id="product">
										<option value="">{{ _lang('Producto') }}</option>

									</select>
								</div>
							</div>
						{{-- @else
							<div class="col-md-6 {{$class}}">
								<div class="form-group select-product-container">

									<label class="control-label">{{ _lang('Select Product/Service') }}</label>
									<select class="form-control" data-value="products.id" data-display="item_name"
											data-table="items" data-where="2"  name="product" id="product">
										<option value="">{{ _lang('Select Product') }}</option>
										<option value="{{$item->product->id}}">{{ $item->item_name}}</option>

									</select>
								</div>
							</div> --}}
						{{-- @endif --}}

							</div>
						</div>

						

						

						<div class="col-md-6 {{$class}}">
							<div class="form-group">
								<label class="control-label">{{ _lang('Fecha de entrega') }}</label>
								<input type="date" class="form-control" name="fecha_entrega" value="{{$invoice->fecha_entrega}}">
							</div>
						</div>

						<div class="col-md-6 ">
							<div class="form-group">
								<label class="control-label">{{ _lang('Retiro') }}</label>
								<select class="form-control select2"  name="retiro" id="retiro">
									<option value="0">Seleccionar</option>
									<option {{$invoice->retiro == 1 ? 'selected' : ''}} value="1">{{ _lang
									('Retirado') }}</option>
									<option {{$invoice->retiro == 2 ? 'selected' : ''}} value="2">{{ _lang
									('Enviado') }}</option>
								</select>
							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">{{ _lang('Entregado a') }}</label>
								<input type="text" name="entregado_a" class="form-control" value="{{
								$invoice->entregado_a  }}">
							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">{{ _lang('Entregado por') }}</label>
								<select class="form-control select2 "  name="entregado_por"
										id="entregado_por">
										@if ($rol != 'despacho')
											<option value="">{{ _lang('Seleccionar') }}</option>
										@endif
									@foreach($users as $u)
										@if ($rol == 'despacho')
											@if(auth()->id() == $u->id) 
												<option  selected value="{{$u->id}}">{{ $u->name}}</option>
											@endif


											@else
												<option {{$u->id == $invoice->entregado_por ? 'selected' : ''}} value="{{$u->id}}">{{ $u->name}}</option>
										@endif
										
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">{{ _lang('Ubicación') }}</label>
								<input type="text" name="ubicacion" class="form-control" value="{{$invoice->ubicacion}}">
								
							</div>
						</div>

						
						<div class="col-md-6">
							<label class="control-label">{{ _lang('Guia') }}</label>
							<input type="file" class="form-control" id="imagen" name="imagen">
							@isset($invoice->guia)
							<div class="mt-3">
								<img class="img-fluid" src="{{asset('public/uploads/guia/'.$invoice->guia)}}" >
							</div>

							@endisset
						</div>


						<div class="col-md-3">
							<label class="" for="companySelect">Empresa</label>
							<select id="company_id_s" disabled class="form-control">
			
								{{ list_company_entrar() }}
							
							</select>
							<input type="hidden" name="company_id" id="company_id" value="{{ $invoice->company_id }}">
							
						</div>
						<div class="col-md-3 py-4">
							<div class="form-check">
			
								<input disabled type="checkbox" id="is_usd" @if($invoice->is_usd == 1) checked @endif name="is_usd" class="form-check-input" value="1">
								<label class="form-check-label" for="is_usd">Monto en usd</label>
			
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<label for="tasa_usd">Tasa Usd</label>
								<!--<input type="number" class="form-control" step="0.01" id="tasa_usd" name="tasa" value="{{ $invoice->tasa }}">-->
								<input readonly="readonly"  type="text" class="form-control" id="tasa_usd" name="tasa" value="{{ $invoice->tasa }}">
							</div>
							
						</div>
						<div class="col-md-12 {{ $class }}">
                            <button id="cancelar"
                                type="button"
                                class="btn btn-danger"
                                data-coreui-toggle="modal"
                                data-coreui-target="#exampleModal">
                                Devolver Productos
                            </button>
                        </div>
						<!--Order table -->
						@php $currency = currency(); @endphp
						
						@php $taxes = App\Tax::where("company_id",company_id())->get(); @endphp
						
						<div class="col-md-12 {{$class}}">
							<div class="table-responsive">
								<table id="order-table" class="table table-bordered">
									<thead>
										<tr>
										    <th><input type="checkbox" id="seleccionar-todos"></th>
											<th>{{ _lang('Name') }}</th>
											<th>{{ _lang('Description') }}</th>
											<th class="text-center wp-100">{{ _lang('Quantity') }}</th>
											<th class="text-right">{{ _lang('Unit Cost').' $/usd' }}</th>
											
											<th class="text-right">{{ _lang('Sub Total').' $/usd' }}</th>
											<th class="text-right">{{ _lang('USD convertidos').' '.$currency }}</th>
											<th class="text-right">Id Vehiculo</th>
											{{--	<th class="text-center">{{ _lang('Action') }}</th> --}}
										</tr>
									</thead>
									<tbody>
									<?php $q_anuladosr =(isset($anulados) && $anulados!="" ) ? explode(",", $anulados):array();	?>
										@foreach($invoice->invoice_items as $item)
										{{-- {{ dd($item->product->marcaModelo->marca->marca) }} --}}
											<tr <?php echo (in_array($item->product_id,$allReturnItemIds)) ? 'class="row-disabled"' :''; ?> id="product-{{ $item->product_id }}"  data-id="{{ $item->id }}">
											<td>@if(!in_array($item->product_id,$allReturnItemIds)) <input name="bank_check" type="checkbox" class="fila-seleccionada" data-id="{{ $item->id }}"> @endif </td>
												<td>
													<b>{{ $item->item->item_name }}
													@isset($item->product->marcaModelo)
														<b>{{ $item->item->item_name }} {{ $item->product->marcaModelo->marca->marca }} {{ $item->product->marcaModelo->modelo->modelo }}
														@else
															Sin marca Sin modelo
													@endisset
												</b><br>
													
												</td>
												<td class="description"><input type="text" name="product_description[]" class="form-control input-description" value="{{ $item->description }}"></td>

												<td class="text-center quantity">1 <input type="hidden" value="1" name="quantity[]" min="1" class="form-control input-quantity text-center" value="{{ $item->quantity }}" max="1"></td>

												{{-- @if($item->item->item_type == 'product')
												
												@else
												<td class="text-center quantity"><input type="number" name="quantity[]" min="1" class="form-control input-quantity text-center" value="{{ $item->quantity }}"></td>
												@endif --}}
												@php
													$usdConvertidos='';
													if($invoice->is_usd == 1){
														$usdConvertidos= ($item->unit_cost * $invoice->tasa); 
													}
												@endphp
												
												<td class="text-right unit-cost"><input type="text" name="unit_cost[]" data-id="{{ $item->product_id }}" onChange="monto_en_usd(this,{{ $item->product_id }})" class="form-control input-unit-cost text-right" value="{{ $item->unit_cost }}"></td>
												
												<td class="text-right sub-total"><input type="text" name="sub_total[]" class="form-control input-sub-total text-right" value="{{ $item->sub_total }}" readonly></td>


												<td class="usd"><input disabled id="usd_monto-{{ $item->product_id }}" type="text" class="form-control input-usd text-right" value = {{$usdConvertidos ?? ''}}></td>


												<td>
													{{ $item->company_id == 1 ? 'PM-' :  ''}}{{ $item->company_id == 2 ? 'PC-' : ''}}{{ $item->product->nro_interno }}
													<input type="hidden" name="autos[]" value="{{$item->product->nro_interno}}">
												</td>
												{{-- <td class="text-center">
													<button <?php echo (in_array($item->product_id,$q_anuladosr)) ? 'disabled' :''; ?> {{ $anulada }} type="button" class="btn btn-danger btn-xs remove-product"><i class='fa fa-trash'></i></button>
												</td> --}}
												<input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
												<input type="hidden" name="product_items_id[]" value="{{ $item->item_id }}">
												<input type="hidden" name="invoiceitem_id[]" value="{{ $item->id }}">
												<input type="hidden" name="product_tax[]" class="input-product-tax" value="{{ $item->tax_amount }}">
											</tr>
										@endforeach
									</tbody>
									<tfoot class="tfoot active">
										<tr>
											<th></th>
											<th>{{ _lang('Total') }}</th>
											<th></th>
											<th class="text-center" id="total-qty">0</th>
											
											<th class="text-right" id="total">0.00</th>
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
				
						<div class="col-md-12 {{$class}}">
							<div class="form-group">
								<label class="control-label">{{ _lang('Note') }}</label>						
								<textarea class="form-control" rows="4" name="note">{{ $invoice->note }}</textarea>
							</div>
						</div>

						<div class="col-lg-3 mb-2 {{$class}}">
							<label>{{ _lang('Revendedores') }}</label>
							<select class="form-control select2 select-filter" name="revendedor">
								<option value="">{{ _lang('Revendedores') }}</option>
								{{ create_option('users','id',['name','email'],$invoice->revendedor,array('role_id=' =>
								$rol_revendedor)) }}
							</select>
						</div>
				
						<div class="col-md-12">
							<div class="form-group">
								<button type="submit" {{ $anulada }} class="btn btn-primary">{{ _lang('Update') }}</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<select class="form-control d-none" id="tax-selector">
	@foreach($taxes as $tax)
		<option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}" data-tax-rate="{{ $tax->rate }}">{{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
	@endforeach
</select>
<div class="modal fade" id="modalProduct" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title" id="exampleModalLabel">Anular Item</h5>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
		  
		</div>
		<div class="modal-body">

		<form id="formProduct">
          <!--<div class="form-group">
            <label for="recipient-name" class="col-form-label">Recipient:</label>
			pendiente
			<select id="estado_prod" name="estado_prod" required class="form-control">
                    @forelse($estatus_anulado as $key => $value)
                        <option <?php echo ($key=="Item inventario") ? "selected": ""; ?> value="{{ $value }}">{{ $value }}</option>
                    @empty
                    @endforelse
        	</select>
          </div>-->
          <div class="form-group">
            <label for="message-text" class="col-form-label">Observaciones:</label>
            <textarea class="form-control" name="observacion-text" id="observacion-text"></textarea>
          </div>
		  <input name="idProd" id="idProd" type="hidden" value="">
		  <input name="iditems" id="iditems" type="hidden" value="">
		  <input name="estado_prod" id="estado_prod" type="hidden" value="pendiente">
		  <input name="id_coti" id="id_coti" type="hidden" value="{{ $id }}">
		  <input name="anular_cotizacion" id="anular_cotizacion" type="hidden" value="no">
        </form>
		  <!--<select class="form-control" id="estado_prod">
			<option value="quitar">Solo quitar</option>
			<option value="descompuesto">Descompuesto</option>
		  </select>-->
		</div>
		<div class="modal-footer">
		  <button type="button" id="product_eliminar_nw" class="btn btn-primary">Continuar</button>
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
	  </div>
	</div>
  </div>
@endsection
			
@section('js-script')
<script>
	var client_id = "{{ $invoice->client_id }}";
	var client_currency = "{{ $invoice->client->currency }}";
</script>

<script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/invoice/edit.js?v=1.3') }}"></script>
<script>


	let is_usd = $('#is_usd');

	if(is_usd.is(':checked')){
            $('#tasa_usd').prop('disabled',false);
        }else{
            $('#tasa_usd').prop('disabled',true);
        }

	is_usd.change(function () {
		if(is_usd.is(':checked')){
            $('#tasa_usd').prop('disabled',false);
        }else{
            $('#tasa_usd').prop('disabled',true);
        }
	})



    let car = $('#car_id');
    let product = $('#product');

	if($('#company_id').val() != '') {
                        // console.log(data.company);
                $('#service').data('company', $('#company_id').val());
                car.data('company', $('#company_id').val());
                //console.log(car.data('company'));
				$('#company_id_s').val( $('#company_id').val());
           
	}

    if(car.val()){

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

        //console.log(product.data('option'));
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
    }
    car.change(function() {

        product.prop('data-option','products.car_id = ' + $(this).val());
       // console.log(product.prop('data-option'));
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



    })
	
  $('#seleccionar-todos').on('click', function() {
    $('.fila-seleccionada').prop('checked', $(this).prop('checked'));
  });
  
   $('#cancelar').on('click', function(event) {
	  ///let nbchecked = $('#order-table input[name="bank_check"]:checked').length;
	  
	  var ids = $('#order-table input[name="bank_check"]:checked').map(function (i, chk) {
						return $(chk).data("id");
						}).get();
		let nbchecked= ids.length;
	  
	  let totalchecked = $('#order-table input[name="bank_check"]').length;

	  if (nbchecked == 0){
		  alert("Debe seleccionar Producto");
		  return false;
	  }
	  
	  $("#anular_cotizacion").val("no");
	  if (nbchecked == totalchecked){
		  let userConfirmed = confirm("Se esta devolviendo todos los productos la cotizacion sera anulada ?");

			if (!userConfirmed) {
				return false;
			} 
			$("#anular_cotizacion").val("si");
	  }

	  	event.stopPropagation();
		//let row = $(this).parent().parent();
		//let idProd = row.attr('id').split('-')[1];
		//let iditems = row.data("id");
		$("#idProd").val(ids);
		//$("#iditems").val(iditems);
		$('#modalProduct').modal('show');
	   
	   
  });
  
  $('#product_eliminar_nw').click(function(event) {
		event.preventDefault();
		var form = document.querySelector('#formProduct');
		//let ids = $('#idProd').val();
		var myformData = new FormData(form);        
			myformData.append('_token', $('meta[name="csrf-token"]').attr('content'));
		$.ajax({
			url:_url + '/invoices/comisiones_anulados',
			method: 'post',
			processData: false,
			contentType: false,
			cache: false,
			data: myformData,
			success:function(result)
				{
					//$('#product-' + id).remove();
					//update_summary();
					setTimeout(function(){
					//	$( "#target" ).trigger( "submit" );
					//$("#formId").submit();
					window.location.reload();
					}, 500);
					$('#modalProduct').modal('hide');
					return false;
				
				}
			});
	})
	
  
  	$(document).on('click', '.remove-product-directo',  function(event){
		event.stopPropagation();
		let row = $(this).parent().parent();
		row.remove();
	    update_summary();
	});

</script>
@endsection
				  
				  
				  
				  