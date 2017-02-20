var me = $('#kz-floor div[data-user=' + name + ']');
var x = 0;
var y = 0;
var maxX = $("#kz-floor").width() - 100;
var maxY = $("#kz-floor").height() - 240;
var delta = 0;
var maxDelta = 20;
var deltaStep = 5;
var deltaCD = setTimeout("deltadown()", 100);
var moveDir = "right";
var moveAccel = false;
var screenRepose = setInterval("repose()", 1000/30);
//var screenRedraw = setInterval("redraw()", 1000/4);
var bodyY = -53;
var bodyX = -20;
var bodyMY = -200;
var bodyMX = -265;
var bodyDY = 0;
var bodyDX = 0;
var bodyD = 0;
var bodyDM = 2;
var textFocus = false;
var tX;
var tY;

$(document).keydown(function(e) {
	if (textFocus) {
		if (e.which == 13) {
			//$("#u1").protipShow({ title: $("#kz-text").val(), trigger: "sticky", position: "top" });
			//setTimeout("$('#u1').protipHide()", 2000);
			//$("#kz-text").val("");
		}
	}
	else {
		var oldDir = moveDir;
		switch (e.which) {
			case 87: // w
			case 38: // up
				moveDir = "up";
			break;
			case 65: // as
			case 37: // left
				moveDir = "left";
			break;
			case 68: // d
			case 39: // right
				moveDir = "right";
			break;
			case 83: // s
			case 40: // down
				moveDir = "down";
			break;
			default:
				return;
			break;
		}
		if (oldDir == moveDir) {
			delta += deltaStep;
			if (delta > maxDelta) delta = maxDelta;
		}
		else {
			delta = deltaStep;
			bodyD = bodyDM;
		}
		if (bodyD >= bodyDM) {
			redraw();
			bodyD = 0;
		}
		else {
			bodyD++;
		}
		clearTimeout(deltaCD);
		deltaCD = setTimeout("deltadown()", 100);
		e.preventDefault();
	}
});

function deltadown() {
	if (delta > 0) {
		delta -= deltaStep;
	}
	if (delta > 0) {
		deltaCD = setTimeout("deltadown()", 100);
	}
	else if (delta == 0) {
		bodyDY = 0;
		redraw();
	}
}

function repose() {
	switch (moveDir) {
		case "up":
			y -= delta;
		break;
		case "down":
			y += delta;
		break;
		case "left":
			x -= delta;
		break;
		case "right":
			x += delta;
		break;
	}
	if (x < 0) x = 0;
	if (y < 0) y = 0;
	if (x > maxX) x = maxX;
	if (y > maxY) y = maxY;
	me.css("left", x + "px");
	me.css("top", y + "px");
}
function redraw() {
	switch (moveDir) {
		case "up":
			if (bodyDX != 3) {
				bodyDX = 3;
				bodyDY = 0;
			}
		break;
		case "down":
			if (bodyDX != 0) {
				bodyDX = 0;
				bodyDY = 0;
			}
		break;
		case "left":
			if (bodyDX != 1) {
				bodyDX = 1;
				bodyDY = 0;
			}
		break;
		case "right":
			if (bodyDX != 2) {
				bodyDX = 2;
				bodyDY = 0;
			}
		break;
	}
	tX = bodyX + (bodyDX * bodyMX);
	tY = bodyY + (bodyDY * bodyMY);
	me.css("background-position", tY + "px " + tX + "px");
	bodyDY++;
	if (bodyDY > 3) bodyDY = 0;
}

$("#kazoku-textinput").on("focus", function(e) {
	textFocus = true;
});
$("#kazoku-textinput").on("focusout", function(e) {
	textFocus = false;
});


// test
$.protip();
var el = $("#u1 .kz-name");