$(document).ready(function() {
	opened_menu = $('li.menu-current').find('ul');
	opened_menu.css('display', 'block');
	$('li.menu-main-0').click(function () {
		$(this).parents('ul').find('ul').css('display', 'none');
		$(this).find('ul').css('display', 'block');
		$("li.menu-main-0").removeClass('menu-current');
		$(this).addClass('menu-current');
	});
});
