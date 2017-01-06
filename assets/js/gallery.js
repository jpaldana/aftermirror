$('#gallery-cDesc').wysihtml5();
$('#gallery-file').change(function() {
	$('#gallery-file-upl').submit();
});
$('#gallery-upl-btn').on('click', function() {
	$('#gallery-file').click();
});
$('#gallery-upl-btn-url').on('click', function() {
	var val = prompt("URL of image?");
	if (val !== null) {
		$('#gallery-file-url').attr('value', val);
		$('#gallery-file-upl').submit();
	}
});