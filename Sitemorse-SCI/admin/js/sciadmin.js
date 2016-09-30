
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

jQuery(function() {

jQuery( "input[value*='sitemorse_data']" ).parent().parent().hide()

jQuery("#sitemorse_verify").click(function() {
	sitemorseSCI["intoIframe"] = true;
	sitemorseSCI["publish"] = true;
	loadSCIPreview();
});

jQuery("#sci-post-preview").click(function() {
	sitemorseSCI["intoIframe"] = true;
	sitemorseSCI["publish"] = false;
	loadSCIPreview("show");
});

jQuery("#sci-post-toggle").click(function() {
	jQuery("#darkcover").toggle();
});

if (jQuery("#sitemorse_marked").length) {
	disableMarked();
	jQuery("#sitemorse_marked").click(function() {
		disableMarked();
		uncheckMarked();
	});
}

if (window.location.href.endsWith("&sm_assess")) {
	setTimeout(function() {
		jQuery("#sci-post-preview").trigger("click"); }, 750);
}

});

function sitemorseRedirect(url) {
	if (parent.top.jQuery("#sci-post-preview").length &&
		!jQuery("#sitemorse_prevent_redirect").length) {
		window.location.replace(url);
	}
}

function disableMarked() {
	if (jQuery("#sitemorse_marked").is(":checked"))
		jQuery(".markedDisable").prop("disabled", false);
	else jQuery(".markedDisable").prop("disabled", true);
}

function uncheckMarked() {
	if (jQuery("#sitemorse_marked").is(":checked"))
		jQuery(".markedDisable").prop("checked", true);
	else jQuery(".markedDisable").prop("checked", false);
}

function setParentSCI(results) { //sci in iframe, configure parent
	if (!parent.sitemorseSCI["intoIframe"]) return;
	if (parent.sitemorseSCI["publish"]) {
		parent.publishSCI(results);
	} else {
		parent.showSCIFrame();
	}
	parent.top.jQuery("#sci-post-preview-container").hide();
	parent.top.jQuery("#sci-post-toggle-container"
		).css("display", "inline-block");
	if (results != "error")
		parent.top.jQuery("#sitemorse_preview_controls").show();
	parent.showResultsIcon(results);
}

function loadSCIPreview() {
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
		id:	"sitemorse_preview_controls",
	}).appendTo("#darkcover");

	jQuery("<img src='" + sitemorseSCI["baseImgPath"] + "cross.png' />"
		).appendTo("#sitemorse_preview_controls");

	jQuery("<img src='" + sitemorseSCI["baseImgPath"] + "refresh.png' />"
		).click(function() {
		sitemorseSCI["intoIframe"] = true;
		sitemorseSCI["publish"] = false;
		loadSCIPreview("show");
	}).appendTo("#sitemorse_preview_controls");

	jQuery("<div>", {
		id: "sitemorse_preview_iframe_container",
	}).appendTo("#darkcover");

	jQuery("<iframe>", {
		id: "sitemorse_preview_iframe",
		name: "sitemorse_preview_iframe",
		frameborder: 0
	}).height(0).width(0).appendTo("#sitemorse_preview_iframe_container");

	var oldtarget = jQuery("#post-preview").attr("target");
	jQuery("#post-preview").attr("target", "sitemorse_preview_iframe");
	jQuery("#post-preview").trigger("click");
	jQuery("#post-preview").attr("target", oldtarget);
	jQuery("#sciloading").show();
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
		var msg = "(" + p[num].occurrences + ") " + p[num].message + " number: +" + num;
		priorities.push(msg);
	}
	return priorities.slice(0, 20);
}

