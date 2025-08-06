<?php
/**
 * Utility functions for Yenolx Cargo Service
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get coupon count
 */
function yenolx_get_coupons_count($args = array()) {
    global $wpdb;
    
    $defaults = array(
        'status' => '',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $table_name = $wpdb->prefix . 'yenolx_coupons';
    $where = array();
    $prepare_values = array();
    
    if ($args['status'] !== '') {
        $where[] = "status = %d";
        $prepare_values[] = $args['status'];
    }
    
    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = "SELECT COUNT(*) FROM $table_name $where_clause";
    
    if (!empty($prepare_values)) {
        $count = $wpdb->get_var($wpdb->prepare($query, $prepare_values));
    } else {
        $count = $wpdb->get_var($query);
    }
    
    return intval($count);
}



/**
 * Generate random string
 */
function yenolx_generate_random_string($length = 10) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

/**
 * Format date
 */
function yenolx_format_date($date_string) {
    $date = new DateTime($date_string);
    return $date->format('F j, Y g:i A');
}

/**
 * Get status badge HTML
 */
function yenolx_get_status_badge($status) {
    $status_class = sanitize_title($status);
    return '<span class="yenolx-status-badge yenolx-status-' . $status_class . '">' . esc_html($status) . '</span>';
}

/**
 * Get order statistics by date range
 */
function yenolx_get_order_stats_by_date($start_date, $end_date) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_orders';
    
    $stats = array(
        'total_orders' => 0,
        'total_revenue' => 0,
        'orders_by_status' => array(),
        'orders_by_country' => array(),
    );
    
    // Get total orders and revenue
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(*) as total_orders, SUM(final_price_eur) as total_revenue 
        FROM $table_name 
        WHERE DATE(created_at) BETWEEN %s AND %s",
        $start_date,
        $end_date
    ));
    
    if ($result) {
        $stats['total_orders'] = intval($result->total_orders);
        $stats['total_revenue'] = floatval($result->total_revenue);
    }
    
    // Get orders by status
    $statuses = array(
        'Order Confirmed',
        'Ready for Pickup',
        'Picked Up',
        'In Transit to Italy',
        'In Transit to Sri Lanka',
        'At Sri Lanka Office',
        'In Transit to Home',
        'Delivered',
    );
    
    foreach ($statuses as $status) {
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
            WHERE status = %s AND DATE(created_at) BETWEEN %s AND %s",
            $status,
            $start_date,
            $end_date
        ));
        $stats['orders_by_status'][$status] = intval($count);
    }
    
    // Get orders by country
    $countries_table = $wpdb->prefix . 'yenolx_countries';
    $country_results = $wpdb->get_results($wpdb->prepare(
        "SELECT c.name_en, COUNT(o.id) as order_count 
        FROM $countries_table c 
        LEFT JOIN $table_name o ON c.id = o.country_id 
        WHERE DATE(o.created_at) BETWEEN %s AND %s 
        GROUP BY c.id 
        ORDER BY order_count DESC",
        $start_date,
        $end_date
    ));
    
    $stats['orders_by_country'] = $country_results;
    
    return $stats;
}

/**
 * Get coupon statistics
 */
function yenolx_get_coupon_stats() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_coupons';
    
    $stats = array(
        'total_coupons' => 0,
        'active_coupons' => 0,
        'total_used' => 0,
        'most_used_coupons' => array(),
    );
    
    // Get total and active coupons
    $result = $wpdb->get_row(
        "SELECT 
            COUNT(*) as total_coupons,
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_coupons,
            SUM(used_count) as total_used
        FROM $table_name"
    );
    
    if ($result) {
        $stats['total_coupons'] = intval($result->total_coupons);
        $stats['active_coupons'] = intval($result->active_coupons);
        $stats['total_used'] = intval($result->total_used);
    }
    
    // Get most used coupons
    $most_used = $wpdb->get_results(
        "SELECT code, used_count 
        FROM $table_name 
        WHERE used_count > 0 
        ORDER BY used_count DESC 
        LIMIT 5"
    );
    
    $stats['most_used_coupons'] = $most_used;
    
    return $stats;
}

