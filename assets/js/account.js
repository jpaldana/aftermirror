$('#profile-picture-file-upl').change(function() {
	//$('#profile-picture-file-upl-form').submit();
	var file = this.files[0];
	if (file.type.substring(0, 5) === "image") {
		var formData = new FormData($('#profile-picture-file-upl-form')[0]);
		$('#profile-picture-file-upl-progress').show(200);
		$.ajax({
			url: $('#profile-picture-file-upl-form').attr('action'),
			type: 'POST',
			xhr: function() {
				var myXhr = $.ajaxSettings.xhr();
				if (myXhr.upload) {
					myXhr.upload.addEventListener('progress', profileImageUploadHandler, false);
				}
				return myXhr;
			},
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function() { $.notify('Uploading...', { className: "info" }); },
			success: function() { $.notify('Successfully uploaded profile picture!', { className: "success" }); $('#profile-picture-file-upl-progress').hide(200); location.reload(); },
			error: function() { $.notify('Failed to upload.', { className: "warn" }); }
		});
	}
	else {
		$.notify("Invalid file. You can only upload images.", { className: "warn" });
	}
});
$('#profile-image-upload-btn').off('click').on('click', function() {
	$('#profile-picture-file-upl').click();
});

function profileImageUploadHandler(e) {
	if (e.lengthComputable) {
		$('#profile-picture-file-upl-progress').attr({ value: e.loaded, max: e.total });
	}
}