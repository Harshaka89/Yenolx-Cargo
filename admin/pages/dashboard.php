<?php
/**
 * Admin page for Yenolx Cargo Service
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['yenolx_save_settings'])) {
        // Save settings
        $settings = array(
            'milan_to_sl_rate' => floatval($_POST['milan_to_sl_rate']),
            'sl_delivery_rate' => floatval($_POST['sl_delivery_rate']),
            'currency_symbol' => sanitize_text_field($_POST['currency_symbol']),
            'email_from_name' => sanitize_text_field($_POST['email_from_name']),
            'email_from_address' => sanitize_email($_POST['email_from_address']),
            'tracking_id_prefix' => sanitize_text_field($_POST['tracking_id_prefix']),
            'tracking_id_length' => intval($_POST['tracking_id_length']),
        );
        
        foreach ($settings as $key => $value) {
            update_option('yenolx_' . $key, $value);
        }
        
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'yenolx-cargo') . '</p></div>';
    }
}

// Get current settings
$milan_to_sl_rate = get_option('yenolx_milan_to_sl_rate', 3.50);
$sl_delivery_rate = get_option('yenolx_sl_delivery_rate', 1.00);
$currency_symbol = get_option('yenolx_currency_symbol', '€');
$email_from_name = get_option('yenolx_email_from_name', get_bloginfo('name'));
$email_from_address = get_option('yenolx_email_from_address', get_option('admin_email'));
$tracking_id_prefix = get_option('yenolx_tracking_id_prefix', 'YCS');
$tracking_id_length = get_option('yenolx_tracking_id_length', 10);

// Get dashboard statistics
$stats = yenolx_get_dashboard_stats();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="yenolx-dashboard">
        <!-- Statistics Cards -->
        <div class="yenolx-stats-grid">
            <div class="yenolx-stat-card">
                <h3><?php _e('Total Orders', 'yenolx-cargo'); ?></h3>
                <div class="yenolx-stat-value"><?php echo number_format($stats['total_orders']); ?></div>
            </div>
            
            <div class="yenolx-stat-card">
                <h3><?php _e('Today\'s Orders', 'yenolx-cargo'); ?></h3>
                <div class="yenolx-stat-value"><?php echo number_format($stats['today_orders']); ?></div>
            </div>
            
            <div class="yenolx-stat-card">
                <h3><?php _e('Total Revenue', 'yenolx-cargo'); ?></h3>
                <div class="yenolx-stat-value"><?php echo $currency_symbol . number_format($stats['total_revenue'], 2); ?></div>
            </div>
            
            <div class="yenolx-stat-card">
                <h3><?php _e('Today\'s Revenue', 'yenolx-cargo'); ?></h3>
                <div class="yenolx-stat-value"><?php echo $currency_symbol . number_format($stats['today_revenue'], 2); ?></div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="yenolx-charts-section">
            <div class="yenolx-chart-container">
                <h3><?php _e('Orders by Status', 'yenolx-cargo'); ?></h3>
                <div class="yenolx-chart">
                    <?php if (!empty($stats['orders_by_status'])): ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Status', 'yenolx-cargo'); ?></th>
                                    <th><?php _e('Count', 'yenolx-cargo'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['orders_by_status'] as $status => $count): ?>
                                    <tr>
                                        <td><?php echo esc_html($status); ?></td>
                                        <td><?php echo number_format($count); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p><?php _e('No orders found.', 'yenolx-cargo'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="yenolx-chart-container">
                <h3><?php _e('Orders by Country', 'yenolx-cargo'); ?></h3>
                <div class="yenolx-chart">
                    <?php if (!empty($stats['orders_by_country'])): ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Country', 'yenolx-cargo'); ?></th>
                                    <th><?php _e('Orders', 'yenolx-cargo'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['orders_by_country'] as $country): ?>
                                    <tr>
                                        <td><?php echo esc_html($country->name_en); ?></td>
                                        <td><?php echo number_format($country->order_count); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p><?php _e('No orders found.', 'yenolx-cargo'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Settings Form -->
        <div class="yenolx-settings-section">
            <h2><?php _e('General Settings', 'yenolx-cargo'); ?></h2>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Milan to Sri Lanka Rate (per kg)', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="number" name="milan_to_sl_rate" step="0.01" min="0" value="<?php echo esc_attr($milan_to_sl_rate); ?>" class="regular-text">
                            <p class="description"><?php _e('Rate per kg for shipments from Milan to Sri Lanka.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Sri Lanka Local Delivery Rate (per kg)', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="number" name="sl_delivery_rate" step="0.01" min="0" value="<?php echo esc_attr($sl_delivery_rate); ?>" class="regular-text">
                            <p class="description"><?php _e('Rate per kg for local delivery within Sri Lanka.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Currency Symbol', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="currency_symbol" value="<?php echo esc_attr($currency_symbol); ?>" class="regular-text">
                            <p class="description"><?php _e('Currency symbol to display throughout the site.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Email From Name', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="email_from_name" value="<?php echo esc_attr($email_from_name); ?>" class="regular-text">
                            <p class="description"><?php _e('Name to use for outgoing emails.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Email From Address', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="email" name="email_from_address" value="<?php echo esc_attr($email_from_address); ?>" class="regular-text">
                            <p class="description"><?php _e('Email address to use for outgoing emails.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Tracking ID Prefix', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="tracking_id_prefix" value="<?php echo esc_attr($tracking_id_prefix); ?>" class="regular-text">
                            <p class="description"><?php _e('Prefix for generated tracking IDs.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Tracking ID Length', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="number" name="tracking_id_length" min="5" max="20" value="<?php echo esc_attr($tracking_id_length); ?>" class="regular-text">
                            <p class="description"><?php _e('Length of the random part of tracking IDs.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="yenolx_save_settings" class="button button-primary" value="<?php _e('Save Settings', 'yenolx-cargo'); ?>">
                </p>
            </form>
        </div>
    </div>
</div>