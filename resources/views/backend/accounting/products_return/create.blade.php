@extends('layouts.app')

@section('content')
    <link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <span class="d-none panel-title">{{ _lang('Crear Devolución de Producto') }}</span>

                <div class="card-body">
                    <form method="post" class="validate" autocomplete="off" action="{{ url('products_returns') }}"
                        enctype="multipart/form-data">

                        <div class="row">

                            {{ csrf_field() }}
                            <!--<div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">
                                        <input type="checkbox" id="manual" name="manual" value="1">
                                        {{ _lang('Manual') }}
                                    </label>
                                </div>
                            </div>-->
							   <input type="hidden" id="manual" name="manual" value="0">
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Return Date') }}</label>
                                    <input type="text" class="form-control datepicker" name="return_date"
                                        value="{{ old('return_date') }}" readOnly="true" required>
                                </div>
                            </div>
                           
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Facturas') }}</label>
                                    <select class="form-control select2" name="invoice_id" id="invoice_id" required>
                                        <option value="">{{ _lang('Select One') }}</option>
                                        @foreach ($invoices as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->invoice_number . ' ' . $item->client->contact_name }}</option>
                                        @endforeach
                                    </select>

                                    <input type="text" class="form-control" name="invoice_id_manual" id="invoice_id_manual"
                                        value="{{ old('invoice_id_manual') }}">

                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group select-product-container">
                                    <label class="control-label">{{ _lang('Select Product') }}</label>
                                    <select class="form-control select2" data-value="id" name="product_id[]" id="product_id" multiple="true">
                                        <option value="">{{ _lang('Select Product') }}</option>
                                    </select>

                                    <select class="form-control select2" name="product_id_manual" id="product_id_manual">
                                        <option value="">{{ _lang('Select Product') }}</option>
                                        @foreach ($products as $item)
                                            <option value="{{ $item->id }}">
                                                {{  $item->item_name }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>

                            <div class="col-md-2 d-none">
                                <div class="form-group select-qty-container">
                                    <label class="control-label">{{ _lang('Quantity') }}</label>
                                    <select class="form-control" name="qty" id="qty">
                                        <option valuew="">{{ _lang('Seleccionar Cantidad') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">{{ _lang('Note') }}</label>
                                    <textarea class="form-control" name="note">{{ old('note') }}</textarea>
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
@endsection

@section('js-script')
    <script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('public/backend/assets/js/products_return/create.js?v=1.2') }}"></script>
@endsection
