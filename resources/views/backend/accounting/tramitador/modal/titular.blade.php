@php
    $checkpoinId = 1;
@endphp


<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('tramitadores.set.titular') }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}
    <input name="vehiculo_id" type="hidden" value="{{ $car->id }}">
    <div class="row">
      
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Datos del Titular') }} </label>
                <textarea required type="text" class="form-control" name="titular">{{$car->titular}}</textarea>
            </div>
        </div>
       
    </div>

    <div class="col-md-12 mt-2">
        <div class="form-group">
            {{-- <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button> --}}
            <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
        </div>
    </div>

</form>
