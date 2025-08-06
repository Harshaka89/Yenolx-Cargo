<?php
/**
 * Admin functions for Yenolx Cargo Service
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'includes/utility-functions.php';
require_once YENOLX_CARGO_PATH . 'includes/email-functions.php';

/**
 * Get countries list
 */
function yenolx_get_countries() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_countries';
    $countries = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 1 ORDER BY name_en ASC");
    
    return $countries;
}

/**
 * Get country by ID
 */
function yenolx_get_country($country_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_countries';
    $country = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $country_id));
    
    return $country;
}

/**
 * Add country
 */
function yenolx_add_country($data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_countries';
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'name_en' => sanitize_text_field($data['name_en']),
            'name_si' => sanitize_text_field($data['name_si']),
            'name_ta' => sanitize_text_field($data['name_ta']),
            'delivery_time_range_1' => sanitize_text_field($data['delivery_time_range_1']),
            'delivery_time_range_2' => sanitize_text_field($data['delivery_time_range_2']),
            'delivery_time_range_3' => sanitize_text_field($data['delivery_time_range_3']),
            'status' => isset($data['status']) ? 1 : 0,
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%d')
    );
    
    return $result ? $wpdb->insert_id : false;
}

/**
 * Update country
 */
function yenolx_update_country($country_id, $data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_countries';
    
    $result = $wpdb->update(
        $table_name,
        array(
            'name_en' => sanitize_text_field($data['name_en']),
            'name_si' => sanitize_text_field($data['name_si']),
            'name_ta' => sanitize_text_field($data['name_ta']),
            'delivery_time_range_1' => sanitize_text_field($data['delivery_time_range_1']),
            'delivery_time_range_2' => sanitize_text_field($data['delivery_time_range_2']),
            'delivery_time_range_3' => sanitize_text_field($data['delivery_time_range_3']),
            'status' => isset($data['status']) ? 1 : 0,
        ),
        array('id' => $country_id),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%d'),
        array('%d')
    );
    
    return $result !== false;
}

/**
 * Delete country
 */
function yenolx_delete_country($country_id) {
    global $wpdb;
    
    // Check if country has orders
    $orders_table = $wpdb->prefix . 'yenolx_orders';
    $order_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $orders_table WHERE country_id = %d", $country_id));
    
    if ($order_count > 0) {
        return false; // Cannot delete country with existing orders
    }
    
    // Delete pricing for this country
    $pricing_table = $wpdb->prefix . 'yenolx_pricing';
    $wpdb->delete($pricing_table, array('country_id' => $country_id), array('%d'));
    
    // Delete country
    $countries_table = $wpdb->prefix . 'yenolx_countries';
    $result = $wpdb->delete($countries_table, array('id' => $country_id), array('%d'));
    
    return $result !== false;
}

/**
 * Get pricing for country
 */
function yenolx_get_pricing($country_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_pricing';
    $pricing = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE country_id = %d ORDER BY weight_kg ASC",
        $country_id
    ));
    
    return $pricing;
}

/**
 * Add pricing
 */
function yenolx_add_pricing($country_id, $weight_kg, $price_eur) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_pricing';
    
    // Check if pricing already exists for this weight
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE country_id = %d AND weight_kg = %f",
        $country_id,
        $weight_kg
    ));
    
    if ($existing > 0) {
        return false; // Pricing already exists
    }
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'country_id' => $country_id,
            'weight_kg' => $weight_kg,
            'price_eur' => $price_eur,
        ),
        array('%d', '%f', '%f')
    );
    
    return $result ? $wpdb->insert_id : false;
}

/**
 * Update pricing
 */
function yenolx_update_pricing($pricing_id, $price_eur) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_pricing';
    
    $result = $wpdb->update(
        $table_name,
        array('price_eur' => $price_eur),
        array('id' => $pricing_id),
        array('%f'),
        array('%d')
    );
    
    return $result !== false;
}

/**
 * Delete pricing
 */
function yenolx_delete_pricing($pricing_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_pricing';
    $result = $wpdb->delete($table_name, array('id' => $pricing_id), array('%d'));
    
    return $result !== false;
}

