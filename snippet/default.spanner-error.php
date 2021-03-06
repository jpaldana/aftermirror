<?php
$img = $data["image"];
$title = isset($data["title"]) ? $data["title"] : "Oh no! You reached a bad page.";
$content = isset($data["content"]) ? $data["content"] : "Please notify a site admin if this error persists.<br/><span style='color: #bbb;'><i class='fa fa-warning'></i> " . $data["error"] . "</span>";
$href = $data["href"];
$text = $data["text"];

$style = isset($data["style"]) ? $data["style"] : "";

if ($img) {
	echo "
		<section class='wrapper style3 special darkspan' style='background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.5)), url({$img}); height: 480px; padding-top: 140px;{$style}'>
	";
}
else {
	echo "
		<section class='wrapper special' style='height: 480px; padding-top: 200px;{$style}'>
	";
}
?>

	<div class="inner">
		<header class="major narrow">
			<h2><img src='/images/sad.png' alt='' /><br/><?php echo $title; ?></h2>
			<p><?php echo $content; ?></p>
		</header>
		<?php
			if ($href && $text) {
				echo "
					<ul class='actions'>
						<li><a href='{$href}' class='button big alt'>{$text}</a></li>
					</ul>
				";
			}
		?>
	</div>
</section>