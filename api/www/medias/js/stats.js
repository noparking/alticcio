(function($) {
	$.fn.doubletWidgetStats = function(settings) {
		var area = {};
		var dico;
		var active_zone;
		var last_action;
		var year_switch;

		function apiRequest(uri, callback, item_callbacks) {
			params = {
				key : settings.key,
				format : "jquery",
				widget : 2
			}
			if (settings.user && settings.user.id) {
				params.track = settings.user.id;
			}
			$.getJSON(settings.api + "/" + uri + "?callback=?", params, function (data) {
				if (data.error) {
					alert("API Doublet : " + data.message);
					return false;
				}
				callback(data, data.jq.html, data.jq.items);
				for (i in item_callbacks) {
					for (j in data.jq.items[i]) {
						item_callbacks[i](data, data.jq.items[i][j]);
					}
				}
			});
		}

		function display(zone) {
			for (z in area) {
				area[z].hide();
			}
			if (active_zone != zone) {
				zone.previous = active_zone;
			}
			zone.show();
			active_zone = zone;
		}

		function close(zone) {
			if (zone.previous) {
				display(zone.previous);
			}
		}

		function getProduits() {
			apiRequest('catalogue/products', function(data, html, items) {
				products_area.prepend(html);
			}, {
				'show_categories' : function (data, item) {
					$(item.selector).parent().children(item.toggle).hide();
					$(item.selector).bind(item.event, function (event) {
						var element = $(this).parent();
						element.children(item.toggle).toggle();
						element.toggleClass("collapse");
						element.toggleClass("expand");
					});
				},
				'show_product' : function (data, item) {
					$(item.selector).bind(item.event, function (event) {
						getStatsProduit(item.id);
						last_action = function(id) { return function () { getStatsProduit(id); }} (item.id);
						event.stopPropagation();
					});
				}
			});
		}

		function getStatsGeneral() {
			var annee = year_switch.val(); 
			apiRequest("stats/general/"+annee, function (data, html, items) {
				area.general.html(html);
				display(area.general);
			});
		}

		function getStatsClients() {
			var annee = year_switch.val(); 
			apiRequest("stats/clients/"+annee, function (data, html, items) {
				area.clients.html(html);
				display(area.clients);
			});
		}
		
		function getStatsProduit(id) {
			var annee = year_switch.val(); 
			apiRequest("stats/product/"+annee+"/"+id, function (data, html, items) {
				area.product.html(html);
				display(area.product);
			});
		}

		this.each(function () {
			var element = $(this);
			apiRequest('widget/stats', function(data, html, items) {
				dico = data.jq.dico;
				element.append(html);
				products_area = $(items['products_area'][0].selector);
				area.general = $(items['general_area'][0].selector);
				area.product = $(items['product_area'][0].selector);
				area.clients = $(items['clients_area'][0].selector);
				for (z in area) {
					area[z].hide();
				}
				getProduits();
			}, {
				'widget' : function (data, item) {
					var widget_width = settings.width ? settings.width : 960;
					var offset = 260;
					var right_width = widget_width - offset;
					$(item.selector).width(widget_width);
					$(item.right).width(right_width);
					if (settings.title) {
						$(item.title).html(settings.title);
					}
					if (settings.slogan) {
						$(item.slogan).html(settings.slogan);
					}
					if (settings.theme) {
						$(item.selector).addClass(settings.theme);
					}
					if (settings.corner) {
						$(item.selector).addClass("corner-" + 4 * settings.corner);						
					}
				},
				'year_switch' : function (data, item) {
					year_switch = $(item.selector);
					year_switch.change(function () {
						if (last_action) {
							last_action();
						}
					});
					getStatsGeneral();
					last_action = function () { getStatsGeneral(); }
				},
				'general_switch' : function (data, item) {
					$(item.selector).click(function () {
						getStatsGeneral();
						last_action = function () { getStatsGeneral(); }
					});
				},
				'clients_switch' : function (data, item) {
					$(item.selector).click(function () {
						getStatsClients();
						last_action = function () { getStatsClients(); }
					});
				}
			});
		});

		return this;
	};
})(jQuery)
