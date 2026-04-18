@extends('layouts.app')
@section('content')

{{-- @section('third_party_stylesheets') --}}
    <!--<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
	 <link rel="stylesheet" href="https://cdn.datatables.net/select/1.3.3/css/select.dataTables.min.css">-->
<style>
    .row-not-space {
        width: 110px;
    }
</style>
{{-- @endsection --}}
@section('content')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Registro de actividad</h1>

    <br>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla Registro de actividad
        </div>
        <div class="card-body">
            <table id="data-table" class="table table-striped">
          <thead class="thead-dark">
            <tr>
              <th scope="col">Model</th>
              <th scope="col">Accion</th>
              <th scope="col">Usuario</th>
              <th scope="col">Fecha</th>
              <th scope="col">Valores Anteriores</th>
              <th scope="col">Nuevos Valores</th>
            </tr>
          </thead>
          <tbody >
            
          </tbody>
        </table>

        </div>
    </div>

</div>
@endsection

@section('js-script')

    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/jszip-2.5.0/dt-1.10.24/b-1.7.0/b-html5-1.7.0/b-print-1.7.0/datatables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script>
    <script>
         var table = $('#data-table').DataTable({
			processing: true,
            serverSide: true,
            searchDelay: 2000,
            paging: true,
            orderCellsTop: true,
            fixedHeader: true,
			width: "auto",
			autoWidth: false,
            ajax: "{{ route('activityLog.index') }}",
            iDisplayLength: "25",
            dom: "<'row'<'col-md-3'l><'col-md-5 mb-2'B><'col-md-4 justify-content-end'f>>tr<'row'<'col-md-5'i><'col-md-7 mt-2'p>>",
            "buttons": [
                {extend: 'excel',text: '<i class="bi bi-file-earmark-excel-fill"></i> Excel',
				exportOptions: {columns: ':visible:not(.notexport)'}},
                {extend: 'csv',text: '<i class="bi bi-file-earmark-excel-fill"></i> CSV', exportOptions: {columns: ':visible:not(.notexport)'}},
                {extend: 'print',
                    text: '<i class="bi bi-printer-fill"></i> Print',
                    title: "Bancos",
					exportOptions: {columns: ':visible:not(.notexport)'},
                    customize: function (win) {
                        $(win.document.body).find('h1').css('font-size', '15pt');
                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('h1').css('margin-bottom', '20px');
                        $(win.document.body).css('margin', '35px 25px');
                    }
                },
            ],
            ordering: false,
			columns: [
                    { data: 'model', name: 'model' },
                    { data: 'event', name: 'event' },
					{ data: 'usuario', name: 'usuario' },
                   { data: 'created_at', name: 'created_at' },
                    { data: 'valores_ant', name: 'valores_ant' },
                    { data: 'valores_nue', name: 'valores_nue' },
                ],
                lengthMenu: [25, 50, 100]
        });
		
    </script>
@endsection