@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-12">

        <a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Create') }}" href="{{ route('lugarentrega.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>

        <div class="card mt-2">

            <div class="card-body">
                <table id="roles_table" class="table table-bordered data-table table-striped">
                    <thead>
                        <tr>
                            <th>{{ _lang('Id') }}</th>
                            <th>{{ _lang('Lugar de entregas') }}</th>
                            <th class="text-center notexport">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lugar_entregas as $m_m)
                        <tr data-id="row_{{ $m_m->id }}">
                            <td class='name'>{{ $m_m->id }}</td>
                            <td class='name'>{{ $m_m->nombre }}</td>
                            
                            <td class="text-center">
                                <div class="dropdown">
                                  <button class="btn btn-primary dropdown-toggle btn-xs" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                  {{ _lang('Action') }}
                                  <i class="fas fa-angle-down"></i>
                                  </button>
                                  <form action="{{ action('LugarEntregaController@destroy', $m_m['id']) }}" method="post">
                                    {{ csrf_field() }}
                                    <input name="_method" type="hidden" value="DELETE">
                                    
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a href="{{ action('LugarEntregaController@edit', $m_m['id']) }}" data-title="{{ _lang('Update') }}" class="dropdown-item ajax-modal"><i class="mdi mdi-pencil"></i> {{ _lang('Edit') }}</a>
                                        <a href="{{ action('LugarEntregaController@show', $m_m['id']) }}" data-title="{{ _lang('View') }}" class="dropdown-item ajax-modal"><i class="mdi mdi-eye"></i> {{ _lang('View') }}</a>
                                        <button class="btn-remove dropdown-item" type="submit"><i class="mdi mdi-delete"></i> {{ _lang('Delete') }}</button>
                                    </div>
                                  </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js-script')
<script>
    var table; 

    $(function() {
        $('#roles_table').css('width', '100%');
        
        table = $('#roles_table').DataTable({
            destroy: true, 
            responsive: true,
            pageLength: 10, 
            ordering: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Exportar a Excel',
                    title: 'Lugares de Entrega',
                    exportOptions: {
                        columns: ':not(.notexport)'
                    }
                }
            ]
        });
    });
</script>
@endsection