/**
 * Get orders
 */
function yenolx_get_orders($args = array()) {
    global $wpdb;
    
    $defaults = array(
        'status' => '',
        'country_id' => 0,
        'date_from' => '',
        'date_to' => '',
        'search' => '',
        'orderby' => 'created_at',
        'order' => 'DESC',
        'limit' => 20,
        'offset' => 0,
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $table_name = $wpdb->prefix . 'yenolx_orders';
    $where = array();
    $prepare_values = array();
    
    if (!empty($args['status'])) {
        $where[] = "status = %s";
        $prepare_values[] = $args['status'];
    }
    
    if (!empty($args['country_id'])) {
        $where[] = "country_id = %d";
        $prepare_values[] = $args['country_id'];
    }
    
    if (!empty($args['date_from'])) {
        $where[] = "DATE(created_at) >= %s";
        $prepare_values[] = $args['date_from'];
    }
    
    if (!empty($args['date_to'])) {
        $where[] = "DATE(created_at) <= %s";
        $prepare_values[] = $args['date_to'];
    }
    
    if (!empty($args['search'])) {
        $where[] = "(tracking_id LIKE %s OR sender_name LIKE %s OR sender_email LIKE %s OR receiver_name LIKE %s)";
        $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
        $prepare_values[] = $search_term;
        $prepare_values[] = $search_term;
        $prepare_values[] = $search_term;
        $prepare_values[] = $search_term;
    }
    
    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = "SELECT * FROM $table_name $where_clause ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d";
    $prepare_values[] = $args['limit'];
    $prepare_values[] = $args['offset'];
    
    $orders = $wpdb->get_results($wpdb->prepare($query, $prepare_values));
    
    return $orders;
}

/**
 * Get order count
 */
function yenolx_get_orders_count($args = array()) {
    global $wpdb;
    
    $defaults = array(
        'status' => '',
        'country_id' => 0,
        'date_from' => '',
        'date_to' => '',
        'search' => '',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $table_name = $wpdb->prefix . 'yenolx_orders';
    $where = array();
    $prepare_values = array();
    
    if (!empty($args['status'])) {
        $where[] = "status = %s";
        $prepare_values[] = $args['status'];
    }
    
    if (!empty($args['country_id'])) {
        $where[] = "country_id = %d";
        $prepare_values[] = $args['country_id'];
    }
    
    if (!empty($args['date_from'])) {
        $where[] = "DATE(created_at) >= %s";
        $prepare_values[] = $args['date_from'];
    }
    
    if (!empty($args['date_to'])) {
        $where[] = "DATE(created_at) <= %s";
        $prepare_values[] = $args['date_to'];
    }
    
    if (!empty($args['search'])) {
        $where[] = "(tracking_id LIKE %s OR sender_name LIKE %s OR sender_email LIKE %s OR receiver_name LIKE %s)";
        $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
        $prepare_values[] = $search_term;
        $prepare_values[] = $search_term;
        $prepare_values[] = $search_term;
        $prepare_values[] = $search_term;
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
 * Get order by ID
 */
function yenolx_get_order($order_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_orders';
    $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $order_id));
    
    return $order;
}

/**
 * Get order by tracking ID
 */
function yenolx_get_order_by_tracking_id($tracking_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_orders';
    $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE tracking_id = %s", $tracking_id));
    
    return $order;
}

/**
 * Update order status
 */
function yenolx_update_order_status($order_id, $status, $notes = '') {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_orders';
    
    $result = $wpdb->update(
        $table_name,
        array('status' => $status),
        array('id' => $order_id),
        array('%s'),
        array('%d')
    );
    
    if ($result !== false) {
        // Add tracking history entry
        $tracking_table = $wpdb->prefix . 'yenolx_order_tracking';
        $wpdb->insert(
            $tracking_table,
            array(
                'order_id' => $order_id,
                'status' => $status,
                'notes' => $notes,
            ),
            array('%d', '%s', '%s')
        );
        
        // Send status update email
        yenolx_send_status_update_email($order_id);
        
        return true;
    }
    
    return false;
}

/**
 * Update order special notes
 */
function yenolx_update_order_notes($order_id, $notes) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_orders';
    
    $result = $wpdb->update(
        $table_name,
        array('special_notes' => $notes),
        array('id' => $order_id),
        array('%s'),
        array('%d')
    );
    
    return $result !== false;
}

/**
 * Get order tracking history
 */
function yenolx_get_order_tracking_history($order_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_order_tracking';
    $history = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE order_id = %d ORDER BY created_at ASC",
        $order_id
    ));
    
    return $history;
}

