// tooltip.js

wmtt = null;

document.onmousemove = updateWMTT;

function updateWMTT(e) {
	x = (document.all) ? window.event.x + document.body.scrollLeft: e.pageX;
	y = (document.all) ? window.event.y : e.pageY;
	if (wmtt != null) {
		verschieb = 0;
		if (window.outerWidth < (x+420)) verschieb = 415;
		wmtt.style.left = (x + 10 - verschieb) + "px";
		wmtt.style.top = (y + 20) + "px";
	}
}

function showWMTT(id) {
	wmtt = document.getElementById(id);
	wmtt.style.zIndex = 2;
	wmtt.style.display = "block";
}

function hideWMTT() {
	wmtt.style.display = "none";
}