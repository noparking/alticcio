$(document).ready(function () {
	$("textarea.dteditor").dteditor({
		'tags' : [
			{'tag' : "em", 'button' : "<em>Emphasis</em>"},
			{'tag':	"strong", 'button' : "<strong>Strong</strong>"},
			{'tag':	"u", 'button' : "<u>Underline</u>"},
			{'tag':	"strike", 'button' : "<strike>Strike</strike>"}
		],
		'lists' : [
			{'tag' : "ul", 'button' : "List"},
			{'tag' : "ol", 'button' : "List123"}
		],
		'forms' : [
			{
				'tag' : "img",
				'button' : "Image",
				'confirm' : "Confirm",
				'cancel' : "Cancel",
				'ajax' : image_upload_url,
				'waiting' : "Uploading file, please wait...",
				'callback' : insertImage,
				'fields' : [
					{'name' : "image", 'type' : "file", 'label' : "Image : "},
					{'name' : "title", 'type' : "text", 'label' : "Title : ", 'selection' : true},
					{'name' : "size", 'type' : "select", 'label' : "Resize : ", 'options' : [
						{'value' : 100, 'label' : "100x100"},
						{'value' : 200, 'label' : "200x200"},
						{'value' : 300, 'label' : "300x300"},
					]}
				]
			},
			{
				'tag' : "a",
				'button' : "Link",
				'confirm' : "Confirm",
				'cancel' : "Cancel",
				'callback' : insertLink,
				'fields' : [
					{'name' : "href", 'type' : "text", 'label' : "URL : "},
					{'name' : "text", 'type' : "hidden", 'selection' : true}
				]
			}
		],
		'preview' : {'open' : "Preview", 'close' : "Close preview" }
	});
});

var insertLink = function (data) {
	var begin = '<a href="' + data.href + '">' + data.text;
	return {'html' : begin + '</a>', 'offset' : begin.length};
}

var insertImage = function (data) {
	var html = '<img src="' + data.src + '" title="' + data.title + '" alt="' + data.alt + '" />';
	return {'html' : html, 'offset' : html.length};
}
