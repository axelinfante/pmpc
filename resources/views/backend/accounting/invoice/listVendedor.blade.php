@extends('layouts.app')

@section('content')
    <style>
        #vehiculo th {
            width: 100%;
            min-width: 80px
        }

        #piezas th {
            width: 100%;
            min-width: 80px
        }
    </style>
    <div class="row">
        <div class="col-lg-12">

            <div class="card mt-2">
                <span class="panel-title d-none">{{ _lang('Buscar Piezas') }}</span>
                <div class="card-body">

                    {{-- <form action="{{route('buscador_de_piezas')}}"> --}}
						{{-- <div id="parametros" class="row">

                        <!-- Marca -->
                        <div class="col-md-2 col-sm-6 px-2">
                            <label class="control-label">{{ _lang('Marca') }}</label>
                            <select class="form-control select-filter Select2" data-value="id" data-display="marca"
                                data-table="marcas" data-where="" id="marca">
                                <option value="">{{ _lang('Select One') }}</option>
                                @forelse($marcas as $marca)
                                    <option value="{{ $marca->id }}">{{ $marca->marca }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>

                        <!-- Marca manual -->
                        <div class="col-md-2 col-sm-6 px-2">
                            <label class="control-label">{{ _lang('Marca manual') }}</label>
                            <input class="form-control select-filter" id="marca-input" name="marca-input">
                        </div>

                        <!-- Modelo manual -->
                        <div class="col-md-2 col-sm-6 px-2">
                            <label class="control-label">{{ _lang('Modelo manual') }}</label>
                            <input class="form-control select-filter" id="modelo-input" name="modelo-input">
                        </div>

                        <!-- Pieza -->
                        <div class="col-md-2 col-sm-6 px-2">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Pieza') }}</label>
                                <input type="text" name="pieza" id="pieza" class="form-control select-filter">
                            </div>
                        </div>

                        <!-- Motor -->
                        <div class="col-md-2 col-sm-6 px-2">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Motor') }}</label>
                                <input type="text" name="motor" id="motor" class="form-control select-filter">
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-2 col-sm-6 px-2">
                            <div class="form-group">
                                <label  class="control-label">{{ _lang('Estado') }}</label>
                                <select class="form-control select-filter Select2" name="estado" id="estado">
                                    <option value="">{{ _lang('Select One') }}</option>
                                     @forelse($estados as $estado)
                                        <option value="{{ $estado->id }}">{{ $estado->estado }}</option>
                                    @empty
                                @endforelse
                                </select>
                            </div>
                        </div>

                        <!-- Nro Interno -->
                        <div class="col-md-2 col-sm-6 px-2">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Nro Interno') }}</label>
                                <input type="number" name="id_car" id="id_car" class="form-control select-filter">
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6 px-2 pt-4">
                            <div class="form-group">
                                <button id="limpiar" class="btn btn-success">Limpiar</button>
                            </div>
                        </div>

						</div>
						--}}


                    {{-- </form> --}}


                    <hr>

                    <div class="row">
                        <div class="col-12" style="background-color: #faff7e">


                            <div class="card mt-2">
                                <span class="panel-title d-none">{{ _lang('List Product') }}</span>


                                <div class="card-body">
                                    <div class="col-md-12 text-right">
                                        <div class="form-group">
                                            <button id="abrir-drive" class="btn btn-primary"
                                                onclick="abrirPreciosReferencia()">Ver precios de referencia</button>
                                        </div>
                                    </div>

                                    <h3 class="my-3">Piezas</h3>
                                    <table id="piezas" name="piezas" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID de producto</th>
                                                <th>{{ _lang('Nro Interno') }}</th>
                                                <th>{{ _lang('Product') }}</th>
                                                <th class="text-right">{{ _lang('Marca') }}</th>
                                                <th class="text-right">{{ _lang('Modelo') }}</th>
                                                <th class="text-right">{{ _lang('nº motor') }}</th>
                                                <th class="text-right">{{ _lang('nº oblea') }}</th>
                                                <th class="text-right">{{ _lang('Deposito') }}</th>
                                                <th class="text-right">{{ _lang('Ubicacion') }}</th>
                                                <th class="text-right">{{ _lang('Detalle') }}</th>
                                                <th class="text-center">{{ _lang('Action') }}</th>
                                                <th class="text-center">Lote</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            {{-- @php $currency = currency(); @endphp
                                    @forelse($products as $product)
                                        @php
                                            if($product->company_id == 1) {
                                      $in = 'PM-';
                                  }else if($product->company_id == 2){
                                      $in = 'PC-';
                                  }
                                        @endphp
                                        <tr id="row_{{ $product->id }}">
                                            <td class=''>{{ $in.$product->id }}</td>
                                            <td class=''>{{ $in.$product->nro_interno }} </td>
                                            <td class='item_id'>{{ $product->item->item_name }}</td>
                                            <td class='product_cost text-right'>{{ ($product->marcaModelo->marca->marca ?? '') .' ' .
                            ($product->marcaModelo->modelo->modelo ?? '')
                            }}</td>

<td>{{ $product->nro_motor }}</td>
<td>{{ $product->nro_oblea }}</td>
<td>{{ $product->idDeposito }}</td>
<td>{{ $product->ubicacion }}</td>
                                            <td class='product_stock text-center'>
                                                @forelse($product->category as $c)
                                                    <div>
                                                        <h5>{{ $c->categoria->nombre }}
                                                            @if (!empty($c->categoria->color))
                                                                <span class=""><i
                                                                            style="color:{{$c->categoria->color}}" class="fa
                                        fa-square"></i></span>
                                                            @endif
                                                        </h5>

                                                    </div>

                                                @empty
                                                @endforelse
                                            </td>

                                            <td class="text-center">

                                                <a href="{{action('InvoiceController@create', ['idProduct' =>
                                                $product->id])
                                                }}" class="btn btn-primary
