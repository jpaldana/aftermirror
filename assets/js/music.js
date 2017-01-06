$("a.folderToggle").on("click", function(e) {
	var $target = $("#" + $(this).attr("data-toggle"));
	$target.toggle(500);
	
	e.preventDefault();
});

$(".mediaitem").on("click", function(e) {
	$("audio").each(function() {
		//$(this)[0].stop();
		$(this).remove();
	});
	$("#" + $(this).attr("data-container")).append(
		"<audio autoplay controls style='width: 100%;'><source src='/music.ps?m=" + $(this).attr("data-src") + "' type='audio/mp3' /></audio>"
	);
});