/**
 * Get coupons
 */
function yenolx_get_coupons($args = array()) {
    global $wpdb;
    
    $defaults = array(
        'status' => '',
        'orderby' => 'created_at',
        'order' => 'DESC',
        'limit' => 20,
        'offset' => 0,
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
    
    $query = "SELECT * FROM $table_name $where_clause ORDER BY {$args['orderby']} {$args['order']} LIMIT %d OFFSET %d";
    $prepare_values[] = $args['limit'];
    $prepare_values[] = $args['offset'];
    
    $coupons = $wpdb->get_results($wpdb->prepare($query, $prepare_values));
    
    return $coupons;
}

/**
 * Add coupon
 */
function yenolx_add_coupon($data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_coupons';
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'code' => strtoupper(sanitize_text_field($data['code'])),
            'discount_type' => sanitize_text_field($data['discount_type']),
            'discount_value' => floatval($data['discount_value']),
            'min_order_value' => floatval($data['min_order_value']),
            'max_uses' => !empty($data['max_uses']) ? intval($data['max_uses']) : null,
            'start_date' => !empty($data['start_date']) ? $data['start_date'] : null,
            'end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
            'status' => isset($data['status']) ? 1 : 0,
        ),
        array('%s', '%s', '%f', '%f', '%d', '%s', '%s', '%d')
    );
    
    return $result ? $wpdb->insert_id : false;
}

/**
 * Update coupon
 */
function yenolx_update_coupon($coupon_id, $data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_coupons';
    
    $update_data = array(
        'code' => strtoupper(sanitize_text_field($data['code'])),
        'discount_type' => sanitize_text_field($data['discount_type']),
        'discount_value' => floatval($data['discount_value']),
        'min_order_value' => floatval($data['min_order_value']),
        'status' => isset($data['status']) ? 1 : 0,
    );
    
    if (!empty($data['max_uses'])) {
        $update_data['max_uses'] = intval($data['max_uses']);
    } else {
        $update_data['max_uses'] = null;
    }
    
    if (!empty($data['start_date'])) {
        $update_data['start_date'] = $data['start_date'];
    } else {
        $update_data['start_date'] = null;
    }
    
    if (!empty($data['end_date'])) {
        $update_data['end_date'] = $data['end_date'];
    } else {
        $update_data['end_date'] = null;
    }
    
    $result = $wpdb->update(
        $table_name,
        $update_data,
        array('id' => $coupon_id),
        array('%s', '%s', '%f', '%f', '%d', '%d', '%s', '%s'),
        array('%d')
    );
    
    return $result !== false;
}

/**
 * Delete coupon
 */
function yenolx_delete_coupon($coupon_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_coupons';
    $result = $wpdb->delete($table_name, array('id' => $coupon_id), array('%d'));
    
    return $result !== false;
}

/**
 * Get dashboard statistics
 */
