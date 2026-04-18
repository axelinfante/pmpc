(function($) {
	"use strict";
    // let temp = $("#btn1").clone();
    // $("#btn1").click(function(){
    //     $("#btn1").after(temp);
    // });

    // //Creamos una fila en el head de la tabla y lo clonamos para cada columna

    $('#vehiculos_table thead tr').clone(true).appendTo( '#vehiculos_table thead' );
    $('#vehiculos_table thead tr:eq(1) th').each( function (i) {
        var title = $(this).text(); //es el nombre de la columna
		if(i != 0) {
            $(this).html( '<input type="text" value="" class="form-control filtros" placeholder="Search...'+title+'" />' );

            $( '.filtros', this ).on( 'change', function () {
                if ( vehiculo_table.column(i).search() !== this.value ) {

                    vehiculo_table
                        .column(i)
                        .search( this.value )
                        .draw();
                }

            } );
		}else{
            $(this).html( '' );
		}

    } );


    var vehiculo_table = $('#vehiculos_table').DataTable({
		processing: true,
		serverSide: true,
         scrollX: true,
        orderCellsTop: true,
        fixedHeader: true,
        ajax: ({
			url : _url + '/vehiculo/get_table_data',
			method: "POST",
			data: function (d) {
				d._token =  $('meta[name="csrf-token"]').attr('content');

                if($('select[name=client_id]').val() != ''){
	                d.client_id = $('select[name=client_id]').val();
	            }

                if($('select[name=status]').val() != null){
                	d.status = JSON.stringify($('select[name=status]').val());
                }

            },
			 error: function (request, status, error) {
				console.log(request.responseText);
			 }
		}),
		"columns" : [
            { data : "action", name : "action", searcheable : false },
			{  data : 'id', name : 'id' },
			{  data : 'dominio', name : 'dominio' },
			//{  data : 'anulado', name : 'anulado' },
			// {  data : 'nro_interno', name : 'nro_interno' },
			//{  data : 'fecha_asignacion', name : 'fecha_asignacion' },
			// {  data : 'forma', name : 'forma' },
			{  data : 'company', name : 'company' },
			{  data : 'tramitador', name : 'tramitador' },

			//{  data : 'tramitador_compania', name : 'tramitador_compania' },
			{  data : 'siniestro', name : 'siniestro' },
			
			{  data : 'marca_modelo', name : 'marca_modelo' },
			// {  data : 'motor', name : 'motor_nro' },
			// {  data : 'tipo_baja', name : 'tipo_baja' },
			// {  data : 'asegurado', name : 'asegurado' },
			// {  data : 'contacto', name : 'contacto' },
			// {  data : 'lugar_retiro', name : 'lugar_retiro' },
			// {  data : 'localidad', name : 'localidad' },
			// {  data : 'provincia', name : 'provincia' },
			// {  data : 'estado', name : 'estado', searcheable : false},
			//{  data : 'entregado_a', name : 'entregado_a' },
			// {  data : 'fecha_entrega', name : 'fecha_entrega' },
			//{  data : 'observacion_admin', name : 'observacion_admin' },
            //{  data : 'fecha_recepcion', name : 'fecha_recepcion' },
            // {  data : 'coordinar_retiro', name : 'coordinar_retiro' },
            // {  data : 'fecha_envio_doc', name : 'fecha_envio_doc' },
            // {  data : 'chasis', name : 'chasis' },
            // {  data : 'fecha_confirmacion_contacto', name : 'fecha_confirmacion_contacto' },
            // {  data : 'fecha_limite_retiro', name : 'fecha_limite_retiro' },
            // {  data : 'responsable_retiro', name : 'responsable_retiro' },
            // {  data : 'crp_nro', name : 'crp_nro' },
            // {  data : 'kilometraje', name : 'kilometraje' },
            // {  data : 'retira', name : 'retira' },
            // {  data : 'pieza_no_disponible', name : 'pieza_no_disponible' },
            // {  data : 'control', name : 'control' },
            // {  data : 'observacion_retiro', name : 'observacion_retiro', },


		],
        "dom": 'lrtip',
		responsive: true,
		"bStateSave": true,
		"bAutoWidth":false,	
		"ordering": false,
		"searching": true,
		"language": {
		   "decimal":        "",
		   "emptyTable":     $lang_no_data_found,
		   "info":           $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " + $lang_entries,
		   "infoEmpty":      $lang_showing_0_to_0_of_0_entries,
		   "infoFiltered":   "(filtered from _MAX_ total entries)",
		   "infoPostFix":    "",
		   "thousands":      ",",
		   "lengthMenu":     $lang_show + " _MENU_ " + $lang_entries,
		   "loadingRecords": $lang_loading,
		   "processing":     $lang_processing,
		   "search":         $lang_search,
		   "zeroRecords":    $lang_no_matching_records_found,
		   "paginate": {
			  "first":      $lang_first,
			  "last":       $lang_last,
			  "next":       $lang_next,
			  "previous":   $lang_previous
		   }
		}
	}).on( 'init.dt', function () {
         $('[data-toggle="tooltip"]').tooltip();
    });

    $('.select-filter').on('change', function(e) {
        vehiculo_table.draw();
    });


	vehiculo_table.search('').columns().search('').draw();


})(jQuery);

