@extends('layouts.public')

@section('content')
    <style type="text/css">
        @media all {
            .classic-table {
                width: 100%;
                color: #000;
            }

            .classic-table td {
                color: #000;
            }

            #invoice-item-table th,
            #invoice-item-table td {
                border: 1px solid #000;
            }

            #invoice-summary-table td {
                border: 1px solid #000 !important;
            }

            #invoice-payment-history-table {
                margin-bottom: 50px;
            }

            #invoice-payment-history-table th,
            #invoice-payment-history-table td {
                border: 1px solid #000 !important;
            }

            #invoice-view {
                padding: 15px;
            }

            .invoice-note {
                margin-bottom: 50px;
            }

            .table th {
                background-color: whitesmoke !important;
                color: #000;
            }

            .table td {
                color: #2d2d2d;
            }

            .base_color {
                background-color: whitesmoke !important;
            }

        }
    </style>

    <div class="row">
        <div class="col-12">

            @include('backend.client_panel.invoice_template.invoice_actions')

            <div class="btn-group mb-1">
                <a class="btn btn-primary btn-round print" href="#" data-print="invoice-view">
                    {{ _lang('Print Invoice') }}</a>
                <a class="btn btn-danger btn-round"
                    href="{{ url('invoices/download_pdf/' . encrypt($invoice->id)) }}">{{ _lang('PDF Invoice') }}</a>
            </div>

            <div class="card clearfix">

                @php $base_currency = get_company_field( $invoice->company_id, 'base_currency', 'USD' ); @endphp
                @php $date_format = get_company_field($invoice->company_id, 'date_format','Y-m-d'); @endphp
                @php $currency = currency($base_currency); @endphp

                @if ($invoice->related_to == 'contacts' && isset($invoice->client))
                    @php $client_currency = $invoice->client->currency; @endphp
                    @php $client = $invoice->client; @endphp
                @else
                    @php $client_currency = $invoice->project->client->currency; @endphp
                    @php $client = $invoice->project->client; @endphp
                @endif

                @php
                    $paid = 0;
                    foreach ($invoice->transaction as $pagos) {
                        if ($pagos->type == 'income') {
                            $paid = $paid + $pagos->base_amount;
                        }
                    }
                @endphp

                <span class="panel-title d-none">{{ _lang('View Invoice') }}</span>

                <div class="card-body">
                    <div id="invoice-view">
                        <div>
                            <table class="classic-table">
                                <tbody>
                                    <tr class="top">
                                        <td colspan="2">
                                            <table class="classic-table">
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <h3><b>{{ get_company_field($invoice->company_id, 'company_name') }}</b>
                                                            </h3>
                                                            {{ get_company_field($invoice->company_id, 'address') }}<br>
                                                            {{ get_company_field($invoice->company_id, 'email') }}<br>
                                                            {!! get_company_field($invoice->company_id, 'vat_id') != ''
                                                                ? _lang('VAT ID') . ': ' . clean(get_company_field($invoice->company_id, 'vat_id')) . '<br>'
                                                                : '' !!}
                                                            {!! get_company_field($invoice->company_id, 'reg_no') != ''
                                                                ? _lang('REG NO') . ': ' . clean(get_company_field($invoice->company_id, 'reg_no')) . '<br>'
                                                                : '' !!}
                                                        </td>
                                                        <td class="float-right">
                                                            <img src="{{ get_company_logo($invoice->company_id) }}"
                                                                class="wp-100">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>

                                    <tr class="information">
                                        <td colspan="2" class="pt-5">
                                            <div class="row">
                                                <div class="invoice-col-6 pt-3">
                                                    <h5><b>{{ _lang('Invoice To') }}</b></h5>
													{{ $client->dni_cuit}}<br>
                                                    {{ $client->contact_name }}<br>
                                                    {{ $client->contact_email }}<br>
                                                    {!! $client->company_name != '' ? clean($client->company_name) . '<br>' : '' !!}
                                                    {!! $client->address != '' ? clean($client->address) . '<br>' : '' !!}
                                                    {!! $client->vat_id != '' ? _lang('VAT ID') . ': ' . clean($client->vat_id) . '<br>' : '' !!}
                                                    {!! $client->reg_no != '' ? _lang('REG NO') . ': ' . clean($client->reg_no) . '<br>' : '' !!}

                                                </div>

                                                <!--Company Address-->
                                                <div class="invoice-col-6 pt-3">
                                                    <div class="d-inline-block float-md-right">
                                                        <!--<h5><b>{{ _lang('Invoice Details') }}</b></h5>-->

                                                        <b>{{ _lang('Cotización') }} #:</b> {{ $invoice->invoice_number }}<br>

                                                        <b>{{ _lang('Invoice Date') }}:</b>
														{{ date($date_format, strtotime($invoice->invoice_date)) }}<br>
														<b>{{ _lang('Vendedor') }} :</b> {{ $invoice->vendedor->name ?? '' }}<br>
                                                        <!--<b>{{ _lang('Due Date') }}:</b>
                                                        {{ date($date_format, strtotime($invoice->due_date)) }}<br>-->
                                                        <b>{{ _lang('Payment Status') }}:</b>
                                                        {{ _dlang(str_replace('_', ' ', $invoice->status)) }}<br>
														@if ($invoice->ubicacion)
                                                        <b>{{ _lang('Ubicación') }}:</b> {{ $invoice->ubicacion }}<br>
                                                    @endif
                                                    <b>NO INCLUYE IVA NI OTROS IMPUESTOS</b><br>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!--End Invoice Information-->

                        <!--Invoice Product-->
                        <div class="table-responsive">
                            <table class="table table-bordered mt-2" id="invoice-item-table">
                                <thead class="base_color">
                                    <tr>
										<th>Id_Producto / Nro Oblea</th>
										<th>{{ _lang('Name') }}</th>
                                        <th>{{ _lang('Marca y modelo') }}</th>
                                        <th>Nro interno Vehiculo</th>
                                        <th class="text-center wp-100">{{ _lang('Quantity') }}</th>
                                        <th class="text-right">{{ _lang('Unit Cost') }}</th>
                                        <th class="text-right wp-100">{{ _lang('Discount') }}</th>
                                        <th>{{ _lang('Tax') }}</th>
                                        <th class="text-right">{{ _lang('Line Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="invoice">
                                    @foreach ($invoice->invoice_items as $item)
                                        <tr id="product-{{ $item->item_id }}">
											<td>
                                                <b>{{ $item->product->id }}/{{ $item->product->nro_oblea }}</b>
                                            </td>
                                            <td>
                                                <b>{{ $item->item->item_name }}</b><br>{{ $item->description }}
                                            </td>
											<td><b>
                                                    @isset($item->product->marcaModelo)
                                                        {{ $item->product->marcaModelo->marca->marca ?? '' }}
                                                        {{ $item->product->marcaModelo->modelo->modelo ?? '' }}
                                                    @elseif (isset($item->product->vehiculo->marca_modelo))
                                                        {{ $item->product->vehiculo->marca_modelo->marca->marca ?? '' }}
                                                        {{ $item->product->vehiculo->marca_modelo->modelo->modelo ?? '' }}
                                                    @else
                                                        Sin marca Sin modelo
                                                    @endisset
                                                </b></td>
											<td class="text-center">
                                                @isset($item->product->vehiculo)
                                                     {{  nroInternoAlias($item->product->vehiculo->company_id,$item->product->vehiculo->tipo_vehiculo,$item->product->vehiculo->id) }}
                                                    <!-- {{ $item->product->vehiculo->company_id == 1 ? 'PM-' : 'PC-' }}{{ $item->product->vehiculo->id }} -->
                                                @else
                                                {{  nroInternoAlias($item->product->company_id,null,$item->product->nro_interno) }}
                                                @endisset
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-right">{{ decimalPlace($item->unit_cost, $currency) }}</td>
                                            <td class="text-right">{{ decimalPlace($item->discount, $currency) }}</td>
                                            <td>{!! clean(object_to_tax($item->taxes, 'name')) !!}</td>
                                            <td class="text-right">{{ decimalPlace($item->sub_total, $currency) }}</td>
                                        </tr>
										 @if ($invoice->note != '')
                                         {{-- <tr>
                                            <td colspan="2">
                                                <small>{{ $invoice->note }}</small>
                                            </td>
                                            <td colspan="4">
                                             </td>   
										</tr> --}}
                                           @endif  
										   
										   @if (in_array(($item->product->id ?? 0),$allReturnItemIds))
                                        <tr>
                                            <td colspan="2">
                                                <small>{{ "Producto Devuelto" }}</small>
                                            </td>
                                            <td colspan="4"></td>   
                                        </tr>
                                        @endif          
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--End Invoice Product-->

                        <!--Summary Table-->
                        <div class="invoice-summary-right">
                            <table class="table table-bordered" id="invoice-summary-table">
                                <tbody>
                                    <tr>
                                        <td><b>{{ _lang('Sub Total') }}</b></td>
                                        <td class="text-right">
                                            <b>{{ decimalPlace($invoice->grand_total - $invoice->tax_total, $currency) }}</b>
                                        </td>
                                    </tr>
                                    @foreach ($invoice_taxes as $tax)
                                        <tr>
                                            <td>{{ $tax->name }}</td>
                                            <td class="text-right">
                                                <span>{{ decimalPlace($tax->tax_amount, $currency) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td><b>{{ _lang('Grand Total') }}</b></td>
                                        <td class="text-right">
                                            <b>{{ decimalPlace($invoice->grand_total, $currency) }}</b>
                                            @if ($client_currency != $base_currency)
                                                <br><b>{{ decimalPlace($invoice->converted_total, currency($client_currency)) }}</b>
                                            @endif
                                        </td>
                                    </tr>
									<tr>
                                        <td>{{ _lang('Total Cobrado') }}</td>
                                        <td class="text-right">
                                            <span style="{{ $invoice->grand_total - $paid < 0 ? 'color: #ff0000; font-weight: bold;' : '' }}">
                                                {{ decimalPlace($paid, $currency) }}
                                            </span>

                                            @if ($client_currency != $base_currency)
                                                <br><span>{{ decimalPlace(convert_currency($base_currency, $client_currency, $paid), currency($client_currency)) }}</span>
                                            @endif
                                        </td>
                                    </tr>
									 @if (!$salesReturns->isEmpty())
                                        <tr>
                                        <td>{{ _lang('Devoluciónes') }}</td>
                                        <td class="text-right">
                                            <span>{{ decimalPlace($salesReturnstotal, $currency) }}</span>
                                        </td>
                                    </tr>   
                                    @endif
                                   @if ($invoice->status != 'Paid')
                                        <tr>
                                            <td>{{ _lang('Amount Due')}}</td>
                                            <td class="text-right">
                                                @php
                                                    $amount_due = $invoice->grand_total - $paid - ($salesReturnstotal ?? 0);
                                                @endphp
                                                <span style="{{ $amount_due < 0 ? 'color: #ff0000; font-weight: bold;' : '' }}">
                                                    {{ decimalPlace($amount_due, $currency) }}
                                                </span>
                                                @if ($client_currency != $base_currency)
                                                    <br>
                                                    @php
                                                        $converted_amount_due = convert_currency(
                                                            $base_currency,
                                                            $client_currency,
                                                            $amount_due,
                                                        );
                                                    @endphp
                                                    <span style="{{ $converted_amount_due < 0 ? 'color: #ff0000; font-weight: bold;' : '' }}">
                                                        {{ decimalPlace($converted_amount_due, currency($client_currency)) . "----" }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <!--End Summary Table-->

                        <div class="clearfix"></div>

                        @if (!$transactions->isEmpty())
                            <div class="table-responsive">
                                <table class="table table-bordered" id="invoice-payment-history-table">
                                    <thead class="base_color">
                                        <tr>
                                            <td colspan="5" class="text-center"><b>{{ _lang('Payment History') }}</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ _lang('Date') }}</th>
                                            <th>{{ _lang('Account') }}</th>
                                            <th class="text-right">{{ _lang('Amount') }}</th>
                                            <th class="text-right">{{ _lang('Tasa') }}</th>
                                            <th>{{ _lang('Payment Method') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $transaction)
                                            <tr id="transaction-{{ $transaction->id }}">
                                                <td>{{ date($date_format, strtotime($transaction->trans_date)) }}</td>
                                                <td>{{ $transaction->account->account_title . ' - ' . $transaction->account->account_currency }}
                                                </td>
                                                <td class="text-right">
                                                    {{ decimalPlace($transaction->amount, currency($transaction->account->account_currency)) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ decimalPlace($transaction->tasa, currency($transaction->account->account_currency)) }}
                                                </td>
                                                <td>{{ $transaction->payment_method->name == 'Abono cc'? 'Saldo a Favor Cliente': $transaction->payment_method->name}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        @if (!$salesReturns->isEmpty())
                            <div class="table-responsive">
                                <table class="table table-bordered" id="invoice-payment-history-table">
                                    <thead class="base_color">
                                        <tr>
                                            <td colspan="4" class="text-center"><b>{{ _lang('Historial devoluciones') }}</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ _lang('Nro') }}</th>
                                            <th>{{ _lang('Date') }}</th>
                                            <th>{{ _lang('Observaciones') }}</th>
                                            <th>{{ _lang('Productos') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @php $total=0; @endphp
                                        @foreach ($salesReturns as $dev)
                                            <tr id="transaction-{{ $dev->id }}">
                                                <td>{{ $dev->id }}</td>
                                                <td>{{ date($date_format, strtotime($dev->return_date)) }}</td>
                                                <td>{{ $dev->note }}
                                                </td>
                                                <td>
                                                    @php
                                                    $total+=$dev->grand_total;
                                                    $html = '';
                                                    if (!empty($dev->sales_return_items)) {
                                                        foreach ($dev->sales_return_items as $pieza) {
                                                            $html .=  '('. (($pieza->company_id == 1) ? 'PM-' : 'PC-') . $pieza->product_id . ") "   .  $pieza->product->item->item_name . '<br>';
                                                        }
                                                    }
                                                    echo $html;
                                                    @endphp
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!--Invoice Note-->
                        @if ($invoice->note != '')
                           {{-- <div>
                                <div class="invoice-note">{{ $invoice->note }}</div>
						   </div> --}}
                        @endif
                        <!--End Invoice Note-->

                        <!--Invoice Footer Text-->
                      
                            <div>
								<div class="invoice-note">Nota: Saldo parcial , solo valido con resumen de cuenta corriente</div>
							  @if (get_company_field($invoice->company_id, 'invoice_footer') != '')
                                <div class="invoice-note">{!! xss_clean(get_company_field($invoice->company_id, 'invoice_footer')) !!}</div>
							  @endif
                            </div>
                        
                        <!--End Invoice Note-->
                    </div>
                </div>
            </div>
        </div><!--End Classic Invoice Column-->
    </div><!--End Classic Invoice Row-->
@endsection