function yenolx_get_dashboard_stats() {
    global $wpdb;
    
    $stats = array();
    
    // Total orders
    $orders_table = $wpdb->prefix . 'yenolx_orders';
    $stats['total_orders'] = $wpdb->get_var("SELECT COUNT(*) FROM $orders_table");
    
    // Today's orders
    $today = current_time('Y-m-d');
    $stats['today_orders'] = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $orders_table WHERE DATE(created_at) = %s",
        $today
    ));
    
    // Orders by status
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
    
    $stats['orders_by_status'] = array();
    foreach ($statuses as $status) {
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $orders_table WHERE status = %s",
            $status
        ));
        $stats['orders_by_status'][$status] = $count;
    }
    
    // Orders by country
    $countries_table = $wpdb->prefix . 'yenolx_countries';
    $stats['orders_by_country'] = $wpdb->get_results(
        "SELECT c.name_en, COUNT(o.id) as order_count 
        FROM $countries_table c 
        LEFT JOIN $orders_table o ON c.id = o.country_id 
        GROUP BY c.id 
        ORDER BY order_count DESC"
    );
    
    // Total revenue
    $stats['total_revenue'] = $wpdb->get_var("SELECT SUM(final_price_eur) FROM $orders_table");
    
    // Today's revenue
    $stats['today_revenue'] = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(final_price_eur) FROM $orders_table WHERE DATE(created_at) = %s",
        $today
    ));
    
    // Coupon usage
    $coupons_table = $wpdb->prefix . 'yenolx_coupons';
    $stats['total_coupons'] = $wpdb->get_var("SELECT COUNT(*) FROM $coupons_table");
    $stats['active_coupons'] = $wpdb->get_var("SELECT COUNT(*) FROM $coupons_table WHERE status = 1");
    
    return $stats;
}

/**
 * Export orders to CSV
 */
function yenolx_export_orders_csv($args = array()) {
    global $wpdb;
    
    $defaults = array(
        'status' => '',
        'country_id' => 0,
        'date_from' => '',
        'date_to' => '',
        'search' => '',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $table_name = $wpdb->prefix . 'yenolx_orders';
    $where = array();
    $prepare_values = array();
    
    if (!empty($args['status'])) {
        $where[] = "status = %s";
        $prepare_values[] = $args['status'];
    }
    
    if (!empty($args['country_id'])) {
        $where[] = "country_id = %d";
        $prepare_values[] = $args['country_id'];
    }
    
    if (!empty($args['date_from'])) {
        $where[] = "DATE(created_at) >= %s";
        $prepare_values[] = $args['date_from'];
    }
    
    if (!empty($args['date_to'])) {
        $where[] = "DATE(created_at) <= %s";
        $prepare_values[] = $args['date_to'];
    }
    
    if (!empty($args['search'])) {
        $where[] = "(tracking_id LIKE %s OR sender_name LIKE %s OR sender_email LIKE %s OR receiver_name LIKE %s)";
        $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
        $prepare_values[] = $search_term;
        $prepare_values[] = $search_term;
        $prepare_values[] = $search_term;
        $prepare_values[] = $search_term;
    }
    
    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = "SELECT * FROM $table_name $where_clause ORDER BY created_at DESC";
    
    if (!empty($prepare_values)) {
        $orders = $wpdb->get_results($wpdb->prepare($query, $prepare_values));
    } else {
        $orders = $wpdb->get_results($query);
    }
    
    if (empty($orders)) {
        return false;
    }
    
    // Create CSV content
    $filename = 'yenolx-orders-' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, array(
        'Tracking ID',
        'Country',
        'Weight (kg)',
        'Price (EUR)',
        'SL Delivery',
        'Coupon Code',
        'Discount (EUR)',
        'Final Price (EUR)',
        'Sender Name',
        'Sender Email',
        'Sender Phone',
        'Sender Address',
        'Sender City',
        'Sender Postal Code',
        'Sender Country',
        'Receiver Name',
        'Receiver Phone',
        'Receiver Address',
        'Receiver City',
        'Receiver Postal Code',
        'Status',
        'Special Notes',
        'Created At',
        'Updated At',
    ));
    
    // Add order data
    foreach ($orders as $order) {
        fputcsv($output, array(
            $order->tracking_id,
            $order->country_id,
            $order->weight_kg,
            $order->price_eur,
            $order->sl_delivery ? 'Yes' : 'No',
            $order->coupon_code,
            $order->discount_eur,
            $order->final_price_eur,
            $order->sender_name,
            $order->sender_email,
            $order->sender_phone,
            $order->sender_address,
            $order->sender_city,
            $order->sender_postal_code,
            $order->sender_country,
            $order->receiver_name,
            $order->receiver_phone,
            $order->receiver_address,
            $order->receiver_city,
            $order->receiver_postal_code,
            $order->status,
            $order->special_notes,
            $order->created_at,
            $order->updated_at,
        ));
    }
    
    fclose($output);
    exit;
}