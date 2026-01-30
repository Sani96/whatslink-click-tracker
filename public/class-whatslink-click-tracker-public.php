<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wpsani.store
 * @since      1.0.0
 *
 * @package    WhatsLink_Click_Tracker
 * @subpackage WhatsLink_Click_Tracker/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WhatsLink_Click_Tracker
 * @subpackage WhatsLink_Click_Tracker/public
 * @author     WPSani <support@wpsani.store>
 */
class WhatsLink_Click_Tracker_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WhatsLink_Click_Tracker_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $loader = null ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->loader = $loader;
		$this->setup_hooks();
	}

	/**
	 * Register the hooks for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function setup_hooks() {
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts');
		$this->loader->add_action( 'wp_ajax_whatslink_click_tracker_log_click', $this, 'whatslink_click_tracker_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_whatslink_click_tracker_log_click', $this, 'whatslink_click_tracker_callback' );
	}

	/**
	 * Callback function for logging clicks.
	 *
	 * @since    1.0.0
	 */
	function whatslink_click_tracker_callback() {
		check_ajax_referer( 'whatslink_click_tracker_nonce', 'nonce' );
		global $wpdb;
		$url = isset($_POST['url']) ? esc_url_raw( wp_unslash($_POST['url']) ) : '';
		$referrer = isset($_POST['referrer']) ? esc_url_raw( wp_unslash($_POST['referrer']) ) : '';
		if ( empty( $url )) {
			wp_send_json_error( [ 'message' => 'Missing URL data' ] );
		}
		$post_id = intval($_POST['post_id'] ?? 0);
		$post    = $post_id ? get_post($post_id) : null;	

		$post_title = $post ? $post->post_title : 'Unknown';
		$post_type  = $post ? $post->post_type : 'Unknown';
		if ($post_id === 0) {
			$post_title = 'Homepage';
			$post_type  = 'page';
		}
		
		$permalink = $post ? get_permalink($post_id) : $url;
		$parsed_url = wp_parse_url($permalink);
		parse_str($parsed_url['query'] ?? '', $query_args);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$wpdb->prefix . 'whatslink_click_tracker_clicks',
			array(
				'post_title'     => $post_title,
				'post_type'      => $post_type,
				'permalink'      => $permalink,
				'click_datetime' => current_time('mysql'),
				'referrer'       => $referrer ? $referrer : '-',
				'utm_source'     => $query_args['utm_source'] ?? '-',
				'utm_medium'     => $query_args['utm_medium'] ?? '-',
				'utm_campaign'   => $query_args['utm_campaign'] ?? '-',
			),
			array( '%s','%s','%s','%s','%s','%s','%s','%s' )
		);
		do_action( 'whatslink_click_tracker_log_registered', [
			'post_title'     => $post_title,
			'post_type'      => $post_type,
			'permalink'      => $permalink,
			'click_datetime' => current_time('mysql'),
			'referrer'       => $referrer ? $referrer : '-',
			'utm_source'     => $query_args['utm_source'] ?? '-',
			'utm_medium'     => $query_args['utm_medium'] ?? '-',
			'utm_campaign'   => $query_args['utm_campaign'] ?? '-',
		] );
		wp_send_json_success();


	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WhatsLink_Click_Tracker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WhatsLink_Click_Tracker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( is_admin() || wp_doing_ajax() || is_feed() ) {
			return;
		}
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/whatslink-click-tracker-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WhatsLink_Click_Tracker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WhatsLink_Click_Tracker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'js/whatslink-click-tracker-public.js',
			array('jquery'),
			$this->version,
			true
		);		

		wp_localize_script(
			$this->plugin_name,
			'whatslink_click_tracker',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('whatslink_click_tracker_nonce'),
				'post_id'  => get_queried_object_id(),
			)
		);

	}
}
