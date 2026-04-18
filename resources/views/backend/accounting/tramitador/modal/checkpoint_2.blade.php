@php
    $checkpoinId = 2;
    $responsable_entregas = [
            1 => 'Asegurado',
            2 => 'Gestor Compañia',
            3 => 'Productor',
            4 => 'Compañia'
        ];
@endphp


<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('tramitadores.update') }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}
    <input name="vehiculo_id" type="hidden" value="{{ $vehiculo_id }}">
    <input name="checkpoint_id" type="hidden" value="{{ $checkpoint_id }}">
    <div class="row">

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Coordinar retiro') }} 
                    <input required type="checkbox" class="" {{ $car->coordinar_retiro ==1? 'checked':''}} name="coordinar_retiro" value="1">
                </label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha Solicitud de retiro') }} </label>
                <input required type="date" class="form-control" name="fecha_limite_retiro" value="{{$car->fecha_limite_retiro}}">
            </div>
        </div>

      
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Fecha de retiro') }} </label>
                @if (strtolower(auth()->user()->role->name) == 'retiros' )
                <input required type="date" class="form-control" name="fecha_limite_retiro" value="{{$car->fecha_limite_retiro}}">
                @else 
                <label class="control-label">{{$car->fecha_retiro}} </label>
                @endif            
            </div>
        </div>
      
        
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Observaciones') }} </label>
                <textarea type="text" class="form-control" name="observaciones">{{ old('observaciones', $checkpoint_vehiculo->observaciones) }}</textarea>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Estatus') }} </label>
                <select class="form-control" name="status">
                    <option {{ ($checkpoint_vehiculo->status == 'iniciado') ? "selected":''}} value="iniciado">Iniciado</option>
                    @if ($car->fecha_retiro) 
                    <option {{ ($checkpoint_vehiculo->status == 'completado') ? "selected":''}} value="completado">Completado</option>
                @endif
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
