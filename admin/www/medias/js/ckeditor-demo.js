$(document).ready(function() {
	CKEDITOR.replace('ckeditor_demo', {
		language : lang,
		toolbar : 'Doublet',
		toolbar_Doublet : [
			['Source'],
			['Bold','Italic','Underline','Strike'],
			['NumberedList','BulletedList'],
			['Link','Unlink'],
			['Image']
		],
		filebrowserUploadUrl : upload_url,
		on : {
			instanceReady : function() {
				this.dataProcessor.writer.setRules('p', {
					lineBreakChars : "\n"
				});
			}
		}
	});
});
