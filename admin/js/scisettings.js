
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

jQuery(function(){

if (jQuery("#sitemorse_marked").length) {
	disableMarked();
	jQuery("#sitemorse_marked").click(function() {
		disableMarked();
		uncheckMarked();
	});
}

}); //jQuery(function()

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
