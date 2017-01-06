$('#storage-file-upl').change(function() {
	var formData = new FormData($('#storage-file-upl-form')[0]);
	$('#storage-file-upl-progress').show(200);
	$.ajax({
		url: $('#storage-file-upl-form').attr('action'),
		type: 'POST',
		xhr: function() {
			var myXhr = $.ajaxSettings.xhr();
			if (myXhr.upload) {
				myXhr.upload.addEventListener('progress', storageUploadHandler, false);
			}
			return myXhr;
		},
		data: formData,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function() { $.notify('Uploading...', { className: "info" }); },
		success: function() { $.notify('File successfully uploaded!', { className: "success" }); $('#storage-file-upl-progress').hide(200); },
		error: function() { $.notify('Failed to upload.', { className: "warn" }); }
	});
});
$('#storage-upload-btn').off('click').on('click', function() {
	$('#storage-file-upl').click();
});

function storageUploadHandler(e) {
	if (e.lengthComputable) {
		$('#storage-file-upl-progress').attr({ value: e.loaded, max: e.total });
	}
}