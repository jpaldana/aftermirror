<?php
$img = $data["image"];
$title = $data["title"];
$content = $data["content"];
$href = $data["href"];
$text = $data["text"];

$section = isset($data["section"]) ? $data["section"] : "wrapper style1";
$article = isset($data["article"]) ? $data["article"] : "feature left";
$style = isset($data["style"]) ? $data["style"] : "";
?>
<section class="<?php echo $section; ?>" style="<?php echo $style; ?>">
	<div class="inner">
		<article class="<?php echo $article; ?>">
			<?php
				if ($img) {
					echo "
						<span class='image'><img src='{$img}' alt='' /></span>
					";
				}
			?>
			<div class="content">
				<h2><?php echo $title; ?></h2>
				<p><?php echo $content; ?></p>
				<?php
					if ($href && $text) {
						echo "
							<ul class='actions'>
								<li><a href='{$href}' class='button alt'>{$text}</a></li>
							</ul>
						";
					}
				?>
			</div>
		</article>
	</div>
</section>