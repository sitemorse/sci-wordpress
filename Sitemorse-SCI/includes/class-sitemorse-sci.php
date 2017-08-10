<?php

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


/**
 * @package Sitemorse_SCI
 * @version 1.0
 */
/*
Plugin Name: Sitemorse SCI
Plugin URI: http://sitemorse.com
Description: The Sitemorse SCI plugin allows you to access Sitemorse tests and metrics before your pages are published, to ensure pages fully conform to standards.
Author: Sitemorse (UK Sales) Ltd
Version: 1.0
*/

require "lib/SCIClient.php";

function sm_image_url($fn) {
	return plugins_url() . "/Sitemorse-SCI/includes/images/" . $fn;
}

function sm_save_meta($post_ID, $priorities, $url) {
	if (!$priorities) $status = 1;
	else $status = 0;
	$sm_data = sm_get_meta($post_ID);
	$st = "0";
	if (array_key_exists("status", $sm_data)) $st = $sm_data["status"];
	$sm_data["last_status"] = $status;
	$sm_data["last_url"] = $url;
	$sm_data["last_priorities"] = $priorities;
	$sm_data["last_date"] = date("m/d/Y");
	$sm_data = $st . base64_encode(json_encode($sm_data));
	delete_post_meta($post_ID, "sitemorse_data");
	add_post_meta($post_ID, "sitemorse_data", $sm_data);
}

function sm_publish_meta($post_ID) {
	$sm_data = sm_get_meta($post_ID);
	$st = $sm_data["last_status"];
	$sm_data["status"] = $st;
	$sm_data["url"] = $sm_data["last_url"];
	$sm_data["priorities"] = $sm_data["last_priorities"];
	$sm_data["date"] = $sm_data["last_date"];
	$sm_data = $st . base64_encode(json_encode($sm_data));
	delete_post_meta($post_ID, "sitemorse_data");
	add_post_meta($post_ID, "sitemorse_data", $sm_data);
}

function sm_get_meta($post_ID) {
	$sm_data = substr(get_post_meta($post_ID, "sitemorse_data", true), 1);
	$sm_data = json_decode(base64_decode($sm_data), $assoc=true);
	if (is_array($sm_data)) return $sm_data;
	return ["last_status" => "", "last_url" => "", "last_priorities" => 0,
		"last_date" => ""];
}

