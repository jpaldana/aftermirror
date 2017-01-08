$("a[alt-href]").on("click", function(e) {
	e.preventDefault();
	location.href = $(this).attr("alt-href");
});