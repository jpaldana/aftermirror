<?php
$img = $data["image"];
$title = $data["title"];
$subtitle = $data["subtitle"];
$content = $data["content"];
$text = isset($data["text"]) ? $data["text"] : "View";

$style = isset($data["style"]) ? $data["style"] : "";

if ($img) {
	echo "
		<section class='wrapper style3 special darkspan' style='background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.5)), url({$img});{$style}'>
	";
}
else {
	echo "
		<section class='wrapper special' style='{$style}'>
	";
}

$togglehash = "tgl_" . sha1("toggle".$title);
?>

	<div class="inner">
		<header class="major narrow">
			<h2><?php echo $title; ?></h2>
			<h5><?php echo $subtitle; ?></h5>
			<div id="<?php echo $togglehash; ?>" style="display: none;"><?php echo $content; ?></div>
		</header>
		<?php
			echo "
				<ul class='actions'>
					<li><span class='button big alt toggle' data-toggle='{$togglehash}'>{$text}</span></li>
				</ul>
			";
		?>
	</div>
</section>