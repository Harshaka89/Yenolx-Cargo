<?php
/**
 * Email functions for Yenolx Cargo Service
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'includes/utility-functions.php';

/**
 * Send order confirmation email
 */
function yenolx_send_order_confirmation_email($order_id) {
    global $wpdb;
    
    // Get order details
    $table_name = $wpdb->prefix . 'yenolx_orders';
    $order = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $order_id
    ));
    
    if (!$order) {
        return false;
    }
    
    // Get country details
    $country_table = $wpdb->prefix . 'yenolx_countries';
    $country = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $country_table WHERE id = %d",
        $order->country_id
    ));
    
    // Get email settings
    $from_name = get_option('yenolx_email_from_name', get_bloginfo('name'));
    $from_address = get_option('yenolx_email_from_address', get_option('admin_email'));
    
    // Set email headers
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . $from_address . '>',
    );
    
    // Get email template
    $subject = __('Order Confirmation - ' . $order->tracking_id, 'yenolx-cargo');
    
    // Prepare email content
    ob_start();
    include YENOLX_CARGO_PATH . 'templates/emails/order-confirmation.php';
    $message = ob_get_clean();
    
    // Send email
    $result = wp_mail($order->sender_email, $subject, $message, $headers);
    
    // Log email sending
    if ($result) {
        yenolx_log_debug('Order confirmation email sent successfully', array(
            'order_id' => $order_id,
            'tracking_id' => $order->tracking_id,
            'email' => $order->sender_email,
        ));
    } else {
        yenolx_log_debug('Failed to send order confirmation email', array(
            'order_id' => $order_id,
            'tracking_id' => $order->tracking_id,
            'email' => $order->sender_email,
        ));
    }
    
    return $result;
}

/**
 * Send status update email
 */
function yenolx_send_status_update_email($order_id) {
    global $wpdb;
    
    // Get order details
    $table_name = $wpdb->prefix . 'yenolx_orders';
    $order = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $order_id
    ));
    
    if (!$order) {
        return false;
    }
    
    // Get tracking history
    $tracking_table = $wpdb->prefix . 'yenolx_order_tracking';
    $tracking_history = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $tracking_table WHERE order_id = %d ORDER BY created_at DESC LIMIT 1",
        $order_id
    ));
    
    // Get email settings
    $from_name = get_option('yenolx_email_from_name', get_bloginfo('name'));
    $from_address = get_option('yenolx_email_from_address', get_option('admin_email'));
    
    // Set email headers
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . $from_address . '>',
    );
    
    // Get email template
    $subject = __('Order Status Update - ' . $order->tracking_id, 'yenolx-cargo');
    
    // Prepare email content
    ob_start();
    include YENOLX_CARGO_PATH . 'templates/emails/status-update.php';
    $message = ob_get_clean();
    
    // Send email
    $result = wp_mail($order->sender_email, $subject, $message, $headers);
    
    // Log email sending
    if ($result) {
        yenolx_log_debug('Status update email sent successfully', array(
            'order_id' => $order_id,
            'tracking_id' => $order->tracking_id,
            'email' => $order->sender_email,
            'status' => $order->status,
        ));
    } else {
        yenolx_log_debug('Failed to send status update email', array(
            'order_id' => $order_id,
            'tracking_id' => $order->tracking_id,
            'email' => $order->sender_email,
            'status' => $order->status,
        ));
    }
    
    return $result;
}

/**
 * Send admin notification email
 */
