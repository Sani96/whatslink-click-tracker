<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php 
$whatslink_click_tracker_columns = apply_filters('whatslink_click_tracker_table_columns', [
    'post_title'     => __('Post Title', 'whatslink-click-tracker'),
    'post_type'      => __('Post Type', 'whatslink-click-tracker'),
    'permalink'      => __('Permalink', 'whatslink-click-tracker'),
    'click_datetime' => __('Datetime', 'whatslink-click-tracker'),
]);
if ( ! defined('WHATSLINK_CLICK_TRACKER_PRO_IS_LICENSE_ACTIVE') || ! WHATSLINK_CLICK_TRACKER_PRO_IS_LICENSE_ACTIVE ) {    
    $whatslink_click_tracker_columns['utm_source']  = __('UTM Source (Pro)', 'whatslink-click-tracker');
    $whatslink_click_tracker_columns['utm_medium']  = __('UTM Medium (Pro)', 'whatslink-click-tracker');
    $whatslink_click_tracker_columns['utm_campaign'] = __('UTM Campaign (Pro)', 'whatslink-click-tracker');
    $whatslink_click_tracker_columns['referrer']    = __('Referrer (Pro)', 'whatslink-click-tracker');
}


?>
<div id="whatslink-click-tracker-notice"></div>
<div class="wrap">
    
    <h1>
        <?php esc_html_e( 'WhatsLink Click Tracker', 'whatslink-click-tracker' ); ?>
        <span id="whatslink-click-tracker-total"></span>
    </h1>

    <div id="whatslink-click-tracker-search-container">
        <input 
            type="text" 
            id="whatslink-click-tracker-search" 
            placeholder="<?php esc_attr_e( 'Search by post title or permalink…', 'whatslink-click-tracker' ); ?>" 
        />
        <span class="dashicons dashicons-search"></span>
    </div>

    <table id="whatslink-click-tracker-table" class="widefat fixed striped">
        <thead>
            <tr>
                <?php foreach ( $whatslink_click_tracker_columns as $key => $label ) :?>
                    <th data-orderby="<?php echo esc_attr($key) ?>" class="sortable">
                        <a href="#" data-orderby="<?php echo esc_attr($key) ?>" data-order="desc">
                            <?php echo esc_html($label) ?>
                            <span class="sorting-indicators">
                                <span class="sorting-indicator asc"></span>
                                <span class="sorting-indicator desc"></span>
                            </span>
                        </a>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>


        <tbody>
            <tr>
                <td colspan="4"><span class="dashicons dashicons-hourglass"></span> <?php esc_html_e( 'Loading logs…', 'whatslink-click-tracker' ); ?></td>
            </tr>
        </tbody>
    </table>
    
    <div class="whatslink-click-tracker-pagination-reset-container">
        <div id="whatslink-click-tracker-pagination" style="margin-top:1em;"></div>
    
        <button 
            id="whatslink-click-tracker-reset" 
            class="button button-danger" 
            style="margin-top: 1em;"
        >
            <span class="dashicons dashicons-trash"></span>
            <?php esc_html_e( 'Reset Logs', 'whatslink-click-tracker' ); ?>
        </button>
    </div>

    <?php if ( ! defined( 'WHATSLINK_CLICK_TRACKER_PRO_IS_LICENSE_ACTIVE' ) || ! WHATSLINK_CLICK_TRACKER_PRO_IS_LICENSE_ACTIVE ) : ?>
        <?php include_once plugin_dir_path( dirname( __FILE__ ) ) . 'partials/whatslink-click-tracker-footer-bar.php'; ?>
    <?php endif; ?>
</div>