class Sitemorse_SCI {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since		1.0.0
	 * @access	 protected
	 * @var			Sitemorse_SCI_Loader		$loader		Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since		1.0.0
	 * @access	 protected
	 * @var			string		$plugin_name		The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since		1.0.0
	 * @access	 protected
	 * @var			string		$version		The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since		1.0.0
	 */
	public function __construct() {

		$this->plugin_name = "Sitemorse SCI";
		$this->version = "1.0.0";

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sitemorse_SCI_Loader. Orchestrates the hooks of the plugin.
	 * - Sitemorse_SCI_i18n. Defines internationalization functionality.
	 * - Sitemorse_SCI_Admin. Defines all hooks for the admin area.
	 * - Sitemorse_SCI_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since		1.0.0
	 * @access	 private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-sitemorse-sci-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-sitemorse-sci-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'admin/class-sitemorse-sci-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'public/class-sitemorse-sci-public.php';

		$this->loader = new Sitemorse_SCI_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sitemorse_SCI_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since		1.0.0
	 * @access	 private
	 */
	private function set_locale() {
		return;

		$plugin_i18n = new Sitemorse_SCI_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since		1.0.0
	 * @access	 private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Sitemorse_SCI_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_pages' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_form' );
		$this->loader->add_action( 'admin_bar_menu', $plugin_admin, 'sci_link', 9999 );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'set_admin_globals' );
		$this->loader->add_action( 'show_admin_bar', $plugin_admin, 'show_admin_bar' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since		1.0.0
	 * @access	 private
	 */
	private function define_public_hooks() {

		$plugin_public = new Sitemorse_SCI_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_head', $plugin_public, 'set_globals' );

}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since		1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since		 1.0.0
	 * @return		string		The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since		 1.0.0
	 * @return		Plugin_Name_Loader		Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since		 1.0.0
	 * @return		string		The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}


/***********************************************************/
/*																												 */
/*						Sitemorse SCI Preview page									 */
/*																												 */
/***********************************************************/


function sitemorse_post_submit_meta_box( $post, $args = array() ) {
		global $action;

		$sitemorse_status = $post->sitemorse_status;
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object($post_type);
		$can_publish = current_user_can($post_type_object->cap->publish_posts);
?>
<div class="submitbox" id="submitpost">
 
<div id="minor-publishing">
 
<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
<div style="display:none;">
<?php submit_button( __( 'Save' ), 'button', 'save' ); ?>
</div>
 
<div id="minor-publishing-actions">
<div id="save-action">
<?php if ( 'publish' != $post->post_status && 'future' != $post->post_status && 'pending' != $post->post_status ) { ?>
<input <?php if ( 'private' == $post->post_status ) { ?>style="display:none"<?php } ?> type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save Draft'); ?>" class="button" />
<span class="spinner"></span>
<?php } elseif ( 'pending' == $post->post_status && $can_publish ) { ?>
<input type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save as Pending'); ?>" class="button" />
<?php } ?>
&nbsp;&nbsp;
</div>
<?php if ( is_post_type_viewable( $post_type_object ) ) : ?>
<div id="preview-action">
<?php
$preview_link = esc_url( get_preview_post_link( $post ) );
if ( 'publish' == $post->post_status ) {
	$preview_button = __( 'Preview' );
} else {
	$preview_button = __( 'Preview' );
}
?>
<?php $sm_logo_src = sm_image_url("sm-icon-gray.gif");?>
<a class="preview button" href="<?php echo $preview_link; ?>" target="wp-preview-<?php echo (int) $post->ID; ?>" id="post-preview"><?php echo $preview_button; ?></a>
<div class="preview button button-smicon" id="sci-post-preview"><img src="<?php echo $sm_logo_src; ?>" /><p>&nbsp;Assess</p></div>
<input type="hidden" name="wp-preview" id="wp-preview" value="" />
</div>
<?php endif; // public post type ?>
<?php
/**
 * Fires before the post time/date setting in the Publish meta box.
 *
 * @since 4.4.0
 *
 * @param WP_Post $post WP_Post object for the current post.
 */
do_action( 'post_submitbox_minor_actions', $post );
?>
<div class="clear"></div>
</div><!-- #minor-publishing-actions -->
 
<div id="misc-publishing-actions">
 
<div class="misc-pub-section misc-pub-post-status"><label for="post_status"><?php _e('Status:') ?></label>
<span id="post-status-display">
<?php
switch ( $post->post_status ) {
		case 'private':
				_e('Privately Published');
				break;
		case 'publish':
				_e('Published');
				break;
		case 'future':
				_e('Scheduled');
				break;
		case 'pending':
				_e('Pending Review');
				break;
		case 'draft':
		case 'auto-draft':
				_e('Draft');
				break;
}
?>
</span>
<?php if ( 'publish' == $post->post_status || 'private' == $post->post_status || $can_publish ) { ?>
<a href="#post_status" <?php if ( 'private' == $post->post_status ) { ?>style="display:none;" <?php } ?>class="edit-post-status hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit status' ); ?></span></a>
 
<div id="post-status-select" class="hide-if-js">
<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr( ('auto-draft' == $post->post_status ) ? 'draft' : $post->post_status); ?>" />
<select name='post_status' id='post_status'>
<?php if ( 'publish' == $post->post_status ) : ?>
<option<?php selected( $post->post_status, 'publish' ); ?> value='publish'><?php _e('Published') ?></option>
<?php elseif ( 'private' == $post->post_status ) : ?>
<option<?php selected( $post->post_status, 'private' ); ?> value='publish'><?php _e('Privately Published') ?></option>
<?php elseif ( 'future' == $post->post_status ) : ?>
<option<?php selected( $post->post_status, 'future' ); ?> value='future'><?php _e('Scheduled') ?></option>
<?php endif; ?>
<option<?php selected( $post->post_status, 'pending' ); ?> value='pending'><?php _e('Pending Review') ?></option>
<?php if ( 'auto-draft' == $post->post_status ) : ?>
<option<?php selected( $post->post_status, 'auto-draft' ); ?> value='draft'><?php _e('Draft') ?></option>
<?php else : ?>
<option<?php selected( $post->post_status, 'draft' ); ?> value='draft'><?php _e('Draft') ?></option>
<?php endif; ?>
</select>
 <a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e('OK'); ?></a>
 <a href="#post_status" class="cancel-post-status hide-if-no-js button-cancel"><?php _e('Cancel'); ?></a>
</div>
 
<?php } ?>
</div><!-- .misc-pub-section -->
 
<div class="misc-pub-section misc-pub-visibility" id="visibility">
<?php _e('Visibility:'); ?> <span id="post-visibility-display"><?php
 
if ( 'private' == $post->post_status ) {
		$post->post_password = '';
		$visibility = 'private';
		$visibility_trans = __('Private');
} elseif ( !empty( $post->post_password ) ) {
		$visibility = 'password';
		$visibility_trans = __('Password protected');
} elseif ( $post_type == 'post' && is_sticky( $post->ID ) ) {
		$visibility = 'public';
		$visibility_trans = __('Public, Sticky');
} else {
		$visibility = 'public';
		$visibility_trans = __('Public');
}
 
echo esc_html( $visibility_trans ); ?></span>
<?php if ( $can_publish ) { ?>
<a href="#visibility" class="edit-visibility hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit visibility' ); ?></span></a>
 
<div id="post-visibility-select" class="hide-if-js">
<input type="hidden" name="hidden_post_password" id="hidden-post-password" value="<?php echo esc_attr($post->post_password); ?>" />
<?php if ($post_type == 'post'): ?>
<?php endif; ?>
<input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="<?php echo esc_attr( $visibility ); ?>" />
<input type="radio" name="visibility" id="visibility-radio-public" value="public" <?php checked( $visibility, 'public' ); ?> /> <label for="visibility-radio-public" class="selectit"><?php _e('Public'); ?></label><br />
<?php if ( $post_type == 'post' && current_user_can( 'edit_others_posts' ) ) : ?>
<span id="sticky-span"><input id="sticky" name="sticky" type="checkbox" value="sticky" <?php checked( is_sticky( $post->ID ) ); ?> /> <label for="sticky" class="selectit"><?php _e( 'Stick this post to the front page' ); ?></label><br /></span>
<?php endif; ?>
<input type="radio" name="visibility" id="visibility-radio-password" value="password" <?php checked( $visibility, 'password' ); ?> /> <label for="visibility-radio-password" class="selectit"><?php _e('Password protected'); ?></label><br />
<span id="password-span"><label for="post_password"><?php _e('Password:'); ?></label> <input type="text" name="post_password" id="post_password" value="<?php echo esc_attr($post->post_password); ?>"	maxlength="20" /><br /></span>
<input type="radio" name="visibility" id="visibility-radio-private" value="private" <?php checked( $visibility, 'private' ); ?> /> <label for="visibility-radio-private" class="selectit"><?php _e('Private'); ?></label><br />
 
<p>
 <a href="#visibility" class="save-post-visibility hide-if-no-js button"><?php _e('OK'); ?></a>
 <a href="#visibility" class="cancel-post-visibility hide-if-no-js button-cancel"><?php _e('Cancel'); ?></a>
</p>
</div>
<?php } ?>
 
</div><!-- .misc-pub-section -->
 
<?php
/* translators: Publish box date format, see http://php.net/date */
$datef = __( 'M j, Y @ H:i' );
if ( 0 != $post->ID ) {
		if ( 'future' == $post->post_status ) { // scheduled for publishing at a future date
				$stamp = __('Scheduled for: <b>%1$s</b>');
		} elseif ( 'publish' == $post->post_status || 'private' == $post->post_status ) { // already published
				$stamp = __('Published on: <b>%1$s</b>');
		} elseif ( '0000-00-00 00:00:00' == $post->post_date_gmt ) { // draft, 1 or more saves, no date specified
				$stamp = __('Publish <b>immediately</b>');
		} elseif ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // draft, 1 or more saves, future date specified
				$stamp = __('Schedule for: <b>%1$s</b>');
		} else { // draft, 1 or more saves, date specified
				$stamp = __('Publish on: <b>%1$s</b>');
		}
		$date = date_i18n( $datef, strtotime( $post->post_date ) );
} else { // draft (no saves, and thus no date specified)
		$stamp = __('Publish <b>immediately</b>');
		$date = date_i18n( $datef, strtotime( current_time('mysql') ) );
}
 
if ( ! empty( $args['args']['revisions_count'] ) ) :
		$revisions_to_keep = wp_revisions_to_keep( $post );
?>
<div class="misc-pub-section misc-pub-revisions">
<?php
		if ( $revisions_to_keep > 0 && $revisions_to_keep <= $args['args']['revisions_count'] ) {
				echo '<span title="' . esc_attr( sprintf( __( 'Your site is configured to keep only the last %s revisions.' ),
						number_format_i18n( $revisions_to_keep ) ) ) . '">';
				printf( __( 'Revisions: %s' ), '<b>' . number_format_i18n( $args['args']['revisions_count'] ) . '+</b>' );
				echo '</span>';
		} else {
				printf( __( 'Revisions: %s' ), '<b>' . number_format_i18n( $args['args']['revisions_count'] ) . '</b>' );
		}
?>
		<a class="hide-if-no-js" href="<?php echo esc_url( get_edit_post_link( $args['args']['revision_id'] ) ); ?>"><span aria-hidden="true"><?php _ex( 'Browse', 'revisions' ); ?></span> <span class="screen-reader-text"><?php _e( 'Browse revisions' ); ?></span></a>
</div>
<?php endif;
 
if ( $can_publish ) : // Contributors don't get to choose the date of publish ?>
<div class="misc-pub-section curtime misc-pub-curtime">
		<span id="timestamp">
		<?php printf($stamp, $date); ?></span>
		<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js"><span aria-hidden="true"><?php _e( 'Edit' ); ?></span> <span class="screen-reader-text"><?php _e( 'Edit date and time' ); ?></span></a>
		<fieldset id="timestampdiv" class="hide-if-js">
		<legend class="screen-reader-text"><?php _e( 'Date and time' ); ?></legend>
		<?php touch_time( ( $action === 'edit' ), 1 ); ?>
		</fieldset>
</div><?php // /misc-pub-section ?>
<?php endif; ?>
 
<?php
/**
 * Fires after the post time/date setting in the Publish meta box.
 *
 * @since 2.9.0
 * @since 4.4.0 Added the `$post` parameter.
 *
 * @param WP_Post $post WP_Post object for the current post.
 */
do_action( 'post_submitbox_misc_actions', $post );
?>
</div>
<div class="clear"></div>
</div>
 
<div id="major-publishing-actions">
<?php
/**
 * Fires at the beginning of the publishing actions section of the Publish meta box.
 *
 * @since 2.7.0
 */
do_action( 'post_submitbox_start' );
?>
<div id="delete-action">
<?php
if ( current_user_can( "delete_post", $post->ID ) ) {
		if ( !EMPTY_TRASH_DAYS )
				$delete_text = __('Delete Permanently');
		else
				$delete_text = __('Move to Trash');
		?>
<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
} ?>
</div>
 
<div id="publishing-action">
<span class="spinner"></span>
<?php
if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
		if ( $can_publish ) :
				if ( !empty($post->post_date_gmt) && time() < strtotime( $post->post_date_gmt . ' +0000' ) ) : ?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Schedule') ?>" />
				<?php submit_button( __( 'Schedule' ), 'primary button-large', 'publish', false ); ?>
<?php		else : ?>
				<input name="original_publish" type="hidden" id="original_publish" style="display:none" value="<?php esc_attr_e('Publish') ?>" />
				<input type="submit" name="publish" id="publish" class="button button-primary button-large" style="display:none" value="Publish">
				<?php $sm_logo_src = sm_image_url("sm-icon-blue.gif");?>
				<div id="sitemorse_verify" class="sci-post-preview-container">
					<img src="<?php echo $sm_logo_src; ?>" />&nbsp;
					<p>Publish</p>
				</div>
<?php		endif;
		else : ?>
				<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>" />
				<?php submit_button( __( 'Submit for Review' ), 'primary button-large', 'publish', false ); ?>
<?php
		endif;
} else {
		$sm_logo_src = sm_image_url("sm-icon-blue.gif");
?>
				<div id="sitemorse_verify" class="sci-post-preview-container">
					<img src="<?php echo $sm_logo_src; ?>" />&nbsp;
					<p>Publish</p>
				</div>
				<input name="original_publish" type="hidden" style="display:none;"
					id="original_publish" value="<?php esc_attr_e('Update') ?>" />
				<input name="save" type="submit" style="display:none;"
					class="button button-primary button-large" id="publish" value="<?php esc_attr_e('Update') ?>" />
<?php
} ?>
</div>
<div class="clear"></div>
</div>
</div>
 
