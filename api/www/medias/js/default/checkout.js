$(document).ready(function () {
	if ($("#same-address").attr('checked')) {
		$("#fieldset-facturation").toggle();
	}
	$("#same-address").change(function () {
		$("#fieldset-facturation").toggle();
	});

	$("#form-checkout-commande-paiement").change(function() {
		if ($(this).val() == "mandat") {
			alert("Veuillez noter que seules les administrations peuvent payer par mandat.");
		}
	});

	$(".autosubmit").each(function () {
		$(this).submit();
	});

	$(".lastform").submit(function () {
		//$(".doublet-cart-checkout").colorbox.close();
	});
});

