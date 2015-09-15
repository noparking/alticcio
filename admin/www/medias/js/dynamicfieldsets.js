(function($) {
	$.fn.dynamicfieldsets = function(selector, field, default_fieldsets = {}, default_values = {}, replacements = {}) {
		this.each(function () {
			var element = $(this);
			var select = element.find(selector);
			var original_select = select.clone();
			var fieldset = element.find(".dynamicfieldset");
			var fieldsets = {};
			
			select.change(function() {
				var key = $(this).val();
				var value = $(this).find(":selected").text();
				if (key != 0) {
					create_fieldset(key, value);
					$(this).val(0);
					$(this).blur();
				}
			});

			for (i in default_fieldsets) {
				create_fieldset(i, default_fieldsets[i], default_values[i]);
			}

			function create_fieldset(key, value, default_values = {}) {
				var html = fieldset.html();
				for (i in replacements) {
					var re = new RegExp(i, 'g')
					html = html.replace(re, replacements[i]);
				}
				html = html.replace(/KEY/g, key);
				html = html.replace(/VALUE/g, value);
				html = html.replace(/FIELD/g, field);
				var new_fieldset = $(html);
				new_fieldset.data("id_fieldset", key);

				for (i in default_values) {
					var name = field + '\\[' + key + '\\]\\[' + i + '\\]';
					new_fieldset.find("[name='" + name + "']").val(default_values[i]);
				}

				new_fieldset.find(".delete-fieldset").click(function () {
					var id_fieldset = new_fieldset.data("id_fieldset");
					new_fieldset.remove();
					delete fieldsets[id_fieldset];
					update_select();
					return false;
				});
				select.parent().append(new_fieldset);
				fieldsets[key] = key;
				update_select();
			}

			function update_select() {
				select.html(original_select.html());
				for (i in fieldsets) {
					select.find('option[value="' + i + '"]').remove();
				}
			}

			return this;
		});
	};
})(jQuery)
