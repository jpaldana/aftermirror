<?php
include("module/GitStatus.php");
$gitStatus = new gitStatus();
$_gitHeadStatus = $gitStatus->getGitCommitHead();
?>
		</div>
		
		<!-- Footer -->
			<footer id="footer">
				<div class="inner">
					<ul class="icons">
						<li><a href="/watch.do" class="icon fa-video-camera">
							<span class="label">Anime</span>
						</a></li>
						<li><a href="/music.do" class="icon fa-music">
							<span class="label">Music</span>
						</a></li>
						<li><a href="/gallery.do" class="icon fa-picture-o">
							<span class="label">Gallery</span>
						</a></li>
						<li><a href="/library.do" class="icon fa-book">
							<span class="label">Library</span>
						</a></li>
						<li><a href="/cloud.do" class="icon fa-cloud">
							<span class="label">Storage</span>
						</a></li>
					</ul>
					<ul class="copyright">
						<li>made with <i class="fa fa-heart"></i> &mdash; <a href="https://aftermirror.com">after|mirror</a> build.<?php echo $gitStatus->getGitCommitHead() . " (" . $gitStatus->lastModified() . " ago)"; ?></li>
						<li>design: <a href="https://templated.co/">TEMPLATE</a>.</li>
					</ul>
				</div>
			</footer>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/skel.min.js"></script>
			<script src="assets/js/util.js"></script>
			<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
			<script src="assets/js/main.js"></script>
		
		<?php
			if (isset($data) && isset($data["js"])) {
				foreach ($data["js"] as $js) {
					echo "<script src='{$js}'></script>";
				}
			}
		?>
	</body>
</html>