btn-xs " target="_blank" data-title=" '._lang ( 'Venta') .'"><i class="ti-shopping-cart-full"></i></a>
                                                    <a href="{{action('ProductController@show', $product['id'])}}" data-title="{{ _lang('View Product') }}" class="btn btn-primary btn-xs ajax-modal"><i class="ti-eye"></i></a>

                                            </td>
                                        </tr>
                                        @empty

                                    @endforelse --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
					<hr>
					<div class="row">
                        <div class="col-12" style="background-color: #ffa5a5">


                            <div class="card mt-2">
                                <div class="card-body">

                                    <h3 class="my-3">Vehiculos</h3>
									<table id="vehiculo" class="table table-bordered ">
                        <thead>
                            <tr>
                                <th>{{ _lang('Nro interno') }}</th>


                                <th>{{ _lang('Marca') }}</th>
                                <th>{{ _lang('Modelo') }}</th>
                                <th>{{ _lang('Estado') }}</th>
                                <th>{{ _lang('Motor en marcha') }}</th>
                                <th>{{ _lang('Fecha entrega 04') }}</th>
                                <th>{{ _lang('Motor') }}</th>
                                <th>{{ _lang('Dominio') }}</th>
                                <th>{{ _lang('Tipo de baja') }}</th>

                                <th>{{ _lang('Deposito') }}</th>
                                {{-- <th >{{ _lang('Provincia') }}</th> --}}


                                {{--                            <th >{{ _lang('En cotizaciones') }}</th> --}}
                                <th>{{ _lang('Piezas ausentes') }}</th>
                                <th>{{ _lang('Vendidas') }}</th>
                                <th>{{ _lang('Piezas Reservadas') }}</th>
                                <th>{{ _lang('Piezas Defectuosas') }}</th>
                                <th>{{ _lang('kilometraje') }}</th>

                                <th class="text-center">{{ _lang('Action') }}</th>
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
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    {{-- <script src="{{ asset('public/backend/assets/js/ajax-datatable/vehiculo.js?v=1.0.0') }}"></script> --}}
    <script>
        const routes = {
            exportExcel: "{{ route('piezas.export.excel') }}",
            exportPDF: "{{ route('vehiculo-expense.export.pdf') }}",
            csrfToken: "{{ csrf_token() }}"
        };

       var Estados_tables = <?php echo json_encode($estados); ?>;
	   var lugarentregas_tables = <?php echo json_encode($lugar_entregas); ?>;

        $(document).ready(function() {
            $('#vehiculo thead tr')
                .clone(true)
                // .addClass('filters')
                .appendTo('#vehiculo thead');


            $('#vehiculo thead tr:eq(1) th').each(function(i) {
                var title = $(this).text(); //Obtenemos el nombre de la columna
               if (i == 14) {
                    $(this).hide();
                    return;
                }
				
				 

                if (i == 3) {
					/*var select = $('<select id="tmp' + title + '" multiple="true" class="form-control selectestado Select2"></select>')
				.appendTo( $(this).empty() )
				.on( 'change', function () {
                            $(this).select2('close');
                             var vals = $('option:selected', this).map(function (index, element) {
                        	return $.fn.dataTable.util.escapeRegex($(element).val());
                        }).toArray().join(',');
                            vehiculo_table.column(i).search(vals.length > 0 ? vals : '', true, false).draw();
							//vehiculo_table.column(i).search(val ? val : '', false, false).draw();
                              
                               //vehiculo_table.column(i).search( val ? '^'+val+'$' : '', true, false ).draw();

				} );

						for (const row_x of Estados_tables) {
								//console.log(row_x.id+"-"+row_x.estado);
								select.append( '<option value="'+row_x.id+'">'+row_x.estado+'</option>' )
						}*/
						
				var select = $('<select id="tmp' + title + '" multiple="true" class="form-control selectestado select2"></select>')
				.appendTo( $(this).empty() )
				.on( 'change', function () {
					  		var val = $(this).val();
							vehiculo_table.column( i ).search(val ? val : '', false, false).draw();
				} );
				select.append( '<option value="-1">VACIOS</option>' );
				for (const row_x of Estados_tables) {
					select.append( '<option value="'+row_x.id+'">'+row_x.estado+'</option>' )
				}
                return;
			}
			
			if (i == 9) {
						var select = $('<input style="width:120px;" type="checkbox" id="mostrar-todos-deposito1">vacios <select id="Deposito_file1" multiple="true" class="form-control select2"></select>')
				.appendTo( $(this).empty() )
				.on( 'change', function () {
					  		var val = $(this).val();
							vehiculo_table.column( i ).search(val ? val : '', false, false).draw();
				} );
			//	select.append( '<option value="-1">VACIOS</option>' );
				for (const row_x of lugarentregas_tables) {
					select.append( '<option value="'+row_x.id+'">'+row_x.nombre+'</option>' )
				}
				let campoInput = $('#Deposito_file1');
				
				$('#mostrar-todos-deposito1').change(function () {
                          let buscar= ($(this).is(':checked')) ? "-1":"";
                            if ($(this).is(':checked')) {
                                campoInput.next().hide();
                            } else {
                               campoInput.next().show();
                            }

                            vehiculo_table
                            .column(i)
                            .search(buscar)
                            .draw();
                        });
				return;			
			}
			
			
			
			 if (i == 11 || i == 12) { 
			 
			 $(this).html('<input style="width: 300px" type="text" class="form-control filtrov" />');
                // Agregamos un evento a los controles de búsqueda para que se dispare cuando el usuario escriba o cambie el valor
                $('input', this).on('change', function() {
                    // Si el valor del control de búsqueda es diferente al valor de búsqueda actual de la columna
                    if (vehiculo_table.column(i).search() !== this.value) {
                        vehiculo_table
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
			 
			 
			 return;
			 }

                $(this).html('<input style="width: 100px" type="text" class="form-control filtrov"  />');
                // Agregamos un evento a los controles de búsqueda para que se dispare cuando el usuario escriba o cambie el valor
                $('input', this).on('change', function() {
                    // Si el valor del control de búsqueda es diferente al valor de búsqueda actual de la columna
                    if (vehiculo_table.column(i).search() !== this.value) {
                        vehiculo_table
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            });
            var vehiculo_table = $('#vehiculo').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                orderCellsTop: true,
                fixedHeader: true,
                searching: true,
                ajax: ({
                    url: _url + '/invoices/get_table_autos_buscador',
                    method: "POST",
                    data: function(d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');

                        if ($('#marca-input').val() != '') {
                            d.marcaInput = $('#marca-input').val();
                        }

                        if ($('#modelo-input').val() != '') {
                            d.modeloInput = $('#modelo-input').val();
                        }

                        if ($('#marca').val() != '') {
                            d.marca = $('#marca').val();
                        }

                        if ($('#modelo').val() != '') {
                            d.modelo = $('#modelo').val();
                        }

                        if ($('#pieza').val() != '') {
                            d.pieza = $('#pieza').val();
                        }

                        if ($('#motor').val() != '') {
                            d.motor = $('#motor').val();
                            // console.log($('#motor').val());
                        }
                        // console.log($('#id_car').val())

                        if ($('#id_car').val() != '') {
                            d.car_id = $('#id_car').val();
                        }

                        if ($('select[name=status]').val() != null) {
                            d.status = JSON.stringify($('select[name=status]').val());
                        }

                        if ($('#estado').val() != '') {
                            d.estado = $('#estado').val();
                        }

                    },
                    error: function(request, status, error) {
                      //  console.log(request.responseText);
                    }
                }),
                "columns": [

                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'marca',
                        name: 'marca'
                    },
                    {
                        data: 'modelo',
                        name: 'modelo'
                    },
                    {
                        data: 'estado',
                        name: 'estado',
                        searchable: true
                    },

                    {
                        data: 'motor_en_marcha',
                        name: 'motor_en_marcha'
                    },


                    {
                        data: 'fecha_entrega_asegurado_cia',
                        name: 'fecha_entrega_asegurado_cia',
                        searchable: false,  // corrigiendo typo: "searcheable" -> "searchable"
                        render: function(data, type, row) {
                            if (data) {
                                // Convertir la fecha al formato DD-MM-YYYY
                                let date = new Date(data);
                                let day = String(date.getDate()).padStart(2, '0');
                                let month = String(date.getMonth() + 1).padStart(2, '0'); // +1 porque los meses van de 0-11
                                let year = date.getFullYear();
                                return `${day}-${month}-${year}`;
                            }
                            return data; // Si no hay dato, retorna el valor original
                        }
                    },



                    {
                        data: 'motor_nro',
                        name: 'motor_nro'
                    },
                    {
                        data: 'dominio',
                        name: 'dominio'
                    },

                    {
                        data: 'tipo_baja',
                        name: 'tipo_baja',
                        searcheable: false
                    },
                    {
                        data: 'localidad',
                        name: 'localidad'
                    },




                    {
                        data: 'pieza_no_disponible',
                        name: 'pieza_no_disponible',
                        searcheable: false
                    },
                    {
                        data: 'pieza_vendidas',
                        name: 'pieza_vendidas',
                        searcheable: false
                    },
                    { 
                        data: 'pieza_reservadas', 
                        name: 'pieza_reservadas', 
                        searcheable: false 
                    },
                    {
                        data: 'piezas_defectuosas',
                        name: 'piezas_defectuosas'
                    },
                    {
                        data: 'kilometraje',
                        name: 'kilometraje'
                    },
                    {
                        data: "action",
                        name: "action",
                        searcheable: false
                    },
                    // {  data : 'fecha_ingreso', name : 'fecha_ingreso' },


                ],
                // "dom": 'lrtip',
                // responsive: true,
                "dom": 'Bfrtip',
                buttons: [
                   {
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
                        $('.filtrov').val('');
                        $("#tmpEstado").select2('destroy').val("").select2();
                        //vehiculo_table.search('');
                        vehiculo_table.search('').columns().search('').draw();
                       	//$('.selectestado').val('').trigger('change');
                      }
                    },
                'excelHtml5'
                    ],
                "bStateSave": true,
                "bAutoWidth": false,
                "ordering": false,
                "searching": true,
                "language": {
                    "decimal": "",
                    "emptyTable": $lang_no_data_found,
                    "info": $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " +
                        $lang_entries,
                    "infoEmpty": $lang_showing_0_to_0_of_0_entries,
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": $lang_show + " _MENU_ " + $lang_entries,
                    "loadingRecords": $lang_loading,
                    "processing": $lang_processing,
                    "search": $lang_search,
                    "zeroRecords": $lang_no_matching_records_found,
                    "paginate": {
                        "first": $lang_first,
                        "last": $lang_last,
                        "next": $lang_next,
                        "previous": $lang_previous
                    }
                }
            }).on('init.dt', function() {
                $('[data-toggle="tooltip"]').tooltip();
            });

            $('.select-filter').on('change', function(e) {
                vehiculo_table.draw();
                pieza_table.draw();
            });


            //vehiculo_table.search('').columns().search('').draw();





            //let marca = $('#marca');
            //let modelo = $('#modelo');
            //let marca_modelo = $('#marca_modelo');

            // marca.change(function() {
            //     modelo.html(`<option value="">{{ _lang('Select One') }}</option>`);
            //     $.ajax({
            //         url: "{{ route('modelosByMarca') . '/' }}" + marca.val(),
            //         dataType: 'json',
            //         success: function(res) {
            //             console.log(res);
            //             let html = `<option value="">{{ _lang('Select One') }}</option>`;
            //             res.map(r => {
            //                 html +=
            //                     `<option value="${r.idModelo}">${r.modelo.modelo}</option>`;
            //             })


            //             modelo.html(html);
            //             //modelo.select2();

            //         }

            //     })
            // });



            $('#limpiar').on('click', function(e) {

                e.preventDefault();
                $("#marca").select2('destroy').val("").select2();
                $("#estado").select2('destroy').val("").select2();
                $('.filtrov').val('');
                $('.filtros').val('');
                $('.select-filter').val('');
                $("#tmpEstado").select2('destroy').val("").select2();
                pieza_table.search('').columns().search('').draw();
                vehiculo_table.search('').columns().search('').draw();
                /*
                pieza_table.search('');
                vehiculo_table.search('');
                //$("#estado").select2('destroy').val("").select2();
                $('#estado').val('').trigger('change');*/

            });



            


            // $('#vehiculo thead tr')
            //     .clone(true)
            //     .addClass('filters')
            //     .appendTo('#vehiculo thead');
            //
            /*$('#piezas thead tr')
                .clone(true)
                .addClass('filters')
                .appendTo('#piezas thead');*/

            $('#piezas thead tr').clone().addClass('filters').prependTo('#piezas thead');
            $('#piezas thead tr:eq(0) th').each(function(i) {
            //$('#piezas thead tr:eq(1) th').each(function(i) {
                var title = $(this).text();
                if (i != 10 && i != 11) {
					
					if (i == 7) {
						var select = $('<input style="width:120px;" type="checkbox" id="mostrar-todos-deposito">vacios <select id="Deposito_file" multiple="true" class="form-control select2"></select>')
				.appendTo( $(this).empty() )
				.on( 'change', function () {
					  		var val = $(this).val();
							pieza_table.column( i ).search(val ? val : '', false, false).draw();
				} );
			//	select.append( '<option value="-1">VACIOS</option>' );
				for (const row_x of lugarentregas_tables) {
					select.append( '<option value="'+row_x.id+'">'+row_x.nombre+'</option>' )
				}
				let campoInput = $('#Deposito_file');
				
				$('#mostrar-todos-deposito').change(function () {
                          let buscar= ($(this).is(':checked')) ? "-1":"";
                            if ($(this).is(':checked')) {
                                campoInput.next().hide();
                            } else {
                               campoInput.next().show();
                            }

                            pieza_table
                            .column(i)
                            .search(buscar)
                            .draw();
                        });
						
					} else{
						$(this).html('<input style="width: 100px" type="text" class="form-control filtros"  />');
						}
						
                } else {
                    if (i == 10) {
                        $(this).html('');
                    }
                    if (i == 11) {
                        $(this).html('<input type="checkbox" id="select-all">');
                    }
                }
                $('input', this).on('change', function() {
                    if (pieza_table.column(i).search() !== this.value) {
                        pieza_table
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            });


            var selectedRows = {};
            var pieza_table = $('#piezas').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                orderCellsTop: true,
                fixedHeader: true,
                searching: true,
                ajax: ({
                    url: _url + '/invoices/get_table_piezas_buscador',
                    method: "POST",
                    data: function(d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');

                        if ($('#marca').val() != '') {
                            d.marca = $('#marca').val();
                        }

                        if ($('#marca-input').val() != '') {
                            d.marcaInput = $('#marca-input').val();
                        }
                        if ($('#modelo-input').val() != '') {
                            d.modeloInput = $('#modelo-input').val();
                        }

                        if ($('#modelo').val() != '') {
                            d.modelo = $('#modelo').val();
                        }

                        if ($('#pieza').val() != '') {
                            d.pieza = $('#pieza').val();
                        }

                        if ($('#motor').val() != '') {
                            d.motor = $('#motor').val();
                        }
                        // console.log($('#id_car').val())

                        if ($('#id_car').val() != '') {
                            d.car_id = $('#id_car').val();
                        }

                         if ($('#estado').val() != '') {
                            d.estado = $('#estado').val();
                        }

/*                        if ($('select[name=estado]').val() != null) {
                            d.estado = JSON.stringify($('select[name=estado]').val());
                        }*/

                    },
                    error: function(request, status, error) {
                        console.log(request.responseText);
                    }
                }),
                "columns": [
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'nro_interno',
                        name: 'nro_interno'
                    },
                    {
                        data: 'product',
                        name: 'product'
                    },


                    {
                        data: 'marca',
                        name: 'marca'
                    },

                    {
                        data: 'modelo',
                        name: 'modelo'
                    },

                    {
                        data: 'nro_motor',
                        name: 'nro_motor'
                    },

                    {
                        data: 'nro_oblea',
                        name: 'nro_oblea'
                    },


                    {
                        data: 'deposito',
                        name: 'deposito'
                    },
                    {
                        data: 'ubicacion',
                        name: 'ubicacion'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: "action",
                        name: "action",
                        searchable: false
                    },
                    {
                        data: null,
                        name: 'select',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `<input type="checkbox" class="row-checkbox" data-id="${row.id}">`;
                        }
                    },
                    // {  data : 'fecha_ingreso', name : 'fecha_ingreso' },

                ],
                //dom: 'Bfrtip',
				dom: 'Bfrltip',
				pageLength: 25,
				lengthMenu: [[ 25, 100, 200, 500], [25, 100,200, 500]],
				buttons: [
                    {
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
                         $('.filtros').val('');
                        pieza_table.search('').columns().search('').draw();
                          }
                    },
                    {
                    extend: 'excelHtml5',
                    text: 'Exportar a Excel',
                    action: function() {
                        let params = pieza_table.ajax.params();

                        $.ajax({
                            url: routes.exportExcel,
                            type: 'POST',
                            data: {
                                ...params,
                                selected_ids: Object.keys(selectedRows).map(key => key.split('-')[1]) ,
                                _token: routes.csrfToken
                            },
                            xhrFields: {
                                responseType: 'blob'
                            },
                            success: function(response) {

                                let blob = new Blob([response], {
                                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                                });
                                let link = document.createElement('a');
                                link.href = window.URL.createObjectURL(blob);
                                link.download =
                                    'piezas.xlsx';
                                link.click();
                            },
                            error: function(xhr) {

                                alert('Hubo un error al exportar el archivo.');
                            }
                        });
                    }
                }],
                "bStateSave": true,
                "bAutoWidth": false,
                "ordering": false,
                "searching": true,
                "language": {
                    "decimal": "",
                    "emptyTable": $lang_no_data_found,
                    "info": $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " +
                        $lang_entries,
                    "infoEmpty": $lang_showing_0_to_0_of_0_entries,
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": $lang_show + " _MENU_ " + $lang_entries,
                    "loadingRecords": $lang_loading,
                    "processing": $lang_processing,
                    "search": $lang_search,
                    "zeroRecords": $lang_no_matching_records_found,
                    "paginate": {
                        "first": $lang_first,
                        "last": $lang_last,
                        "next": $lang_next,
                        "previous": $lang_previous
                    }
                }
            }).on('init.dt', function() {
                $('[data-toggle="tooltip"]').tooltip();
            });

            /*$('.select-filter').on('change', function(e) {
                pieza_table.draw();
            });*/


            //pieza_table.search('').columns().search('').draw();

            // Manejo del checkbox `select-all`
            $('#select-all').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox').prop('checked', isChecked);

                // Actualizar el objeto `selectedRows` basado en el estado del checkbox general
                $('#piezas tbody tr').each(function() {
                    const id = $(this).find('.row-checkbox').data('id');
                    if (isChecked) {
                        selectedRows[id] = true;
                    } else {
                        delete selectedRows[id];
                    }
                });
            });


            $('#piezas').on('change', '.row-checkbox', function() {
                const id = $(this).data('id');
                if ($(this).is(':checked')) {
                    selectedRows[id] = true; // Marcar como seleccionado
                } else {
                    delete selectedRows[id]; // Quitar de la selección
                }

                // Desmarcar el select-all si se desmarca alguna fila
                const allChecked = $('.row-checkbox:checked').length === $('.row-checkbox').length;
                $('#select-all').prop('checked', allChecked);
            });


            pieza_table.on('draw', function() {
                $('.row-checkbox').each(function() {
                    const id = $(this).data('id');
                    if (selectedRows[id]) {
                        $(this).prop('checked', true);
                    }
                });

                // Actualizar el estado del checkbox select-all
                const allChecked = $('.row-checkbox:checked').length === $('.row-checkbox').length;
                $('#select-all').prop('checked', allChecked);
            });

