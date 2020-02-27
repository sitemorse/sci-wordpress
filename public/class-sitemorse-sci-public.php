<?php
/**
 *
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
 * @author     Sitemorse (UK Sales) Ltd
 */
class Sitemorse_SCI_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name = 'Sitemorse SCI';

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
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_register_style( 'scipublic', plugins_url( '/includes/css/scipublic.css', dirname( __FILE__ ) ), array(), null, 'all' );
		wp_enqueue_style( 'scipublic' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_register_script( 'sitemorse-scicommon', plugins_url( '/includes/js/scicommon.js', dirname( __FILE__ ) ), array( 'jquery' ) );
		wp_register_script( 'sitemorse-sci', plugins_url( '/public/js/sci.js', dirname( __FILE__ ) ), array( 'jquery' ) );
		wp_register_script( 'sitemorse-sciiframe', plugins_url( '/includes/js/sciiframe.js', dirname( __FILE__ ) ), array( 'jquery' ) );
		wp_enqueue_script( 'sitemorse-sciiframe' );
		wp_enqueue_script( 'sitemorse-scicommon' );
		wp_enqueue_script( 'sitemorse-sci' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function set_globals() {

		$base_img = plugins_url( '/includes/images/', dirname( __FILE__ ) );
		echo <<<CONTENT
<script type='text/javascript'>
	if (typeof sitemorseSCI !== 'undefined') {
		sitemorseSCI["baseImgPath"] = "$base_img";
	}
</script>
CONTENT;

	}

}