<?php
}

function change_publish_meta_box() {
	remove_meta_box("submitdiv", "post", "side");
	add_meta_box("submitdiv", "Publish",
		"sitemorse_post_submit_meta_box", null, "side", "high");
}
add_action( 'add_meta_boxes_post', 'change_publish_meta_box' );
add_action( 'add_meta_boxes_page', 'change_publish_meta_box' );

add_action("edit_form_after_title", function() {
	global $post, $wp_meta_boxes;
	do_meta_boxes(get_current_screen(), "advanced", $post);
	unset($wp_meta_boxes[get_post_type($post)]["advanced"]);
});

/*
*	If current page is preview in iframe, redirect to SCI
*/
function get_sci_admin_url($postID, $current_url=null) {
	if (!$current_url) {
		$current_url = get_option( 'home' ) . $_SERVER["REQUEST_URI"];
		$hide_menu = (strlen($_SERVER["QUERY_STRING"]) ? "&" : "?")
		  . "sitemorseSCI";
		$current_url .= $hide_menu;
	}
	$postID_url = "";
	if ($postID) {
		$postID_url .= "&postID=$postID";
	}
	return admin_url("admin.php?page=sitemorse_redirect_page") .
		$postID_url . "&url=" . urlencode($current_url);
}


add_action("wp_head", "sci_admin_redirect");
function sci_admin_redirect() {
	if (is_preview()) {
		$admin_url = get_sci_admin_url(get_the_ID());
		echo <<<CONTENT
<script type='text/javascript'>
	iframePreviewRedirect('${admin_url}');
</script>
CONTENT;
	}
	if (is_404()) {
		echo <<<CONTENT
<script type='text/javascript'>
if (parent.top.jQuery("#sci-post-preview").length) {
	parent.top.showSCI();
}
</script>
CONTENT;
	}
}

