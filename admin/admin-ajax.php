<?php
/**
 * Admin AJAX handlers
 * 
 * This file handles AJAX requests for the Yenolx Cargo Service plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'includes/utility-functions.php';
require_once YENOLX_CARGO_PATH . 'includes/email-functions.php';

/**
 * Admin AJAX class
 */
class Yenolx_Cargo_Admin_AJAX {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX handlers
        add_action('wp_ajax_yenolx_save_country', array($this, 'save_country'));
        add_action('wp_ajax_yenolx_delete_country', array($this, 'delete_country'));
        add_action('wp_ajax_yenolx_save_pricing', array($this, 'save_pricing'));
        add_action('wp_ajax_yenolx_delete_pricing', array($this, 'delete_pricing'));
        add_action('wp_ajax_yenolx_save_coupon', array($this, 'save_coupon'));
        add_action('wp_ajax_yenolx_delete_coupon', array($this, 'delete_coupon'));
        add_action('wp_ajax_yenolx_update_order_status', array($this, 'update_order_status'));
        add_action('wp_ajax_yenolx_save_settings', array($this, 'save_settings'));
    }
    
    /**
     * Save country
     */
    public function save_country() {
        // Check nonce
        check_ajax_referer('yenolx_admin_nonce', 'nonce');
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'yenolx-cargo'));
        }
        
        // Get and sanitize data
        $country_id = isset($_POST['country_id']) ? intval($_POST['country_id']) : 0;
        $name_en = sanitize_text_field($_POST['name_en']);
        $name_si = sanitize_text_field($_POST['name_si']);
        $name_ta = sanitize_text_field($_POST['name_ta']);
        $delivery_time_range_1 = sanitize_text_field($_POST['delivery_time_range_1']);
        $delivery_time_range_2 = sanitize_text_field($_POST['delivery_time_range_2']);
        $delivery_time_range_3 = sanitize_text_field($_POST['delivery_time_range_3']);
        $status = isset($_POST['status']) ? 1 : 0;
        
        global $wpdb;
        
        if ($country_id > 0) {
            // Update existing country
            $result = $wpdb->update(
                $wpdb->prefix . 'yenolx_countries',
                array(
                    'name_en' => $name_en,
                    'name_si' => $name_si,
                    'name_ta' => $name_ta,
                    'delivery_time_range_1' => $delivery_time_range_1,
                    'delivery_time_range_2' => $delivery_time_range_2,
                    'delivery_time_range_3' => $delivery_time_range_3,
                    'status' => $status,
                ),
                array('id' => $country_id),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%d'),
                array('%d')
            );
        } else {
            // Insert new country
            $result = $wpdb->insert(
                $wpdb->prefix . 'yenolx_countries',
                array(
                    'name_en' => $name_en,
                    'name_si' => $name_si,
                    'name_ta' => $name_ta,
                    'delivery_time_range_1' => $delivery_time_range_1,
                    'delivery_time_range_2' => $delivery_time_range_2,
                    'delivery_time_range_3' => $delivery_time_range_3,
                    'status' => $status,
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%d')
            );
        }
        
        if ($result !== false) {
            wp_send_json_success(__('Country saved successfully.', 'yenolx-cargo'));
        } else {
            wp_send_json_error(__('Failed to save country.', 'yenolx-cargo'));
        }
    }
    
    /**
     * Delete country
     */
    public function delete_country() {
        // Check nonce
        check_ajax_referer('yenolx_admin_nonce', 'nonce');
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'yenolx-cargo'));
        }
        
        // Get and sanitize data
        $country_id = intval($_POST['country_id']);
        
        global $wpdb;
        
        // Delete country
        $result = $wpdb->delete(
            $wpdb->prefix . 'yenolx_countries',
            array('id' => $country_id),
            array('%d')
        );
        
        // Also delete related pricing
        $wpdb->delete(
            $wpdb->prefix . 'yenolx_pricing',
            array('country_id' => $country_id),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(__('Country deleted successfully.', 'yenolx-cargo'));
        } else {
            wp_send_json_error(__('Failed to delete country.', 'yenolx-cargo'));
        }
    }
    
    /**
     * Save pricing
     */
    public function save_pricing() {
        // Check nonce
        check_ajax_referer('yenolx_admin_nonce', 'nonce');
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'yenolx-cargo'));
        }
        
        // Get and sanitize data
        $pricing_id = isset($_POST['pricing_id']) ? intval($_POST['pricing_id']) : 0;
        $country_id = intval($_POST['country_id']);
        $weight_kg = floatval($_POST['weight_kg']);
        $price_eur = floatval($_POST['price_eur']);
        
        global $wpdb;
        
        if ($pricing_id > 0) {
            // Update existing pricing
            $result = $wpdb->update(
                $wpdb->prefix . 'yenolx_pricing',
                array(
                    'country_id' => $country_id,
                    'weight_kg' => $weight_kg,
                    'price_eur' => $price_eur,
                ),
                array('id' => $pricing_id),
                array('%d', '%f', '%f'),
                array('%d')
            );
        } else {
            // Insert new pricing
            $result = $wpdb->insert(
                $wpdb->prefix . 'yenolx_pricing',
                array(
                    'country_id' => $country_id,
                    'weight_kg' => $weight_kg,
                    'price_eur' => $price_eur,
                ),
                array('%d', '%f', '%f')
            );
        }
        
        if ($result !== false) {
            wp_send_json_success(__('Pricing saved successfully.', 'yenolx-cargo'));
        } else {
            wp_send_json_error(__('Failed to save pricing.', 'yenolx-cargo'));
        }
    }
    
    /**
     * Delete pricing
     */
    public function delete_pricing() {
        // Check nonce
        check_ajax_referer('yenolx_admin_nonce', 'nonce');
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'yenolx-cargo'));
        }
        
        // Get and sanitize data
        $pricing_id = intval($_POST['pricing_id']);
        
        global $wpdb;
        
        // Delete pricing
        $result = $wpdb->delete(
            $wpdb->prefix . 'yenolx_pricing',
            array('id' => $pricing_id),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(__('Pricing deleted successfully.', 'yenolx-cargo'));
        } else {
            wp_send_json_error(__('Failed to delete pricing.', 'yenolx-cargo'));
        }
    }
    
    /**
     * Save coupon
     */
    public function save_coupon() {
        // Check nonce
        check_ajax_referer('yenolx_admin_nonce', 'nonce');
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'yenolx-cargo'));
        }
        
        // Get and sanitize data
        $coupon_id = isset($_POST['coupon_id']) ? intval($_POST['coupon_id']) : 0;
        $code = sanitize_text_field($_POST['code']);
        $discount_type = sanitize_text_field($_POST['discount_type']);
        $discount_value = floatval($_POST['discount_value']);
        $min_order_value = floatval($_POST['min_order_value']);
        $max_uses = isset($_POST['max_uses']) && !empty($_POST['max_uses']) ? intval($_POST['max_uses']) : null;
        $start_date = !empty($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : null;
        $end_date = !empty($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : null;
        $status = isset($_POST['status']) ? 1 : 0;
        
        global $wpdb;
        
        if ($coupon_id > 0) {
            // Update existing coupon
            $result = $wpdb->update(
                $wpdb->prefix . 'yenolx_coupons',
                array(
                    'code' => $code,
                    'discount_type' => $discount_type,
                    'discount_value' => $discount_value,
                    'min_order_value' => $min_order_value,
                    'max_uses' => $max_uses,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'status' => $status,
                ),
                array('id' => $coupon_id),
                array('%s', '%s', '%f', '%f', '%d', '%s', '%s', '%d'),
                array('%d')
            );
        } else {
            // Insert new coupon
            $result = $wpdb->insert(
                $wpdb->prefix . 'yenolx_coupons',
                array(
                    'code' => $code,
                    'discount_type' => $discount_type,
                    'discount_value' => $discount_value,
                    'min_order_value' => $min_order_value,
                    'max_uses' => $max_uses,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'status' => $status,
                ),
                array('%s', '%s', '%f', '%f', '%d', '%s', '%s', '%d')
            );
        }
        
        if ($result !== false) {
            wp_send_json_success(__('Coupon saved successfully.', 'yenolx-cargo'));
        } else {
            wp_send_json_error(__('Failed to save coupon.', 'yenolx-cargo'));
        }
    }
    
    /**
     * Delete coupon
     */
    public function delete_coupon() {
        // Check nonce
        check_ajax_referer('yenolx_admin_nonce', 'nonce');
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'yenolx-cargo'));
        }
        
        // Get and sanitize data
        $coupon_id = intval($_POST['coupon_id']);
        
        global $wpdb;
        
        // Delete coupon
        $result = $wpdb->delete(
            $wpdb->prefix . 'yenolx_coupons',
            array('id' => $coupon_id),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(__('Coupon deleted successfully.', 'yenolx-cargo'));
        } else {
            wp_send_json_error(__('Failed to delete coupon.', 'yenolx-cargo'));
        }
    }
    
    /**
     * Update order status
     */
    public function update_order_status() {
        // Check nonce
        check_ajax_referer('yenolx_admin_nonce', 'nonce');
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'yenolx-cargo'));
        }
        
        // Get and sanitize data
        $order_id = intval($_POST['order_id']);
        $status = sanitize_text_field($_POST['status']);
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        
        global $wpdb;
        
        // Update order status
        $result = $wpdb->update(
            $wpdb->prefix . 'yenolx_orders',
            array(
                'status' => $status,
                'special_notes' => $notes,
            ),
            array('id' => $order_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            // Add tracking history
            $wpdb->insert(
                $wpdb->prefix . 'yenolx_order_tracking',
                array(
                    'order_id' => $order_id,
                    'status' => $status,
                    'notes' => $notes,
                ),
                array('%d', '%s', '%s')
            );
            
            // Send email notification
            $this->send_status_update_email($order_id, $status);
            
            wp_send_json_success(__('Order status updated successfully.', 'yenolx-cargo'));
        } else {
            wp_send_json_error(__('Failed to update order status.', 'yenolx-cargo'));
        }
    }
    
    /**
     * Save settings
     */
    public function save_settings() {
        // Check nonce
        check_ajax_referer('yenolx_admin_nonce', 'nonce');
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'yenolx-cargo'));
        }
        
        // Get and sanitize settings
        $settings = array(
            'milan_to_sl_rate' => floatval($_POST['milan_to_sl_rate']),
            'sl_delivery_rate' => floatval($_POST['sl_delivery_rate']),
            'currency_symbol' => sanitize_text_field($_POST['currency_symbol']),
            'email_from_name' => sanitize_text_field($_POST['email_from_name']),
            'email_from_address' => sanitize_email($_POST['email_from_address']),
            'tracking_id_prefix' => sanitize_text_field($_POST['tracking_id_prefix']),
            'tracking_id_length' => intval($_POST['tracking_id_length']),
        );
        
        // Update options
        foreach ($settings as $key => $value) {
            update_option('yenolx_' . $key, $value);
        }
        
        wp_send_json_success(__('Settings saved successfully.', 'yenolx-cargo'));
    }
    
    /**
     * Send status update email
     */
    private function send_status_update_email($order_id, $status) {
        // Get order details
        global $wpdb;
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yenolx_orders WHERE id = %d",
            $order_id
        ));
        
        if (!$order) {
            return;
        }
        
        // Get email template
        $email_subject = __('Order Status Update', 'yenolx-cargo');
        $email_body = $this->get_status_email_template($order, $status);
        
        // Send email
        wp_mail($order->sender_email, $email_subject, $email_body);
    }
    
    /**
     * Get status email template
     */
    private function get_status_email_template($order, $status) {
        ob_start();
        include YENOLX_CARGO_PATH . 'templates/emails/status-update.php';
        return ob_get_clean();
    }
}

// Initialize admin AJAX
new Yenolx_Cargo_Admin_AJAX();