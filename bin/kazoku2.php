<?php
if (isset($_GET["picker"])) {
	$col = array();
	$colt = array();
	include("module/cachedquery.php");

	if (isset($_GET["query"])) {
		$title = base64_decode($_GET["query"]);
		$background = "";
		$titleHash = substr(sha1($title), 0, 8);
		$bgPath = strtr("data/media/{$titleHash}.background.jpg", array(" " => "_"));
		if (file_exists($bgPath)) $background = $bgPath;
		
		echo "
		<div class='queryBox' style='background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url({$background});'>
		<h2>{$title}</h2>
		<select class='queryLoader' data-title=\"{$title}\">
			<option>select...</option>
		";
		foreach ($col[$title] as $episode => $links) {
			$ts = time_since(time() - $colt["{$title}{$episode}"]);
			$data = "";
			$types = "";
			
			foreach ($links as $type => $src) {
				$data .= "{$type};{$src}|";
				$types .= "{$type}, ";
			}
			$data = substr($data, 0, -1);
			$types = substr($types, 0, -2);
			
			if ($episode > 0) {
				echo "<option value='{$data}'>Episode {$episode} &mdash; {$ts} ago ({$types})</option>";
			}
			else {
				echo "<option value='{$data}'>Movie &mdash; {$ts} ago ({$types})</option>";
			}
		}
		echo "
		</select>
		<br/>
		<small>or click browse again to start over</small>
		</div>
		";
	}
	else {
		echo "<div class='poster-wrapper'>";
		foreach ($col as $title => $blob) {
			$background = "";
			$titleHash = substr(sha1($title), 0, 8);
			$bgPath = strtr("data/media/{$titleHash}.background.jpg", array(" " => "_"));
			if (file_exists($bgPath)) $background = $bgPath;
			$titleEnc = base64_encode($title);
			
			echo "
				<div class='poster' style='background-image: url({$bgPath});' data-title='{$titleEnc}'><span class='posterText'>{$title}</span><span class='posterBtn'><i class='fa fa-chevron-circle-right'></i></span></div>
			";
		}
		echo "</div>";
	}
}
else {
	requireLogin();
	
	var_dump(AUTH_USER);
	$page->block("header", array("css" => array("//gitcdn.link/repo/DoclerLabs/Protip/master/protip.min.css", "/assets/css/kazoku3.css", "/assets/css/kazoku-range.css")));
	$preload = "";
	$preload_title = "";
	
	$roomID = AUTH_USER . "-" . substr(sha1(uniqid()), 0, 3);
	if (isset($_GET["r"])) $roomID = $_GET["r"];

	echo "
	<div id='browseDialog' style='display: none;'>
	</div>
	
	<div style='position: absolute; left: 0; top: 45px;'>
	<div id='kazoku-min-log'>
		<i class='fa fa-wifi'></i> <span id='kazoku-ping'>&mdash;</span>&nbsp;&nbsp;
		<input type='checkbox' id='kazoku-status-force-preload' /><label for='kazoku-status-force-preload'>Preload?</label>
		<input type='checkbox' id='kazoku-status-prefer-hd' /><label for='kazoku-status-prefer-hd'>Prefer HD?</label>
		<span id='kazoku-status-preload-progress-wrapper'><progress id='kazoku-status-preload-progress' value='0' max='0'></progress> <span id='kazoku-status-preload-progress-text'>0</span>%</span>
		<span class='button special small' id='browseDialogBtn'>Browse...</span>
	</div>
	</div>

	<div id='kz-container'>

	<div style='width: 100%;' id='kazoku-popout'>
	<!-- Controls -->
	<div id='kz-controller'>
		<input type='text' id='kazoku-textinput' placeholder='Say something...' />
	</div>
	<br />
	<!-- Screen -->
	<div id='kz-video-container'>
		<div class='row uniform'>
			<div class='9u 12u$(small) kazoku-wrapper' style='background-color: #000; padding: 0;'>
				<video id='kazoku-media' style='box-sizing: border-box; width: 100%; min-height: 400px;'>
					<source src='' type='video/mp4' />
				</video>
				<div id='control'>
					<input type='range' id='ctl_seeker' value='0' max='1' />
					<div style='float: left; padding-left: 10px;'>
						<span class='separator'></span>
						<span class='fa fa-play' id='ctl_play'></span>
						<span class='fa fa-pause' id='ctl_pause'></span>
						<span class='separator'></span>
						<span class='fa fa-volume-off' id='ctl_vol_mute'></span>
						<span class='fa fa-volume-down' id='ctl_vol_down'></span>
						<span id='ctl_vol'>100</span>%&nbsp;
						<span class='fa fa-volume-up' id='ctl_vol_up'></span>
						<span class='separator'></span>
						<span id='ctl_time_label'>0:00 / 0:00</span>
					</div>
					<div style='float: right; padding-right: 10px;'>
						<i class='fa fa-expand' id='ctl_fullscreen'></i>
					</div>
					<br clear='all' />
				</div>
			</div>
			<div class='3u 12u$(small)'>
				<div id='chat'>
				</div>
			</div>
		</div>
	</div>

	<!--
	<div class='row uniform'>
		<div class='9u 12u$(small) kazoku-wrapper' style='background-color: #000; padding: 0;'>
			<video id='kazoku-media' style='box-sizing: border-box; width: 100%; min-height: 400px;'>
				<source src='' type='video/mp4' />
			</video>
			<div id='control'>
				<input type='range' id='ctl_seeker' value='0' max='1' />
				<div style='float: left; padding-left: 10px;'>
					<span class='separator'></span>
					<span class='fa fa-play' id='ctl_play'></span>
					<span class='fa fa-pause' id='ctl_pause'></span>
					<span class='separator'></span>
					<span class='fa fa-volume-off' id='ctl_vol_mute'></span>
					<span class='fa fa-volume-down' id='ctl_vol_down'></span>
					<span id='ctl_vol'>100</span>%&nbsp;
					<span class='fa fa-volume-up' id='ctl_vol_up'></span>
					<span class='separator'></span>
					<span id='ctl_time_label'>0:00 / 0:00</span>
				</div>
				<div style='float: right; padding-right: 10px;'>
					<i class='fa fa-expand' id='ctl_fullscreen'></i>
				</div>
				<br clear='all' />
			</div>
		</div>
		<div class='3u 12u$(small)'>
			<div id='chat'>
			</div>
			<input type='text' placeholder='Say something...' id='kazoku-textinput' />
		</div>
	</div>
	-->
	
	<!-- Floor -->
	<div id='kz-floor'>
		<!--
		<div class='kz-user' id='u1'>
			<span class='kz-name'>" . AUTH_USER . "</span>
			<div class='kz-head' style='background-image: url(/account.ps?pic=" . AUTH_USER . ");'></div>
		</div>
		<div class='kz-user' id='u2'>
			<span class='kz-name'>athenyx</span>
			<div class='kz-head' style='background-image: url(/account.ps?pic=athenyx);'></div>
		</div>
		-->
	</div>
	
	</div>
	
	<script>
		var name = '" . AUTH_USER . "';
		var room = '{$roomID}';
		var preload_title = '{$preload_title}';
		var preload = '{$preload}';
		var server = '" . AM_SERVER . "';
	</script>
	";

	$page->block("footer", array("js" => array("https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.4.5/socket.io.min.js", "https://cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.2/jquery.scrollTo.min.js", "https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.1/js.cookie.min.js", "https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.10/clipboard.min.js", "//gitcdn.link/repo/DoclerLabs/Protip/master/protip.min.js", "/assets/js/kz-play.js", "/assets/js/kazoku3.js")));	
}
?>