$(document).ready(function () {
	$(".db-date").datepicker({
		showButtonPanel: true,
    onClose: function() {
      date = $(this).datepicker("getDate");
      id = $(this).attr('id').replace("-visible", "");
      if (date) {
        $('#' + id).val(date.getTime()/1000);
      }
    }
  });
});