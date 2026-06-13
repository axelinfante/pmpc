	<form class="validate ajax-submit" autocomplete="off" action="{{ route('products_returns.update', $product_return->id) }}" method="POST">
    @csrf
  
    <div class="col-12">
    <div class="row">
        
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Product Name') }}</label>						
                <input type="text" class="form-control-plaintext" name="item_name" value="{{ '('.($product_return->producto->id ?? ''). ') ' .$product_return->producto->item->item_name ?? null }}" readonly style="font-weight: 500;">
            </div>
			 <input type="hidden" name="status" value="{{ $status }}">
			
        </div>
        
	<div class="col-md-12">
    <div class="form-group">
        <label class="control-label text-muted" style="font-weight: 600;">
            <i class="fas fa-history text-primary mr-1"></i> {{ _lang('Observaciones Previas') }}
        </label>
        <div class="p-3 bg-light rounded text-secondary border d-block" 
             style="min-height: 80px; max-height: 180px; overflow-y: auto; white-space: pre-line; font-size: 0.9rem; text-align: left; vertical-align: top; line-height: 1.4;">{{ trim($product_return->note) ?: _lang('Sin observaciones registradas.') }}</div>
    </div>
</div>
        
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Nuevas Observaciones / Piezas') }}</label>						
                <textarea class="form-control" name="note" placeholder="{{ _lang('Escriba las observaciones aquí...') }}" required></textarea>
            </div>
        </div> 

        <div class="col-md-12">
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ _lang('Update') }}</button>
            </div>
        </div>

    </div>
</div>
</form>