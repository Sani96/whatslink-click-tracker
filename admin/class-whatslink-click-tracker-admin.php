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
		if ( ! defined('WHATSLINK_CLICK_TRACKER_PRO_VERSION') ) {
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

		if ( ! defined( 'WHATSLINK_CLICK_TRACKER_PRO_VERSION' ) ) {
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
}