
var total_quantity = 0;
var total_discount = 0;
var total_tax = 0;
var product_total = 0;
var grand_total = 0;
var current_row;

(function($) {
    "use strict";
	update_summary();

	$(document).on('change', '#client_id', function() {
		client_id = $(this).val();
		$.ajax({
			url: _url + '/contacts/get_client_info/' + client_id,
			beforeSend: function(){
				$("#preloader").css("display","block");
			},success: function(data){
				$("#preloader").css("display","none");
				var json = JSON.parse(data);
				$(".client_currency").html(json['currency']);
				client_currency = json['currency'];
				convert_currency( $("#product_total").val() );
			}
		});
	});
	
	$(document).on('change', '#project_id', function() {
		var project_id = $(this).val();
		$.ajax({
			url: _url + '/projects/get_project_info/' + project_id,
			beforeSend: function(){
				$("#preloader").css("display","block");
			},success: function(data){
				$("#preloader").css("display","none");
				var json = JSON.parse(data);
				$(".client_currency").html(json['client']['currency']);
				client_currency = json['client']['currency'];
				convert_currency( $("#product_total").val() );
			}
		});
	});

	/*$(document).on('change', '#product,#service', function() {
	    var product_id = $(this).val();
		if( product_id == '' ){
			return;
		}
		

		//console.log(product_id);
		
	    //if product has already in order table
	    if ($("#order-table > tbody > #product-" + product_id).length > 0) {
			var line = $("#order-table > tbody > #product-" + product_id);
			var quantity = parseFloat($(line).find(".input-quantity").val());

			//validacion de producto solamente se acepta 1
			if (quantity==1) return "";
			
			$(line).find(".input-quantity").val(quantity + 1).trigger('change');
			$("#product").val("").trigger('change');;
			
			return;		
	    }

        let showCar = true;
        // service es productos en stock
        if ($(this).prop('id') == 'service'){
            showCar = false
        }
        //productos en stock
        let service = $(this);

	    //Ajax request for getting product details
	    $.ajax({
	        method: "GET",
	        url: _url + '/products/get_product/' + product_id,
	        beforeSend: function() {
	            $("#preloader").fadeIn(100);
	        },
	        success: function(data) {
	            $("#preloader").fadeOut(100);
	            var json = JSON.parse(data);
	            var item = json['item'];
	            var product = json['product'];
	            //var tax = json['tax'];

				//console.log(product);
                if (service.prop('id') == 'service'){
                    $('#service').data('company', product['company_id']);
                }
	            if (item['item_type'] == 'product') {
	                var product_price = parseFloat(product['product_price']);

	                // If Stock not available
	                var available_quantity = json['available_quantity'];
	                if( available_quantity < 1 ){
	                		alert("Sorry, Out of Stock !");
	                		$("#product").val("");
	                		return;
	                }

	            } else if (item['item_type'] == 'service') {
	                var product_price = parseFloat(product['cost']);
	            }
	            

				let company = ''
                if(product['company_id'] == 1) {
                    company = 'PM-'
                }

                if(product['company_id'] == 2) {
                    company = 'PC-'
                }

	            //Tax Value calculation
	            var unit_cost = product_price;
	            var sub_total = product_price;

				
				var tax_selector = $("#tax-selector").html();

				let interno = product['nro_interno'];

				let marca = 'Sin marca';
                let modelo = 'Sin marca';
                if(product.marca_modelo) {
                    marca = product.marca_modelo.marca.marca;
                    product.marca_modelo.modelo.modelo;
                }


				// <td class="text-right discount"><input type="text" name="discount[]" class="form-control input-discount text-right" value="0.00"></td>
				// 							<td class="text-right tax"><select class="form-control selectpicker input-tax" name="tax[${product['id']}][]" title="${$lang_select_tax}" multiple="true">${tax_selector}</select></td>

				
				//${ showCar ?  $('#product').prop('data-idCar') ? $('#product').prop('data-idCar') : '' : ''}
	            var product_row = `<tr id="product-${product['id']}">
											<td><button type="button" class="btn btn-danger btn-xs remove-product-directo"><i class='fa fa-trash'></i></button></td>
											<td><b>${product['id']} / ${product['nro_oblea'] != null ? product['nro_oblea'] : ''}</b></td>
											<td><b>${item['item_name']} ${marca} ${modelo}</b></td>
											<td class="description"><input type="text" name="product_description[]" class="form-control input-description" value="${product['description'] != null ? product['description'] : ''}"></td>
											<td class="text-center quantity">1 <input type="hidden" value="1" name="quantity[]" min="1" class="form-control input-quantity text-center" max="${available_quantity}"></td>
											<td class="text-right unit-cost"><input type="text" name="unit_cost[]" data-id="${product['id']}" onChange="monto_en_usd(this,${product['id']})" class="form-control input-unit-cost text-right" value="${unit_cost.toFixed(2)}"></td>
											
											<td class="text-right sub-total"><input type="text" name="sub_total[]" class="form-control input-sub-total text-right" value="${sub_total.toFixed(2)}" readonly></td>
											<td class="text-right usd"><input disabled id="usd_monto-${product['id']}" type="text" class="form-control input-usd text-right" ></td>
											<td>
											${company} ${interno}
											<input type="hidden" name="autos[]" value="${ showCar ? $('#product').prop('data-idCar') ? $('#product').prop('data-idCar') : '' : ''}">
</td>
											<input type="hidden" name="product_id[]" value="${product['id']}">
											<input type="hidden" name="product_items_id[]" value="${product['item_id']}">											
											<input type="hidden" name="invoiceitem_id[]" value="0">
											<input type="hidden" name="product_tax[]" class="input-product-tax" value="0">
									</tr>`;
									
	            $("#order-table > tbody").append(product_row);
	            update_summary();

	            $("#product").val("").trigger('change');
	            $("#service").val("").trigger('change');
				$('.selectpicker').selectpicker('render');

	        }
	    });

	});*/


	$(document).on('change','#service', function() {
	    var product_id = $(this).val();
		if( product_id == '' ){
			return;
		}
		

		//console.log(product_id);
		
	    //if product has already in order table
	    if ($("#order-table > tbody > #product-" + product_id).length > 0) {
			var line = $("#order-table > tbody > #product-" + product_id);
			var quantity = parseFloat($(line).find(".input-quantity").val());

			//validacion de producto solamente se acepta 1
			if (quantity==1) return "";
			
			$(line).find(".input-quantity").val(quantity + 1).trigger('change');
			$("#product").val("").trigger('change');;
			
			return;		
	    }

        let showCar = true;
        // service es productos en stock
        if ($(this).prop('id') == 'service'){
            showCar = false
        }
        //productos en stock
        let service = $(this);

	    //Ajax request for getting product details
	    $.ajax({
	        method: "GET",
	        url: _url + '/products/get_product/' + product_id,
	        beforeSend: function() {
	            $("#preloader").fadeIn(100);
	        },
	        success: function(data) {
	            $("#preloader").fadeOut(100);
	            var json = JSON.parse(data);
	            var item = json['item'];
	            var product = json['product'];
	            //var tax = json['tax'];

				//console.log(product);
                if (service.prop('id') == 'service'){
                    $('#service').data('company', product['company_id']);
                }
	            if (item['item_type'] == 'product') {
	                var product_price = parseFloat(product['product_price']);

	                // If Stock not available
	                var available_quantity = json['available_quantity'];
	                if( available_quantity < 1 ){
	                		alert("Sorry, Out of Stock !");
	                		$("#product").val("");
	                		return;
	                }

	            } else if (item['item_type'] == 'service') {
	                var product_price = parseFloat(product['cost']);
	            }
	            

				let company = ''
                if(product['company_id'] == 1) {
                    company = 'PM-'
                }

                if(product['company_id'] == 2) {
                    company = 'PC-'
                }

	            //Tax Value calculation
	            var unit_cost = product_price;
	            var sub_total = product_price;

				
				var tax_selector = $("#tax-selector").html();

				let interno = product['nro_interno'];

				let marca = 'Sin marca';
                let modelo = 'Sin marca';
                if(product.marca_modelo) {
                    marca = product.marca_modelo.marca.marca;
                    product.marca_modelo.modelo.modelo;
                }


				// <td class="text-right discount"><input type="text" name="discount[]" class="form-control input-discount text-right" value="0.00"></td>
				// 							<td class="text-right tax"><select class="form-control selectpicker input-tax" name="tax[${product['id']}][]" title="${$lang_select_tax}" multiple="true">${tax_selector}</select></td>

				
				//${ showCar ?  $('#product').prop('data-idCar') ? $('#product').prop('data-idCar') : '' : ''}
	            var product_row = `<tr id="product-${product['id']}">
											<td><button type="button" class="btn btn-danger btn-xs remove-product-directo"><i class='fa fa-trash'></i></button></td>
											<td><b>${product['id']} / ${product['nro_oblea'] != null ? product['nro_oblea'] : ''}</b></td>
											<td><b>${item['item_name']} ${marca} ${modelo}</b></td>
											<td class="description"><input type="text" name="product_description[]" class="form-control input-description" value="${product['description'] != null ? product['description'] : ''}"></td>
											<td class="text-center quantity">1 <input type="hidden" value="1" name="quantity[]" min="1" class="form-control input-quantity text-center" max="${available_quantity}"></td>
											<td class="text-right unit-cost"><input type="text" name="unit_cost[]" data-id="${product['id']}" onChange="monto_en_usd(this,${product['id']})" class="form-control input-unit-cost text-right" value="${unit_cost.toFixed(2)}"></td>
											
											<td class="text-right sub-total"><input type="text" name="sub_total[]" class="form-control input-sub-total text-right" value="${sub_total.toFixed(2)}" readonly></td>
											<td class="text-right usd"><input disabled id="usd_monto-${product['id']}" type="text" class="form-control input-usd text-right" ></td>
											<td>
											${company} ${interno}
											<input type="hidden" name="autos[]" value="${ showCar ? $('#product').prop('data-idCar') ? $('#product').prop('data-idCar') : '' : ''}">
</td>
											<input type="hidden" name="product_id[]" value="${product['id']}">
											<input type="hidden" name="product_items_id[]" value="${product['item_id']}">											
											<input type="hidden" name="invoiceitem_id[]" value="0">
											<input type="hidden" name="product_tax[]" class="input-product-tax" value="0">
									</tr>`;
									
	            $("#order-table > tbody").append(product_row);
	            update_summary();

	            $("#product").val("").trigger('change');
	            $("#service").val("").trigger('change');
				$('.selectpicker').selectpicker('render');

	        }
	    });

	});
	
	$(document).on('change', '#product', function() {
	    var product_id = $(this).val();
		if( product_id == '' ){
			return;
		}

	    //if product has already in order table
	    if ($("#order-table > tbody > #product-" + product_id).length > 0) {
			var line = $("#order-table > tbody > #product-" + product_id);
			var quantity = parseFloat($(line).find(".input-quantity").val());
			if (quantity==1) return "";
			$(line).find(".input-quantity").val(quantity + 1).trigger('change');
			$("#product").val("").trigger('change');;
			return;		
	    }
		
					let InternoVehiculo = $('#car_id option:selected').val();
					let textoVehiculo = $('#car_id option:selected').text();
					let textoPieza    = $(this).find('option:selected').text();
					
					let product = {
						id: product_id,
						item_name: textoPieza,
						marca_modelo: textoVehiculo,
						item_id: product_id
					};

				/*	let partes = textoVehiculo.split('-').map(p => p.trim());

					let prefijo        = partes[0] || '';
					let numeroSinCeros = parseInt(partes[1], 10) || 0; 
					let internos_new   = `${prefijo}-${numeroSinCeros}`; 

					let vehiculo = partes.slice(2).join(' - '); 

					let product = {
						id: product_id,
						item_name: textoPieza,
						marca_modelo: vehiculo,
						item_id: product_id
					};*/
					
					   var unit_cost = 1;
					   var sub_total = 1;

					let product_row = `
						<tr id="product-${product.id}">
							<td>
								<button type="button" class="btn btn-danger btn-xs remove-product-directo">
									<i class="fa fa-trash"></i>
								</button>
							</td>
							<td></td>
							<td><b>${product.item_name} ${product.marca_modelo}</b></td>
							<td class="description">
								<input type="text" name="product_new_description[]" class="form-control input-description" value="">
							</td>
							<td class="text-center quantity">
								1 
								<input type="hidden" value="1" name="quantity_new[]" min="1" class="form-control input-quantity text-center" max="1">
							</td>
							<td class="text-right unit-cost">
								<input type="text" name="unit_new_cost[]" data-id="${product.id}" onChange="monto_en_usd(this, ${product.id})" class="form-control input-unit-cost text-right" value="${unit_cost.toFixed(2)}">
							</td>
							<td class="text-right sub-total">
								<input type="text" name="sub_new_total[]" class="form-control input-sub-total text-right" value="${sub_total.toFixed(2)}" readonly>
							</td>
							<td class="text-right usd">
								<input disabled id="usd_monto-${product.id}" type="text" class="form-control input-usd text-right">
							</td>
							<td>${InternoVehiculo}</td>
							<input type="hidden" name="product_new_id[]" value="-1">
							<input type="hidden" name="product_new_interno[]" value="${InternoVehiculo}">
							<input type="hidden" name="product_new_items_id[]" value="${product.item_id}">
							<input type="hidden" name="product_new_tax[]" class="input-product-tax" value="0">
						</tr>`;


					$("#order-table > tbody").append(product_row);
					update_summary();
										
										
		/*								    if (item['item_type'] == 'product') {
	                var product_price = parseFloat(product['product_price']);

	                // If Stock not available
	                var available_quantity = json['available_quantity'];
	                if( available_quantity < 1 ){
	                		alert("Sorry, Out of Stock !");
	                		$("#product").val("");
	                		return;
	                }

	            } else if (item['item_type'] == 'service') {*/
			
			/*
			   var product_row = `<tr id="product-${product['id']}">
											<td><button type="button" class="btn btn-danger btn-xs remove-product-directo"><i class='fa fa-trash'></i></button></td>
											<td><b>${product['id']} / ${product['nro_oblea'] != null ? product['nro_oblea'] : ''}</b></td>
											<td><b>${item['item_name']} ${marca} ${modelo}</b></td>
											<td class="description"><input type="text" name="product_description[]" class="form-control input-description" value="${product['description'] != null ? product['description'] : ''}"></td>
											<td class="text-center quantity">1 <input type="hidden" value="1" name="quantity[]" min="1" class="form-control input-quantity text-center" max="${available_quantity}"></td>
											<td class="text-right unit-cost"><input type="text" name="unit_cost[]" data-id="${product['id']}" onChange="monto_en_usd(this,${product['id']})" class="form-control input-unit-cost text-right" value="${unit_cost.toFixed(2)}"></td>
											
											<td class="text-right sub-total"><input type="text" name="sub_total[]" class="form-control input-sub-total text-right" value="${sub_total.toFixed(2)}" readonly></td>
											<td class="text-right usd"><input disabled id="usd_monto-${product['id']}" type="text" class="form-control input-usd text-right" ></td>
											<td>
											${company} ${interno}
											<input type="hidden" name="autos[]" value="${ showCar ? $('#product').prop('data-idCar') ? $('#product').prop('data-idCar') : '' : ''}">
</td>
											<input type="hidden" name="product_id[]" value="${product['id']}">
											<input type="hidden" name="product_items_id[]" value="${product['item_id']}">											
											<input type="hidden" name="invoiceitem_id[]" value="0">
											<input type="hidden" name="product_tax[]" class="input-product-tax" value="0">
									</tr>`;
									
	            $("#order-table > tbody").append(product_row);
	            update_summary();
				*/
			
      	    //Ajax request for getting product details
	   /* $.ajax({
	        method: "GET",
	        url: _url + '/products/get_product/' + product_id,
	        beforeSend: function() {
	            $("#preloader").fadeIn(100);
	        },
	        success: function(data) {
	            $("#preloader").fadeOut(100);
	            var json = JSON.parse(data);
	            var item = json['item'];
	            var product = json['product'];
	            //var tax = json['tax'];

				//console.log(product);
                if (service.prop('id') == 'service'){
                    $('#service').data('company', product['company_id']);
                }
	            if (item['item_type'] == 'product') {
	                var product_price = parseFloat(product['product_price']);

	                // If Stock not available
	                var available_quantity = json['available_quantity'];
	                if( available_quantity < 1 ){
	                		alert("Sorry, Out of Stock !");
	                		$("#product").val("");
	                		return;
	                }

	            } else if (item['item_type'] == 'service') {
	                var product_price = parseFloat(product['cost']);
	            }
	            

				let company = ''
                if(product['company_id'] == 1) {
                    company = 'PM-'
                }

                if(product['company_id'] == 2) {
                    company = 'PC-'
                }

	            //Tax Value calculation
	            var unit_cost = product_price;
	            var sub_total = product_price;

				
				var tax_selector = $("#tax-selector").html();

				let interno = product['nro_interno'];

				let marca = 'Sin marca';
                let modelo = 'Sin marca';
                if(product.marca_modelo) {
                    marca = product.marca_modelo.marca.marca;
                    product.marca_modelo.modelo.modelo;
                }


	            var product_row = `<tr id="product-${product['id']}">
											<td><button type="button" class="btn btn-danger btn-xs remove-product-directo"><i class='fa fa-trash'></i></button></td>
											<td><b>${product['id']} / ${product['nro_oblea'] != null ? product['nro_oblea'] : ''}</b></td>
											<td><b>${item['item_name']} ${marca} ${modelo}</b></td>
											<td class="description"><input type="text" name="product_description[]" class="form-control input-description" value="${product['description'] != null ? product['description'] : ''}"></td>
											<td class="text-center quantity">1 <input type="hidden" value="1" name="quantity[]" min="1" class="form-control input-quantity text-center" max="${available_quantity}"></td>
											<td class="text-right unit-cost"><input type="text" name="unit_cost[]" data-id="${product['id']}" onChange="monto_en_usd(this,${product['id']})" class="form-control input-unit-cost text-right" value="${unit_cost.toFixed(2)}"></td>
											
											<td class="text-right sub-total"><input type="text" name="sub_total[]" class="form-control input-sub-total text-right" value="${sub_total.toFixed(2)}" readonly></td>
											<td class="text-right usd"><input disabled id="usd_monto-${product['id']}" type="text" class="form-control input-usd text-right" ></td>
											<td>
											${company} ${interno}
											<input type="hidden" name="autos[]" value="${ showCar ? $('#product').prop('data-idCar') ? $('#product').prop('data-idCar') : '' : ''}">
</td>
											<input type="hidden" name="product_id[]" value="${product['id']}">
											<input type="hidden" name="product_items_id[]" value="${product['item_id']}">											
											<input type="hidden" name="invoiceitem_id[]" value="0">
											<input type="hidden" name="product_tax[]" class="input-product-tax" value="0">
									</tr>`;
									
	            $("#order-table > tbody").append(product_row);
	            update_summary();

	            $("#product").val("").trigger('change');
	            $("#service").val("").trigger('change');
				$('.selectpicker').selectpicker('render');

	        }*/
	    //});

	});

	$('#tasa_usd').change(function() {
        $('.input-unit-cost').each(function(index) {
            //console.log(index + ": " + $(this).text());
            //console.log($(this).attr('id'));
            //console.log($(this))

            monto_en_usd($(this), $(this).data('id'));
        });
    })
	
	$(document).on('keyup change', '.input-quantity, .input-unit-cost, .input-discount', function() {
	    var line = $(this).parent().parent();
		var line_qnty = parseFloat($(line).find('.input-quantity').val());
		var line_unit_cost = parseFloat($(line).find('.input-unit-cost').val());
		var line_discount = 0;//parseFloat($(line).find('.input-discount').val());
		var line_total = (line_qnty * line_unit_cost) - line_discount;
		
		$(line).find('.input-sub-total').val(line_total);
		
		//Update TAX
		var product_tax = 0;

		$.each($(line).find('select.input-tax').val(), function(index, value) {
			var tax_rate = $(line).find('select.input-tax').find('option[value="' + value + '"]').data('tax-rate');
			var tax_type = $(line).find('select.input-tax').find('option[value="' + value + '"]').data('tax-type');

			if (tax_type == 'percent') {
				product_tax += (line_total / 100) * tax_rate;
			} else if (tax_type == 'fixed') {
				product_tax += tax_rate;
			}
		});
		
		$(line).find(".input-product-tax").val(product_tax);
		
		update_summary();
	});

//Click remove product
	$(document).on('click', '.remove-product',  function(event){
		event.stopPropagation();
		let row = $(this).parent().parent();
		let idProd = row.attr('id').split('-')[1];
		let iditems = row.data("id");
		$("#idProd").val(idProd);
		$("#iditems").val(iditems);
		$('#modalProduct').modal('show');
	});


	$('#product_eliminar').click(function(event) {
		event.preventDefault();
		var form = document.querySelector('#formProduct');
		let id = $('#idProd').val();
		var myformData = new FormData(form);        
			myformData.append('_token', $('meta[name="csrf-token"]').attr('content'));
		$.ajax({
			url:_url + '/invoices/comisiones_anulados',
			method: 'post',
			processData: false,
			contentType: false,
			cache: false,
			data: myformData,
			success:function(result)
				{
					$('#product-' + id).remove();
					 	update_summary();
					$('#modalProduct').modal('hide');
					
					setTimeout(function(){
					//	$( "#target" ).trigger( "submit" );
					$("#formId").submit();
					}, 2000);

				
				}
			});
	})
	
	//fin //Click remove product
	/*$(document).on('click', '.remove-product', function() {
		let row = $(this).parent().parent();
		$('#modalProduct').modal('show');
		let idProd = $(this).parent().parent().attr('id').split('-')[1];
		$('#product_eliminar').click(function() {
			let estado = $('#estado_prod').val();
		*/
/*			$.ajax({
				url: _url+ '/products/cambiar-estado/' +idProd +'/'+estado
			})*/

	/*		alert();

			row.remove();
	    	update_summary();

			$('#modalProduct').modal('hide');
		})
	    
	});
*/

	//Select Tax
	$(document).on('change', 'select.input-tax', function(event) {
		event.stopPropagation();
		var elem = $(this);
		var line = $(elem).parent().parent().parent();
		var line_total = $(line).find('.input-sub-total').val();
		var product_tax = 0;

		$.each($(this).val(), function(index, value) {
			var tax_rate = $(elem).find('option[value="' + value + '"]').data('tax-rate');
			var tax_type = $(elem).find('option[value="' + value + '"]').data('tax-type');

			if (tax_type == 'percent') {
				product_tax += (line_total / 100) * tax_rate;
			} else if (tax_type == 'fixed') {
				product_tax += tax_rate;
			}
		});
		
		$(line).find(".input-product-tax").val(product_tax);
		update_summary();
	});


	$(document).on('change','#related_to',function(){
	   if($(this).val() == 'projects'){
	   	 $("#projects").removeClass('d-none');
	   	 $("#contacts").addClass('d-none');
	   }else{
	   	 $("#projects").addClass('d-none');
	   	 $("#contacts").removeClass('d-none');
	   }
	});

	if($('#company_id').val() != '') {
				// console.log(data.company);
		$('#service').data('company', $('#company_id').val());
		$('#car_id').data('company', $('#company_id').val());
		//console.log(car.data('company'));
		$('#company_id_s').val( $('#company_id').val());

		if ($(".select2-ajax").length) {
            $('.select2-ajax').each(function(i, obj) {

                if( $(this).prop('id') == 'service' || $(this).prop('id') == 'car_id') {

                    $(this).select2('destroy');

                    var display2 = "";
                    if( typeof  $(this).data('display2') !== "undefined" ){
                        display2 = "&display2=" +  $(this).data('display2');
                    }

                    var display3 = "";
                    if( typeof  $(this).data('display3') !== "undefined" ){
                        display3 = "&display3=" +  $(this).data('display3');
                    }
                    var company = "";
                    if( typeof  $(this).data('company') !== "undefined" ){
                        company = "&company=" +  $(this).data('company');
                    }

                    var option = "";
                    if( typeof  $(this).data('option') !== "undefined" ){
                        option = "&option=" +  $(this).data('option');
                    }


                    $(this).select2({

                        ajax: {
                            url: _url + '/ajax/get_table_data?table=' + $(this).data('table') + '&value=' + $(this).data('value') + '&display=' + $(this).data('display') + display2 + display3 + '&where=' + $(this).data('where') + option + company,
                            // async: false,
                            processResults: function (data) {

                                return {
                                    results: data
                                };
                            }
                        }
                    });
                }


            });
        }

	}
})(jQuery);


