$(document).ready(function() {

	$("li.flag").click(function() {
		$("li.flag").removeClass("selected-flag");
		$(this).addClass("selected-flag");
	});
	$("li.flag").click(function() {
		var flag = $(this).find("img").clone();
		flag.attr('src' ,flag.attr('src').replace('size=60', 'size=600'));
		$("#flag").html(flag);
		$("#flag img").click(function(event) {
			event.stopPropagation();
			var x = event.pageX - event.target.offsetLeft;
			var y = event.pageY - event.target.offsetTop;
			var url = $(this).attr('src').replace("drapeau.php", "zone.php");
			url = url + "&x=" + x + "&y=" + y;
			$(".colors").css('position', "absolute");
			$(".colors").css('left', event.pageX);
			$(".colors").css('top', event.pageY);
			$(".colors").css('display', "block");
			$("body").unbind("click");
			$("body").click(function() {
				$(".colors").css('display', "none");
			});
			$("li.color").unbind("click");
			$("li.color").click(function() {
				var color = $.fmtColor($(this).css("background-color"), "hexadecimal").replace("#", "");
				$.getJSON(url, function(data) {
					var src = $("#flag img").attr('src');
					if (!src.match("zone" + data.zone)) {
						src += "&zone" + data.zone + "=" + color;
					}
					else {
						var reg = new RegExp("zone" + data.zone + "=[0-9a-f]{6}");
						src = src.replace(reg, "zone" + data.zone + "=" + color);
					}
					$("#flag img").attr('src', src);
				});
			});
		});
	});

});
