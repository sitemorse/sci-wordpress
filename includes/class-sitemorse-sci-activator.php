<?php
/**
 * Sitemorse SCI WordPress Plugin
 * Copyright (C) 2016 Sitemorse (UK Sales) Ltd
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
 *
 * Fired during plugin activation
 *
 * @link       http://www.sitemorse.com
 * @since      1.0.0
 *
 * @package    Sitemorse_SCI
 * @subpackage Sitemorse_SCI/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sitemorse_SCI
 * @subpackage Sitemorse_SCI/includes
 * @author     Sitemorse (UK Sales) Ltd
 */
class Sitemorse_SCI_Activator {

	/**
	 * Run on activate.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$args       = array( 'post_type' => array( 'post', 'page' ) );
		$post_query = new WP_Query( $args );
		if ( $post_query->have_posts() ) {
			while ( $post_query->have_posts() ) {
				$post_query->the_post();
				$sm_data = get_post_meta( get_the_ID(), 'sitemorse_data', true );
				if ( ! $sm_data ) {
					add_post_meta( get_the_ID(), 'sitemorse_data', 0 );

				}
			}
		}
	}

}
