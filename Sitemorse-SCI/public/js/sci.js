
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
	jQuery("<iframe>", {
		id: "sitemorse_iframe",
		name: "sitemorse_iframe",
		frameborder: 0
	}).attr('src',admin_url).height(0).width(0).css("position", "absolute")
		.appendTo("body");

	var oldtarget = jQuery("#post-preview").attr("target");
	jQuery("#post-preview").attr("target", "sitemorse_iframe");
	jQuery("#post-preview").trigger("click");
	jQuery("#post-preview").attr("target", oldtarget);
	jQuery("#sciloading").show();
}
