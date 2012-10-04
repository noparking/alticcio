$(document).ready(function () {
	$("#form-acheter").ajaxForm({
		beforeSubmit: function() {
			$("#attente").css("display", "block");
		},
		success: function(response) {
			$("#attente").css("display", "none");
			document.location = response;
		}
	});
});
