
/**
 * Sitemorse SCI Wordpress Plugin
 * Copyright (C) 2016 Sitemorse Ltd
 *
 * This file is part of Sitemorse SCI.
 *
 * Sitemorse SCI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.

 * Sitemorse SCI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with Sitemorse SCI.  If not, see <http://www.gnu.org/licenses/>.
**/

var sitemorseSCI = {"intoIframe": false, //if the sci is loaded into an iframe
	"preventPublish": false,
	"baseImgPath": ""};

function snapshotListener(evt) {
	if (evt.origin.substr(0,28) == "https://secure.sitemorse.com") {
		var iframe = document.getElementById("sitemorse_preview_iframe");
		evt.source.postMessage("OK", evt.origin);
		setTimeout(function() {
			if (window.location.href != evt.data) {
				window.location = evt.data;
			} else {
				jQuery("#darkcover").trigger("click");
			}
		}, 1000);
	}
}

if (window.addEventListener) {
	window.addEventListener("message", snapshotListener, false);
} else {
	window.attachEvent("onmessage", snapshotListener);
}

