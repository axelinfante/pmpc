@extends('layouts.app')
<style>
table.dataTable {
            table-layout: fixed !important;
            width: 100% !important;
        }
        table.dataTable td {
            white-space: normal !important;
            overflow-wrap: break-word !important;
            word-wrap: break-word !important;
        }
</style>

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card mt-2">
            <span class="panel-title d-none">{{ _lang('List Product') }}</span>
            <div class="card-body">
                <div class="row">

                     <div class="col mb-2">
                     <a class="btn btn-primary btn-xs" data-title="{{ _lang('Add Product') }}" href="{{ route('products.create') }}"><i
                     class="ti-plus"></i> {{ _lang('Add New') }}</a>
                     </div>
                </div>

                <hr>
                <!--<table id="table-data-product" class="table-bordered"> -->
				<div class="table-responsive">
				<table id="table-data-product" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID de producto</th>
                                <th class="text-right">{{ _lang('Fecha registro') }}</th>
                                <th class="text-right">{{ _lang('Fecha ingreso a stock') }}</th>
                                <th>{{ _lang('Nro Interno') }}</th>
                                <th>{{ _lang('Dominio') }}</th>
                                <th>{{ _lang('Product') }}</th>
                                <th>{{ _lang('Marca') }}</th>
                                <th>{{ _lang('Modelo') }}</th>
                                <th>{{ _lang('nº motor') }}</th>
                                <th>{{ _lang('nº oblea') }}</th>
                                <th style="width: 150px; min-width: 150px;" >{{ _lang('Deposito') }}</th>
                                <th>{{ _lang('Ubicacion') }}</th>
                                <th>{{ _lang('Descripcion') }}</th>
                                <th class="notexport">{{ _lang('Accciones disponibles') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
				</div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')

<script>
     var table; 
     
        $(function() {
              $('#table-data-product').css('width', '100%');
                table = $('#table-data-product').appTable({
					processing: true,
					serverSide: true,
                    title:"Productos Anulados",
                    ajax: "{{ url('products/anulados') }}", 
                    visibleButtonsFilter:true, 
                    visibleButtons: {
                        reset: true,
                        excel: true,
                        print: false
                    },
                    columnFilters: ['input', 'daterangepicker','input',,,,,,,,{type: 'select',data: @json($lugar_entregas)}], 
                    columns: [
                    { data: 'productsid', name: 'productsid'},
                    { data: 'created_at', name: 'created_at' },
                    { data: 'fecha_ingreso_a_stock', name: 'fecha_ingreso_a_stock' },
                    { data: 'interno', name: 'nro_interno' },
                    { data: 'dominio', name: 'dominio' },
                    { data: 'productItem', name: 'productItem' },
                    { data: 'marca', name: 'marca' },
                    { data: 'modelo', name: 'modelo' },
                    { data: 'nro_motor', name: 'nro_motor' },
                    { data: 'nro_oblea', name: 'nro_oblea' },
                    { data: 'deposito', name: 'deposito' },
                    { data: 'ubicacion', name: 'ubicacion' },
                    { data: 'description', name: 'description' },
                    { data: 'action', name: 'action', searchable: false, orderable: false}
                ],
                });
  });

/* {{-- 
             table = $('#table-data-product').DataTable({
                processing:true,
                serverSide:true,
                ajax: "{{ url('products/anulados') }}",
                width: "auto",
                columns: [
                    { data: 'id', name: 'id'},
                    { data: 'created_at', name: 'created_at' },
                    { data: 'fecha_ingreso_a_stock', name: 'fecha_ingreso_a_stock' },
                    { data: 'interno', name: 'nro_interno' },
                    { data: 'dominio', name: 'dominio' },
                    { data: 'productItem', name: 'productItem' },
                    { data: 'marca', name: 'marca' },
                    { data: 'modelo', name: 'modelo' },
                    { data: 'nro_motor', name: 'nro_motor' },
                    { data: 'nro_oblea', name: 'nro_oblea' },
                    { data: 'deposito', name: 'deposito' },
                    { data: 'ubicacion', name: 'ubicacion' },
                    { data: 'description', name: 'description' },
                    { data: 'action', name: 'action',  searchable: false, orderable: false}

                ],
            order: [
                [0, 'desc']
            ],
            dom: 'Bfrtip',
            orderCellsTop: true,
            buttons: [
                {
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
                    //$('#table-data-product input').val('').change();
                    //$('#table-data-product select').val('').change();
                    table.search('').columns().search('').draw();
                    $('.filtros').val('');
					$('.select-filter').val('').trigger('change');
                                }
                },
                {
                    extend: 'excelHtml5',
                    text: 'Exportar a Excel',
                    title: 'Lista de Productos anulados',
                    exportOptions: {
                        columns: ':visible'
                    }
                   ,action: newexportaction
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Exportar a PDF',
                    title: 'Lista de Productos', // Título del archivo PDF
                    orientation: 'landscape', // Orientación horizontal
                    pageSize: 'A4', // Tamaño del papel
                    exportOptions: {
                        columns: ':visible' // Exporta solo columnas visibles
                    },
                    action: newexportaction,
                    customize: function(doc) {
                        // Personaliza el diseño del PDF
                        doc.styles.tableHeader = {
                            bold: true,
                            fontSize: 10,
                            color: 'black',
                            fillColor: '#f2f2f2'
                        };
                        doc.defaultStyle.fontSize = 8; // Tamaño de fuente general
                    }
                }
             ],

            });*/ 


     /*       $('#table-data-product thead tr').clone().prependTo('#table-data-product thead');

                $('#table-data-product thead tr:eq(0) th').each(function(i) {
                var title = $(this).text();
                //$(this).html('<input type="text" placeholder="Search" />');
                if (i == 12) {
                    $(this).hide();
                }

               if(i == 1) {

                    $(this).html( '<input type="text" id="fecha_ingreso" name="fecha_ingreso" value="" class="form-control select-filter" placeholder="Search...'+title+'" />' );
            }else if(i == 10){

                $(this).html('<input type="checkbox" id="mostrar-todos-input">vacios <input id="input-text" style="width:100%;" type="text" placeholder="' + title + '" />');

                    let campoInput = $('#input-text');
                    
                    $("#mostrar-todos-input").change(function () {
                            $buscar= ($(this).is(':checked')) ? "todos":"";
                            if ($(this).is(':checked')) {
                                campoInput.hide();
                            } else {
                               campoInput.val(''); 
                               campoInput.show();
                            }

                            table
                            .column(i)
                            .search($buscar)
                            .draw();
                        });


                    $('#input-text', this).on('change', function() {
                        if (table.column(i).search() !== this.value) {
                            table
                            .column(i)
                            .search(this.value)
                            .draw();
                        }
                    });

            }else{

                $(this).html('<input class="filtros" style="width:100%;" type="text" placeholder="' + title + '" />');

                $('input', this).on('change', function() {
                    if (table.column(i).search() !== this.value) {
                        table
                        .column(i)
                        .search(this.value)
                        .draw();
                    }
                });

            }
         });

*/
     /*           
        $('#fecha_ingreso').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            }
        });

                $('#fecha_ingreso').on('change', function(e) {
            let val = $(this).val();
            table.columns(1).search(val ? val : '', true, false );
            table.draw();
        });
    
    
        $('#fecha_ingreso').on('apply.daterangepicker', function(ev, picker) {
                let daterango =(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                    $(this).val(daterango);
                    table.columns(1).search(daterango);
                    table.draw();
            });

            $('#fecha_ingreso').on('cancel.daterangepicker', function(ev, picker) {
                $('#fecha_ingreso').val(null).trigger('change');    
        });
*/

       /* function newexportaction(e, dt, button, config) {

           this.processing( true );
         var self = this;
         var oldStart = dt.settings()[0]._iDisplayStart;
         dt.one('preXhr', function (e, s, data) {
             // Just this once, load all data from the server...
             data.start = 0;
             //data.length = 2147483647;
             data.length = -1;
             dt.one('preDraw', function (e, settings) {
                 // Call the original action function
                 if (button[0].className.indexOf('buttons-copy') >= 0) {
                     $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
                 } else if (button[0].className.indexOf('buttons-excel') >= 0) {
                     $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
                         $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
                         $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
                 } else if (button[0].className.indexOf('buttons-csv') >= 0) {
                     $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
                         $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
                         $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
                 } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
                     $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
                         $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
                         $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
                 } else if (button[0].className.indexOf('buttons-print') >= 0) {
                     $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
                 }
                 dt.one('preXhr', function (e, s, data) {
                     // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                     // Set the property to what it was before exporting.
                     settings._iDisplayStart = oldStart;
                     data.start = oldStart;
                 });
                 // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
                 setTimeout(dt.ajax.reload, 0);
                 // Prevent rendering of the full data to the DOM
                 return false;
             });
         });
         // Requery the server with the new one-time export settings
         dt.ajax.reload();
         this.processing( false );
     }
*/

 /*    $('.dataTables_filter input')
    .unbind('keypress keyup input')
    .bind('change input', function (e) {
        if ($(this).val().length >= 3 && e.keyCode == 13) {
            table.search(this.value).draw();
        }
    });
--}}
*/

  async function toggleStock(btn) {
    const itemId = btn.dataset.id;
    const response = await fetch('{{ route("toggleStock") }}',{
        headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
        method: 'POST',
        body: JSON.stringify({ 
                id: itemId 
            })
    })

    
    const data = await response.json();
    //console.log(data);

    if (typeof table !== 'undefined' && table !== null) {
            table.ajax.reload(null, false);
        }
  }
    </script>
       
@endsection