/*function update_summary(){
    total_quantity = 0;
    total_discount = 0;
    total_tax = 0;
    product_total = 0;

    $("#order-table > tbody > tr").each(function(index, obj) {
        total_quantity = total_quantity + parseFloat($(this).find(".input-quantity").val());
        total_discount = total_discount + parseFloat($(this).find(".input-discount").val());
        total_tax = total_tax + parseFloat($(this).find(".input-product-tax").val());
        product_total = product_total + parseFloat($(this).find(".input-sub-total").val());
    });

    $("#total-qty").html(total_quantity);
    $("#total-discount").html(_currency + ' ' + total_discount.toFixed(2));
    $("#total-tax").html(_currency + ' ' + total_tax.toFixed(2));
    $("#total").html(_currency + ' ' + product_total.toFixed(2));
    $("#product_total").val(product_total.toFixed(2));
    $("#tax_total").val(total_tax.toFixed(2));
    
	
	grand_total = product_total + total_tax;
	if(client_currency != ''){
		 convert_currency(grand_total);
	}else{
		$("#converted_amount").html(_currency + ' ' + grand_total.toFixed(2));
	}
		
} */

function convert_currency(amount){
	$.ajax({
		method: "GET",
		url: _url + '/convert_currency/' + _from_currency + '/' + client_currency + '/' + amount,
		beforeSend: function(){
			//$("#preloader").css("display","block"); 
		},success: function(data){
			//$("#preloader").css("display","none");
			var json = JSON.parse(data);
			$("#converted_amount").html(json['currency2_symbol'] +' '+ json['amount_decimal']);
		}		
	});
}

