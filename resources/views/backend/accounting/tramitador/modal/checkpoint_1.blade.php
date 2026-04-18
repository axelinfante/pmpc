@php
    $checkpoinId = 1;
@endphp


<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('tramitadores.update') }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}
    <input name="vehiculo_id" type="hidden" value="{{ $vehiculo_id }}">
    <input name="checkpoint_id" type="hidden" value="{{ $checkpoint_id }}">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha confirmacion contacto') }} </label>
                <input required type="date" class="form-control" name="fecha_confirmacion"
                    value="{{$car->fecha_confirmacion_contacto}}">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Asegurado') }} </label>
                <input required type="text" class="form-control" name="asegurado"
                    value="{{$car->asegurado}}">
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Contacto') }} </label>
                <input required type="text" class="form-control" name="contacto"
                    value="{{$car->contacto}}">
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Observaciones') }} </label>
                <textarea type="text" class="form-control" name="observaciones">{{$checkpoint_vehiculo->observaciones}}</textarea>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Estatus') }} </label>
                <select class="form-control" name="status">
                    <option {{ ($checkpoint_vehiculo->status == 'iniciado') ? "selected":''}} value="iniciado">Iniciado</option>
                    <option {{ ($checkpoint_vehiculo->status == 'completado') ? "selected":''}} value="completado">Completado</option>
                </select>
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
