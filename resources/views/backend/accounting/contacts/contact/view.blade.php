@extends('layouts.app')


@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="d-none panel-title">{{ _lang('View Contact') }}</div>

                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-lg-12 align-self-center">
                            <div class="contact-profile text-center">
                                <div class="contact-profile-image">
                                    <img src="{{ asset('public/uploads/contacts/' . $contact->contact_image ?? '') }}"
                                        alt="" class="thumb-contact rounded-circle">
                                </div>
                                <div class="contact-profile-detail">
                                    <h4 class="mt-2">{{ $contact->contact_name ?? '' }}</h4>
                                    <p class="mb-0">{{ $contact->group->name ?? ''}}</p>
                                </div>
                            </div>
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end card-body-->

                <div class="card-body p-3">
                    <ul class="nav flex-column nav-tabs settings-tab">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#general-info"
                                aria-expanded="true"><i class="far fa-user"></i> {{ _lang('General') }}</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#client-summary"
                                aria-expanded="false"><i class="fas fa-credit-card"></i>
                                {{ _lang('Resumen de Cuenta') }}</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#invoices" aria-expanded="false"><i
                                    class="fas fa-file-invoice-dollar"></i> {{ _lang('Cotizaciones') }}</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#quotations"
                                aria-expanded="false"><i class="fas fa-file-invoice"></i> {{ _lang('Reservas') }}</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#sales_return" aria-expanded="false"><i
                                    class="fas fa-file-invoice-dollar"></i> {{ _lang('Devolucion') }}</a></li>						
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ajuste_contables" aria-expanded="false"><i
                                    class="fas fa-file-invoice-dollar"></i> {{ _lang('Ajuste Saldos') }}</a></li>										
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#email" aria-expanded="false"><i
                                    class="far fa-envelope-open"></i> {{ _lang('Email') }}</a></li>
                    <li class="nav-item"><a class="nav-link"
                                href="{{ action('ContactController@edit', $contact['id']) }}"><i class="far fa-edit"></i>
                                {{ _lang('Edit') }}</a></li>
								
                        {{-- <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#projects" aria-expanded="false"><i class="fas fa-briefcase"></i> {{ _lang('Projects') }}</a></li> --}}
                        
                        {{-- <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#transactions"
                                aria-expanded="false"><i class="fas fa-credit-card"></i> {{ _lang('Transactions') }}</a>
                        </li> --}}

                        
						 	
                    </ul>
                </div><!--end card-body-->
            </div><!--end card-->
        </div><!--end col-->

        @php

            $currency = currency();
            $base_currency = base_currency();
            $date_format = get_company_option('date_format', 'Y-m-d');

        @endphp

        <div class="col-md-9">
            <div class="tab-content" id="crm-tab">

                <div id="general-info" class="tab-pane active">
                    <div class="card">
                        <div class="card-body">
						<div class="row">
						{{--	 <div class="col-lg-6 mb-3">
                                    <div class="card">
                                        <div class="seo-fact sbg1">
                                            <div class="p-4">
                                                <div class="seofct-icon">
                                                    <i class="ti-file"></i>
                                                    <span class="float-right">{{ _lang('Invoice Value') }}</span>
                                                </div>
                                                <h2 class="text-right">
                                                    {{ decimalPlace($invoice_value->grand_total, $currency) }}
                                                </h2>
                                            </div>
                                        </div>
                                    </div>
                                </div> 

                                <div class="col-lg-6 mb-3">
                                    <div class="card">
                                        <div class="seo-fact sbg2">
                                            <div class="p-4">
                                                <div class="seofct-icon">
                                                    <i class="ti-check-box"></i>
                                                    <span class="float-right">{{ _lang('Total Payment') }}</span>
                                                </div>
                                                <h2 class="text-right">
                                                    {{ decimalPlace($invoice_value->paid, $currency) }}
                                                </h2>
                                            </div>
                                        </div>
                                    </div>
                                </div> 

                                <div class="col-lg-6 mb-3">
                                    <div class="card">
                                        <div class="seo-fact sbg3">
                                            <div class="p-4">
                                                <div class="seofct-icon">
                                                    <i class="ti-info-alt"></i>
                                                    <span class="float-right">{{ _lang('Total Due') }}</span>
                                                </div>
                                                <h2 class="text-right">
                                                    {{ decimalPlace($invoice_due_amount->grand_total - $invoice_due_amount->paid, $currency) }}
                                                </h2>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
						--}}

                            </div> 

                            <table class="table table-striped">
                                <thead>
                                    <th colspan="2">
                                        <h5>{{ _lang('General Information') }}</h5>
                                    </th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ _lang('Profile Type') }}</td>
                                        <td><b>{{ _lang($contact->profile_type) }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Company Name') }}</td>
                                        <td><b>{{ $contact->company_name }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Contact Name') }}</td>
                                        <td><b>{{ $contact->contact_name }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Group') }}</td>
                                        <td><b>{{ $contact->group->name }}</b></td>
                                    </tr>

                                    {{-- <tr><td>{{ _lang('Reg No') }}</td><td><b>{{ $contact->reg_no }}</b></td></tr> --}}
                                    <tr>
                                        <td>{{ _lang('Contact Email') }}</td>
                                        <td><b>{{ $contact->contact_email }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Contact Phone') }}</td>
                                        <td><b>{{ $contact->contact_phone }}</b></td>
                                    </tr>
                                    {{-- <tr><td>{{ _lang('Country') }}</td><td><b>{{ $contact->country }}</b></td></tr> --}}

                                    <tr>
                                        <td>{{ _lang('City') }}</td>
                                        <td><b>{{ $contact->city }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('State') }}</td>
                                        <td><b>{{ $contact->state }}</b></td>
                                    </tr>

                                    {{-- <tr>
                                        <td>{{ _lang('Currency') }}</td>
                                        <td><b>{{ $contact->currency }} ({!! xss_clean(get_currency_symbol($contact->currency)) !!})</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Zip') }}</td>
                                        <td><b>{{ $contact->zip }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Address') }}</td>
                                        <td><b>{{ $contact->address }}</b></td>
                                    </tr>

                                    <tr>
                                        <td>{{ _lang('CUIT - DNI') }}</td>
                                        <td><b>{{ $contact->dni_cuit }}</b></td>
                                    </tr> --}}
                                    {{-- <tr><td>{{ _lang('Facebook') }}</td><td><b>{{ $contact->facebook }}</b></td></tr> --}}
                                    {{-- <tr><td>{{ _lang('Twitter') }}</td><td><b>{{ $contact->twitter }}</b></td></tr> --}}
                                    {{-- <tr><td>{{ _lang('Linkedin') }}</td><td><b>{{ $contact->linkedin }}</b></td></tr> --}}
                                    <tr>
                                        <td>{{ _lang('Remarks') }}</td>
                                        <td><b>{{ $contact->remarks }}</b></td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-striped">
                                <thead>
                                    <th colspan="2">
                                        <h5>Informacion de facturación</h5>
                                    </th>
                                </thead>
                                <tbody>


                                    <tr>
                                        <td>{{ _lang('Currency') }}</td>
                                        <td><b>{{ $contact->currency }} ({!! xss_clean(get_currency_symbol($contact->currency)) !!})</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Zip') }}</td>
                                        <td><b>{{ $contact->zip }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Address') }}</td>
                                        <td><b>{{ $contact->address }}</b></td>
                                    </tr>

                                    <tr>
                                        <td>{{ _lang('CUIT - DNI') }}</td>
                                        <td><b>{{ $contact->dni_cuit }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Estado de iva') }}</td>
                                        <td><b>{{ $contact->estadoIva }}</b></td>
                                    </tr>
                                    {{-- <tr><td>{{ _lang('Facebook') }}</td><td><b>{{ $contact->facebook }}</b></td></tr> --}}
                                    {{-- <tr><td>{{ _lang('Twitter') }}</td><td><b>{{ $contact->twitter }}</b></td></tr> --}}
                                    {{-- <tr><td>{{ _lang('Linkedin') }}</td><td><b>{{ $contact->linkedin }}</b></td></tr> --}}
                                    {{-- <tr>
                                        <td>{{ _lang('Remarks') }}</td>
                                        <td><b>{{ $contact->remarks }}</b></td>
                                    </tr> --}}
                                </tbody>
                            </table> 

                            <table class="table table-striped mt-4">
                                <thead>
                                    <th colspan="2">
                                        <h5>{{ _lang('Datos de envío') }}</h5>
                                    </th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ _lang('Nombre') }}</td>
                                        <td><b>{{ $contact->nombre_env }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Apellidos') }}</td>
                                        <td><b>{{ $contact->apellidos_env }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('DNI') }}</td>
                                        <td><b>{{ $contact->dni_env }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Dirección') }}</td>
                                        <td><b>{{ $contact->calle_env }} {{ $contact->numero_env }} {{ $contact->piso_env }} {{ $contact->depto_env }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('CP') }}</td>
                                        <td><b>{{ $contact->cp_env }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Localidad') }}</td>
                                        <td><b>{{ $contact->localidad_env }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Provincia') }}</td>
                                        <td><b>{{ $contact->pcia_env }}</b></td>
                                    </tr>
                                    <tr>
                                        <td>{{ _lang('Teléfono') }}</td>
                                        <td><b>{{ $contact->tel_env }}</b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="projects" class="tab-pane fade">
                    
                    
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-bordered data-table">
                                <thead>
                                    <tr>
                                        <th>{{ _lang('Name') }}</th>
                                        <th>{{ _lang('Start Date') }}</th>
                                        <th>{{ _lang('End Date') }}</th>
                                        <th>{{ _lang('Status') }}</th>
                                        <th>{{ _lang('Progress') }}</th>
                                        <th class="text-center">{{ _lang('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($contact->projects as $project)
                                        <tr>
                                            <td><a
                                                    href="{{ action('ProjectController@show', $project->id) }}">{{ $project->name }}</a>
                                            </td>
                                            <td>{{ date($date_format, strtotime($project->start_date)) }}</td>
                                            <td>{{ date($date_format, strtotime($project->end_date)) }}</td>
                                            <td>{!! clean(project_status($project->status)) !!}</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar"
                                                        style="width: {{ $project->progress }}%;"
                                                        aria-valuenow="{{ $project->progress }}" aria-valuemin="0"
                                                        aria-valuemax="100">{{ $project->progress }}%</div>
                                                </div>
                                            </td>
                                            <td>
                                                <form action="{{ action('ProjectController@destroy', $project['id']) }}"
                                                    class="text-center" method="post">
                                                    <a href="{{ action('ProjectController@show', $project['id']) }}"
                                                        class="btn btn-primary btn-xs"><i class="ti-eye"></i></a>
                                                    <a href="{{ action('ProjectController@edit', $project['id']) }}"
                                                        data-title="'. _lang('Update Project') .'"
                                                        class="btn btn-warning btn-xs ajax-modal"><i
                                                            class="ti-pencil"></i></a>
                                                    {{ csrf_field() }}
                                                    <input name="_method" type="hidden" value="DELETE">
                                                    <button class="btn btn-danger btn-xs btn-remove" type="submit"><i
                                                            class="ti-eraser"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="invoices" class="tab-pane fade">
                    <div class="card">
                        <div class="row">
                            {{-- <div class="col-lg-3 mb-2">
                                <label>{{ _lang('Invoice Number') }}</label>
                                <input type="text" class="form-control select-filter" name="invoice_number"
                                    id="invoice-number">
                            </div>
        
                            <div class="col-lg-3 mb-2">
                                <label>{{ _lang('Status') }}</label>
                                <select class="form-control select2 select-filter"
                                    data-placeholder="{{ _lang('Invoice Status') }}" name="status" multiple="true">
                                    <option value="Unpaid">{{ _lang('Unpaid') }}</option>
                                    <option value="Paid">{{ _lang('Paid') }}</option>
                                    <option value="Partially_Paid">{{ _lang('Partially Paid') }}</option>
                                    <option value="Canceled">{{ _lang('Canceled') }}</option>
                                </select>
                            </div> --}}

                            <div class="col-lg-3">
                                <label>{{ _lang('Filtrar por fecha') }}</label>
                                <input type="text" class="form-control select-filter" id="date_range"
                                    autocomplete="off" name="date_range">
                            </div>

                            {{-- <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-info" data-toggle="modal" data-target="#retirar">Retirar</button>
                            </div> --}}



                            

                        </div>
                        <div class="card-body">
                            <table id='invoice_table' class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th>
                                        <!-- Check para seleccionar todas las filas -->
                                        <th>{{ _lang('Cotización') }}</th>
                                        <th>{{ _lang('Fecha') }}</th>
										<th>{{ _lang('Observacion') }}</th> 
                                        <th class="text-right">{{ _lang('Importe') }}</th>
                                        <th class="text-right">{{ _lang('Cobrado') }}</th>
                                        <th class="text-right">{{ _lang('Devoluciones') }}</th>
                                        <th class="text-right">{{ _lang('Saldo') }}</th>
                                        <th class="text-center">{{ _lang('Status') }}</th>
                                        <th class="text-center">{{ _lang('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoices as $invoice)
                                        @php
											$total_saldo=0;
                                            $paid = 0;
											$retiro=0;
                                            foreach ($invoice->transaction as $pagos) {
                                                if ($pagos->type == 'income') {
                                                    $paid = $paid + $pagos->amount;
                                                }
                                            }
											foreach ($invoice->retiros as $retiros_cliente) {
                                                if ($retiros_cliente->type == 'expense' && $retiros_cliente->dr_cr == 'dr') {
                                                    $retiro = $retiro + $retiros_cliente->amount;
                                                }
                                            }
											
											foreach ($invoice->retiros_cliente_origen as $retiros_cliente_origen) {
                                                if ($retiros_cliente_origen->type == 'income' && $retiros_cliente_origen->dr_cr == 'cr') {
                                                    $retiro = $retiro + $retiros_cliente_origen->amount;
                                                }
                                            }
											
                                            $html="";
                                            $paid_dev = 0;
                                            $product_return_ = DB::select("select invoices.id,invoices.invoice_number,invoice_items.product_id,products_returns.product_id as productoid, invoice_items.sub_total from `invoices` inner join `invoice_items` on `invoice_items`.`invoice_id` = `invoices`.`id` left join `products_returns` on products_returns.invoice_id=invoices.id and  products_returns.product_id=invoice_items.product_id /*AND products_returns.status='procesada'*/ WHERE `invoices`.`related_to` = 'contacts' AND invoices.id IN ($invoice->id)
                                            GROUP BY invoices.id,invoices.invoice_number,invoice_items.product_id");
                                               
                                                    if (isset($product_return_)) {
                                                        //$html='Anulado</br>';
                                                        foreach ($product_return_  as $pieza) {
                                                            if (!is_null($pieza->productoid)){
                                                            $paid_dev=$paid_dev+$pieza->sub_total;
                                                            $html .= "*.-".$pieza->product_id . '</br>';
                                                            }
                                                        }
                                                        $html= ($html != "") ? "Anulado</br>$html":""; 
                                                    }
                                        @endphp
										@php $total_saldo = (($invoice->grand_total+$retiro) - ($paid+$paid_dev)); @endphp
                                        <tr id="row_{{ $invoice->id }}">
                                            <td><input type="checkbox" class="row-checkbox"></td>
                                            <!-- Checkbox por fila -->
                                            <td class='invoice_number'>
											{{ $invoice->company_id == 1 ? 'PM-': 'PC-' }}{{ $invoice->invoice_number }}</td>
                                            <td class='due_date'>
                                                {{ date($date_format, strtotime($invoice->invoice_date)) }}</td>
                                            <td >{!! $html !!}</td>
                                            <td class='grand_total text-right'>
                                                {{ decimalPlace($invoice->grand_total, $currency) }}</td>
                                            <td class='paid text-right'>{{ decimalPlace($paid, $currency) }}</td>
											<td class='paid text-right'>{{ decimalPlace($paid_dev, $currency) }}</td>
                                            <td class='paid text-right'
                                                style="{{ $total_saldo < 0 ? 'color: #ff0000; font-weight: bold;' : '' }}">
                                                {{ decimalPlace($total_saldo, $currency) }}</td>

                                            <td class='status text-center'>{!! ($total_saldo < 0) ? "<span class='badge badge-danger'>A favor cliente</span>" : strip_tags(invoice_status($invoice->status), '<span>') !!}</td>
                                            <td class="text-center">

                                                <div class="dropdown">
                                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                        data-toggle="dropdown">{{ _lang('Action') }}
                                                        <i class="fa fa-angle-down"></i></button>
                                                    <ul class="dropdown-menu">
													 @if ($invoice->status != 'Canceled' && $total_saldo > 0 )
                                                        <a class="dropdown-item"
                                                            href="{{ action('InvoiceController@edit', $invoice->id) }}"><i
                                                                class="fas fa-edit"></i> {{ _lang('Edit') }}</a>
                                                        <a class="dropdown-item ajax-modal"
                                                            href="{{ url('invoices/create_payment/' . $invoice->id) }}"
                                                            data-title="{{ _lang('Make Payment') }}"><i
                                                                class="fas fa-credit-card"></i>
                                                            {{ _lang('Make Payment') }}</a>		
														@endif
                                                        <a class="dropdown-item"
                                                            href="{{ action('InvoiceController@show', $invoice->id) }}"
                                                            data-title="{{ _lang('View Invoice') }}"
                                                            data-fullscreen="true"><i class="fas fa-eye"></i>
                                                            {{ _lang('View') }}</a>
                                                        <a class="dropdown-item ajax-modal"
                                                            href="{{ url('invoices/view_payment/' . $invoice->id) }}"
                                                            data-title="{{ _lang('View Payment') }}"
                                                            data-fullscreen="true"><i class="fas fa-credit-card"></i>
                                                            {{ _lang('View Payment') }}</a>
                                                        @if (auth()->user()->role->name == 'Gerencial' || auth()->user()->role->name == 'Cajera')
                                                            <form
                                                                action="{{ action('InvoiceController@destroy', $invoice['id']) }}"
                                                                method="post">
                                                                {{ csrf_field() }}
                                                                <input name="_method" type="hidden" value="DELETE">
                                                                <button class="button-link btn-remove" type="submit"><i
                                                                        class="fas fa-trash-alt"></i>
                                                                    {{ _lang('Delete') }}</button>
                                                            </form>
                                                        @endif

                                                    </ul>
                                                </div>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>

                <div id="quotations" class="tab-pane fade">
                    @php $currency = currency() @endphp
                    <div class="card">
                        <div class="card-body">
                            <table id='quotation_table' class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ _lang('No. Reserva') }}</th>
                                        <th>{{ _lang('Date') }}</th>
                                        <th>{{ _lang('Interno') }}</th>
                                        <th>{{ _lang('Detalle') }}</th>
                                        <th class="text-right">{{ _lang('Importe') }}</th>
                                        <th class="text-center">{{ _lang('Vendedor') }}</th>
                                        <th class="text-center">{{ _lang('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($quotations as $quotation)
                                        <tr id="row_{{ $quotation->id }}">
                                            <td class='invoice_number'>{{ $quotation->quotation_number }}</td>

                                            <td class='due_date'>
                                                {{ date($date_format, strtotime($quotation->quotation_date)) }}</td>
                                            <td class='Interno'>{{ $quotation->vehiculo->str_interno() }}</td>
                                            <td class='detalle'>
                                                @php $items = ''; @endphp
                                                @foreach ($quotation->quotation_items as $item)
                                                    @php $items.= $item->item->item_name . ';' @endphp
                                                @endforeach
                                                {{ $items }}
                                            </td>
                                            <td class='grand_total text-right'>
                                                {{ decimalPlace($quotation->grand_total, $currency) }}
                                            </td>
                                            <td class='vendedor'>
                                                {{ $quotation->vendedor->name }}
                                            </td>
                                            <td class="text-center">



                                                <div class="dropdown">
                                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                        data-toggle="dropdown">{{ _lang('Action') }}
                                                        <i class="fa fa-angle-down"></i></button>
                                                    <ul class="dropdown-menu">
                                                        <a class="dropdown-item"
                                                            href="{{ action('QuotationController@edit', $quotation->id) }}"><i
                                                                class="fas fa-edit"></i> {{ _lang('Edit') }}</a>
                                                        <a class="dropdown-item"
                                                            href="{{ action('QuotationController@show', $quotation->id) }}"
                                                            data-title="{{ _lang('View Invoice') }}"
                                                            data-fullscreen="true"><i class="fas fa-eye"></i>
                                                            {{ _lang('View') }}</a>
                                                        <a class="dropdown-item"
                                                            href="{{ action('QuotationController@convert_invoice', $quotation->id) }}"><i
                                                                class="fas fa-credit-card"></i>
                                                            {{ _lang('Convert to Invoice') }}</a>

                                                        <form
                                                            action="{{ action('QuotationController@destroy', $quotation->id) }}"
                                                            method="post">
                                                            {{ csrf_field() }}
                                                            <input name="_method" type="hidden" value="DELETE">
                                                            <button class="button-link btn-remove" type="submit"><i
                                                                    class="fas fa-trash-alt"></i>
                                                                {{ _lang('Delete') }}</button>
                                                        </form>

                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <div id="client-summary" class="tab-pane fade">
                    <div class="card">
                        <div class="row">
                            <div class="col-lg-3">
                                <label>{{ _lang('Filtrar por fecha') }}</label>
                                <input type="text" class="form-control select-filter" id="date_range"
                                    autocomplete="off" name="date_range">
                            </div>

                        </div>
                        <div class="card-body">
						{{-- <div class="my-2">
                            <a class="btn btn-outline-danger btn-xs ajax-modal" data-title="{{ _lang('Add Expense') }}" href="{{ route('expense.create') }}"><i class="ti-minus"></i>  {{ _lang('Retirar') }}</a>
						</div> --}}
                            <table id='summaries_table' class="table">
                                <thead>
                                    <tr>
									{{-- <th><input type="checkbox" id="select-all"></th> --}}
                                        <!-- Check para seleccionar todas las filas -->
                                        <th>{{ _lang('Fecha') }}</th>
                                        <th>{{ _lang('Tipo Movimiento') }}</th>
										<th>{{ _lang('Referencia') }}</th>
                                        <th>{{ _lang('Nota') }}</th>
                                        <th class="text-right">{{ _lang('Debe') }}</th>
                                        <th class="text-right">{{ _lang('Haber') }}</th>
                                        <th class="text-right">{{ _lang('Saldo') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="email" class="tab-pane fade">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ url('contacts/send_email/' . $contact->id) }}" class="validate"
                                method="post">
                                {{ csrf_field() }}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ _lang('Email Subject') }}</label>
                                        <input type="text" class="form-control" name="email_subject"
                                            value="{{ old('email_subject') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ _lang('Email Message') }} *</label>
                                        <textarea class="form-control summernote" name="email_message">{{ old('email_message') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit"
                                            class="btn btn-primary">{{ _lang('Send Email') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
				
				<div id="sales_return" class="tab-pane fade">
				{{-- @php $currency = currency() @endphp --}}
                    <div class="card">
                        <div class="card-body">
                            <table id='sales_return_table' class="table table-bordered">
                                <thead>
                                    <tr>
									 <th class="align-middle">Fecha</th>
                                     <th class="align-middle">No. Devolución</th>
									<th class="align-middle">Cotización</th>									 
						             <th class="align-middle">Descripción</th>
						             <th class="align-middle">Productos Devueltos</th>
						             <th class="text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
				
				
				<div id="ajuste_contables" class="tab-pane fade">
                    <div class="card">
                        <div class="card-body">
						
                            <table class="table table-bordered" id="data-ajuste-saldos">
                                <thead>
                                <tr>
                                    <th class="align-middle">Fecha</th>
									<th class="align-middle">Tipo</th>
                                    <th class="align-middle">Referencia</th>
						            <th class="align-middle">Descripción</th>
						            <th class="align-middle">Débito</th>
						            <th class="align-middle">Crédito</th>
						            <th class="align-middle">Saldo</th>
                                </tr>
                                </thead>
                                <tbody>
                               
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
				
				

            </div> <!--End TAB-->
        </div><!--End Col-->
    </div><!--End Row-->
@endsection


@section('js-script')
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>

    <script>
        (function($) {
            "use strict";

            $('.nav-tabs a').on('shown.bs.tab', function(event) {
                var tab = $(event.target).attr("href");
                var url = "{{ url('contacts/' . $contact->id) }}";
                history.pushState({}, null, url + "?tab=" + tab.substring(1));
            });

            @if (isset($_GET['tab']))
                $('.nav-tabs a[href="#{{ $_GET['tab'] }}"]').tab('show')
            @endif




        })(jQuery);
    </script>
    <script>
        $(document).ready(function() {
			
		let id = "{{ $contact['id'] }}";	
        var data_ajuste_saldos = $('#data-ajuste-saldos').DataTable({
			processing: true,
			serverSide: true,
            /*responsive: true,*/
            bAutoWidth: false,
			ajax: {
			url: "{{ route('contacts.movimiento_saldo') }}",
            method: "POST",
            data: function (d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                //if (id != null) {
                    d.id = id;
                //}

                
            },
            error: function (request, status, error) {  
                //console.log(request.responseText);
            }
			},
			"initComplete":function( settings, json){
				    if ( json.saldo_actual > 0 ) {
						//data_ajuste_saldos.button('2').enable(); // Habilita el primer botón (Editar)
						$('#retirar-button').prop('disabled', false);
					} else {
						//data_ajuste_saldos.button('2').disable(); // Deshabilita el primer botón
						$('#retirar-button').prop('disabled', true);
						//table.button('2-1').disable();
					}
            // call your function here
			},
            dom: "<'row'<'col-md-3'l><'col-md-5 mb-2'B><'col-md-4 justify-content-end'f>>tr<'row'<'col-md-5'i><'col-md-7 mt-2'p>>",
            "buttons": [
                {extend: 'excel',text: '<i class="bi bi-file-earmark-excel-fill"></i> Excel',
				title: "Ajuste de Saldos",
				},
                {extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> Print',
                    title: "Ajuste de Saldos",
                    exportOptions: {
                        columns: [ 0, 1, 2, 3, 4 ]
                    },
                    customize: function (win) {
                        $(win.document.body).find('h1').css('font-size', '15pt');
                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).css('margin', '35px 25px');
                    }
                },
				/*{
                    text: 'Retirar',
			        className: 'btn btn-xs ajax-modal',
					titleAttr: 'Add a new record',
					//enabled: false,
					init: function (dt, node, config) {
						$(node).attr('href', "{{ url('contacts/create_payment'.'/'.$contact['id']) }}/")
					},
					 attr: {
					title: "Retirar",
					id: "retirar-button"
					}
				}*/
            ],
            ordering: false,
			columns: [
            {data: 'date', name: 'date'},
			{data: 'movimiento', name: 'movimiento'},
            {data: 'referencia', name: 'referencia'},
			{data: 'note', name: 'note'},
            {data: 'debe', name: 'debe'},
            {data: 'haber', name: 'haber'},
            {data: 'saldo', name: 'saldo'},
            /*{data: 'action', name: 'action', orderable: false, searchable: false},*/
			],
        });
			
			
	 var sales_return_table = $('#sales_return_table').DataTable({
			processing: true,
			serverSide: true,
            /*responsive: true,*/
            bAutoWidth: false,
			ajax: {
			url: "{{ route('contacts.mov_devolucion_saldo') }}",
            method: "POST",
            data: function (d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                //if (id != null) {
                    d.id = id;
                //}

                
            },
            error: function (request, status, error) {  
                //console.log(request.responseText);
            }
			},
			"initComplete":function( settings, json){
				    if ( json.saldo_actual > 0 ) {
						$('#retirar-button').prop('disabled', false);
					} else {
						$('#retirar-button').prop('disabled', true);
					}
            // call your function here
			},
            dom: "<'row'<'col-md-3'l><'col-md-5 mb-2'B><'col-md-4 justify-content-end'f>>tr<'row'<'col-md-5'i><'col-md-7 mt-2'p>>",
            "buttons": [
                {extend: 'excel',text: '<i class="bi bi-file-earmark-excel-fill"></i> Excel',
				title: "Ajuste de Saldos",
				},
                {extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> Print',
                    title: "Ajuste de Saldos",
                    exportOptions: {
                        columns: [ 0, 1, 2, 3, 4 ]
                    },
                    customize: function (win) {
                        $(win.document.body).find('h1').css('font-size', '15pt');
                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).css('margin', '35px 25px');
                    }
                },
				/*{
                    text: 'Retirar',
			        className: 'btn btn-xs ajax-modal',
					titleAttr: 'Add a new record',
					//enabled: false,
					init: function (dt, node, config) {
						$(node).attr('href', "{{ url('contacts/create_payment'.'/'.$contact['id']) }}/")
					},
					 attr: {
					title: "Retirar",
					id: "retirar-button"
					}
				}*/
            ],
            ordering: false,
			columns: [
            {data: 'date', name: 'date'},
			{data: 'referencia', name: 'referencia'},
			{data: 'documento_id', name: 'documento_id'},
			{data: 'note', name: 'note'},
            {data: 'adicional', name: 'adicional'},
			{data: 'haber', name: 'haber'},
			],
        });

		
			

			
            var invoice_table = $('#invoice_table').DataTable({
                responsive: true,
                bAutoWidth: false,
                ordering: false,
                dom: "<'row'<'col-md-3'l><'col-md-5 mb-2'B><'col-md-4 justify-content-end'f>>tr<'row'<'col-md-5'i><'col-md-7 mt-2'p>>",
				lengthMenu: [[25, 50, 100, 250], [25, 50, 100, 250]],
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                        titleAttr: 'Exportar a Excel',
                        exportOptions: {
                            columns: ':not(:last-child)', // Excluir la última columna
                            modifier: {
                                selected: true // Exportar solo seleccionadas si hay alguna seleccionada
                            }
                        },
                        action: function(e, dt, button, config) {
                            // Verifica si hay filas seleccionadas
                            var selectedRows = dt.rows({
                                selected: true
                            }).count();
                            if (selectedRows === 0) {
                                // Si no hay filas seleccionadas, exportar todo
                                $.extend(config.exportOptions.modifier, {
                                    selected: null
                                });
                            }
                            // Ejecutar el botón estándar
                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button,
                                config);
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> Exportar a PDF',
                        titleAttr: 'Exportar a PDF',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':not(:last-child)', // Excluir la última columna
                            modifier: {
                                selected: true // Exportar solo seleccionadas si hay alguna seleccionada
                            }
                        },
                        action: function(e, dt, button, config) {
                            var selectedRows = dt.rows({
                                selected: true
                            }).count();
                            if (selectedRows === 0) {
                                $.extend(config.exportOptions.modifier, {
                                    selected: null
                                });
                            }
                            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button,
                                config);
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        titleAttr: 'Imprimir',
                        exportOptions: {
                            columns: ':not(:last-child)', // Excluir la última columna
                            modifier: {
                                selected: true // Exportar solo seleccionadas si hay alguna seleccionada
                            }
                        },
                        action: function(e, dt, button, config) {
                            var selectedRows = dt.rows({
                                selected: true
                            }).count();
                            if (selectedRows === 0) {
                                $.extend(config.exportOptions.modifier, {
                                    selected: null
                                });
                            }
                            $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button,
                                config);
                        }
                    }
                ],
                language: {
                    decimal: "",
                    emptyTable: $lang_no_data_found,
                    info: $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " +
                        $lang_entries + " ",
                    infoEmpty: $lang_showing_0_to_0_of_0_entries,
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    lengthMenu: $lang_show + " _MENU_ " + $lang_entries,
                    loadingRecords: $lang_loading,
                    processing: $lang_processing,
                    search: $lang_search,
                    zeroRecords: $lang_no_matching_records_found,
                    paginate: {
                        first: $lang_first,
                        last: $lang_last,
                        next: $lang_next,
                        previous: $lang_previous
                    }
                },
                select: {
                    style: 'multi', // Permitir selección múltiple
                    selector: '.row-checkbox' // Seleccionar solo al hacer clic en el checkbox
                },
                columnDefs: [{
                        orderable: false,
                        className: 'select-checkbox',
                        targets: 0 // Primera columna con los checkboxes
                    },
                    {
                        orderable: false,
                        targets: -1 // Desactivar orden en la última columna (acciones)
                    }
                ]
            });

            $('#select-all').on('click', function() {
                if (this.checked) {
                    invoice_table.rows().select();
                } else {
                    invoice_table.rows().deselect();
                }
            });
            $('#date_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD-MM-YYYY',
                    cancelLabel: 'Clear'
                }
            });

            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                    'DD-MM-YYYY'));

                minDate = picker.startDate.format('YYYY-MM-DD');
                maxDate = picker.endDate.format('YYYY-MM-DD');
                invoice_table.draw();
            });

            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                minDate = null;
                maxDate = null;
                invoice_table.draw();
            });

            var minDate = null;
            var maxDate = null;

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var dueDate = data[1];
                var formattedDate = moment(dueDate, 'DD-MM-YYYY').format('YYYY-MM-DD');

                if (!minDate && !maxDate) {
                    return true;
                }

                if (formattedDate >= minDate && formattedDate <= maxDate) {
                    return true;
                }

                return false;
            });

            var quotation_table = $('#quotation_table').DataTable({
                responsive: true,
                bAutoWidth: false,
                ordering: false,
				dom: "<'row'<'col-md-3'l><'col-md-5 mb-2'B><'col-md-4 justify-content-end'f>>tr<'row'<'col-md-5'i><'col-md-7 mt-2'p>>",
				lengthMenu: [[25, 50, 100, 250], [25, 50, 100, 250]],
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                        titleAttr: 'Exportar a Excel',
                        exportOptions: {
                            columns: ':not(:last-child)', // Excluir la última columna
                            modifier: {
                                selected: true // Exportar solo seleccionadas si hay alguna seleccionada
                            }
                        },
                        action: function(e, dt, button, config) {
                            // Verifica si hay filas seleccionadas
                            var selectedRows = dt.rows({
                                selected: true
                            }).count();
                            if (selectedRows === 0) {
                                // Si no hay filas seleccionadas, exportar todo
                                $.extend(config.exportOptions.modifier, {
                                    selected: null
                                });
                            }
                            // Ejecutar el botón estándar
                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button,
                                config);
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> Exportar a PDF',
                        titleAttr: 'Exportar a PDF',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':not(:last-child)', // Excluir la última columna
                            modifier: {
                                selected: true // Exportar solo seleccionadas si hay alguna seleccionada
                            }
                        },
                        action: function(e, dt, button, config) {
                            var selectedRows = dt.rows({
                                selected: true
                            }).count();
                            if (selectedRows === 0) {
                                $.extend(config.exportOptions.modifier, {
                                    selected: null
                                });
                            }
                            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button,
                                config);
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        titleAttr: 'Imprimir',
                        exportOptions: {
                            columns: ':not(:last-child)', // Excluir la última columna
                            modifier: {
                                selected: true // Exportar solo seleccionadas si hay alguna seleccionada
                            }
                        },
                        action: function(e, dt, button, config) {
                            var selectedRows = dt.rows({
                                selected: true
                            }).count();
                            if (selectedRows === 0) {
                                $.extend(config.exportOptions.modifier, {
                                    selected: null
                                });
                            }
                            $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button,
                                config);
                        }
                    }
                ],
                language: {
                    decimal: "",
                    emptyTable: $lang_no_data_found,
                    info: $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " +
                        $lang_entries + " ",
                    infoEmpty: $lang_showing_0_to_0_of_0_entries,
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    lengthMenu: $lang_show + " _MENU_ " + $lang_entries,
                    loadingRecords: $lang_loading,
                    processing: $lang_processing,
                    search: $lang_search,
                    zeroRecords: $lang_no_matching_records_found,
                    paginate: {
                        first: $lang_first,
                        last: $lang_last,
                        next: $lang_next,
                        previous: $lang_previous
                    }
                },
                select: {
                    style: 'multi', // Permitir selección múltiple
                    selector: '.row-checkbox' // Seleccionar solo al hacer clic en el checkbox
                },
                columnDefs: [{
                        orderable: false,
                        className: 'select-checkbox',
                        targets: 0 // Primera columna con los checkboxes
                    },
                    {
                        orderable: false,
                        targets: -1 // Desactivar orden en la última columna (acciones)
                    }
                ]
            });
			
			
			
			var summaries_table = $('#summaries_table').DataTable({
			processing: true,
			serverSide: true,
            /*responsive: true,*/
            bAutoWidth: false,
			lengthMenu: [[25, 50, 100, 250], [25, 50, 100, 250]],
			ajax: {
			url: "{{ route('contacts.mov_resumen_saldo') }}",
            method: "POST",
            data: function (d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                //if (id != null) {
                    d.id = id;
                //}

                
            },
            error: function (request, status, error) {  
                //console.log(request.responseText);
            }
			},
			"initComplete":function( settings, json){
				    if ( json.saldo_actual < 0 ) {
						$('#retirar-button').prop('disabled', false);
					} else {
						$('#retirar-button').prop('disabled', true);
					}
            // call your function here
			},
            dom: "<'row'<'col-md-3'l><'col-md-5 mb-2'B><'col-md-4 justify-content-end'f>>tr<'row'<'col-md-5'i><'col-md-7 mt-2'p>>",
            "buttons": [
                {extend: 'excel',text: '<i class="bi bi-file-earmark-excel-fill"></i> Excel',
				title: "Ajuste de Saldos",
				},
                {extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> Print',
                    title: "Ajuste de Saldos",
                    exportOptions: {
                        columns: [ 0, 1, 2, 3, 4 ]
                    },
                    customize: function (win) {
                        $(win.document.body).find('h1').css('font-size', '15pt');
                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).css('margin', '35px 25px');
                    }
                },
				/*{
                    text: 'Retirar',
			        className: 'btn btn-xs ajax-modal',
					titleAttr: 'Add a new record',
					//enabled: false,
					init: function (dt, node, config) {
						$(node).attr('href', "{{ url('contacts/create_payment'.'/'.$contact['id']) }}/")
					},
					 attr: {
						title: "Retirar",
						id: "retirar-button",
						"data-title": "Retiro de Saldo",
					}
				},*/
				/*{
                    text: 'Abonar Saldo a deudas',
			        className: 'btn ajax-modal',
					titleAttr: 'Add a new record',
					//enabled: false,
					init: function (dt, node, config) {
						$(node).attr('href', "{{ url('contacts/create_payment'.'/'.$contact['id']) }}/")
					},
					 attr: {
					title: "abono a deudas",
					id: "retirar-button"
					}
				}*/
            ],
            ordering: false,
			columns: [
            {data: 'date', name: 'date'},
			{data: 'movimiento', name: 'movimiento'},			
			{data: 'referencia', name: 'referencia'},
			{data: 'note', name: 'note'},
			{data: 'debe', name: 'debe'},
			{data: 'haber', name: 'haber'},
			{data: 'saldo', name: 'saldo'},
			//{data: 'documento_id', name: 'documento_id'},
            //{data: 'adicional', name: 'adicional'},
			
			],
        });

            function FormatNumber(number, numberOfDigits = 2) {
        try {
            return new Intl.NumberFormat('es-ES', {minimumFractionDigits: 2}).format(parseFloat(number).toFixed(numberOfDigits));
        } catch (error) {
            return 0;
        }
    }


    function newexportaction(e, dt, button, config) {

this.processing( true );
var self = this;
var oldStart = dt.settings()[0]._iDisplayStart;
dt.one('preXhr', function (e, s, data) {
  // Just this once, load all data from the server...
  data.start = 0;
  //data.length = 2147483647;
  data.length = -1;
  dt.one('preDraw', function (e, settings) {
      // Call the original action function
      if (button[0].className.indexOf('buttons-copy') >= 0) {
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-excel') >= 0) {
          $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-csv') >= 0) {
          $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
          $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-print') >= 0) {
          $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
      }
      dt.one('preXhr', function (e, s, data) {
          // DataTables thinks the first item displayed is index 0, but we're not drawing that.
          // Set the property to what it was before exporting.
          settings._iDisplayStart = oldStart;
          data.start = oldStart;
      });
      // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
      setTimeout(dt.ajax.reload, 0);
      // Prevent rendering of the full data to the DOM
      return false;
  });
});
// Requery the server with the new one-time export settings
dt.ajax.reload();
this.processing( false );
}
         
        });
    </script>


@endsection
