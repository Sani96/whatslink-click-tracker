<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <h1><?php esc_html_e( 'Export Click Data (CSV)', 'whatslink-click-tracker' ); ?></h1>

    <?php if ( ! defined( 'WHATSLINK_CLICK_TRACKER_PRO_IS_LICENSE_ACTIVE' ) || ! WHATSLINK_CLICK_TRACKER_PRO_IS_LICENSE_ACTIVE ) : ?>
        <div class="overlay-pro-feature">
            <div class="locked-overlay">
                <p>🔒 <?php esc_html_e( 'This feature is available in the Pro version.', 'whatslink-click-tracker' ); ?></p>
                <a href="<?php echo esc_url( 'https://wpsani.store/whatslink-click-tracker-pro' ); ?>" class="button button-primary primary-btn" target="_blank">
                    <?php esc_html_e( 'Upgrade to PRO', 'whatslink-click-tracker' ); ?>
                </a>
            </div>
            <div class="blurred-chart-wrapper">
                <img 
                    src="<?php echo esc_url( plugin_dir_url( dirname( __DIR__ ) ) . 'assets/images/csv-demo.png' ); ?>" 
                    alt="<?php esc_attr_e( 'Export Preview', 'whatslink-click-tracker' ); ?>" 
                    class="locked-chart csv-demo" 
                />
            </div>
        </div>
        <?php include_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/whatslink-click-tracker-footer-bar.php'; ?>
    <?php else : ?>
        <?php do_action( 'whatslink_click_tracker_pro_export_csv_ui' ); ?>
    <?php endif; ?>

</div>
