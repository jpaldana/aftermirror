<?php
requireLogin();

$page->show("header");

$roomID = AUTH_USER . "-" . substr(sha1(uniqid()), 0, 3);
if (isset($_GET["r"])) $roomID = $_GET["r"];

echo "
	<section class='wrapper'>
		<article>
		<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/featherlight/1.7.0/featherlight.min.css' />
		<link rel='stylesheet' href='/assets/css/aftermirror.css' />
		<link rel='stylesheet' href='/assets/css/kazoku-range.css' />
		<div id='kazoku-top-infobar' style='width: 100%;'>
			<div style='float: left; margin-bottom: 8px;'>
				<h4 class='kazoku-title'><span id='kazoku-title-activity'>Room {$roomID}</span></h4>
				<div class='kazoku-clipboard-wrapper'>
					<div class='input-group' style='color: black;'>
						<span class='input-group-addon'>Invite</span>
						<input type='text' class='form-control' id='kazoku-roomURL' value='https://aftermirror.com/theatre.do?r={$roomID}' readonly />
						<span class='input-group-addon kazoku-clipboard-btn' data-clipboard-target='#kazoku-roomURL'><i class='fa fa-clipboard'></i></span>
					</div>
				</div>
			</div>
			<div style='float: right;'>
				<span id='kazoku-load-media-btn' class='button small'>Load Media</span>
			</div>
			<br clear='all' />
		</div>
		<div class='kazoku-wrapper'>
			<div id='kazoku-popout' class='secondary-wrapper'>
				<div class='video-wrapper'>
					<video id='kazoku-media' src=''></video>
					<div class='textlog' id='kazoku-textlogctr'>
						<ul id='kazoku-chat'></ul>
					</div>
					<div class='control'>
						<input type='range' id='ctl_seeker' value='0' max='1' />
						<div style='float: left;'>
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
						<div style='float: right;'>
							<i class='fa fa-arrows-alt' id='ctl_fullscreen'></i>
							<i class='fa fa-expand' id='ctl_expand_player'></i>
						</div>
						<br clear='all' />
					</div>
				</div>
				<input type='text' id='kazoku-textinput' class='underbar' placeholder='say something...' />
				<div id='kazoku-min-log'>
					<i class='fa fa-wifi'></i> <span id='kazoku-ping'>&mdash;</span> 
					<input type='checkbox' id='kazoku-status-socketIO-connected' data-labelauty='Not Connected|Connected' disabled /> 
					<input type='checkbox' id='kazoku-status-force-preload' data-labelauty='No Preload|Forced Preload' /> 
					<span id='kazoku-status-preload-progress-wrapper'><progress id='kazoku-status-preload-progress' value='0' max='0'></progress> <span id='kazoku-status-preload-progress-text'>0</span>%</span>
					<input type='checkbox' id='kazoku-status-prefer-hd' data-labelauty='Standard (SD)|Prefer HD' /> 
					<input type='checkbox' id='kazoku-status-show-notifications' data-labelauty='No Notifications|Show Notifications' disabled /> 
				</div>
			</div>
			<div id='kazoku-statusbar' class='w-profile-image-wrapper'></div>
			<div id='kazoku-log'></div>
		</div>
		</article>
	</section>
	<script>
		var name = '" . AUTH_USER . "';
		var room = '{$roomID}';
	</script>
";

if (isset($_GET["m"])) {
	$dSrc = "";
	if (@unserialize(base64_decode($_GET["m"]))) {
		$media = unserialize(base64_decode($_GET["m"]));
		$dSrc = $media["src"];
	}
	echo "
		<script>
			document.getElementById('kazoku-media').src = '{$dSrc}';
		</script>
	";
}

//print_a($data);

$page->block("footer", array("js" => array("https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.4.5/socket.io.min.js", "https://cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.2/jquery.scrollTo.min.js", "https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.1/js.cookie.min.js", "https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.10/clipboard.min.js", "https://cdnjs.cloudflare.com/ajax/libs/featherlight/1.4.0/featherlight.min.js", "/assets/js/theatre.js")));
?>