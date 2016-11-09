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
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.sitemorse.com
 * @since      1.0.0
 *
 * @package    Sitemorse_SCI
 * @subpackage Sitemorse_SCI/admin
 * @author     Sitemorse Ltd
 */


class Sitemorse_SCI_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name = 'Sitemorse Settings';

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version = '1.0.0';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->elem = new Sitemorse_SCI_Admin_Elements();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_register_style( 'scipublic', plugins_url('Sitemorse-SCI') .
			'/includes/css/scipublic.css', array(), null, 'all' );
		wp_register_style( 'sci', plugins_url('Sitemorse-SCI') .
			'/includes/css/sci.css', array(), null, 'all' );
		wp_enqueue_style( 'scipublic' );
		wp_enqueue_style( 'sci' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-accordion');
		wp_register_script("sitemorse-scicommon", plugins_url() .
			"/Sitemorse-SCI/includes/js/scicommon.js", array( 'jquery' ));
		wp_enqueue_script('sitemorse-scicommon');
		wp_register_script( "sitemorse-sciiframe", plugins_url() .
			"/Sitemorse-SCI/includes/js/sciiframe.js", array( 'jquery' ));
		wp_enqueue_script('sitemorse-sciiframe');
		wp_register_script( "sitemorse-sciadmin", plugins_url() .
			"/Sitemorse-SCI/admin/js/sciadmin.js", array( 'jquery' ));
		wp_enqueue_script('sitemorse-sciadmin');
		wp_register_script( "sitemorse-scisettings", plugins_url() .
			"/Sitemorse-SCI/admin/js/scisettings.js", array( 'jquery' ));
		wp_enqueue_script('sitemorse-scisettings');

	}

	/**
	 *
	 * Sitemorse SCI Settings Section
	 *
	 */

	protected function is_checked($id) {
		$chk = get_option($id);
		return isset($chk["text_string"]) && $chk["text_string"] == "on";
	}

	/**
	 * The Sitemorse Config page
	 *
	 * @since    1.0.0
	 */
	public function sitemorse_config() {

		echo '<div>
			<h1>Sitemorse Configuration</h1><br />
			<form action="options.php" method="post">';
		settings_fields( 'sitemorse_option_group' );
		add_action("wp_head", function() { echo <<<CONTENT
CONTENT;
		});
		do_settings_sections( 'sitemorse_settings_accordion_pre' );
		do_settings_sections( 'sitemorse_settings_section' );
		do_settings_sections( 'sitemorse_settings_div_close' );
		do_settings_sections( 'sitemorse_marked_section' );
		do_settings_sections( 'sitemorse_marked_filters_section' );
		do_settings_sections( 'sitemorse_settings_div_close' );
		do_settings_sections( 'sitemorse_conn_section' );
		do_settings_sections( 'sitemorse_settings_div_close' );
		do_settings_sections( 'sitemorse_settings_div_close' );
		echo '<input name="Submit" type="submit" value="Save Changes" />
			</form></div>';

	}
	
	/**
	 * Shorthand function to register multiple settings
	 *
	 * @since    1.0.0
	 */
	protected function register_settings( $group, $settings ) {

		foreach( $settings as $setting ) {
			register_setting( $group, $setting, function( $input ) { return $input; });
		}

	}

	/**
	 * Shorthand function to add multiple settings
	 *
	 * @since    1.0.0
	 */
	protected function add_settings_fields( $fields_list, $section ) {

	  $args = array();
		foreach( $fields_list as $fields ) {
			$var = $fields[0];
			$name = $fields[1];
			$callback = $fields[2];
			if ( count( $fields ) >= 4 ) {
				$args = $fields[3];
			}
			add_settings_field( $var, $name, $callback, $section,
				'sitemorse_settings', $args );
		}
	}


	/**
	 * Build Sitemorse Settings Page
	 *
	 * @since    1.0.0
	 */
	public function settings_form() {

		add_settings_section('sitemorse_settings', '',
			function() { echo <<<CONTENT
<script type='text/javascript'>
jQuery(function() {
	jQuery("#accordion").accordion({heightStyle: "content",
		collapsible: true});
});
</script>
<style type="text/css">
#mainnav .ui-state-focus {
	outline: none;
}
h2 {
	outline: none !important;
	border: none !important;
}
.ui-accordion-header{
	cursor: pointer;
}
</style>
<div id="accordion">
CONTENT;
	}, 'sitemorse_settings_accordion_pre');

		add_settings_section('sitemorse_settings', '',
			function() { echo "</div>";}, 'sitemorse_settings_div_close');

		add_settings_section( 'sitemorse_settings', '<img src="' .
			sm_image_url("sm-icon-gray.gif") . '" />&nbsp;Main Settings',
			function() { echo '<div><p>General Settings</p>'; }, 'sitemorse_settings_section' );

		add_settings_section( 'sitemorse_settings', '<img src="' .
			sm_image_url("sm-icon-gray.gif") . '" />&nbsp;Marked Content Settings',
			function() { echo '<div><p>Marked content for the Sitemorse scanner.</p>'; },
			'sitemorse_marked_section' );

		add_settings_section( 'sitemorse_settings', 'Marked Content Filters',
			function() { echo '<p>Add filters for Marked Content on functions:</p>'; },
			'sitemorse_marked_filters_section' );

		add_settings_section( 'sitemorse_settings', '<img src="' .
			sm_image_url("sm-icon-gray.gif") . '" />&nbsp;Advanced Settings',
			function() { echo '<div><p>Publishing options</p>'; },
			'sitemorse_conn_section' );

		$this->register_settings( 'sitemorse_option_group', array(
			'sitemorse_licence_key', 'sitemorse_preview', 'sitemorse_user_permission',
			'sitemorse_post', 'sitemorse_ssl', 'sitemorse_marked', 'sitemorse_hostnames',
			'sitemorse_proxy', 'sitemorse_sci_host', 'sitemorse_sci_port',
			'sitemorse_sci_ssl_port', 'sitemorse_headers', 'sitemorse_query',
			'sitemorse_page_list', 'sitemorse_author', 'sitemorse_publish',
			'sitemorse_demo_mode',
			'sitemorse_marked_the_excerpt', 'sitemorse_marked_get_the_excerpt',
			'sitemorse_marked_the_content', 'sitemorse_marked_get_the_content',
			'sitemorse_marked_the_title', 'sitemorse_marked_post_thumbnail' ) );

		$this->add_settings_fields( array(
			array( 'sitemorse_licence_key', 'Licence Keys',
				array( $this->elem, 'licence_key_field' ) ),
			array( 'sitemorse_preview', 'Enable Scanner',
				array( $this->elem, 'preview_field' ) ),
			array( 'sitemorse_user_permission', 'User Permission',
				array( $this->elem, 'user_permission_field' ) ),
			array( 'sitemorse_publish', 'Prevent publishing content with issues?',
				array( $this->elem, 'checkbox_field' ),
				array( 'id' => 'sitemorse_publish',
				'default' => 'off', 'class' => 'markedDisable' ) ),
			array( 'sitemorse_hostnames', 'Additional Hostnames',
				array( $this->elem, 'text_field' ),
				array( 'id' => 'sitemorse_hostnames', 'desc' =>
					'Additional hostnames used in the development environment. Comma separated') ),
			array( 'sitemorse_demo_mode', 'Demo mode',
				array( $this->elem, 'checkbox_field' ),
				array( 'id' => 'sitemorse_demo_mode',
				'default' => 'off', 'class' => 'markedDisable hidden' ) ),
			), 'sitemorse_settings_section' );

		$this->add_settings_fields( array(
			array( "sitemorse_marked", "Enable Marked Content",
				array( $this->elem, 'checkbox_field' ),
				array( 'id' => 'sitemorse_marked' ) ),
			array( "sitemorse_page_list", "Show pages list",
				array( $this->elem, 'checkbox_field' ),
				array( 'id' =>'sitemorse_page_list',
				'default' => 'on', 'class' => 'markedDisable' ) ),
			array( "sitemorse_author", "Include author in marked content tags",
				array( $this->elem, 'checkbox_field' ),
				array( 'id' => 'sitemorse_author',
				'default' => 'on', 'class' => 'markedDisable' ) ),
			), "sitemorse_marked_section");

		$this->add_settings_fields( array(
			array( 'sitemorse_marked_the_title', 'the_title()',
				array( $this->elem, 'checkbox_field' ),
				array( 'id' =>'sitemorse_marked_the_title',
					'default' => 'on', 'class' => 'markedDisable' ) ),
			array( 'sitemorse_marked_the_excerpt', 'the_excerpt()',
				array( $this->elem, 'checkbox_field' ),
				array( 'id' => 'sitemorse_marked_the_excerpt',
					'default' => 'on', 'class' => 'markedDisable' ) ),
			array( "sitemorse_marked_get_the_excerpt", "get_the_excerpt()",
				array( $this->elem, 'checkbox_field' ),
				array( 'id' => 'sitemorse_marked_get_the_excerpt',
					'default' => 'on', 'class' => 'markedDisable' ) ),
			array( "sitemorse_marked_the_content", "the_content()",
				array( $this->elem, 'checkbox_field' ),
				array( 'id' => 'sitemorse_marked_the_content',
					'default' => 'on', 'class' => 'markedDisable' ) ),
			array( "sitemorse_marked_get_the_content", "get_the_content()",
				array( $this->elem, 'checkbox_field' ), 
				array( 'id' => 'sitemorse_marked_get_the_content',
					'default' => 'on', 'class' => 'markedDisable' ) ),
			array( "sitemorse_marked_post_thumbnail", "post_thumbnail_html()",
				array( $this->elem, 'checkbox_field' ), 
				array( 'id' => 'sitemorse_marked_post_thumbnail',
					'default' => 'on', 'class' => 'markedDisable' ) ),
			), 'sitemorse_marked_filters_section' );

		$this->add_settings_fields( array(
			array( 'sitemorse_ssl', 'SSL Connection',
				array( $this->elem, 'checkbox_field' ),
				array( 'id' => 'sitemorse_ssl',
					'desc' => 'SSL connection to Sitemorse server' ) ),
			array( 'sitemorse_post', 'Post Allowed',
				array( $this->elem, 'checkbox_field' ), 
				array( 'id' => 'sitemorse_post',
					'desc' => 'Allow Sitemorse server to process POST requests', 'default' => 'off' ) ),
			array("sitemorse_proxy", "Proxy Hostname:Port",
				array( $this->elem, 'text_field' ),
				array( 'id' => 'sitemorse_proxy', 'desc' =>
					'Local proxy (if used). Please include port number' ) ),
			array( "sitemorse_sci_host", "Sitemorse Server Hostname",
				array( $this->elem, 'text_field' ),
				array( 'id' => 'sitemorse_sci_host', 'default' => 'sci.sitemorse.com',
				'desc' => 'Sitemorse server hostname. Normally sci.sitemorse.com' ) ),
			array( "sitemorse_sci_port", "Sitemorse Server Port",
				array( $this->elem, 'text_field' ),
				array( 'id' => 'sitemorse_sci_port', 'default' => '5371',
					'desc' => 'Normally 5371' ) ),
			array( "sitemorse_sci_ssl_port", "Sitemorse Server SSL Port",
				array( $this->elem, 'text_field' ),
				array( 'id' => 'sitemorse_sci_ssl_port', 'default' => '5372',
					'desc' => 'Normally 5372' ) ),
			array( 'sitemorse_headers', 'Additional HTTP Headers',
				function () {
					$options = get_option('sitemorse_headers');
					echo "<textarea id='sitemorse_headers'  rows='10' cols='40'" .
						" name='sitemorse_headers[text_string]'>"
						. $options['text_string'] . "</textarea>";
				} ),
			array("sitemorse_query", "Additional Query String",
				array( $this->elem, 'text_field' ),
				array( 'id' => 'sitemorse_query' ) )
			), 'sitemorse_conn_section' );

	}


	/**
	 * Create Admin pages
	 *
	 * @since    1.0.0
	 */
	public function add_admin_pages() {

		add_menu_page( 'Sitemorse Redirect', 'Sitemorse Redirect',
			get_option("sitemorse_user_permission"),
			'sitemorse_redirect_page', 'sitemorse_redirect' );
		add_menu_page( 'Sitemorse Connection', 'Sitemorse Connection', 'administrator',
			'sitemorse_conn_test_page', 'sitemorse_conn_test' );
		add_menu_page( 'Sitemorse Results', 'Sitemorse Results',
			get_option("sitemorse_user_permission"),
			'sitemorse_latest_scan_page', 'sitemorse_latest_scan',
			$icon_url=sm_image_url("sm-rondel-plain.png") );
		remove_menu_page( 'sitemorse_redirect_page' );
		remove_menu_page( 'sitemorse_conn_test_page' );
		add_options_page( 'Sitemorse', 'Sitemorse', 'administrator',
			'sitemorse_config_page', array( $this, 'sitemorse_config' ) );

	}

	/**
	 * Add SCI link to the admin bar
	 *
	 * @since    1.0.0
	 */
	public function sci_link( $wp_admin_bar ) {

		if ( is_admin() ) {
			return;
		}
		if ( !current_user_can( get_option( 'sitemorse_user_permission' ) ) ) {
			return;
		}
		if ( get_option( 'sitemorse_preview' ) == 'everywhere'
			|| ( get_option( 'sitemorse_preview' ) == 'preview' && is_preview() )
			|| ( get_option( 'sitemorse_preview' ) == 'edit_preview' && is_preview()
			) ) {
			$hide_menu = ( strlen( $_SERVER['QUERY_STRING'] ) ? '&' : '?') . 'sitemorseSCI';
			$current_url = get_option( 'home' ) . $_SERVER["REQUEST_URI"];
			$admin_url = admin_url( 'admin.php?page=sitemorse_redirect_page' ) .
				'&url=' . urlencode( $current_url . $hide_menu ) . "&closeLoading";
			$sm_logo_src = sm_image_url("sm-icon-dark.gif");
			$sm_logo_src_mo = sm_image_url("sm-icon-darkblue.gif");
			$args = array(
				'id' => 'sitemorse_sci_link',
				"title" => "<script type='text/javascript'>jQuery(function() {
	jQuery('#wp-admin-bar-sitemorse_sci_link').hover(
  function() {
    jQuery('#sm_adminbar_icon').attr('src', '$sm_logo_src_mo');
  }, function() {
    jQuery('#sm_adminbar_icon').attr('src', '$sm_logo_src');
  });
});</script>
	<img id='sm_adminbar_icon' src='$sm_logo_src' />&nbsp; Sitemorse",
				'href'  => '#',
				'meta'  => array(
					'mouseenter' => 'jQuery("#sm_adminbar_icon").attr("src", "' .
						$sm_logo_src_mo .'");',
					'onclick' => 'loadSCIPreview("' . $admin_url . '");'
				)
			);
			$wp_admin_bar->add_node($args);
		}

	}

	/**
	 * Add SCI global js values
	 *
	 * @since    1.0.0
	 */
	public function set_admin_globals() {

		if ( $this->is_checked( 'sitemorse_marked' )
			&& $this->is_checked( 'sitemorse_publish' ) ) {
			$prevent_publish = 'true';
		} else {
			$prevent_publish = 'false';
		}
		$base_img = plugins_url() . "/Sitemorse-SCI/includes/images/";
		echo <<<CONTENT
<script type='text/javascript'>
	sitemorseSCI["preventPublish"] = $prevent_publish;
	sitemorseSCI["baseImgPath"] = "$base_img";
</script>
CONTENT;

	}

	/**
	 * Hide admin bar from SCI scanner
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function show_admin_bar() {

		return !isset($_GET["sitemorseSCI"]);

	}

}


/**
 * Defines HTML elements for the Sitemorse SCI Admin form
 *
 * @link       http://www.sitemorse.com
 * @since      1.0.0
 *
 * @package    Sitemorse_SCI
 * @subpackage Sitemorse_SCI/admin
 * @author     Sitemorse Ltd
 */
