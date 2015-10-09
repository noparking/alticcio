$(document).ready(function() {
	var id_organisations = $("select.organisation").val();
	$("select.organisation").change(function() {
		if ($(this).val() == id_organisations) {
			$("#select-correspondant").show();
			$("#select-correspondant-warning").hide();
		}
		else {
			$("#select-correspondant").hide();
			$("#select-correspondant-warning").show();
		}
	});
});
