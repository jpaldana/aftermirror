/* kazoku */
/* -- vars */

navigatorAjax = false; // override global ajax
var socketIOserver = '//owl.aftermirror.com:8182/';
var socket;

var kazokuNotifyGranted = false;
var kazokuNotifyAvailable = false;
var kazokuJSHookActive = false;
var kazokuEventHookActive = false;
var kazokuPingPongInterval;
var kazokuRoomStatInterval;
var kazokuVideoStatInterval;

var activeSeek = false;
var chatHideDelay = 10000;
var latency = -1;
var kazokuPreload;
var mediaVol = 1.0;
var kazokuExpandedPlayer = false;
var kazokuIsFullscreen = false;

var $media = $("#kazoku-media");

/* -- init */
$("#browseDialogBtn").on("click", function(e) {
	if (!$("#browseDialog").is(":visible")) {
		// reload
		$("#browseDialog").fadeIn(200).html("<section class='wrapper style2' id='outerBrowseDialog'><article id='innerBrowseDialog'><div style='padding-top: 100px; text-align: center;'><h2>loading, please wait...</h2></div></article></section>");
		$("#innerBrowseDialog").load("kazoku.ps?picker");
	}
	else {
		$("#innerBrowseDialog").load("kazoku.ps?picker");
	}
	$(window).scrollTo("#browseDialog", 200, { offset: { top: -50 } });
});
$("#browseDialog").on("click", ".poster", function() {
	$("#innerBrowseDialog").load("kazoku.ps?picker&query=" + $(this).attr("data-title"));
});
$("#browseDialog").on("change", ".queryLoader", function() {
	var links = $(this).val().split("|");
	var data = {};
	for (var i = 0; i < links.length; i++) {
		var split = links[i].split(";");
		data[split[0]] = split[1];
	}
	kazokuLoadMedia($(this).attr("data-title"), data);
	$("#browseDialog").hide();
});

/* -- helpers */
function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
function ss2hhmmss(seconds) {
	seconds = parseInt(seconds);
	var hours = Math.floor(seconds / 3600);
	seconds %= 3600;
	var minutes = Math.floor(seconds / 60);
	var seconds = seconds % 60;
	
	if (seconds < 10) {
		seconds = '0' + seconds;
	}
	if (hours > 0) {
		if (minutes < 10) {
			minutes = '0' + minutes;
		}
		return hours + ':' + minutes + ':' + seconds;
	}
	else {
		return minutes + ':' + seconds;
	}
}
function kazokuPreloadUpdateProgress(e) {
	if (e.lengthComputable) {
		var percent = Math.round((e.loaded / e.total) * 100);
		$('#kazoku-status-preload-progress').attr('max', e.total);
		$('#kazoku-status-preload-progress').attr('value', e.loaded);
		$('#kazoku-status-preload-progress-text').text(percent);
	}
	else {
		console.log('[preload] non-computable length...');
	}
}

/* -- chat */
function kazokuLogHTML(html) {
	$div = $('<div />').html(html);
	$('#chat').append($div);
	$('#chat').scrollTo($div, 100);
}
function kazokuLog(name, text) {
	kazokuLogHTML('<b>' + name + '</b>: ' + htmlEntities(text));
}

/* -- pre-init */
kazokuLog('!', 'kazoku initializing...');
kazokuLog('!', 'attempting to connect...');
kazokuLog('!', 'connecting to (' + socketIOserver + ')...');

/* -- init */
socket = io.connect(socketIOserver);
socket.on('connect', function() {
	kazokuLog('!', 'connected!');
	kazokuEventInit();
});
socket.on('error', function(data) {
	kazokuLog('[error]', data || 'Unknown error.');
	kazokuEventStop();
});
socket.on('connect_error', function(data) {
	$('#kazoku-media')[0].pause();
	kazokuLog('[error]', data || 'Connection error.');
	kazokuEventStop();
});
socket.on('connect_failed', function(data) {
	kazokuLog('[error]', data || 'Failed to connect.');
	kazokuEventStop();
});
socket.on('disconnect', function() {
	$('#kazoku-media')[0].pause();
	kazokuLog('[warn]', 'disconnected! (check internet?)');
	$('#kazoku-ping').html('&infin; ms');
	kazokuEventStop();
});