class Sitemorse_SCI_Admin_Elements {
	/**
	 * Shorthand function for generic text field
	 *
	 * @since    1.0.0
	 */
	public function text_field( $args ) {

		$id = $args['id'];
		$desc = isset( $args['desc'] ) ? $args['desc'] : '';
		$default = isset( $args['default'] ) ? $args['default'] : '';
		$class = isset( $args['class'] ) ? $args['class'] : '';

		$option = get_option( $id, $default );
		echo "<input id='$id' name='${id}[text_string]' type='text'"
			. " class='$class' value='{$option['text_string']}' />";
		if ( $desc ) {
			echo "<br /><p style='font-size:0.8em; font-style: italic;'>$desc</p>";
		}

	}

	/**
	 * Shorthand function for generic checkbox
	 *
	 * @since    1.0.0
	 */
	public function checkbox_field( $args ) {

		$id = $args['id'];
		$desc = isset( $args['desc'] ) ? $args['desc'] : '';
		$default = isset( $args['default'] ) ? $args['default'] : 'on';
		$class = isset( $args['class'] ) ? $args['class'] : '';

		$option = get_option( $id, ['text_string' => $default] );
		$checked = '';
		if ( isset( $option['text_string'] ) && $option['text_string'] == 'on' ) {
			$checked = 'checked';
		}
		echo "<input id='$id' name='${id}[text_string]' " .
			" type='checkbox' " . $checked . " class='$class' />";
		if ( $desc ) {
			echo "<br /><p style='font-size:0.8em; font-style:italic;'>$desc</p>";
		}

	}

