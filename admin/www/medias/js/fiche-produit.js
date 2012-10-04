$(document).ready(function () {
	$(".fiche-produit-zone").sortable({
		connectWith: '.fiche-produit-zone',
		stop: function (event, ui) {
			$(".fiche-produit-zone").each(function () {
				var zone = $(this).attr('id').replace("fiche-produit-zone-", "");
				var i = 0;
				$(this).find("input[name*=zone]").val(zone);
				$(this).find("input[name*=classement]").each(function () {
					$(this).val(i);
					i++;
				});
			});
		}
	}).disableSelection();
});
