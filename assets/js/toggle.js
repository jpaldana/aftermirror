$(".toggle").on("click", function() {
	$("#" + $(this).attr("data-toggle")).fadeToggle(400);
});