/*
*	 On publish, save the sitemorse status
*/

add_action("publish_post", "sm_publish_meta");
add_action("publish_page", "sm_publish_meta");

add_action('wp_dashboard_setup', 'remove_dashboard_widgets' );
function remove_dashboard_widgets() {
	global $wp_meta_boxes;
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
}

add_action('wp_dashboard_setup', 'add_custom_dashboard_activity' );
function add_custom_dashboard_activity() {
	wp_add_dashboard_widget('custom_dashboard_activity', 'Activities', 'sitemorse_dashboard_site_activity');
	wp_add_dashboard_widget('dashboard_widget', 'Sitemorse Tasks', 'sitemorse_dashboard_tasks');
}

function sitemorse_dashboard_site_activity() {
	echo '<div id="activity-widget">';

	$future_posts = sitemorse_dashboard_recent_posts( array(
		'max'     => 5,
		'status'  => 'future',
		'order'   => 'ASC',
		'title'   => __( 'Publishing Soon' ),
		'id'      => 'future-posts',
	) );
	$recent_posts = sitemorse_dashboard_recent_posts( array(
		'max'     => 5,
		'status'  => 'publish',
		'order'   => 'DESC',
		'title'   => __( 'Recently Published' ),
		'id'      => 'published-posts',
	) );

	$recent_comments = wp_dashboard_recent_comments();

	if ( !$future_posts && !$recent_posts && !$recent_comments ) {
		echo '<div class="no-activity">';
		echo '<p class="smiley"></p>';
		echo '<p>' . __( 'No activity yet!' ) . '</p>';
		echo '</div>';
	}

	echo '</div>';
}

