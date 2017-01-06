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

/* -- misc functions */
function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

/* -- logging functions */

function kazokuLogHTML(html) {
	$('#kazoku-log').append($('<div />').html(html));
	$('#kazoku-log').scrollTo('#kazoku-log div:last-child');
}
function kazokuLog(name, text) {
	kazokuLogHTML('<b>' + name + '</b>: ' + text);
}
function kazokuNotifyPermissionGranted() {
	/*
	var kazokuNotifyGrantedNotification = new Notify('Thanks!', {
		body: 'Notifications have been enabled successfully.'
	});
	kazokuNotifyGrantedNotification.show();
	*/
}
function kazokuNotifyPermissionDenied() {
	$('#kazoku-status-show-notifications').prop('checked', false);
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

/* -- JS functions */

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
function chatMessageHTML(html) {
	$('#kazoku-chat').append($('<li />').html(html));
}
function chatMessage(name, message) {
	kazokuLog('[chat] ' + name, htmlEntities(message));
	chatMessageHTML('<b>' + name + '</b>: ' + htmlEntities(message));
	/*
	var kazokuChatNotify = new Notify(name, {
		body: htmlEntities(message),
		icon: '/user/' + name + '/profile.jpg'
	});
	kazokuChatNotify.show();
	*/
}

/* -- simple pre-debug */

kazokuLog('system', 'Kazoku initializing...');
kazokuLog('system', "* This is a work in progress. Please expect bugs and resist trying to break it, k? *");

kazokuLog('system', 'Attempting to establish connection to server...');
kazokuLog('system', 'Connecting to (' + socketIOserver + ')...');

/* -- socket init & event catching */

socket = io.connect(socketIOserver);
socket.on('connect', function() {
	kazokuLog('socketIO', 'Connection successful.');
	$('#kazoku-status-socketIO-connected').prop('checked', true);
	kazokuEventInit();
});
socket.on('error', function(data) {
	kazokuLog('[error] socketIO', data || 'Unknown error.');
	$('#kazoku-status-socketIO-connected').prop('checked', false);
	kazokuEventStop();
});
socket.on('connect_error', function(data) {
	$('#kazoku-media')[0].pause();
	kazokuLog('[error] socketIO', data || 'Connection error.');
	$('#kazoku-status-socketIO-connected').prop('checked', false);
	kazokuEventStop();
});
socket.on('connect_failed', function(data) {
	kazokuLog('[error] socketIO', data || 'Failed to connect.');
	$('#kazoku-status-socketIO-connected').prop('checked', false);
	kazokuEventStop();
});
socket.on('disconnect', function() {
	$('#kazoku-media')[0].pause();
	kazokuLog('[warn] socketIO', 'Disconnected from server!');
	$('#kazoku-status-socketIO-connected').prop('checked', false);
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
function kazokuLoadMedia(title, episode, data) {
	console.log(data);
	socket.emit('set room media', { title: title, episode: episode, src: data });
	$.featherlight.current().close();
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
	kazokuLog('socketIO', 'Connection OK!');
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
			}
			$('#kazoku-statusbar div[data-user=' + user + '] span.ping').text(stat.ping);
		}
	}
});
socket.on('ready media', function(data) {
	$('#kazoku-title-activity').html(data.title + ' &mdash; Episode ' + data.episode);
	if ($('#kazoku-status-prefer-hd').is(':checked')) {
		if (typeof data.media.HD == "string") {
			$('#kazoku-media').attr('src', data.media.HD);
			kazokuLog('system', 'Loaded HD stream: ' + data.title + ' - Episode ' + data.episode);
		}
		else if (typeof data.media.SD == "string") {
			$('#kazoku-media').attr('src', data.media.SD);
			kazokuLog('system', 'Loaded SD stream: ' + data.title + ' - Episode ' + data.episode);
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
			kazokuLog('system', 'Loaded SD stream: ' + data.title + ' - Episode ' + data.episode);
		}
		else if (typeof data.media.HD == "string") {
			$('#kazoku-media').attr('src', data.media.HD);
			kazokuLog('system', 'Loaded HD stream: ' + data.title + ' - Episode ' + data.episode);
		}
		else {
			kazokuLog('[error] system', 'No matching source files.');
			console.log('[error] system: no HD or SD stream?');
			console.log(data);
		}
	}
	if ($('#kazoku-status-force-preload').is(':checked')) {
		kazokuLog('system', 'Force preloading video...');
		$('#kazoku-status-preload-progress-wrapper').show(200);
		kazokuPreload = new XMLHttpRequest();
		kazokuPreload.onload = function() {
			$('#kazoku-media').attr('src', URL.createObjectURL(kazokuPreload.response));
			$('#kazoku-status-preload-progress-wrapper').hide(200);
			kazokuLog('system', 'Preload complete.');
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
	chatMessage(data.username, data.message);
});

/* -- JS handlers */
function kazokuJSHook() {
	if (kazokuJSHookActive) return;
	kazokuLog('system', 'Adding JS event handlers.');
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
	// media btn
	$('#kazoku-load-media-btn').on('click', function(e) {
		$.featherlight('/theatre.ajax.ps?do=listing');
	});
	// preferences
	$('#kazoku-status-force-preload').on('click', function(e) {
		if ($(this).is(':checked')) {
			Cookies.set('kazoku-force-preload', true, { expires: 30, path: '/' });
			kazokuLog('system', 'Force preload has been enabled. This will force the entire video to download video prior to starting the video.');
		}
		else {
			Cookies.set('kazoku-force-preload', false, { expires: 30, path: '/' });
			kazokuLog('system', 'Force preload has been disabled.');
		}
	});
	$('#kazoku-status-prefer-hd').on('click', function(e) {
		if ($(this).is(':checked')) {
			Cookies.set('kazoku-prefer-hd', true, { expires: 30, path: '/' });
			kazokuLog('system', 'Prefer HD has been enabled. The player will show HD video whenever possible.');
		}
		else {
			Cookies.set('kazoku-prefer-hd', false, { expires: 30, path: '/' });
			kazokuLog('system', 'HD is no longer preferred.');
		}
	});
	/*
	$('#kazoku-status-show-notifications').on('click', function(e) {
		if ($(this).is(':checked')) {
			Cookies.set('kazoku-show-notifications', true, { expires: 30, path: '/' });
			kazokuLog('system', 'HTML5 notifications have been enabled. Please allow notification access on your browser if a popup appears.');
			if (!Notify.needsPermission) {
				kazokuNotifyGranted = true;
				console.log('Notify is granted permissions.');
			}
			else if (Notify.isSupported()) {
				Notify.requestPermission(kazokuNotifyPermissionGranted, kazokuNotifyPermissionDenied);
				kazokuNotifyGranted = true;
			}
			else {
				$('#kazoku-status-show-notifications').hide(200);
				kazokuLog('HTML5 notifications are not supported in this browser.');
			}
		}
		else {
			Cookies.set('kazoku-show-notifications', false, { expires: 30, path: '/' });
			kazokuLog('system', 'HTML5 notifications have been disabled.');
		}
	});
	*/
	// restore preferences if set
	if (Cookies.get('kazoku-force-preload') == 'true') {
		$('#kazoku-status-force-preload').click();
	}
	if (Cookies.get('kazoku-prefer-hd') == 'true') {
		$('#kazoku-status-prefer-hd').click();
	} 
	/*
	if (Cookies.get('kazoku-show-notifications') == 'true') {
		$('#kazoku-status-show-notifications').click();
	} 
	*/
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
	$('#ctl_expand_player').on('click', function() {
		if (kazokuExpandedPlayer) {
			kazokuExpandedPlayer = false;
			$('.secondary-wrapper').css({
				width: '',
				height: '',
				top: '',
				left: '',
				position: '',
			});
			$('.video-wrapper').css({
				height: '',
			});
			$('#kazoku-media').css({
				height: '',
				maxHeight: '',
			});
			$('#kazoku-statusbar, #kazoku-log, .footer-block, #kazoku-top-infobar').fadeIn(200);
			$('#ctl_expand_player').removeClass('fa-compress').addClass('fa-expand');
		}
		else {
			kazokuExpandedPlayer = true;
			$('.secondary-wrapper').css({
				width: '100%',
				height: '100%',
				top: '0',
				left: '0',
				position: 'absolute',
			});
			$('.video-wrapper').css({
				height: 'calc(100% - 20px)',
			});
			$('#kazoku-media').css({
				height: '100%',
				maxHeight: '100%',
			});
			$('#kazoku-statusbar, #kazoku-log, .footer-block, #kazoku-top-infobar').fadeOut(200);
			$('#ctl_expand_player').removeClass('fa-expand').addClass('fa-compress');
		}
	});
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
			$('#kazoku-min-log').css('background-color', 'rgba(0,0,0,0)');
			kazokuIsFullscreen = false;
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
			$('#kazoku-min-log').css('background-color', 'black');
			kazokuIsFullscreen = true;
		}
	});
	new Clipboard('.kazoku-clipboard-btn');
	kazokuJSHookActive = true;
}

/* -- additional handlers (socket) */
function kazokuEventInit() {
	if (kazokuEventHookActive) return;
	kazokuLog('system', 'Adding event and socket handlers.');
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
	kazokuLog('system', 'Stopping event timers.');
	clearInterval(kazokuPingPongInterval);
	clearInterval(kazokuRoomStatInterval);
	clearInterval(kazokuVideoStatInterval);
	window.onbeforeunload = function() {};
	kazokuEventHookActive = false;
}