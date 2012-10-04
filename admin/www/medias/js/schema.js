$(document).ready(function () {
     $(".tables_base li a").click(function(){
    	 $("dt").css('background-color','white');
         var nom = $(this).attr('href').replace("#", "");
         $("dt a[name=" + nom + "]").css('background-color','red');
     });
});