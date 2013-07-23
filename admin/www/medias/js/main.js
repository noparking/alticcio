$(document).ready(function () {

	$("input[type=submit][name~=save]").click(function () {
		if ($(this).attr('name') == form_action) {
			$("body").prepend('<div class="saving">' + dico['SauvegardeEnCours'] + '</div>');
		}
	});

});

