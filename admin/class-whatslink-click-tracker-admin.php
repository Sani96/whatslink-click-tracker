<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpsani.store
 * @since      1.0.0
 *
 * @package    WhatsLink_Click_Tracker
 * @subpackage WhatsLink_Click_Tracker/admin
 */

class WhatsLink_Click_Tracker_Admin {

	private $plugin_name;
	private $version;
	protected $loader;

	/**
	 * Initialize the class and set its properties.
	 * 
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of the plugin.
	 * @param    string    $version           The version of the plugin.
	 * @param    object    $loader            The loader instance.
	 * @param    string    $debug_log_file_path The path to the debug log file.
	 */
	public function __construct($plugin_name, $version, $loader = null) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->loader = $loader;
		$this->setup_admin_hooks();
	}

    /**
     * Setup all admin hooks cleanly.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @return   void
     */
	public function setup_admin_hooks() {
		$this->loader->add_action('admin_menu', $this, 'whatslink_click_tracker_add_admin_menu');
		$this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts');
		$this->loader->add_action('wp_ajax_whatslink_click_tracker_get_click_logs', $this, 'whatslink_click_tracker_get_click_logs');
		$this->loader->add_action('wp_ajax_nopriv_whatslink_click_tracker_get_click_logs', $this, 'whatslink_click_tracker_get_click_logs');
		$this->loader->add_action('wp_ajax_whatslink_click_tracker_reset_clicks', $this, 'whatslink_click_tracker_reset_click_logs');
		$this->loader->add_action('admin_init', $this, 'register_license_settings');
		$this->loader->add_action('admin_init', $this, 'handle_license_actions');

	}

	/**
	 * Add the admin menu.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function whatslink_click_tracker_add_admin_menu() {
		add_menu_page(
			'WhatsLink Click Tracker',
			'WhatsLink Click Tracker',
			'manage_options',
			$this->plugin_name,
			array($this, 'whatslink_click_tracker_display_admin_page'),
			'dashicons-phone',
			6
		);
		$label = '';
		if ( ! defined('WHATSLINK_CLICK_TRACKER_PRO_IS_LICENSE_ACTIVE') || ! WHATSLINK_CLICK_TRACKER_PRO_IS_LICENSE_ACTIVE ) {
			$label .= ' <span class="whatslink-click-tracker-pro-badge">PRO</span>';
		}
		add_submenu_page(
			$this->plugin_name,
			'WhatsLink Click Tracker Report',
			'Report'.$label,
			'manage_options',
			'whatslink-click-tracker-report',
			array($this, 'whatslink_click_tracker_display_report_page')
		);

		add_submenu_page(
			$this->plugin_name,
			'Email Settings',
			'Email Settings'.$label,
			'manage_options',
			'whatslink-click-tracker-email-settings',
			[$this, 'whatslink_click_tracker_display_email_settings_page']
		);

		add_submenu_page(
			$this->plugin_name,
			__('Export CSV', 'whatslink-click-tracker'),
			__('Export CSV', 'whatslink-click-tracker').$label,
			'manage_options',
			'whatslink-click-tracker-export-csv',
			[$this, 'whatslink_click_tracker_display_export_csv_page']
		);

		add_submenu_page(
			$this->plugin_name,
			__('License', 'whatslink-click-tracker'),
			__('License', 'whatslink-click-tracker'),
			'manage_options',
			'whatslink-click-tracker-license',
			[$this, 'whatslink_click_tracker_display_license_page']
		);
		

	}

	/**
	 * Display the email settings page.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function whatslink_click_tracker_display_email_settings_page() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/whatslink-click-tracker-email-settings-display.php';
	}

	/**
	 * Display the report page.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function whatslink_click_tracker_display_report_page() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/whatslink-click-tracker-reports-display.php';
	}

	/**
	 * Display the export CSV page.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function whatslink_click_tracker_display_export_csv_page() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/whatslink-click-tracker-export-csv-display.php';
	}

	/**
	 * Get Click Logs.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function whatslink_click_tracker_get_click_logs() {
		check_ajax_referer('whatslink_click_tracker_view_nonce', 'nonce');
		global $wpdb;

		$allowed_orderby = [
			'post_title',
			'post_type',
			'permalink',
			'click_datetime',
			'utm_source',
			'utm_campaign',
			'utm_medium',
			'referrer',
		];

		$orderby = isset($_POST['orderby']) ? sanitize_key(wp_unslash($_POST['orderby'])) : 'click_datetime';
		$order   = isset($_POST['order']) ? strtoupper(sanitize_text_field(wp_unslash($_POST['order']))) : 'DESC';
		$search  = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
		$page    = isset($_POST['page']) ? intval(wp_unslash($_POST['page'])) : 1;
		$page    = max(1, $page);

		if ( ! in_array($orderby, $allowed_orderby, true) ) {
			$orderby = 'click_datetime';
		}
		if ( ! in_array($order, ['ASC', 'DESC'], true) ) {
			$order = 'DESC';
		}

		$limit  = 10;
		$offset = ($page - 1) * $limit;

		$cache_key   = 'whatslink_click_logs_' . md5("{$search}_{$orderby}_{$order}_{$page}");
		$cache_group = 'whatslink_click_tracker';

		$data = wp_cache_get($cache_key, $cache_group);

		if ( false === $data ) {
			// Hardcoded queries
			if ( 'post_title' === $orderby && 'ASC' === $order ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$data = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM wp_whatslink_click_tracker_clicks WHERE (post_title LIKE %s OR permalink LIKE %s) ORDER BY post_title ASC LIMIT %d OFFSET %d",
						"%{$search}%",
						"%{$search}%",
						$limit,
						$offset
					),
					ARRAY_A
				);
			} elseif ( 'post_title' === $orderby && 'DESC' === $order ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$data = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM wp_whatslink_click_tracker_clicks WHERE (post_title LIKE %s OR permalink LIKE %s) ORDER BY post_title DESC LIMIT %d OFFSET %d",
						"%{$search}%",
						"%{$search}%",
						$limit,
						$offset
					),
					ARRAY_A
				);
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$data = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM wp_whatslink_click_tracker_clicks WHERE (post_title LIKE %s OR permalink LIKE %s) ORDER BY click_datetime DESC LIMIT %d OFFSET %d",
						"%{$search}%",
						"%{$search}%",
						$limit,
						$offset
					),
					ARRAY_A
				);
			}

			wp_cache_set($cache_key, $data, $cache_group, 60);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM wp_whatslink_click_tracker_clicks WHERE (post_title LIKE %s OR permalink LIKE %s)",
				"%{$search}%",
				"%{$search}%"
			)
		);

		if ( ! defined( 'WHATSLINK_CLICK_TRACKER_PRO_IS_LICENSE_ACTIVE' ) || ! WHATSLINK_CLICK_TRACKER_PRO_IS_LICENSE_ACTIVE ) {
			foreach ( $data as &$row ) {
				foreach ( [ 'utm_source', 'utm_campaign', 'utm_medium','referrer'] as $field ) {
					$row[ $field ] = '<div class="whatslink-click-tracker-pro-locked-wrapper"><span class="whatslink-click-tracker-pro-locked-content">Pro</span><a href="https://wpsani.store/whatslink-click-tracker-pro" target="_blank" class="whatslink-click-tracker-pro-unlock-link">🔓 Unlock in Pro</a></div>';
				}
			}
		}

		wp_send_json_success(
			[
				'data'  => $data,
				'total' => (int) $total,
			]
		);
	}



	/**
	 * Reset Click Logs.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function whatslink_click_tracker_reset_click_logs() {
		check_ajax_referer('whatslink_click_tracker_reset_nonce', 'nonce');
		if ( ! current_user_can('manage_options') ) {
			wp_send_json_error(['message' => 'Non autorizzato']);
		}
		global $wpdb;
		$table = $wpdb->prefix . 'whatslink_click_tracker_clicks';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query("DELETE FROM " . esc_sql($table));
		wp_cache_delete('whatslink_total_count', 'whatslink_click_tracker');
		wp_cache_flush(); 
		wp_send_json_success(['message' => 'Click Log resetted.']);
	}

	/**
	 * Display the admin page.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function whatslink_click_tracker_display_admin_page() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/whatslink-click-tracker-admin-display.php';
	}

	/**
	 * Enqueue styles for the admin area.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function enqueue_styles() {
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/whatslink-click-tracker-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Enqueue scripts for the admin area.
	 * 
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/whatslink-click-tracker-admin.js', array('jquery'), $this->version, false);
		wp_localize_script(
			$this->plugin_name,
			'whatslink_click_tracker_admin',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'reset_nonce'    => wp_create_nonce('whatslink_click_tracker_reset_nonce'),
				'view_nonce' => wp_create_nonce('whatslink_click_tracker_view_nonce')
			)
		);
	}

	/**
	 * Display the license page.
	 * 
	 * @since    1.0.1
	 * @access   public
	 * @return   void
	 */
	public function whatslink_click_tracker_display_license_page() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/whatslink-click-tracker-license-display.php';	
	}

	/**
	 * Register license settings.
	 * 
	 * @since    1.0.1
	 * @access   public
	 * @return   void
	 */
	public function register_license_settings() {

		register_setting(
			'whatslink_click_tracker_license',
			'whatslink_click_tracker_license_key',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			]
		);

		register_setting(
			'whatslink_click_tracker_license',
			'whatslink_click_tracker_license_status',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_key',
				'default'           => 'missing',
			]
		);

		register_setting(
			'whatslink_click_tracker_license',
			'whatslink_click_tracker_license_checked_at',
			[
				'type'    => 'integer',
				'default' => 0,
			]
		);
	}


	/**
	 * Handle license actions: activate, deactivate, check.
	 * 
	 * @since    1.0.1
	 * @access   public
	 * @return   void
	 */
	public function handle_license_actions() {
		if ( ! is_admin() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$nonce = isset($_POST['whatslink_click_tracker_license_nonce']) ? sanitize_text_field( wp_unslash($_POST['whatslink_click_tracker_license_nonce']) ) : '';
		if ( empty($nonce) ) {
			return; 
		}

		if ( ! wp_verify_nonce( $nonce, 'whatslink_click_tracker_license_action' ) ) {
			return;
		}

		$do_activate   = isset($_POST['whatslink_click_tracker_license_activate']);
		$do_deactivate = isset($_POST['whatslink_click_tracker_license_deactivate']);
		$do_check      = isset($_POST['whatslink_click_tracker_license_check']);

		if ( ! $do_activate && ! $do_deactivate && ! $do_check ) {
			return;
		}

		$license_key = trim( (string) get_option('whatslink_click_tracker_license_key', '') );
		if ( '' === $license_key ) {
			update_option('whatslink_click_tracker_license_status', 'missing');
			$this->license_admin_redirect_with_notice('missing');
			return;
		}

		if ( $do_activate ) {
			$result = $this->edd_license_request('activate_license', $license_key);
			$status = $this->normalize_edd_status($result);
			update_option('whatslink_click_tracker_license_status', $status);
			update_option('whatslink_click_tracker_license_checked_at', time());
			$this->license_admin_redirect_with_notice($status);
			return;
		}

		if ( $do_deactivate ) {
			$result = $this->edd_license_request('deactivate_license', $license_key);
			$status = $this->normalize_edd_status($result, true);
			update_option('whatslink_click_tracker_license_status', $status);
			update_option('whatslink_click_tracker_license_checked_at', time());
			$this->license_admin_redirect_with_notice($status);
			return;
		}

		if ( $do_check ) {
			$result = $this->edd_license_request('check_license', $license_key);
			$status = $this->normalize_edd_status($result);
			update_option('whatslink_click_tracker_license_status', $status);
			update_option('whatslink_click_tracker_license_checked_at', time());
			$this->license_admin_redirect_with_notice($status);
			return;
		}
	}

	/**
	 * Make a request to the EDD license server.
	 * 
	 * @since    1.0.1
	 * @access   private
	 * @param    string    $action       The action to perform: activate_license, deactivate_license, check_license.
	 * @param    string    $license_key  The license key.
	 * @return   array                 The response data.
	 */
	private function edd_license_request($action, $license_key) {
		$store_url = 'https://wpsani.store';
		$item_id   = 1503;

		$response = wp_remote_post(
			$store_url,
			[
				'timeout' => 15,
				'body'    => [
					'edd_action' => $action,
					'license'    => $license_key,
					'item_id'    => $item_id,
					'url'        => home_url(),
				],
			]
		);

		if ( is_wp_error($response) ) {
			return [ 'ok' => false, 'error' => $response->get_error_message() ];
		}

		$code = (int) wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);

		$data = json_decode($body, true);
		if ( 200 !== $code || ! is_array($data) ) {
			return [ 'ok' => false, 'error' => 'invalid_response', 'raw' => $body, 'code' => $code ];
		}

		return $data;
	}

	/**
	 * Normalize the EDD license status.
	 * 
	 * @since    1.0.1
	 * @access   private
	 * @param    array    $data            The response data from EDD.
	 * @param    bool     $is_deactivate   Whether the action was deactivate_license.
	 * @return   string                   The normalized license status.
	 */
	private function normalize_edd_status($data, $is_deactivate = false) {
		// EDD di solito ritorna: ['success'=>true/false, 'license'=>'valid|invalid|expired|...']
		if ( ! is_array($data) ) {
			return 'invalid';
		}

		if ( isset($data['license']) && is_string($data['license']) ) {
			$license = sanitize_key($data['license']);

			// Se fai deactivate e torna "deactivated", mappalo a "inactive" o "missing"
			if ( $is_deactivate && 'deactivated' === $license ) {
				return 'inactive';
			}
			return $license;
		}

		// fallback
		return 'invalid';
	}

	/**
	 * Redirect to the license admin page with a notice.
	 * 
	 * @since    1.0.1
	 * @access   private
	 * @param    string    $status    The license status to show in the notice.
	 * @return   void
	 */
	private function license_admin_redirect_with_notice($status) {
		$url = add_query_arg(
			[
				'page'                    => 'whatslink-click-tracker-license',
				'whatslink_license_notice' => $status,
			],
			admin_url('admin.php')
		);
		wp_safe_redirect($url);
		exit;
	}

}