/*	$('.selectestado').select2({
                multiple: true,
                closeOnSelect: false//,
                //placeholder: "Select a " + title
              });
*/
    //$('#marca').select2();  
    //$('#estado').select2();  
    
    $('.Select2').select2();
     //initially clear select otherwise first option is selected
    //$('.Select2').val(null).trigger('change');


     $('#piezas').parents('.dataTables_wrapper').find('.dataTables_filter input')
    .unbind('keypress keyup input')
    .bind('change input', function (e) {
        if ($(this).val().length >= 3 && e.keyCode == 13) {
            pieza_table.search(this.value).draw();
        }
    });

    $('#vehiculo').parents('.dataTables_wrapper').find('.dataTables_filter input')
    .unbind('keypress keyup input')
    .bind('change input', function (e) {
        if ($(this).val().length >= 3 && e.keyCode == 13) {
            vehiculo_table.search(this.value).draw();
        }
    });


			$('.select2').select2({
                multiple: true,
                closeOnSelect: false//,
                //placeholder: "Select a " + title
              });

        })
    </script>
    <script>
        function abrirPreciosReferencia() {
            const driveUrl =
                'https://docs.google.com/spreadsheets/d/1JWrKcKfOgSKxz1OzWqX_0J24cZaVJQVbmBGPfhq3ogw/edit?gid=1081802679#gid=1081802679';
            window.open(driveUrl, '_blank');
        }
    </script>
@endsection
