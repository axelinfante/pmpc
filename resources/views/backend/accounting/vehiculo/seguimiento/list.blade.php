@php $date_format = get_company_option('date_format','Y-m-d'); @endphp
<form method="post" class="ajax-submit" action="{{route('storeSeguimiento')}}">
    @csrf
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <td>{{ _lang('FECHA ACTUALIZACION DE ESTADO') }}</td>
                    <td>
                        <input class="form-control" type="date" name="fecha_act_estado" value="{{!empty
                        ($car->seguimiento->fecha_act_estado) ? date
                        ($date_format,
                        strtotime
                ($car->seguimiento->fecha_act_estado)) : '' }}"></td>
                </tr>
                <input class="form-control" type="hidden" required name="idCar" value="{{$idCar}}">

                <tr>
                    <td>{{ _lang('Entra a desarme el
') }}</td>
                    <td><input class="form-control" type="date" name="entra_desarme" value="{{ $car->seguimiento->entra_desarme ?? ''
                    }}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('Vendedor de motor') }}</td>
                    <td>
                        {{
                        $vendedor ?? ''
                 }}</td>
                </tr>
                <tr>
                    <td>{{ _lang('Traslado
notificado el') }}</td>
                    <td>
                        <input class="form-control" type="date" name="traslado_notificado" value="{{!empty
                        ($car->seguimiento->traslado_notificado) ? date
                        ($date_format, strtotime
                ($car->seguimiento->traslado_notificado)) : '' }}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('TRAER A') }}</td>
                    <td>
                        <input class="form-control" type="text" name="traer_a" value="{{ $car->seguimiento->traer_a ?? ''}}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('TRASLADADO EL') }}</td>
                    <td>
                        <input class="form-control" type="date" name="fecha_traslado" value="{{!empty($car->seguimiento->fecha_traslado)
                        ? date($date_format, strtotime
                ($car->seguimiento->fecha_traslado)) : '' }}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('Ubicaciòn actual') }}</td>
                    <td>
                        <input class="form-control" type="text" name="ubicacion" value="{{ $car->seguimiento->ubicacion ?? '' }}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('ESTADO') }}</td>
                    <td>
                        <select class="form-control" name="estado" id="">
                            <option value="">Select</option>
                            @foreach($estados as $es)
                                <option {{ ($car->estado->id ?? '') == $es->id ? 'selected' : '' }}
                                        value="{{$es->id}}">{{$es->estado}}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>{{ _lang('Motor vendido o  reservado') }}</td>
                    <td>

                        <select class="form-control" name="motor_vendido_reservado" id="">
                            <option value="0">Seleciona</option>
                            <option {{isset($mVendido) ? 'selected=' : ''}} {{
                            ($car->seguimiento->motor_vendido_reservado ?? '') ==
                            1 ?
                            'selected' :''}}
                                    value="1">Vendido</option>
                            <option {{ ($car->seguimiento->motor_vendido_reservado ?? '') == 2 ? 'selected' :''}}
                                    value="2">Reservado</option>
                        </select>
                    </td>
                </tr>
                <input type="hidden" name="idSeguimiento" value="{{$car->seguimiento->id ?? ''}}">
            </table>

            <input type="submit" class="btn btn-primary" value="Guardar">
        </div>
    </div>
</form>
