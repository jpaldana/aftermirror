<?php
$page->block("header", array("css" => array("https://www.statuscake.com/App/Widget/table.css")));
?>
<section class='wrapper'>
<div class='inner'>
<article>

<div style="background-color: white;">
	<div class="StatusCake"></div>
</div>

</article>
</div>
</section>
<?php
$page->block("footer", array("js" => array("/assets/js/uptime.js")));
?>