/* -- socket handlers */

/* socket functions */
function socketPing() {
	socket.emit('request ping', Date.now(), latency, function(startTime) {
		latency = Date.now() - startTime;
		$('#kazoku-ping').text(latency + ' ms');
	});
}
function socketRoomStat() {
	socket.emit('request room stat');
}
function kazokuLoadMedia(title, data) {
	var episode = ""; // no longer used
	console.log(data);
	socket.emit('set room media', { title: title, episode: episode, src: data });
};
function videoStatUpdate() {
	if ($('#kazoku-media').attr('src') !== '') {
		var mediaCurrent = parseInt($('#kazoku-media')[0].currentTime);
		var mediaDuration = parseInt($('#kazoku-media')[0].duration);
		
		$('#ctl_time_label').text(ss2hhmmss(mediaCurrent) + ' / ' + ss2hhmmss(mediaDuration));
		if (!activeSeek) {
			$('#ctl_seeker').prop('value', mediaCurrent).prop('max', mediaDuration);
		}
		
		socket.emit('player status', { playState: $('#kazoku-media')[0].paused, position: mediaCurrent });
	}
}

/* > status */
socket.on('connection ok', function(data) {
	kazokuLog('! [done]', 'Connection OK!');
	socketPing();
	socketRoomStat();
	kazokuJSHook();
});
socket.on('recv room stat', function(data) {
	for (var user in data){
		if (data.hasOwnProperty(user)) {
			var stat = data[user];
			if ($('#kazoku-statusbar div[data-user=' + user + ']').length === 0) {
				$('#kazoku-statusbar').append(
					$('<div />').attr('data-user', user)
								.addClass('w-profile-picture kazoku-profile-picture')
								.css('background-image', 'url(/account.ps?pic=' + user + ')')
								.append(
									$('<span />').addClass('title') 
												.text(user)
								)
								.append(
									$('<span />').addClass('ping')
												.text('--')
								)
								.append(
									$('<span />').addClass('status')
												.text('--')
								)
				);
				kazokuLogHTML("<div style='text-align: center;'><img src='/account.ps?pic=" + user + "' alt='' style='width: 100%;' /><br/>" + user + " has joined the room!</div>");
			}
			$('#kazoku-statusbar div[data-user=' + user + '] span.ping').text(stat.ping);
		}
	}
});
socket.on('ready media', function(data) {
	$('#kazoku-title-activity').html(data.title);
	if ($('#kazoku-status-prefer-hd').is(':checked')) {
		if (typeof data.media.HD == "string") {
			$('#kazoku-media').attr('src', data.media.HD);
			kazokuLog('!', 'Loaded HD stream: ' + data.title);
		}
		else if (typeof data.media.SD == "string") {
			$('#kazoku-media').attr('src', data.media.SD);
			kazokuLog('!', 'Loaded SD stream: ' + data.title);
		}
		else {
			kazokuLog('[error] system', 'No matching source files.');
			console.log('[error] system: no HD or SD stream?');
			console.log(data);
		}
	}
	else {
		if (typeof data.media.SD == "string") {
			$('#kazoku-media').attr('src', data.media.SD);
			kazokuLog('!', 'Loaded SD stream: ' + data.title);
		}
		else if (typeof data.media.HD == "string") {
			$('#kazoku-media').attr('src', data.media.HD);
			kazokuLog('!', 'Loaded HD stream: ' + data.title);
		}
		else {
			kazokuLog('[error] system', 'No matching source files.');
			console.log('[error] system: no HD or SD stream?');
			console.log(data);
		}
	}
	if ($('#kazoku-status-force-preload').is(':checked')) {
		kazokuLog('!', 'Force preloading video...');
		$('#kazoku-status-preload-progress-wrapper').show(200);
		kazokuPreload = new XMLHttpRequest();
		kazokuPreload.onload = function() {
			$('#kazoku-media').attr('src', URL.createObjectURL(kazokuPreload.response));
			$('#kazoku-status-preload-progress-wrapper').hide(200);
			kazokuLog('!', 'Preload complete.');
		}
		kazokuPreload.open("GET", $('#kazoku-media').attr('src'));
		kazokuPreload.onprogress = kazokuPreloadUpdateProgress;
		kazokuPreload.responseType = 'blob';
		kazokuPreload.send();
	}
});
socket.on('rcv media control', function(action) {
	if (typeof action == 'object') {
		if (action.seek) {
			$('#kazoku-media')[0].currentTime = action.seek;
		}
	}
	else if (action == 'play') {
		$('#kazoku-media')[0].play();
	}
	else if (action == 'pause') {
		$('#kazoku-media')[0].pause();
	}
});
socket.on('player status', function(data) {
	if (Math.abs(data.offset) > 1) {
		$('#kazoku-statusbar div[data-user=' + data.user + '] span.status').text(data.offset);
	}
	else {
		$('#kazoku-statusbar div[data-user=' + data.user + '] span.status').html("<i class='fa fa-check'></i>");
	}
});
/* > chat */
socket.on('recv chat message', function(data) {
	kazokuLog(data.username, data.message);
});

