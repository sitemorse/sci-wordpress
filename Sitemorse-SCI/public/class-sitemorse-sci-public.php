<?php

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

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.sitemorse.com
 * @since      1.0.0
 *
 * @package    Sitemorse_SCI
 * @subpackage Sitemorse_SCI/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sitemorse_SCI
 * @subpackage Sitemorse_SCI/public
 * @author     Sitemorse Ltd
 */
class Sitemorse_SCI_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name = "Sitemorse SCI";

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version = "1.0.0";

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		if (current_user_can(get_option("sitemorse_user_permission"))) {
			wp_register_style( 'scipublic', plugins_url('Sitemorse-SCI') .
				'/includes/css/scipublic.css', array(), null, 'all' );
			wp_enqueue_style( 'scipublic' );
		}

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		if (current_user_can(get_option("sitemorse_user_permission"))) {
			wp_register_script('sitemorse-scicommon', plugins_url() .
			"/Sitemorse-SCI/includes/js/scicommon.js", array('jquery'));
			wp_register_script('sitemorse-sci', plugins_url() .
			"/Sitemorse-SCI/public/js/sci.js", array('jquery'));
			wp_register_script( "sitemorse-sciiframe", plugins_url() .
				"/Sitemorse-SCI/includes/js/sciiframe.js", array( 'jquery' ));
			wp_enqueue_script('sitemorse-sciiframe');
			wp_enqueue_script('sitemorse-scicommon');
			wp_enqueue_script('sitemorse-sci');
		}

	}

	public function set_globals() {

		$base_img = plugins_url() . "/Sitemorse-SCI/includes/images/";
		echo <<<CONTENT
<script type='text/javascript'>
	sitemorseSCI["baseImgPath"] = "$base_img";
</script>
CONTENT;

	}

}
