<?php
$img = $data["image"];
$title = $data["title"];
$content = $data["content"];
$href = $data["href"];
$text = $data["text"];
?>
<section class="wrapper special">
	<div class="inner">
		<header class="major narrow">
			<h2><?php echo $title; ?></h2>
			<p><?php echo $content; ?></p>
		</header>
		<div class="image-grid">
			<?php
				foreach ($img as $blob) {
					echo "
						<a href='{$blob['href']}' class='image'><img src='{$blob['src']}' alt='' /></a>
					";
				}
			?>
		</div>
		<ul class="actions">
			<li><a href="<?php echo $href; ?>" class="button big alt"><?php echo $text; ?></a></li>
		</ul>
	</div>
</section>