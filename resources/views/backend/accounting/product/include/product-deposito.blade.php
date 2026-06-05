<div class="input-group">
    <select id="prod_depo_id-{{ $data->id }}" style="min-width: 120px;" required class="form-control select2">
	 @foreach ($lugar_entregas as $row)
                 <option {{ $row->id == $data->idDeposito ? 'selected' : '' }}   value="{{ $row->id }}">{{ $row->nombre }}</option>
    @endforeach
    </select>
    <div class="input-group-append">
        <button type="button" onClick="ActualizarDeposito({{$data->id}})" class="btn btn-warning">
           <i class="ti-check"></i>
        </button>
    </div>
</div>
