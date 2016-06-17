
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

function loadSCIPreview(admin_url) {
	jQuery("#darkcover").remove();
	jQuery("<div>", {
		id:	"darkcover"
	}).click(function() {
		jQuery("#darkcover").toggle();
	}).appendTo("body");

	jQuery("<div>", {
		id:	"sitemorse_preview_controls",
	}).appendTo("#darkcover");

	jQuery("<img src='" + sitemorseSCI["baseImgPath"] + "cross.png' />"
		).appendTo("#sitemorse_preview_controls");

	jQuery("<div>", {
		id: "sitemorse_preview_iframe_container",
	}).css("width", "100%").css("height", "90%").appendTo("#darkcover");

	jQuery("<iframe>", {
		id: "sitemorse_preview_iframe",
		name: "sitemorse_preview_iframe",
		frameborder: 0
	}).attr('src',admin_url).css("width", "90%")
	.css("height", "100%").css("margin-top", "54px")
		.appendTo("#sitemorse_preview_iframe_container");

	var oldtarget = jQuery("#post-preview").attr("target");
	jQuery("#post-preview").attr("target", "sitemorse_preview_iframe");
	jQuery("#post-preview").trigger("click");
	jQuery("#post-preview").attr("target", oldtarget);
	jQuery("#sciloading").show();
}
