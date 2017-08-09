
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

function setParentSCI(results) { //sci in iframe, configure parent
	//if (!parent.sitemorseSCI["intoIframe"]) return;
	parent.publishSCI(results);
}

function iframePreviewRedirect(url) {
	if (parent.top.jQuery("#sci-post-preview").length)
		window.location.replace(url);
}

function sciFinished(sci_url) {
	parent.sitemorseSCI["url"] = sci_url;
	if (parent.sitemorseSCI["showSCI"])
		showSCI();
}

function closeLoading() {
	jQuery("#darkcover", parent.document).remove();
}