function sitemorse_dashboard_recent_posts( $args ) {
	$query_args = array(
		'post_type'      => array('post', 'page'),
		'post_status'    => $args['status'],
		'orderby'        => 'date',
		'order'          => $args['order'],
		'posts_per_page' => intval( $args['max'] ),
		'no_found_rows'  => true,
		'cache_results'  => false,
		'perm'           => ( 'future' === $args['status'] ) ? 'editable' : 'readable',
	);

	/**
	 * Filter the query arguments used for the Recent Posts widget.
	 *
	 * @since 4.2.0
	 *
	 * @param array $query_args The arguments passed to WP_Query to produce the list of posts.
	 */
	$query_args = apply_filters( 'dashboard_recent_posts_query_args', $query_args );
	$posts = new WP_Query( $query_args );

	if ( $posts->have_posts() ) {

		echo '<div id="' . $args['id'] . '" class="activity-block">';

		echo '<h3>' . $args['title'] . '</h3>';

		echo '<ul>';

		$today    = date( 'Y-m-d', current_time( 'timestamp' ) );
		$tomorrow = date( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) );

		while ( $posts->have_posts() ) {
			$posts->the_post();

			$time = get_the_time( 'U' );
			if ( date( 'Y-m-d', $time ) == $today ) {
				$relative = __( 'Today' );
			} elseif ( date( 'Y-m-d', $time ) == $tomorrow ) {
				$relative = __( 'Tomorrow' );
			} elseif ( date( 'Y', $time ) !== date( 'Y', current_time( 'timestamp' ) ) ) {
				/* translators: date and time format for recent posts on the dashboard, from a different calendar year, see http://php.net/date */
				$relative = date_i18n( __( 'M jS Y' ), $time );
			} else {
				/* translators: date and time format for recent posts on the dashboard, see http://php.net/date */
				$relative = date_i18n( __( 'M jS' ), $time );
			}
			if (get_option("sitemorse_demo_mode")) {
				$relative = 'Today';
			}

			// Use the post edit link for those who can edit, the permalink otherwise.
			$recent_post_link = current_user_can( 'edit_post', get_the_ID() ) ? get_edit_post_link() : get_permalink();

			/* translators: 1: relative date, 2: time, 3: post edit link or permalink, 4: post title */
			$parsed_url = parse_url( get_page_link() );
			$hide_menu = (isset($parsed_url['query']) ? "&" : "?") . "sitemorseSCI";
			$admin_url = admin_url( 'admin.php?page=sitemorse_redirect_page' ) .
				'&url=' . urlencode( get_page_link() . $hide_menu );
			$sm_data = sm_get_meta(get_the_ID());
			if (!$sm_data) $sm_data = [];
			if (!array_key_exists("status", $sm_data)) {
				$sm_link = '<img src="' . sm_image_url("sm-rondel-grey.png") . '" />';
			} elseif ($sm_data["status"]) {
				$sm_link = '<img src="' . sm_image_url("sm-rondel-green.png") . '" />';
			} else {
				$sm_link = '<span style="color:#EA400E;font-size:14px;display:inline;">' .
					'<img src="' . sm_image_url("sm-rondel-red.png") . '" /></span>';
			}
			if (get_option("sitemorse_demo_mode")) {
				$avatar = get_avatar(get_the_author_meta( 'ID' ), 22, "", get_the_author_meta('login'));
			} else {
				$avatar = "";
			}
			if (array_key_exists("date", $sm_data)) {
				$today = new DateTime();
				$sm_date = new DateTime(str_replace("\\", "", $sm_data["date"]));
				$age = $today->diff($sm_date)->format("%a");
			} else {
				$age = 30;
			}
			if (array_key_exists("url", $sm_data) && $age<28)
				$sm_link = '<a href="' . $sm_data["url"] . '">' . $sm_link . '</a>';
			else
				$sm_link = '<a href="' . $admin_url . '">' . $sm_link . '</a>';
			$format = __( '%6$s&nbsp;<span style="min-width:104px; display:inline-block;">' .
				'%1$s, %2$s</span> %5$s&nbsp;&nbsp;' .
				'<a style="width:180px; display:inline-block;"' .
				' href="%3$s">%4$s</a>' );
			printf( "<li>$format</li>", $relative, get_the_time(), $recent_post_link,
				substr(_draft_or_post_title(), 0, 20) . "...", $sm_link, $avatar );
		}

		echo '</ul>';
		echo '</div>';

	} else {
		return false;
	}

	wp_reset_postdata();

	return true;
}

function sitemorse_dashboard_tasks() {
	echo '<div id="sitemorse-activity-widget">';

	$recent_posts = sitemorse_dashboard_task_posts( array(
		'max'     => 25,
		'status'  => 'publish',
		'order'   => 'DESC',
		'title'   => __( '' ),
		'id'      => 'sitemorse-tasks',
	) );

	echo '</div>';
}