function yenolx_send_admin_notification_email($order_id) {
    global $wpdb;
    
    // Get order details
    $table_name = $wpdb->prefix . 'yenolx_orders';
    $order = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $order_id
    ));
    
    if (!$order) {
        return false;
    }
    
    // Get country details
    $country_table = $wpdb->prefix . 'yenolx_countries';
    $country = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $country_table WHERE id = %d",
        $order->country_id
    ));
    
    // Get email settings
    $from_name = get_option('yenolx_email_from_name', get_bloginfo('name'));
    $from_address = get_option('yenolx_email_from_address', get_option('admin_email'));
    $admin_email = get_option('admin_email');
    
    // Set email headers
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . $from_address . '>',
    );
    
    // Get email template
    $subject = __('New Order Received - ' . $order->tracking_id, 'yenolx-cargo');
    
    // Prepare email content
    ob_start();
    include YENOLX_CARGO_PATH . 'templates/emails/admin-notification.php';
    $message = ob_get_clean();
    
    // Send email
    $result = wp_mail($admin_email, $subject, $message, $headers);
    
    // Log email sending
    if ($result) {
        yenolx_log_debug('Admin notification email sent successfully', array(
            'order_id' => $order_id,
            'tracking_id' => $order->tracking_id,
            'admin_email' => $admin_email,
        ));
    } else {
        yenolx_log_debug('Failed to send admin notification email', array(
            'order_id' => $order_id,
            'tracking_id' => $order->tracking_id,
            'admin_email' => $admin_email,
        ));
    }
    
    return $result;
}

/**
 * Get email template header
 */
function yenolx_get_email_header($title = '') {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo esc_html($title); ?></title>
        <style type="text/css">
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .email-header {
                background-color: #2c3e50;
                color: white;
                padding: 20px;
                text-align: center;
            }
            .email-header h1 {
                margin: 0;
                font-size: 24px;
            }
            .email-body {
                padding: 30px;
            }
            .email-footer {
                background-color: #ecf0f1;
                padding: 20px;
                text-align: center;
                font-size: 12px;
                color: #7f8c8d;
            }
            .button {
                display: inline-block;
                background-color: #3498db;
                color: white;
                padding: 12px 24px;
                text-decoration: none;
                border-radius: 4px;
                font-weight: bold;
            }
            .button:hover {
                background-color: #2980b9;
            }
            .order-details {
                background-color: #f8f9fa;
                border-radius: 4px;
                padding: 20px;
                margin: 20px 0;
            }
            .order-details table {
                width: 100%;
                border-collapse: collapse;
            }
            .order-details td {
                padding: 8px 0;
                border-bottom: 1px solid #dee2e6;
            }
            .order-details td:first-child {
                font-weight: bold;
                width: 40%;
            }
            .status-badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: bold;
                text-transform: uppercase;
            }
            .status-confirmed { background-color: #17a2b8; color: white; }
            .status-ready { background-color: #ffc107; color: #212529; }
            .status-picked { background-color: #28a745; color: white; }
            .status-transit-italy { background-color: #007bff; color: white; }
            .status-transit-srilanka { background-color: #6610f2; color: white; }
            .status-at-office { background-color: #fd7e14; color: white; }
            .status-transit-home { background-color: #e83e8c; color: white; }
            .status-delivered { background-color: #20c997; color: white; }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1><?php bloginfo('name'); ?></h1>
                <p><?php _e('Cargo Service', 'yenolx-cargo'); ?></p>
            </div>
            <div class="email-body">
    <?php
    return ob_get_clean();
}

/**
 * Get email template footer
 */
function yenolx_get_email_footer() {
    ob_start();
    ?>
            </div>
            <div class="email-footer">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('All rights reserved.', 'yenolx-cargo'); ?></p>
                <p><?php _e('This is an automated email. Please do not reply to this message.', 'yenolx-cargo'); ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

/**
 * Format currency for email
 */
function yenolx_format_email_currency($amount) {
    $currency_symbol = get_option('yenolx_currency_symbol', '€');
    return $currency_symbol . number_format($amount, 2);
}

/**
 * Get status badge for email
 */
function yenolx_get_email_status_badge($status) {
    $status_class = yenolx_get_order_status_class($status);
    return '<span class="status-badge status-' . $status_class . '">' . esc_html($status) . '</span>';
}