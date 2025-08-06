<?php
/**
 * Settings admin page for Yenolx Cargo Service
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'admin/admin-functions.php';

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
            'thank_you_page_id' => intval($_POST['thank_you_page_id']),
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
$thank_you_page_id = get_option('yenolx_thank_you_page_id', 0);

// Get pages for dropdown
$pages = get_pages();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="yenolx-settings-container">
        <form method="post" action="">
            <div class="yenolx-settings-section">
                <h2><?php _e('Pricing Settings', 'yenolx-cargo'); ?></h2>
                
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
                </table>
            </div>
            
            <div class="yenolx-settings-section">
                <h2><?php _e('Email Settings', 'yenolx-cargo'); ?></h2>
                
                <table class="form-table">
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
                </table>
            </div>
            
            <div class="yenolx-settings-section">
                <h2><?php _e('Tracking Settings', 'yenolx-cargo'); ?></h2>
                
                <table class="form-table">
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
            </div>
            
            <div class="yenolx-settings-section">
                <h2><?php _e('Page Settings', 'yenolx-cargo'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Thank You Page', 'yenolx-cargo'); ?></th>
                        <td>
                            <select name="thank_you_page_id" class="regular-text">
                                <option value="0"><?php _e('Select a page', 'yenolx-cargo'); ?></option>
                                <?php foreach ($pages as $page): ?>
                                    <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($thank_you_page_id, $page->ID); ?>>
                                        <?php echo esc_html($page->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Page to redirect users after placing an order. This page should contain the tracking form shortcode.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="yenolx-settings-section">
                <h2><?php _e('Shortcodes', 'yenolx-cargo'); ?></h2>
                
                <p><?php _e('Use these shortcodes to display forms on your pages:', 'yenolx-cargo'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Order Form', 'yenolx-cargo'); ?></th>
                        <td>
                            <code>[yenolx_order_form]</code>
                            <p class="description"><?php _e('Displays the complete order form with price calculator.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Tracking Form', 'yenolx-cargo'); ?></th>
                        <td>
                            <code>[yenolx_tracking_form]</code>
                            <p class="description"><?php _e('Displays the tracking form for customers to check their order status.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Price Calculator', 'yenolx-cargo'); ?></th>
                        <td>
                            <code>[yenolx_price_calculator]</code>
                            <p class="description"><?php _e('Displays a standalone price calculator.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <p class="submit">
                <input type="submit" name="yenolx_save_settings" class="button button-primary" value="<?php _e('Save Settings', 'yenolx-cargo'); ?>">
            </p>
        </form>
    </div>
</div>