	public function licence_key_field() {

		$options = get_option('sitemorse_licence_key');
		$conn_url = admin_url('admin.php?page=sitemorse_conn_test_page');
		echo <<<CONTENT
<input id='sitemorse_licence_key' size='40' type='text'
 name='sitemorse_licence_key[text_string]'
 onchange='test_connection("$conn_url", this);'
 onkeyup='test_connection("$conn_url", this);'
 value='{$options['text_string']}' />
<script type='text/javascript'>
window.onload = function(e) {
	test_connection("$conn_url",
		document.getElementById("sitemorse_licence_key"));
}
</script>
<div id='sitemorse_connection_icon' />
CONTENT;

	}

	public function preview_field() {

		$options = get_option('sitemorse_preview');
		$everywhere_selected = '';
		$edit_preview_selected = '';
		$preview_selected = '';
		$edit_selected = '';
		if ($options == 'everywhere') $everywhere_selected = 'selected';
		elseif ($options == 'edit_preview') $edit_preview_selected = 'selected';
		elseif ($options == 'preview') $preview_selected = 'selected';
		elseif ($options == 'edit') $edit_selected = 'selected';
		echo <<<CONTENT
<select id="sitemorse_preview" name="sitemorse_preview">
	<option value="everywhere" $everywhere_selected>Everywhere</option>
	<option value="edit_preview" $edit_preview_selected>Edit &amp; Preview sections</option>
	<option value="preview" $preview_selected>Preview Only</option>
	<option value="edit" $edit_selected>Edit Only</option>
</select>
<br />
<p style='font-size:0.8em; font-style: italic;'>Where in Wordpress to allow Sitemorse SCI testing</p>
CONTENT;

	}

	public function user_permission_field() {

		$options = get_option('sitemorse_user_permission');
		$admin_selected = 'selected';
		$editor_selected = '';
		if ($options == 'edit_pages') {
			$admin_selected = '';
			$editor_selected = 'selected';
		}
		echo <<<CONTENT
<select id="sitemorse_user_permission"
	name="sitemorse_user_permission">
	<option value="administrator" $admin_selected>Administrator</option>
	<option value="edit_pages" $editor_selected>Editor</option>
</select>
<br />
<p style='font-size:0.8em; font-style: italic;'>The permissions needed to run the SCI. Defaults to Administrator.</p>
CONTENT;

	}

}
