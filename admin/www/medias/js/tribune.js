$(document).ready(function () {
	function telescopique() {
		var fields = $('#form-tribune-tribune-garde_corps_telescopique, #form-tribune-tribune-bardage_telescopique');
		if ($('#form-tribune-tribune-type').val() == "telescopique") {
			fields.parents(".ligne_form").show();
		}
		else {
			fields.removeAttr("checked").parents(".ligne_form").hide();
		}
	}

	function update_hidden_fields() {
		var salle = $("#form-tribune-tribune-salle").val();
		var type = $("#form-tribune-tribune-type").val();
		var siege_type = $("#form-tribune-tribune-siege_type").val();
		if (tribunes_configurations[salle] && tribunes_configurations[salle][type] && tribunes_configurations[salle][type][siege_type]) {
			var data = tribunes_configurations[salle][type][siege_type];
			for (i in data) {
				switch (i) {
					case 'gradin_hauteur' :
					case 'gradin_profondeur' :
					case 'siege_largeur' :
					case 'siege_profondeur' :
					case 'siege_hauteur' :
						var value = data[i] / 1000.0;
						break;
					default :
						var value = data[i];
						break;
				}
				$("input#form-tribune-tribune-" + i).val(value);
			}
		}
	}

	$('#form-tribune-tribune-type').change(function() {
		telescopique();
	});


	if ($("input[name=expert]").length) {
		$("#form-tribune-tribune-salle, #form-tribune-tribune-type, #form-tribune-tribune-siege_type").change(function () {
			update_hidden_fields();
		});
		update_hidden_fields();
	}
	else {
		telescopique();
	}
});

