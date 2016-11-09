
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
	"showSCI": true,
	"baseImgPath": ""};

function loadSCIPreview(admin_url) {
	jQuery("#darkcover").remove();
	jQuery("<div>", {
		id:	"darkcover"
	}).click(function() {
		jQuery("#darkcover").toggle();
	}).appendTo("body");
	jQuery("<div>", {
		id:	"sciloading"
	}).appendTo("#darkcover");

	jQuery("<div>", {
		id: "sitemorse_iframe_container",
	}).appendTo("#darkcover");

	if (typeof admin_url !== 'undefined') { //called from top menu
		jQuery("<iframe>", {
			id: "sitemorse_iframe",
			name: "sitemorse_iframe",
			frameborder: 0
		}).attr('src',admin_url).height(0).width(0).css("position", "absolute")
			.appendTo("#sitemorse_iframe_container");
	} else { //called from edit section
		jQuery("<iframe>", {
			id: "sitemorse_iframe",
			name: "sitemorse_iframe",
			frameborder: 0
		}).height(0).width(0).appendTo("#sitemorse_iframe_container");

		var oldtarget = jQuery("#post-preview").attr("target");
		jQuery("#post-preview").attr("target", "sitemorse_iframe");
		jQuery("#post-preview").trigger("click");
		jQuery("#post-preview").attr("target", oldtarget);
		jQuery("#sciloading").show();
	}
}

function getPriorities(results) {
	var priorities = [];
	var p = results.result.priorities;
	for (var key in p) {
		if (p[key].total) {
			var diags = p[key].diagnostics;
			for (var diag in diags) {
				var msg = "(" + diags[diag].total + ") " + diags[diag].category
					+ ": " + diags[diag].title;
				priorities.push(msg);
			}
		}
	}
	p = results.result.telnumbers;
	for (var num in p) {
		var msg = "(" + p[num].occurrences + ") " + p[num].message + ": " + num;
		priorities.push(msg);
	}
	return priorities.slice(0, 20);
}

function showSCI() {
	var wp = window;
	if (!wp.sitemorseSCI["url"]) wp = parent;
	window.open(wp.sitemorseSCI["url"], "_blank");
}


function publishSCI(results) {
	var priorities;
	if (results == "error") priorities = "error";
	else priorities = getPriorities(results);
	if (!priorities.length) {
		jQuery("#darkcover").remove();
		jQuery("#publish").trigger("click");
	} else {
		jQuery("#sciloading").remove();
		jQuery("<div>", {
			id: "sciConfirm"
		}).click(function(e) {
		  e.stopPropagation();
		}).appendTo("#darkcover");
		jQuery("<h2>Sitemorse Assessment</h2>").appendTo("#sciConfirm");
		jQuery("<img class='sciMinorCancel' src='" + sitemorseSCI["baseImgPath"] + "form-close.png' />"
			).click(function() {
				jQuery("#darkcover").toggle();
			}).appendTo("#sciConfirm");
		if (priorities == "error") {
			jQuery("<p>The Sitemorse SCI Server could not be contacted." +
			" Article may have issues. Publish anyway?</p>"
				).appendTo("#sciConfirm");
			jQuery("<button id='sciConfirmPublish'>Publish</Button>"
				).click(function() {
					jQuery("#sciConfirm").remove();
					jQuery("#publish").trigger("click");
				}).appendTo("#sciConfirm");
		} else {
			if (sitemorseSCI["preventPublish"]) {
				jQuery("<h3>&nbsp;Could not publish, priority issues</h3>"
					).appendTo("#sciConfirm");
			}
			var table = "<table class='prioritiesTable'>" +
			"<tr><th style='width:100px; text-align:center;'>Occurences</th><th>Issue</th></tr>";
			var p = results.result.priorities;
			for (var key in p) {
				if (!p[key].total) {
					continue;
				}
				var diags = p[key].diagnostics;
				for (var diag in diags) {
					table += "<tr><td style='text-align:center;'>" + diags[diag].total + "</td>" +
						"<td>" + diags[diag].category + " (" + diags[diag].title + ")</td></tr>";
				}
			}
			var p = results.result.telnumbers;
			for (var num in p) {
				table += "<tr><td style='text-align:center;'>" + p[num].occurrences + "</td>" +
					"<td>" + p[num].message + " (" + num + ") </td></tr>";
			}
			table += "<tr><td style='text-align:center;'>" + results.result.totals.wcag2 + "</td>" +
				"<td>Accessibility Total</td></tr>";
			table += "<tr><td style='text-align:center;'>" + results.result.totals.code + "</td>" +
				"<td>Code Quality Total</td></tr>";
			table += "<tr><td style='text-align:center;'>" + results.result.totals.function + "</td>" +
				"<td>Function Total</td></tr>";
			table += "<tr><td style='text-align:center;'>" + results.result.totals.brand + "</td>" +
				"<td>Brand Total</td></tr>";
			table += "<tr><td style='text-align:center;'>" + results.result.totals.spelling + "</td>" +
				"<td>Spelling Total</td></tr>";
			table += "</table>";
			jQuery(table).appendTo("#sciConfirm");
			jQuery("<button id='sciConfirmAssessment'>View Assessment</Button>"
				).click(function() {
					showSCI();
				}).appendTo("#sciConfirm");
			if (!sitemorseSCI["preventPublish"]) {
				jQuery("<button id='sciConfirmPublish'>Publish</Button>"
					).click(function() {
						jQuery("#sciConfirm").remove();
						jQuery("#publish").trigger("click");
					}).appendTo("#sciConfirm");
			}
		}
	}
}
