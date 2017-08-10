
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

jQuery(function() {

jQuery( "input[value*='sitemorse_data']" ).parent().parent().hide()

jQuery("#sitemorse_verify").click(function() {
	sitemorseSCI["intoIframe"] = false;
	sitemorseSCI["publish"] = true;
	sitemorseSCI["showSCI"] = false;
	loadSCIPreview();
});

jQuery("#sci-post-preview").click(function() {
	sitemorseSCI["intoIframe"] = false;
	sitemorseSCI["publish"] = false;
	sitemorseSCI["showSCI"] = false;
	loadSCIPreview();
});

jQuery("#sci-post-toggle").click(function() {
	jQuery("#darkcover").toggle();
});

});
