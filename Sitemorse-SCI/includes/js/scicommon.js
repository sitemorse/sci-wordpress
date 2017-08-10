
/**
 * Sitemorse SCI Wordpress Plugin
 * Copyright (C) 2017 Sitemorse (UK Sales) Ltd
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
		id:	"sciLoading"
	}).click(function(e) {
	  e.stopPropagation();
	}).appendTo("#darkcover");

	jQuery("<div>", {
		id: "sitemorse_iframe_container",
	}).appendTo("#darkcover");
	jQuery("#sciLoading").show();

	if (typeof admin_url !== 'undefined') { //called from top menu
		sitemorseSCI["intoIframe"] = false;
		sitemorseSCI["publish"] = false;
		sitemorseSCI["showSCI"] = false;
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
	}
	if (sitemorseSCI["publish"] == false) {
		jQuery("#sciLoading").css("width", "825px").css("left", "47%");
	}
}


function showSCI($blank="") {
	if (!sitemorseSCI["url"]) {
		window.open(parent.sitemorseSCI["url"], "_blank");
	} else {
		if ($blank) {
			window.open(sitemorseSCI["url"], "_blank");
		} else {
			window.location.href = sitemorseSCI["url"]
		}
	}
}


function publishSCI(results) {
	jQuery("#sciLoading").remove();
	jQuery("<div>", {
		id: "sciConfirm"
	}).click(function(e) {
	}).appendTo("#darkcover");
	if (sitemorseSCI["publish"] == false) {
		jQuery("#sciConfirm").css("width", "825px").css("left", "47%");
	}
	jQuery("<h2>Page Assessment Completed - Click Icons to View</h2>").appendTo("#sciConfirm");
	jQuery("<img class='sciMinorCancel' src='" + sitemorseSCI["baseImgPath"] + "form-close.png' />"
		).appendTo("#sciConfirm");
	jQuery("<div id='sciSnapshot'></div>").appendTo("#sciConfirm");
	jQuery("<div id='sciPublish'></div>").appendTo("#sciConfirm");
	jQuery("#sciPublish").click(function(e) {
		jQuery("#publish").trigger("click");
	});
	if (results == "error") {
		jQuery("#sciSnapshot").css("background-color", "white");
		jQuery("<p>The Sitemorse SCI Server could not be contacted." +
		" Article may have issues. Publish anyway?</p>"
			).appendTo("#sciSnapshot");
		return;
	}
	jQuery("#sciSnapshot").click(function(e) {
		showSCI($blank="_blank");
	});
	var total = results.result.totals;
	var score = results.result.scores;
	var klasses = {"access": total.wcag2?"fail":"pass", "brand":total.brand?"fail":"pass",
		"telnumbers": Object.keys(results.result.telnumbers).length?"fail":"pass",
		"quality": total.quality?"fail":"pass", "seo": score.metadata.score<3?"fail":"pass",
		"performance": score.performance.score<3?"fail":"pass",
		"spelling": total.spelling?"fail":"pass",};
	jQuery("<span class='" + klasses.access + "Icon accessIcon'></span>").appendTo("#sciSnapshot");
	jQuery("<span class='" + klasses.brand + "Icon brandIcon'></span>").appendTo("#sciSnapshot");
	jQuery("<span class='" + klasses.telnumbers + "Icon phoneIcon'></span>").appendTo("#sciSnapshot");
	jQuery("<span class='" + klasses.quality + "Icon codequalityIcon'></span>").appendTo("#sciSnapshot");
	jQuery("<span class='" + klasses.seo + "Icon seoIcon'></span>").appendTo("#sciSnapshot");
	jQuery("<span class='" + klasses.performance + "Icon performanceIcon'></span>").appendTo("#sciSnapshot");
	jQuery("<span class='" + klasses.spelling + "Icon spellingIcon'></span>").appendTo("#sciSnapshot");
	if (klasses["access"] == "pass" && klasses["brand"] == "pass" && klasses["telnumbers"]
		&& klasses["quality"] == "pass" && klasses["spelling"] == "pass") {
		jQuery("#publish").trigger("click");
	} else {
		if (sitemorseSCI["preventPublish"]) {
			jQuery("#sciPublish").off('click');
			jQuery("#sciPublish").click(function(e) {
				alert("Publishing is prevented: Priority Issues");
			});
		}
	}
}
