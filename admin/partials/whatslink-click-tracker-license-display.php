<?php

if (!defined('ABSPATH')) {
    exit;
}

// Get license key and status
$whatslink_click_tracker_license_key = get_option('whatslink_click_tracker_license_key', '');
$whatslink_click_tracker_license_status = get_option('whatslink_click_tracker_license_status', '');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="card">
        <h2><?php esc_html_e('Your License', 'whatslink-click-tracker'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('whatslink_click_tracker_license'); ?>
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="whatslink_click_tracker_license_key">
                                <?php esc_html_e('License key', 'whatslink-click-tracker'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="whatslink_click_tracker_license_key" 
                                   name="whatslink_click_tracker_license_key" 
                                   value="<?php echo esc_attr($license_key); ?>" 
                                   class="regular-text"
                                   placeholder="<?php esc_html_e('Enter your license key', 'whatslink-click-tracker'); ?>">
                            <p class="description">
                                <?php esc_html_e('Your license key was sent via email at the time of purchase. You can also find it in your account downloads section.', 'whatslink-click-tracker'); ?>
                            </p>
                            <div class="whatslink-license-status" style="margin-top: 10px;">
                                <?php if ($license_status === 'valid'): ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                                    <span style="color: #46b450;">
                                        <?php esc_html_e('License Active', 'whatslink-click-tracker'); ?>
                                    </span></p>
                                <?php elseif ($license_status === 'expired'): ?>
                                    <span class="dashicons dashicons-warning" style="color: #f56e28;"></span>
                                    <span style="color: #f56e28;">
                                        <?php esc_html_e('License Expired', 'whatslink-click-tracker'); ?>
                                    </span>
                                <?php elseif ($license_status === 'invalid'): ?>
                                    <span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
                                    <span style="color: #dc3232;">
                                        <?php esc_html_e('License Invalid', 'whatslink-click-tracker'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <?php submit_button(__('Save License', 'whatslink-click-tracker')); ?>
        </form>
        
        <?php if (!empty($license_key)): ?>
            <hr>
            <form method="post" action="">
                <?php wp_nonce_field('whatslink_click_tracker_license_action', 'whatslink_click_tracker_license_nonce'); ?>
                
                <?php if ($license_status !== 'valid'): ?>
                    <input type="submit" 
                           name="whatslink_click_tracker_license_activate" 
                           class="button button-primary" 
                           value="<?php esc_html_e('Activate License', 'whatslink-click-tracker'); ?>">
                <?php else: ?>
                    <input type="submit" 
                           name="whatslink_click_tracker_license_deactivate" 
                           class="button button-secondary" 
                           value="<?php esc_html_e('Deactivate License', 'whatslink-click-tracker'); ?>">
                <?php endif; ?>
                
                <input type="submit" 
                       name="whatslink_click_tracker_license_check" 
                       class="button" 
                       value="<?php esc_html_e('Check License Status', 'whatslink-click-tracker'); ?>">
            </form>
        <?php endif; ?>
    </div>
</div>