function showResultsIcon(results) {
	jQuery(".sitemorse_results_icon").remove();
	var priorities = getPriorities(results);
	var img = "cross-ico.png";
	if (!priorities.length) img = "tick-ico.png";
	jQuery("<img class='sitemorse_results_icon'" +
		"src='" + sitemorseSCI["baseImgPath"] + img + "' />"
		).insertAfter("span #sitemorse_title");
}
function showSCIFrame() {
	jQuery("#sciloading").remove();
	jQuery("#sitemorse_preview_iframe")
		.css("width", "100%").css("height", "90%");
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
		jQuery("<div id='sciConfirm'>" +
			"<h2>Sitemorse Assessment</h2>"
			).appendTo("#darkcover");
		jQuery("<img class='sciMinorCancel' src='" + sitemorseSCI["baseImgPath"] + "form-close.png' />"
			).appendTo("#sciConfirm");
		if (priorities == "error") {
			jQuery("<p>The Sitemorse SCI Server could not be contacted." +
			" Article may have issues. Publish anyway?</p>"
				).appendTo("#sciConfirm");
			jQuery("<button id='sciConfirmPublish'>Publish Anyway</Button>"
				).click(function() {
					jQuery("#sciConfirm").remove();
					jQuery("#publish").trigger("click");
				}).appendTo("#sciConfirm");
			jQuery("<button id='sciConfirmCancel'>Fix Issues</Button>"
				).appendTo("#sciConfirm");
		} else if (sitemorseSCI["preventPublish"]) {
			jQuery("<p>Could not publish, priority issues:</p>"
				).appendTo("#sciConfirm");
			jQuery("<ul><li>" + priorities.slice(0, 10).join("</li><li>")
				+ "</li></ul>").appendTo("#sciConfirm");
			jQuery("<button id='sciConfirmAssessment'>Show Assessment</Button>"
				).click(function() {
					jQuery("#sciConfirm").remove();
					jQuery("#darkcover").toggle();
					showSCIFrame();
				}).appendTo("#sciConfirm");
			jQuery("<button id='sciConfirmCancel'>Fix Issues</Button>"
				).appendTo("#sciConfirm");
		} else {
			var table = "<table class='prioritiesTable'>" +
			"<colgroup>" +
			"  <col style='width:88px;'>" +
			"  <col style='width:266px;'>" +
			"  <col style=''>" +
			"</colgroup>" +
			"<tr>" +
			"  <th colspan='3'><span class='sciConfirmPriorityIcon'></span>" +
			"  You have priority issues. See below for details.</th>" +
			"</tr>" +
			"<tr><th>No. of Issues</th><th>Type of Issue</th><th>Issue / Number</th></tr>";
			var p = results.result.priorities;
			for (var key in p) {
				if (!p[key].total) {
					continue;
				}
				var diags = p[key].diagnostics;
				for (var diag in diags) {
					table += "<tr><td style='text-align:center;'>" + diags[diag].total + "</td>" +
						"<td>" + diags[diag].category + "</td>" +
						"<td>" + diags[diag].title + "</td></tr>";
				}
			}
			var p = results.result.telnumbers;
			for (var num in p) {
				table += "<tr><td style='text-align:center;'>" + p[num].occurrences + "</td>" +
					"<td>" + p[num].message + " number</td>" +
					"<td>" + num + "</td></tr>";
			}
			table += "</table>";
			jQuery(table).appendTo("#sciConfirm");
			jQuery("<button id='sciConfirmPublish'>Publish Anyway</Button>"
				).click(function() {
					jQuery("#sciConfirm").remove();
					jQuery("#publish").trigger("click");
				}).appendTo("#sciConfirm");
			jQuery("<button id='sciConfirmAssessment'>Show Assessment</Button>"
				).click(function() {
					jQuery("#sciConfirm").remove();
					jQuery("#darkcover").toggle();
					showSCIFrame();
				}).appendTo("#sciConfirm");
			jQuery("<button id='sciConfirmCancel'>Fix Issues</Button>"
				).appendTo("#sciConfirm");
		}
	}
}

function test_connection(url, node) {
	var icon = jQuery("#sitemorse_connection_icon");
	icon.removeClass("sitemorse_connection_pass sitemorse_connection_load"
		+ " sitemorse_connection_fail");
	var node = jQuery(node);
	key = node.val();
	var url = url + "&key=" + key;
	if (key.length !== 16) {
		icon.addClass("sitemorse_connection_fail");
		return;
	}
	icon.addClass("sitemorse_connection_load");
	jQuery.ajax({
		dataType: "html",
		url : url,
		success : function(html) {
			var div = jQuery("<div>");
			div.html(html);
			var stat = div.find("#sitemorseConnStatus").text();
			icon.removeClass("sitemorse_connection_load");
			if (stat == "pass") {
				icon.addClass("sitemorse_connection_pass");
			} else {
				icon.addClass("sitemorse_connection_fail");
			}
		}
	});
}