function sitemorse_dashboard_task_posts( $args ) {
	$query_args = array(
		'post_type'      => array('post', 'page'),
		'post_status'    => $args['status'],
		'orderby'        => 'date',
		'order'          => $args['order'],
		'posts_per_page' => intval( $args['max'] ),
		'no_found_rows'  => true,
		'cache_results'  => false,
		'perm'           => ( 'future' === $args['status'] ) ? 'editable' : 'readable',
		'meta_key'   => 'sitemorse_data',
		'meta_value' => '^1',
		'meta_compare' => 'NOT REGEXP',
	);

	/**
	 * Filter the query arguments used for the Recent tasks widget.
	 *
	 * @since 4.2.0
	 *
	 * @param array $query_args The arguments passed to WP_Query to produce the list of posts.
	 */
	$query_args = apply_filters( 'dashboard_recent_posts_query_args', $query_args );
	$posts = new WP_Query( $query_args );

	if ( $posts->have_posts() ) {

		echo '<div id="' . $args['id'] . '" class="activity-block">';

		echo '<h3>' . $args['title'] . '</h3>';

		echo '<table>';

		$today    = date( 'Y-m-d', current_time( 'timestamp' ) );
		$tomorrow = date( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) );

		while ( $posts->have_posts() ) {
			$posts->the_post();

			$time = get_the_time( 'U' );
			if ( date( 'Y-m-d', $time ) == $today ) {
				$relative = __( 'Today' );
			} elseif ( date( 'Y-m-d', $time ) == $tomorrow ) {
				$relative = __( 'Tomorrow' );
			} else {
				/* translators: date and time format for recent posts on the dashboard, see http://php.net/date */
				$relative = date_i18n( __( 'M jS' ), $time );
			}
			$relative .= ", " . get_the_time( 'h:i a' );

			// Use the post edit link for those who can edit, the permalink otherwise.
			$recent_post_link = current_user_can( 'edit_post', get_the_ID() ) ? get_edit_post_link() : get_permalink();

			/* translators: 1: relative date, 2: time, 3: post edit link or permalink, 4: post title */
			$parsed_url = parse_url( get_page_link() );
			$hide_menu = (isset($parsed_url['query']) ? "&" : "?") . "sitemorseSCI";
			$admin_url = admin_url( 'admin.php?page=sitemorse_redirect_page' ) .
				'&url=' . urlencode( get_page_link() . $hide_menu );
			$sm_data = sm_get_meta(get_the_ID());
			$sm_status = "sm-rondel-grey.png";
			$sm_action = "Assess now";
			if (array_key_exists("status", $sm_data) && $sm_data["status"] == 0) {
				$sm_status = "sm-rondel-red.png";
				$sm_action = "Correct issues";
			}
			$sm_link = '<img src="' . sm_image_url($sm_status) . '" />';
			$priorities = "";
			if (array_key_exists("priorities", $sm_data) && $sm_data["priorities"]) {
				$priorities = $sm_data["priorities"];
			}
			if (array_key_exists("date", $sm_data)) {
				$today = new DateTime();
				$sm_date = new DateTime(str_replace("\\", "", $sm_data["date"]));
				$age = $today->diff($sm_date)->format("%a");
			} else {
				$age = 30;
			}
			if (array_key_exists("url", $sm_data) && $age<28)
				$sm_link = '<a href="' . $sm_data["url"] . '" style="color:#EA400E;font-size:14px;">' .
					$sm_link . '</a>';
			else
				$sm_link = '<a href="' . $admin_url . '">' . $sm_link . '</a>';
			$format = __( '%1$s &nbsp;&nbsp;%2$s %3$s<a style="width:180px; display:inline-block;"' .
				' href="%4$s">%5$s</a> %6$s %7$s' );
			echo "<tr><td>" .  get_avatar(get_the_author_meta( 'ID' ), 22, "", get_the_author_meta('login')) .
				"</td><td style='min-width:90px;'>" . get_the_author_meta( 'login' ) .
				"</td><td style='min-width:44px;'>" . $sm_link .
				"</td><td style='min-width:90px;'>" . $sm_action . "</td><td><a href='" .
				$recent_post_link . "' />" . substr(_draft_or_post_title(), 0, 40) . "</a></td><td></tr>";
		}
		echo '</table>';
		echo '</div>';

	} else {
		return false;
	}

	wp_reset_postdata();

	return true;
}

/***********************************************************/
/*																												 */
/*						Sitemorse SCI Magic Comments								 */
/*																												 */
/***********************************************************/

$GLOBALS["sitemorse_sci"] = ["licence" => ""];

function get_sitemorse_sci_globals() {
	if (!isset($_SERVER["HTTP_X_SCI_CONTROL"])) {
		return;
	}
	$sci = $_SERVER["HTTP_X_SCI_CONTROL"];
	$GLOBALS["sitemorse_sci"]["licence"] = substr($sci, 0, 8);
	foreach (explode(" ", strtolower(substr($sci,9))) as $kv) {
		$kv = explode("=", $kv);
		$val = true;
		if (count($kv) == 2 && $kv[1]) $val = $kv[1];
		$GLOBALS["sitemorse_sci"][$kv[0]] = $val;
	}
}

function is_checked($id) {
	$chk = get_option($id);
	return isset($chk["text_string"]) && $chk["text_string"] == "on";
}

function has_licence_in_header() {
	return $GLOBALS["sitemorse_sci"]["licence"] ==
		substr(get_option("sitemorse_licence_key")["text_string"], 0, 8);
}

function has_pageslist_in_header() {
	return isset($GLOBALS["sitemorse_sci"]["pageslist"]);
}

function sitemorse_get_author_long_email() {
	$first_name = get_the_author_meta("first_name");
	$last_name = get_the_author_meta("last_name");
	$email = get_the_author_meta("user_email");
	if ($first_name && $last_name) {
		return "editorid='$first_name $last_name <$email>'";
	} else if ($email) {
		return "editorid='$email'";
	} else {
		return;
	}
}

function sitemorse_mc_title($title) {
	if (is_admin()) return $title;
	$cmsedit = get_option( 'home' ) .
		"/wp-admin/post.php?post=" . get_the_ID() . "&action=edit";
	$magic = "<!--sitemorse:content ignore='none'" .
		" description='" . htmlspecialchars($title . " Title") . "'" .
		" cmsedit='" . htmlspecialchars($cmsedit) . "' " .
		sitemorse_get_author_long_email() . " -->";
	$end = "<!--sitemorse:/content-->";
	return $magic . $title . $end;
}