/* -- JS handlers */
function kazokuJSHook() {
	if (kazokuJSHookActive) return;
	kazokuLog('!', 'Adding JS event handlers.');
	$('#kazoku-textinput').on('keydown', function(e) {
		if (e.which == 13) {
			socket.emit('send chat message', { message: $(this).val() });
			$(this).val('');
		}
	});
	$('#kazoku-chat').on('DOMNodeInserted', 'li', function() {
		// hide chat messages on screen after a certain time period
		$(this).delay(chatHideDelay).hide(400, function() {
			$(this).remove();
		});
	});
	// video controls
	$('.kazoku-wrapper .video-wrapper').on('mouseover', function() {
		$('.control').clearQueue().animate({ opacity: 1 }, 500);
	});
	$('.kazoku-wrapper .video-wrapper').on('mouseout', function() {
		$('.control').clearQueue().animate({ opacity: 0 }, 500);
	});
	// preferences
	$('#kazoku-status-force-preload').on('click', function(e) {
		if ($(this).is(':checked')) {
			Cookies.set('kazoku-force-preload', true, { expires: 30, path: '/' });
			kazokuLog('!', 'Force preload has been enabled. This will force the entire video to download video prior to starting the video.');
		}
		else {
			Cookies.set('kazoku-force-preload', false, { expires: 30, path: '/' });
			kazokuLog('!', 'Force preload has been disabled.');
		}
	});
	$('#kazoku-status-prefer-hd').on('click', function(e) {
		if ($(this).is(':checked')) {
			Cookies.set('kazoku-prefer-hd', true, { expires: 30, path: '/' });
			kazokuLog('!', 'Prefer HD has been enabled. The player will show HD video whenever possible.');
		}
		else {
			Cookies.set('kazoku-prefer-hd', false, { expires: 30, path: '/' });
			kazokuLog('!', 'HD is no longer preferred.');
		}
	});
	// restore preferences if set
	if (Cookies.get('kazoku-force-preload') == 'true') {
		$('#kazoku-status-force-preload').click();
	}
	if (Cookies.get('kazoku-prefer-hd') == 'true') {
		$('#kazoku-status-prefer-hd').click();
	}
	// player controls
	$('#ctl_play').on('click', function() {
		socket.emit('req media control', 'play');
	});
	$('#ctl_pause').on('click', function() {
		socket.emit('req media control', 'pause');
	});
	$('#ctl_vol_mute').on('click', function() {
		mediaVol = 0.0;
		$('#kazoku-media')[0].volume = 0.0;
		$('#ctl_vol').text('0');
	});
	$('#ctl_vol_down').on('click', function() {
		if (mediaVol >= 0.1) {
			mediaVol -= 0.1;
			$('#kazoku-media')[0].volume = mediaVol;
			$('#ctl_vol').text(Math.round(mediaVol * 100));
		}
	});
	$('#ctl_vol_up').on('click', function() {
		if (mediaVol <= 0.9) {
			mediaVol += 0.1;
			$('#kazoku-media')[0].volume = mediaVol;
			$('#ctl_vol').text(Math.round(mediaVol * 100));
		}
	});
	$('#ctl_seeker').on('mousedown', function() {
		activeSeek = true;
	});
	$('#ctl_seeker').on('mouseup', function() {
		activeSeek = false;
	});
	$('#ctl_seeker').on('mousemove', function() {
		if (activeSeek) {
			socket.emit('req media control', { seek: $(this).val() });
		}
	});
	// Expanded Player
	$('#ctl_fullscreen').on('click', function() {
		if (kazokuIsFullscreen) {
			if (document.exitFullscreen) {
				document.exitFullscreen();
			} else if (document.webkitExitFullscreen) {
				document.webkitExitFullscreen();
			} else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if (document.msExitFullscreen) {
				document.msExitFullscreen();
			}
			kazokuIsFullscreen = false;
			$("#kazoku-popout").removeClass("dark");
			$("#chat").css("height", "350px");
		}
		else {
			var i = document.getElementById('kazoku-popout');
			if (i.requestFullscreen) {
				i.requestFullscreen();
			} else if (i.webkitRequestFullscreen) {
				i.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
			} else if (i.mozRequestFullScreen) {
				i.mozRequestFullScreen();
			} else if (i.msRequestFullscreen) {
				i.msRequestFullscreen();
			}
			kazokuIsFullscreen = true;
			$("#kazoku-popout").addClass("dark");
			$("#chat").css("height", "80%");
		}
	});
	// escape player using escape
	$(window).on("keydown", function(e) {
		if (e.which == 27) {
			if (document.exitFullscreen) {
				document.exitFullscreen();
			} else if (document.webkitExitFullscreen) {
				document.webkitExitFullscreen();
			} else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if (document.msExitFullscreen) {
				document.msExitFullscreen();
			}
			kazokuIsFullscreen = false;
			$("#kazoku-popout").removeClass("dark");
			$("#chat").css("height", "350px");
			e.preventDefault();
		}
	});
	// check for preload (init)
	if (preload.length > 0) {
		kazokuLoadMedia(preload_title, { SD: preload });
	}
	new Clipboard('.kazoku-clipboard-btn');
	kazokuJSHookActive = true;
}

/* -- additional handlers (socket) */
function kazokuEventInit() {
	if (kazokuEventHookActive) return;
	kazokuLog('!', 'Adding event and socket handlers.');
	/* -- identify */
	socket.emit('client identify', { username: name, room: room });
	/* -- ping/pong */
	kazokuPingPongInterval = setInterval(socketPing, 2000);
	kazokuRoomStatInterval = setInterval(socketRoomStat, 5000);
	kazokuVideoStatInterval = setInterval(videoStatUpdate, 500);
	window.onbeforeunload = function() { return "Are you sure you would like to leave the room?"; };
	kazokuEventHookActive = true;
}
function kazokuEventStop() {
	if (!kazokuEventHookActive) return;
	kazokuLog('!', 'Stopping event timers.');
	clearInterval(kazokuPingPongInterval);
	clearInterval(kazokuRoomStatInterval);
	clearInterval(kazokuVideoStatInterval);
	window.onbeforeunload = function() {};
	kazokuEventHookActive = false;
}