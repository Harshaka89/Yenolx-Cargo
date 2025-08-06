<?php
/**
 * Orders admin page for Yenolx Cargo Service
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'admin/admin-functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['yenolx_update_order_status'])) {
        // Update order status
        $order_id = intval($_POST['order_id']);
        $status = sanitize_text_field($_POST['status']);
        $notes = sanitize_textarea_field($_POST['notes']);
        
        $result = yenolx_update_order_status($order_id, $status, $notes);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Order status updated successfully.', 'yenolx-cargo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to update order status.', 'yenolx-cargo') . '</p></div>';
        }
    } elseif (isset($_POST['yenolx_update_order_notes'])) {
        // Update order notes
        $order_id = intval($_POST['order_id']);
        $notes = sanitize_textarea_field($_POST['special_notes']);
        
        $result = yenolx_update_order_notes($order_id, $notes);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Order notes updated successfully.', 'yenolx-cargo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to update order notes.', 'yenolx-cargo') . '</p></div>';
        }
    } elseif (isset($_POST['yenolx_export_orders'])) {
        // Export orders
        $export_args = array(
            'status' => isset($_POST['filter_status']) ? sanitize_text_field($_POST['filter_status']) : '',
            'country_id' => isset($_POST['filter_country']) ? intval($_POST['filter_country']) : 0,
            'date_from' => isset($_POST['filter_date_from']) ? sanitize_text_field($_POST['filter_date_from']) : '',
            'date_to' => isset($_POST['filter_date_to']) ? sanitize_text_field($_POST['filter_date_to']) : '',
            'search' => isset($_POST['filter_search']) ? sanitize_text_field($_POST['filter_search']) : '',
        );
        
        yenolx_export_orders_csv($export_args);
    }
}

// Get filter parameters
$filter_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$filter_country = isset($_GET['country']) ? intval($_GET['country']) : 0;
$filter_date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$filter_date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
$filter_search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Get current page
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($paged - 1) * $per_page;

// Get orders
$orders_args = array(
    'status' => $filter_status,
    'country_id' => $filter_country,
    'date_from' => $filter_date_from,
    'date_to' => $filter_date_to,
    'search' => $filter_search,
    'limit' => $per_page,
    'offset' => $offset,
);

$orders = yenolx_get_orders($orders_args);
$total_orders = yenolx_get_orders_count($orders_args);
$total_pages = ceil($total_orders / $per_page);

// Get countries for filter
$countries = yenolx_get_countries();

// Get order statuses
$order_statuses = array(
    'Order Confirmed',
    'Ready for Pickup',
    'Picked Up',
    'In Transit to Italy',
    'In Transit to Sri Lanka',
    'At Sri Lanka Office',
    'In Transit to Home',
    'Delivered',
);

// Get editing order if any
$editing_order = null;
if (isset($_GET['edit_order'])) {
    $editing_order = yenolx_get_order(intval($_GET['edit_order']));
    if ($editing_order) {
        $tracking_history = yenolx_get_order_tracking_history($editing_order->id);
    }
}

// Get currency symbol
$currency_symbol = get_option('yenolx_currency_symbol', '€');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if ($editing_order): ?>
        <!-- Edit Order -->
        <div class="yenolx-form-container">
            <h2><?php printf(__('Edit Order: %s', 'yenolx-cargo'), esc_html($editing_order->tracking_id)); ?></h2>
            
            <!-- Order Details -->
            <div class="yenolx-order-details">
                <h3><?php _e('Order Details', 'yenolx-cargo'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Tracking ID', 'yenolx-cargo'); ?></th>
                        <td><strong><?php echo esc_html($editing_order->tracking_id); ?></strong></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Country', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->country_id); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Weight', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->weight_kg); ?> kg</td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Price', 'yenolx-cargo'); ?></th>
                        <td><?php echo $currency_symbol . number_format($editing_order->price_eur, 2); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('SL Delivery', 'yenolx-cargo'); ?></th>
                        <td><?php echo $editing_order->sl_delivery ? __('Yes', 'yenolx-cargo') : __('No', 'yenolx-cargo'); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Coupon Code', 'yenolx-cargo'); ?></th>
                        <td><?php echo $editing_order->coupon_code ? esc_html($editing_order->coupon_code) : '-'; ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Discount', 'yenolx-cargo'); ?></th>
                        <td><?php echo $currency_symbol . number_format($editing_order->discount_eur, 2); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Final Price', 'yenolx-cargo'); ?></th>
                        <td><strong><?php echo $currency_symbol . number_format($editing_order->final_price_eur, 2); ?></strong></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Created At', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->created_at); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Sender Details -->
            <div class="yenolx-order-details">
                <h3><?php _e('Sender Details', 'yenolx-cargo'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Name', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->sender_name); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Email', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->sender_email); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Phone', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->sender_phone); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Address', 'yenolx-cargo'); ?></th>
                        <td><?php echo nl2br(esc_html($editing_order->sender_address)); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('City', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->sender_city); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Postal Code', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->sender_postal_code); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Country', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->sender_country); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Receiver Details -->
            <div class="yenolx-order-details">
                <h3><?php _e('Receiver Details', 'yenolx-cargo'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Name', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->receiver_name); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Phone', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->receiver_phone); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Address', 'yenolx-cargo'); ?></th>
                        <td><?php echo nl2br(esc_html($editing_order->receiver_address)); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('City', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->receiver_city); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Postal Code', 'yenolx-cargo'); ?></th>
                        <td><?php echo esc_html($editing_order->receiver_postal_code); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Update Status Form -->
            <div class="yenolx-order-details">
                <h3><?php _e('Update Status', 'yenolx-cargo'); ?></h3>
                <form method="post" action="">
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($editing_order->id); ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Current Status', 'yenolx-cargo'); ?></th>
                            <td><strong><?php echo esc_html($editing_order->status); ?></strong></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('New Status', 'yenolx-cargo'); ?></th>
                            <td>
                                <select name="status" class="regular-text">
                                    <?php foreach ($order_statuses as $status): ?>
                                        <option value="<?php echo esc_attr($status); ?>" <?php selected($editing_order->status, $status); ?>>
                                            <?php echo esc_html($status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Notes', 'yenolx-cargo'); ?></th>
                            <td>
                                <textarea name="notes" rows="3" class="large-text"></textarea>
                                <p class="description"><?php _e('Optional notes for this status update.', 'yenolx-cargo'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="yenolx_update_order_status" class="button button-primary" value="<?php _e('Update Status', 'yenolx-cargo'); ?>">
                        <a href="<?php echo admin_url('admin.php?page=yenolx-orders'); ?>" class="button"><?php _e('Back to Orders', 'yenolx-cargo'); ?></a>
                    </p>
                </form>
            </div>
            
            <!-- Special Notes Form -->
            <div class="yenolx-order-details">
                <h3><?php _e('Special Notes', 'yenolx-cargo'); ?></h3>
                <form method="post" action="">
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($editing_order->id); ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Notes', 'yenolx-cargo'); ?></th>
                            <td>
                                <textarea name="special_notes" rows="4" class="large-text"><?php echo esc_textarea($editing_order->special_notes); ?></textarea>
                                <p class="description"><?php _e('Internal notes for this order.', 'yenolx-cargo'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="yenolx_update_order_notes" class="button button-primary" value="<?php _e('Update Notes', 'yenolx-cargo'); ?>">
                    </p>
                </form>
            </div>
            
            <!-- Tracking History -->
            <div class="yenolx-order-details">
                <h3><?php _e('Tracking History', 'yenolx-cargo'); ?></h3>
                <?php if (!empty($tracking_history)): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Status', 'yenolx-cargo'); ?></th>
                                <th><?php _e('Notes', 'yenolx-cargo'); ?></th>
                                <th><?php _e('Date', 'yenolx-cargo'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tracking_history as $history): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($history->status); ?></strong></td>
                                    <td><?php echo nl2br(esc_html($history->notes)); ?></td>
                                    <td><?php echo esc_html($history->created_at); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No tracking history found.', 'yenolx-cargo'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Orders List -->
        <div class="yenolx-form-container">
            <!-- Filters -->
            <h2><?php _e('Filter Orders', 'yenolx-cargo'); ?></h2>
            <form method="get" action="">
                <input type="hidden" name="page" value="yenolx-orders">
                
                <div class="yenolx-filters">
                    <div class="yenolx-filter-group">
                        <label><?php _e('Status:', 'yenolx-cargo'); ?></label>
                        <select name="status">
                            <option value=""><?php _e('All Statuses', 'yenolx-cargo'); ?></option>
                            <?php foreach ($order_statuses as $status): ?>
                                <option value="<?php echo esc_attr($status); ?>" <?php selected($filter_status, $status); ?>>
                                    <?php echo esc_html($status); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="yenolx-filter-group">
                        <label><?php _e('Country:', 'yenolx-cargo'); ?></label>
                        <select name="country">
                            <option value="0"><?php _e('All Countries', 'yenolx-cargo'); ?></option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?php echo esc_attr($country->id); ?>" <?php selected($filter_country, $country->id); ?>>
                                    <?php echo esc_html($country->name_en); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="yenolx-filter-group">
                        <label><?php _e('Date From:', 'yenolx-cargo'); ?></label>
                        <input type="date" name="date_from" value="<?php echo esc_attr($filter_date_from); ?>">
                    </div>
                    
                    <div class="yenolx-filter-group">
                        <label><?php _e('Date To:', 'yenolx-cargo'); ?></label>
                        <input type="date" name="date_to" value="<?php echo esc_attr($filter_date_to); ?>">
                    </div>
                    
                    <div class="yenolx-filter-group">
                        <label><?php _e('Search:', 'yenolx-cargo'); ?></label>
                        <input type="text" name="search" value="<?php echo esc_attr($filter_search); ?>" placeholder="<?php _e('Tracking ID, Name, Email', 'yenolx-cargo'); ?>">
                    </div>
                    
                    <div class="yenolx-filter-group">
                        <input type="submit" class="button" value="<?php _e('Filter', 'yenolx-cargo'); ?>">
                        <a href="<?php echo admin_url('admin.php?page=yenolx-orders'); ?>" class="button"><?php _e('Reset', 'yenolx-cargo'); ?></a>
                    </div>
                </div>
            </form>
            
            <!-- Export Form -->
            <form method="post" action="" style="display: inline; margin-top: 10px;">
                <input type="hidden" name="filter_status" value="<?php echo esc_attr($filter_status); ?>">
                <input type="hidden" name="filter_country" value="<?php echo esc_attr($filter_country); ?>">
                <input type="hidden" name="filter_date_from" value="<?php echo esc_attr($filter_date_from); ?>">
                <input type="hidden" name="filter_date_to" value="<?php echo esc_attr($filter_date_to); ?>">
                <input type="hidden" name="filter_search" value="<?php echo esc_attr($filter_search); ?>">
                <input type="submit" name="yenolx_export_orders" class="button" value="<?php _e('Export to CSV', 'yenolx-cargo'); ?>">
            </form>
            
            <!-- Orders Table -->
            <h2><?php _e('Orders', 'yenolx-cargo'); ?> (<?php echo number_format($total_orders); ?>)</h2>
            
            <?php if (!empty($orders)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Tracking ID', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Customer', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Country', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Weight', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Price', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Status', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Date', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Actions', 'yenolx-cargo'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo esc_html($order->tracking_id); ?></strong></td>
                                <td>
                                    <?php echo esc_html($order->sender_name); ?><br>
                                    <small><?php echo esc_html($order->sender_email); ?></small>
                                </td>
                                <td><?php echo esc_html($order->country_id); ?></td>
                                <td><?php echo esc_html($order->weight_kg); ?> kg</td>
                                <td><?php echo $currency_symbol . number_format($order->final_price_eur, 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo sanitize_title($order->status); ?>">
                                        <?php echo esc_html($order->status); ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($order->created_at)); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=yenolx-orders&edit_order=' . $order->id); ?>" class="button button-small"><?php _e('Edit', 'yenolx-cargo'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <?php
                            echo paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => __('&laquo;'),
                                'next_text' => __('&raquo;'),
                                'total' => $total_pages,
                                'current' => $paged,
                            ));
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <p><?php _e('No orders found.', 'yenolx-cargo'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>