/**
 * Validate email address
 */
function yenolx_validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Sanitize phone number
 */
function yenolx_sanitize_phone($phone) {
    // Remove all non-digit characters
    return preg_replace('/[^0-9]/', '', $phone);
}

/**
 * Format phone number
 */
function yenolx_format_phone($phone) {
    $phone = yenolx_sanitize_phone($phone);
    
    // Basic formatting for international numbers
    if (strlen($phone) === 10) {
        return '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    }
    
    return $phone;
}

/**
 * Get weight in kg from string
 */
function yenolx_get_weight_in_kg($weight_string) {
    // Remove 'kg' and any spaces, then convert to float
    $weight = preg_replace('/[^0-9.]/', '', $weight_string);
    return floatval($weight);
}

/**
 * Is weight valid
 */
function yenolx_is_valid_weight($weight) {
    return is_numeric($weight) && $weight > 0 && $weight <= 1000; // Max 1000kg
}

/**
 * Get delivery time ranges for country
 */
function yenolx_get_delivery_time_ranges($country_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_countries';
    $country = $wpdb->get_row($wpdb->prepare(
        "SELECT delivery_time_range_1, delivery_time_range_2, delivery_time_range_3 
        FROM $table_name 
        WHERE id = %d",
        $country_id
    ));
    
    $ranges = array();
    
    if ($country) {
        if (!empty($country->delivery_time_range_1)) {
            $ranges[] = $country->delivery_time_range_1;
        }
        if (!empty($country->delivery_time_range_2)) {
            $ranges[] = $country->delivery_time_range_2;
        }
        if (!empty($country->delivery_time_range_3)) {
            $ranges[] = $country->delivery_time_range_3;
        }
    }
    
    return $ranges;
}

/**
 * Get order status class
 */
function yenolx_get_order_status_class($status) {
    $status_classes = array(
        'Order Confirmed' => 'confirmed',
        'Ready for Pickup' => 'ready',
        'Picked Up' => 'picked',
        'In Transit to Italy' => 'transit-italy',
        'In Transit to Sri Lanka' => 'transit-srilanka',
        'At Sri Lanka Office' => 'at-office',
        'In Transit to Home' => 'transit-home',
        'Delivered' => 'delivered',
    );
    
    return isset($status_classes[$status]) ? $status_classes[$status] : 'unknown';
}

/**
 * Log debug information
 */
function yenolx_log_debug($message, $data = array()) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $log_message = '[Yenolx Cargo] ' . $message;
        if (!empty($data)) {
            $log_message .= ' - ' . print_r($data, true);
        }
        error_log($log_message);
    }
}

/**
 * Get plugin version
 */
function yenolx_get_plugin_version() {
    return defined('YENOLX_CARGO_VERSION') ? YENOLX_CARGO_VERSION : '1.0.0';
}

/**
 * Check if plugin is compatible with current WordPress version
 */
function yenolx_is_wp_compatible() {
    return version_compare(get_bloginfo('version'), '6.0', '>=');
}

/**
 * Check if plugin is compatible with current PHP version
 */
function yenolx_is_php_compatible() {
    return version_compare(PHP_VERSION, '7.4', '>=');
}

/**
 * Get system requirements status
 */
function yenolx_get_system_requirements() {
    $requirements = array(
        'wp_version' => array(
            'required' => '6.0',
            'current' => get_bloginfo('version'),
            'compatible' => yenolx_is_wp_compatible(),
        ),
        'php_version' => array(
            'required' => '7.4',
            'current' => PHP_VERSION,
            'compatible' => yenolx_is_php_compatible(),
        ),
    );
    
    return $requirements;
}