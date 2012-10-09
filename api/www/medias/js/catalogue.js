(function($) {
	$.fn.widgetCatalogue = function(settings) {
		var area = {};
		var cart = { items : {} };
		var dico;
		var overlay;
		var forfaits;
 		var taux_tva;
		var active_zone;
		var token;

		function getToken () {
			return Math.round(Math.random() * 10000000000000000);
		}

		function apiRequest(uri, callback, item_callbacks) {
			params = {
				key : settings.key,
				format : "jquery",
				widget : 1
			}
			if (settings.user && settings.user.id) {
				params.track = settings.user.id;
			}
			$.getJSON(settings.api + "/" + uri + "?callback=?", params, function (data) {
				if (data.error) {
					alert("API Doublet : " + data.message);
					return false;
				}
				if (data.jq) {
					callback(data, data.jq.html, data.jq.items);
					for (i in item_callbacks) {
						if (data.jq.items[i]) {
							for (j in data.jq.items[i]) {
								item_callbacks[i](data, data.jq.items[i][j]);
							}
						}
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

		function getProduit(id) {
			var prices_option = "";
			if (settings.prices) {
				prices_option = "/"+settings.prices;
			}
			apiRequest("catalogue/product/"+id+prices_option, function (data, html, items) {
				area.product.html(html);
				display(area.product);
			}, {
				'close' : function (data, item) {
					$(item.selector).click(function () {
						display(area.category);
					});
				},
				'image_prev' : function (data, item) {
					$(item.selector).click(function () {
						var active = $(item.active)
						if (active.prev().length) {
							var prev = active.prev();
						}
						else {
							var prev = active.nextAll().eq(-1);
						}
						prev.addClass("active");
						active.removeClass("active");
						return false;
					});
				},
				'image_next' : function (data, item) {
					$(item.selector).click(function () {
						var active = $(item.active)
						if (active.next().length) {
							var next = active.next();
						}
						else {
							var next = active.prevAll().eq(0);
						}
						next.addClass("active");
						active.removeClass("active");
						return false;
					});
				},
				'addtocart' : function(data, item) {
					$(item.selector).click(function () {
						var texte_perso = item.selector_perso_texte ? $(item.selector_perso_texte).val() : "";
						if (cart.items[item.id] && cart.items[item.id][texte_perso]) {
							cart_item = cart.items[item.id][texte_perso];
							cart_item.qty++;
							cart_item.qtyfield.val(cart_item.qty);
							calculateCartItemPrice(item.id, texte_perso);
							alert(cart_item.name + "\n" + cart_item.variant + ' x ' + cart_item.qty + " " + dico['in_your_cart']);
						}
						else {
							texte_perso_url = texte_perso.replace(/\n/g, "%0A").replace(/\+/g, "%2B").replace(/\//g, "%2F");
							apiRequest('widget/catalogue/addtocart/' + token + '/' + item.product_id + '/' + item.id + '/' + texte_perso_url, function (data, html, items) {
								cart.content.append(html);
							}, {
								'cart_item' : function (data, cart_item) {
									if (!cart.items[item.id]) {
										cart.items[item.id] = {};
									}
									if (!cart.items[item.id][texte_perso]) {
										cart.items[item.id][texte_perso] = {};
									}
									cart.items[item.id][texte_perso].obj = $(cart_item.selector);
									cart.items[item.id][texte_perso].name = data.name;
									cart.items[item.id][texte_perso].variant = data.sku.name;
									cart.items[item.id][texte_perso].product = item.product_id;
									cart.items[item.id][texte_perso].pricezone = $(cart_item.price_selector);
									cart.items[item.id][texte_perso].unit_price = cart_item.unit_price;
									cart.items[item.id][texte_perso].qty = 1;
									cart.items[item.id][texte_perso].item_id = cart_item.item_id;
									calculateCartItemPrice(item.id, texte_perso);
									alert(data.name + "\n" + data.sku.name + ' ' + dico['added_to_cart']);
								},
								'removefromcart' : function (data, removefromcart) {
									$(removefromcart.selector).click(function () {
										removeCartItem(item.id, texte_perso);
									});
								},
								'qty' : function(data, qty) {
									cart.items[item.id][texte_perso].qtyfield = $(qty.selector);
									cart.items[item.id][texte_perso].qty = 1;
									cart.items[item.id][texte_perso].qtyfield.change(function () {
										qty = cart.items[item.id][texte_perso].qtyfield.val();
										if (qty == 0) {
											removeCartItem(item.id, texte_perso);
										} else {
											cart.items[item.id][texte_perso].qty = qty;
											calculateCartItemPrice(item.id, texte_perso);
										}
									});
								}
							});
						}
					});
				}
			});
		}

		function removeCartItem(id, texte_perso) {
			if (confirm(dico['Are_you_sure'])) {
				cart.items[id][texte_perso].obj.remove();
				apiRequest('widget/catalogue/removefromcart/' + cart.items[id][texte_perso].item_id);
				delete cart.items[id][texte_perso];
				calculateCartPrice();
			}
			else {
				cart.items[id][texte_perso].qtyfield.val(cart.items[id][texte_perso].qty);
			}
		}

		function clearCart() {
			for (id in cart.items) {
				for (texte in cart.items[id]) {
					cart.items[id][texte].obj.remove();
				}
				delete cart.items[id];
			}
			calculateCartPrice();
		}

		function calculateCartPrice() {
			var total = 0;
			var nb = 0; 
			for (i in cart.items) {
				for (j in cart.items[i]) {
					total += cart.items[i][j].qty * cart.items[i][j].unit_price;
					nb += parseInt(cart.items[i][j].qty);
				}
			}
			var forfait = 0;
			if (total) {
				for (i in forfaits) {
					if (total >= i) {
						forfait = forfaits[i];
					}
				}
			}
			cart.forfaitzone.html(forfait.toFixed(2));
			total += forfait;
			tva = (taux_tva * total / 100);
			cart.tvazone.html(tva.toFixed(2));
			total += tva;
			cart.price = total.toFixed(2);
			cart.pricezone.html(cart.price);
			cart.totalzone.html(cart.price);
			cart.numberzone.html(nb);
			if (cart.price == 0) {
				cart.checkout.hide();
				cart.table.hide();
				cart.empty.show();
			}
			else {
				cart.checkout.show();
				cart.table.show();
				cart.empty.hide();
			}
		}

		function calculateCartItemPrice(id, texte_perso) {
			cart.items[id][texte_perso].pricezone.html((cart.items[id][texte_perso].qty * cart.items[id][texte_perso].unit_price).toFixed(2));
			calculateCartPrice();
		}

		function getCategorie(id, offset) {
			var request_limit = "";
			if (settings.products_per_page) {
				request_limit = "/"+settings.products_per_page+"/"+offset;
			}
			var prices_option = "/HT";
			if (settings.prices) {
				prices_option = "/"+settings.prices;
			}
			apiRequest("catalogue/category/"+id+prices_option+request_limit, function (data, html, items) {
				area.category.html(html);
				display(area.category);
			}, {
				'element' : function(data, item) {
					if (settings.products_hide && jQuery.inArray(item.element, settings.products_hide) > -1) {
						$(item.selector).hide();
					}
				},
				'more' : function(data, item) {
					$(item.selector).click(function () {
						getProduit(item.id);
					});
				},
				'estimate' : function(data, item) {
					$(item.selector).click(function () {
						alert(dico['Estimate'] + item.id);
					});
				},
				'pager_previous' : function(data, item) {
					if (data.offset > 0) {
						$(item.selector).addClass("active");
						var previous_offset = data.offset - data.limit;
						if (previous_offset < 0) {
							previous_offset = 0;
						}
						$(item.selector).click(function () {
							getCategorie(id, previous_offset);
						});
					}
				},
				'pager_next' : function(data, item) {
					if (data.offset + data.nb < data.total) {
						$(item.selector).addClass("active");
						var next_offset = data.offset + data.limit;
						$(item.selector).click(function () {
							getCategorie(id, next_offset);
						});
					}
				},
				'pager_infos' : function(data, item) {
					if (data.limit > 0 && data.total > 0) {
						var page = Math.floor(data.offset / data.limit) + 1;
						var nb_pages = Math.ceil(data.total / data.limit);
						$(item.selector).html(page + "/" + nb_pages);
					}
				}
			});
		}

		function getCatalogue() {
			apiRequest('catalogue', function(data, html, items) {
				catalogue_area.prepend(html);
				if (parseInt(data.home)) {
					getCategorie(data.home, 0);
				}
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
				'show_products' : function (data, item) {
					$(item.selector).bind(item.event, function (event) {
						getCategorie(item.id, 0);
						event.stopPropagation();
					});
				}
			});
		}

		this.each(function () {
			var element = $(this);
			apiRequest('widget/catalogue', function(data, html, items) {
				dico = data.jq.dico;
				forfaits = data.forfaits;
				taux_tva = data.jq.tva ? data.jq.tva : 0;
				element.append(html);
				overlay = $(items['overlay'][0].selector);
				overlay.hide();
				catalogue_area = $(items['catalogue_area'][0].selector);
				area.category = $(items['category_area'][0].selector);
				area.product = $(items['product_area'][0].selector);
				area.cart = $(items['cart_area'][0].selector);
				for (z in area) {
					area[z].hide();
				}
				cart.content = $(items['cart_content'][0].selector);
				cart.pricezone = $(items['cart_price'][0].selector);
				cart.totalzone = $(items['cart_total'][0].selector);
				cart.forfaitzone = $(items['cart_forfait'][0].selector);
				cart.tvazone = $(items['cart_tva'][0].selector);
				cart.numberzone = $(items['cart_number'][0].selector);
				cart.empty = $(items['cart_empty'][0].selector);
				cart.table = $(items['cart_table'][0].selector);
				cart.table.hide();
				token = getToken();
				getCatalogue();
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
					if (settings.contact) {
						$(item.contact).html(settings.contact);
					}
					if (settings.theme) {
						$(item.selector).addClass(settings.theme);
					}
					if (settings.corner) {
						$(item.selector).addClass("corner-" + 4 * settings.corner);						
					}
				},
				'action_cart' : function (data, item) {
					$(item.selector).click(function () {
						display(area.cart);
					});
				},
				'cart_close' : function (data, item) {
					$(item.selector).click(function () {
						close(area.cart);
					});
				},
				'cart_checkout' : function (data, item) {
					cart.checkout = $(item.selector);
					$(item.selector).hide();
					cart.href = $(item.selector).attr('href');
					$(item.selector).click(function () {
						href = cart.href + "&token=" + token + "&cart=";
						var separator = "";
						for (id in cart.items) {
							for (texte in cart.items[id]) {
								href += separator + cart.items[id][texte].item_id + "x" + cart.items[id][texte].qty;
								separator = ",";
							}
						}
						if (settings.logo) {
							href += "&logo=" + settings.logo;
						}
						var w = window.open(href);
						overlay.width(element.width());
						overlay.height(element.height());
						overlay.show();
						var watchClose = setInterval(function() {
							if (w.closed) {
								clearTimeout(watchClose);
								var uri = "catalogue/commande/" + token;
								var params = { key: settings.key }
								$.getJSON(settings.api + "/" + uri + "?callback=?", params, function (ok) {
									if (ok) {
										clearCart();
										token = getToken();
									}
								});
								overlay.hide();
							}
						}, 200);

						return false;
					});
				}
			});
		});

		return this;
	};
})(jQuery)

