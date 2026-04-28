
(function ($) {
	"use strict";

	$('#invoice_id_manual').hide();
	$('#product_id_manual').select2('destroy');
	$('#product_id_manual').hide();

	$(document).on('change', '#invoice_id', function () {
		let invoice_id = $(this).val();
		let select = $('#product_id');
		select.select2('destroy');
		select.empty();
		//select.append('<option value="">Seleccionar Producto</option>');
		if (invoice_id != '') {
			$.ajax({
				url: _url + '/invoices/get_items/' + invoice_id,
				dataType: 'Json',
				beforeSend: function () {
					$("#preloader").css("display", "block");
				}, success: function (data) {
					$("#preloader").css("display", "none");



					$.each(data.data, function (index, product) {
						select.append('<option value="' + product.id + '" data-quantity="' + product.quantity + '">'  + '('+product.product_id+') '+product.item.item_name + '</option>');
					});

					select.select2();
				}
			});
		}

	});
	$(document).on('change', '#product_id', function () {
		let quantity = $(this).select2('data')[0].element.dataset.quantity;
		let select = $('#qty');
		select.empty();
		select.append('<option value="">Seleccionar Cantidad</option>');

		for (let i = 1; i <= quantity; i++) {
			select.append('<option value="' + i + '">' + i + '</option>');
		}
		select.val('1');

	});

	$(document).on('click', '#manual', function () {

		if ($(this).prop('checked')) {
			$('#invoice_id').removeAttr('required');
			$('#invoice_id').select2('destroy');
			$('#invoice_id').hide();

			$('#product_id').removeAttr('required');
			$('#product_id').select2('destroy');
			$('#product_id').hide();

			$('#invoice_id_manual').attr('required', 'required');
			$('#invoice_id_manual').show();

			$('#product_id_manual').attr('required', 'required');
			$('#product_id_manual').select2();
			$('#product_id_manual').show();
			
		}
		else {

			$('#invoice_id').attr('required', 'required');
			$('#invoice_id').select2();

			$('#product_id').attr('required', 'required');
			$('#product_id').select2();

			$('#invoice_id_manual').removeAttr('required');
			$('#invoice_id_manual').hide();

			$('#product_id_manual').removeAttr('required');
			$('#product_id_manual').select2('destroy');
			$('#product_id_manual').hide();

			$('#invoice_id').show();
			
		}
	});


})(jQuery);