function sitemorse_mc_post($content) {
	$cmsedit = get_option( 'home' ) .
		"/wp-admin/post.php?post=" . get_the_ID() . "&action=edit";
	$magic = "<!--sitemorse:content ignore='none'" .
		" description='" . the_title_attribute("echo=0") . " Post" . "' " .
		" cmsedit='" . htmlspecialchars($cmsedit) . "' " .
		sitemorse_get_author_long_email() . " -->";
	$end = "<!--sitemorse:/content-->";
	return $magic . $content . $end;
}

function sitemorse_mc_image($content) {
	$cmsedit = get_option( 'home' ) .
		"/wp-admin/post.php?post=" . get_post_thumbnail_id() . "&action=edit";
	$magic = "<!--sitemorse:content ignore='none' description="
	 . "'Media Library Image' cmsedit='" . htmlspecialchars($cmsedit) . "' " .
		sitemorse_get_author_long_email() . " -->";
	$end = "<!--sitemorse:/content-->";
	return $magic . $content . $end;
}

function sitemorse_mc_pagelist($content) {
	$pl = "<!--sitemorse:pageslist pageslist='" .
		str_replace("--", "&#45;&#45;", htmlspecialchars(sitemorse_get_articles())) . "' -->";
	echo $pl . $content;
}

get_sitemorse_sci_globals();
if (has_licence_in_header() && is_checked("sitemorse_marked_the_title"))
	add_filter("the_title", "sitemorse_mc_title", 9999);
if (has_licence_in_header() && is_checked("sitemorse_marked_the_excerpt"))
	add_filter("the_excerpt", "sitemorse_mc_post", 9999);
if (has_licence_in_header() && is_checked("sitemorse_marked_get_the_excerpt"))
	add_filter("get_the_excerpt", "sitemorse_mc_post", 9999);
if (has_licence_in_header() && is_checked("sitemorse_marked_the_content"))
	add_filter("the_content", "sitemorse_mc_post", 9999);
if (is_checked("sitemorse_marked_get_the_content"))
	add_filter("get_the_content", "sitemorse_mc_post", 9999);
if (is_checked("sitemorse_marked_post_thumbnail"))
	add_filter("post_thumbnail_html", "sitemorse_mc_image", 9999);
if (has_licence_in_header() and has_pageslist_in_header() and
	is_checked("sitemorse_page_list")) {
	add_filter("wp_head", "sitemorse_mc_pagelist");
}

function sitemorse_get_articles() {
	$args = array(
		"sort_column"	=> "post_modified",
		"number"	=> 100,
		"sort_order"	 => "DESC"
	);
	$results = get_pages($args);
	$jsontext = "[";
	foreach($results as $result) {
		$jsontext .= "{";
		foreach($result as $key => $value) {
			if ($key == "ID")
				$jsontext .= '"id": "' . $value . '",';
			if ($key == "guid")
				$jsontext .= '"url": "' . addslashes($value) . '",';
			if ($key == "post_modified")
				$jsontext .= '"lastChanged": "' . $value . '",';
			if ($key == "post_author") {
				$jsontext .= '"lastEditorId": "' . $value . '",';
				$user_info = get_userdata($value);
				$jsontext .= '"lastEditorEmail": "' . $user_info->user_email . '",';
			}
		}
		$jsontext = substr_replace($jsontext, "", -1);
		$jsontext .= "},";
	}
	$jsontext = substr_replace($jsontext, "", -1);
	$jsontext .= "]";
	return $jsontext;
}


/***********************************************************/
/*																												 */
/*							Sitemorse SCI Request											*/
/*																												 */
/***********************************************************/

function sitemorse_conn_test() {
	$error = "";
	try {
		$sci = new SCIClient($_GET["key"]);
		$r = $sci->establishConnection();
	} catch(Exception $e) {
		$error = $e->getMessage();
	}
	if ($error) echo "<h1 id='sitemorseConnStatus'>fail</h1>";
	else echo "<h1 id='sitemorseConnStatus'>pass</h1>";
}

function sci_args($preview_url) {
	$args = [];
	$cookie_list = [];
	foreach ($_COOKIE as $k=>$v) {
		array_push($cookie_list,	$k . "=" . $v);
	}
	$cookie_list = [parse_url($preview_url, PHP_URL_HOST) => $cookie_list];
	$args["cookies"] = $cookie_list;
	$sec = get_option("sitemorse_ssl");
	$args["serverSecure"] = false;
	if (isset($sec["text_string"]) && $sec["text_string"] == "on")
		$args["serverSecure"] = true;
	$post_option = get_option("sitemorse_post");
	$post_allowed = isset($post_option["text_string"]) &&
		$post_option["text_string"] == "on";
	$args["postAllowed"] = $post_allowed;
	$proxy_option = get_option("sitemorse_proxy");
	if (isset($proxy_option["text_string"]) && $proxy_option["text_string"]) {
		$proxy = explode(":", $proxy_option["text_string"]);
		if (count($proxy) < 2)
			throw new Exception("Proxy server must be in format hostname:port");
		$args["proxyHostname"] = $proxy[0];
		$args["proxyPort"] = $proxy[1];
	}
	$sci_host = get_option("sitemorse_sci_host");
	if (isset($sci_host["text_string"]) && $sci_host["text_string"])
		$args["serverHostname"] = $sci_host["text_string"];
	$sci_port = get_option("sitemorse_sci_port");
	if (isset($sci_port["text_string"]) && $sci_port["text_string"])
		$args["serverPort"] = $sci_port["text_string"];
	$sci_ssl = get_option("sitemorse_sci_ssl_port");
	if ($args["serverSecure"] && isset($sci_ssl["text_string"])
		&& $sci_ssl["text_string"])
		$args["serverPort"] = $sci_ssl["text_string"];
	$headers = get_option("sitemorse_headers");
	if (isset($headers["text_string"]) && $headers["text_string"])
		$args["extraHeaders"] = explode("\n", $headers["text_string"]);
	$extra_query = get_option("sitemorse_query");
	if (isset($extra_query["text_string"]) && $extra_query["text_string"])
		$args["extraQuery"] = $extra_query["text_string"];
	return $args;
}

