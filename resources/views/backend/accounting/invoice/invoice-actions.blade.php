<?php $amount_due = $invoice->grand_total - $paid; ?>
<input type="text" class="form-control mb-2" id="invoice_link_2" value="{{ url('client/view_invoice/'.md5($invoice->id)) }}" readOnly="true">
<div>
	<a class="btn btn-dark btn-xs" href="javascript:void(0);" id="copy_link"><i class="far fa-copy"></i> {{ _lang('Copy Inovice Link') }}</a>
	<a class="btn btn-primary btn-xs print" href="#" data-print="invoice-view"><i class="fas fa-print"></i> {{ _lang('Print') }}</a>
	<a class="btn btn-danger btn-xs" href="{{ url('invoices/download_pdf/'.encrypt($invoice->id)) }}"><i class="fas fa-file-pdf"></i> {{ _lang('Export PDF') }}</a>
	<a class="btn btn-secondary btn-xs ajax-modal" data-title="{{ _lang('Send Email') }}" href="{{ url('invoices/create_email/'.$invoice->id) }}"><i class="fas fa-envelope-open-text"></i> {{ _lang('Send Email') }}</a>
	{{-- @if($invoice->status == 'Unpaid' || $invoice->status == 'Partially_Paid' ) --}}
	@if($amount_due > 0 ) 
		<a class="btn btn-success btn-xs ajax-modal" data-title="{{ _lang('Receive Payment') }}" href="{{ url('invoices/create_payment/'.$invoice->id) }}"><i class="far fa-credit-card"></i> {{ _lang('Record a Payment') }}</a>
	@endif
	@if(auth()->user()->role->name == 'Cajera' || !auth()->user()->role_id )
	<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
		Anular
	</button>
	@endif
	<a class="btn btn-warning btn-xs" href="{{ action('InvoiceController@edit', $invoice->id) }}"><i class="fas fa-edit"></i> {{ _lang('Edit') }}</a>
	<a class="btn btn-primary btn-xs printb" href="#" data-print="invoice-view"><i class="fas fa-print"></i> {{ _lang('Linea blanca') }}</a>
</div>




<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">{{ _lang('Marcar como anulada') }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form action="{{ route('invoices.mark_as_cancelled',$invoice->id) }}" method="get">
			<div class="modal-body">



					<div class="col-md-12">
						<div class="form-group">
							<label class="control-label">¿Por que se anula la venta?</label>
							<textarea class="form-control" rows="4" name="note">{{ old('note') }}</textarea>
						</div>
					</div>


			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
				<button class="btn btn-info btn-xs btn-remove-2-cancelar" ><i class="fas
		fa-times"></i> {{ _lang('Mark As Cancelled') }}</button>
			</div>
			</form>
		</div>
	</div>
</div>
