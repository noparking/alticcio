$(document).ready(function () {
	$(".personnalisation-gabarit").hide();
	$(".personnalisation-gabarit-" + $("#select-gabarit").val()).show();
	$("#select-gabarit").change(function () {
		$(".personnalisation-gabarit").hide();
		$(".personnalisation-gabarit-" + $(this).val()).show();
	});
});

