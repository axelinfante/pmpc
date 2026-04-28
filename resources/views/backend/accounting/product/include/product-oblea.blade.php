<div class="input-group d-flex justify-content-center">
    <input id="prod_id-{{ $data->id }}" style="min-width: 10px;max-width: 200px;" type="text" class="form-control" value="{{ $data->nro_oblea}}">
    <div class="input-group-append">
        <button type="button"  onClick="ActualizarOblea({{$data->id}})" class="btn btn-warning">
           <i class="ti-check"></i>
        </button>
    </div>
</div>