function sitemorse_priority_issues($results) {
	$total = 0;
	$results_object = json_decode($results);
	$priorities = $results_object->result->priorities;
	foreach ($priorities as $cat => $diags) {
		if ($diags->total) $total += $diags->total;
	}
	$priorities = $results_object->result->telnumbers;
	foreach ($priorities as $number => $diags) {
		if (property_exists($diags, "priority")) $total += $diags->total;
	}
	return $total;
}

function sitemorse_redirect() {
	if ( !isset($_GET["url"]) )
		return;
	$preview_url = $_GET["url"];
	$args = sci_args($preview_url);
	echo "<h2>Redirecting to Sitemorse...</h2>";
	$sci = new SCIClient(get_option("sitemorse_licence_key")["text_string"],
		$args=$args);
	$hostnames_option = explode(",",
		get_option("sitemorse_hostnames")["text_string"]);
	$hostnames = [];
	foreach ($hostnames_option as $hostname) {
		$p = parse_url(trim($hostname), PHP_URL_HOST);
		if ($p)
			array_push($hostnames, $p);
	}
	$url = "";
	$error = "";
	try {
		$r = @$sci->performTest($preview_url, $hostnames);
	} catch(Exception $e) {
		$sm_logo_src = sm_image_url("sm-icon-gray.gif");
		?>
<div style="float:left; margin-top:12px;"><img src="<?php echo $sm_logo_src; ?>" /></div>
<div style="float:left;">
	<h1 id='sitemorseConnStatus'>&nbsp;Sitemorse Connection Failure</h1>
	<br />
	<p>Please check your connection settings.</p>
</div>
<script type="text/javascript">
setParentSCI("error");
</script>
		<?php
		return;
	}
	$url = $r["url"] . "&ce";
	$results = $r["results"];
	$priorities = sitemorse_priority_issues($results);
	if (array_key_exists("postID",$_GET)) {
		sm_save_meta($_GET["postID"], $priorities, $url);
	}
	echo <<<CONTENT
<script type="text/javascript">
setParentSCI($results);
</script>
CONTENT;
	if ($r["debug"]) {
		echo "<h2>SCI url</h2>";
		echo "<p>$url</p>";
		if (isset($sitemorse_status)) {
			echo "<h2>Priority PASS</h2>";
		} else {
			echo "<h2>Priority Failures</h2>";
			var_dump($results);
		}
	} else {
		echo '<script type="text/javascript">' .
'  sciFinished("' . $url . '");' .
'</script>';
	}
	if ($error) {
		$pattern = "/\message '(.*?)\'/";
		preg_match($pattern, $error, $m);
		if (isset($m[1]))
			$error = $m[1];
		echo '<p><strong>SCI Error:</strong> ' . $error . '</p>';
	}
}


function sitemorse_latest_scan() {
	$SM_URL = "https://secure.sitemorse.com/sci-api.json" .
		"?op=last_scan&licence_key=%s&url=%s";
	echo "<h2 id='sitemorseScanTitle'>Sitemorse Latest Scan</h2>";
	echo "<div id='sitemorseScanContent'></div>";
	$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
		$_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	$current_url = urlencode($proto . $_SERVER['HTTP_HOST'] . '/');
	$sm_url = sprintf($SM_URL,
		get_option("sitemorse_licence_key")["text_string"],
		$current_url);
	$sm_json = file_get_contents($sm_url);
	$header = "Cookie: ";
	foreach ($http_response_header as $h) {
		if (substr($h, 0, 10) == "Set-Cookie") {
			$header .= substr($h, 12) . ";";
		}
	}
	$opts = array('http' => array('header'=> $header));
	$context = stream_context_create($opts);
	$sm_json = file_get_contents($sm_url, false, $context);
	$sm_data = json_decode($sm_json, $assoc=true);
	$url =  isset($sm_data["url"]) ? $sm_data["url"] : "";
	?>
<script type="text/javascript">
jQuery(function() {
	var url = "<?php echo $url; ?>";
	if (url) {
		var popup_window = window.open(url, "_blank");
		try {
			popup_window.focus();
			window.history.back();
		}
		catch (e) {
			jQuery("#sitemorseScanTitle").text("Sitemorse Pop-up blocked");
			jQuery("#sitemorseScanContent").empty();
			jQuery("#sitemorseScanContent").append(
				jQuery("<p>Please enable Pop-ups for this domain</p>"));
		}
	} else {
		jQuery("#sitemorseScanTitle").text("No Sitemorse Results");
		jQuery("#sitemorseScanContent").empty();
		jQuery("#sitemorseScanContent").append(
			jQuery("<p>No Sitemorse results to display.</p>"));
	}
});
</script>
	<?php
}
?>