function monto_en_usd (input, idProd ) {
    let tasa =  $('#tasa_usd');
    let usdConvertidos = 0;

    input = $(input);

    if(tasa.val() != '' || tasa.val() != 0 && tasa.val() != undefined) {
        usdConvertidos = input.val() * tasa.val();
    }

    $('#usd_monto-'+idProd).val(usdConvertidos);

    // console.log(usdConvertidos);
}

// Función auxiliar para dar formato de moneda/número
function formatearNumero(monto, locale = 'es-AR') {
    let valor = parseFloat(monto) || 0;
    return valor.toLocaleString(locale, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function update_summary() {
    // Declaración correcta de variables locales con 'let'
    let total_quantity = 0;
    let total_discount = 0;
    let total_tax = 0;
    let product_total = 0;

    $("#order-table > tbody > tr").each(function () {
        // '|| 0' evita que parseFloat devuelva NaN si el input está vacío
        let qty = parseFloat($(this).find(".input-quantity").val()) || 0;
        let discount = parseFloat($(this).find(".input-discount").val()) || 0;
        let tax = parseFloat($(this).find(".input-product-tax").val()) || 0;
        let subtotal = parseFloat($(this).find(".input-sub-total").val()) || 0;

        total_quantity += qty;
        total_discount += discount;
        total_tax += tax;
        product_total += subtotal;
    });

    // 1. Mostrar en HTML con formato visual limpio ($ 1.000.000,00)
    $("#total-qty").html(total_quantity);
    $("#total-discount").html(_currency + ' ' + formatearNumero(total_discount));
    $("#total-tax").html(_currency + ' ' + formatearNumero(total_tax));
    $("#total").html(_currency + ' ' + formatearNumero(product_total));

    // 2. Asignar a inputs oculta/enviables manteniendo formato numérico limpio (1000000.00)
    $("#product_total").val(product_total.toFixed(2));
    $("#tax_total").val(total_tax.toFixed(2));

    /** Conversión de Moneda **/
    let grand_total = product_total + total_tax;

    if (typeof client_currency !== 'undefined' && client_currency !== '') {
        convert_currency(grand_total);
    } else {
        $("#converted_amount").html(_currency + ' ' + formatearNumero(grand_total));
    }
}