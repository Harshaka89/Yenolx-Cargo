<?php
/**
 * Coupons admin page for Yenolx Cargo Service
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'admin/admin-functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['yenolx_add_coupon'])) {
        // Add new coupon
        $result = yenolx_add_coupon($_POST);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Coupon added successfully.', 'yenolx-cargo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to add coupon.', 'yenolx-cargo') . '</p></div>';
        }
    } elseif (isset($_POST['yenolx_edit_coupon'])) {
        // Edit coupon
        $coupon_id = intval($_POST['coupon_id']);
        $result = yenolx_update_coupon($coupon_id, $_POST);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Coupon updated successfully.', 'yenolx-cargo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to update coupon.', 'yenolx-cargo') . '</p></div>';
        }
    } elseif (isset($_POST['yenolx_delete_coupon'])) {
        // Delete coupon
        $coupon_id = intval($_POST['coupon_id']);
        $result = yenolx_delete_coupon($coupon_id);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Coupon deleted successfully.', 'yenolx-cargo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to delete coupon.', 'yenolx-cargo') . '</p></div>';
        }
    }
}

// Get current page
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($paged - 1) * $per_page;

// Get filter parameters
$filter_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Get coupons
$coupons_args = array(
    'status' => $filter_status,
    'limit' => $per_page,
    'offset' => $offset,
);

$coupons = yenolx_get_coupons($coupons_args);

// Get total coupons count
$total_coupons = yenolx_get_coupons_count($coupons_args);
$total_pages = ceil($total_coupons / $per_page);

// Get editing coupon if any
$editing_coupon = null;
if (isset($_GET['edit_coupon'])) {
    global $wpdb;
    $coupon_id = intval($_GET['edit_coupon']);
    $table_name = $wpdb->prefix . 'yenolx_coupons';
    $editing_coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $coupon_id));
}

// Get currency symbol
$currency_symbol = get_option('yenolx_currency_symbol', '€');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if ($editing_coupon): ?>
        <!-- Edit Coupon Form -->
        <div class="yenolx-form-container">
            <h2><?php _e('Edit Coupon', 'yenolx-cargo'); ?></h2>
            <form method="post" action="">
                <input type="hidden" name="coupon_id" value="<?php echo esc_attr($editing_coupon->id); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Coupon Code', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="code" value="<?php echo esc_attr($editing_coupon->code); ?>" class="regular-text" required>
                            <p class="description"><?php _e('Unique code for the coupon.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Discount Type', 'yenolx-cargo'); ?></th>
                        <td>
                            <select name="discount_type" class="regular-text">
                                <option value="fixed" <?php selected($editing_coupon->discount_type, 'fixed'); ?>>
                                    <?php _e('Fixed Amount', 'yenolx-cargo'); ?>
                                </option>
                                <option value="percentage" <?php selected($editing_coupon->discount_type, 'percentage'); ?>>
                                    <?php _e('Percentage', 'yenolx-cargo'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Discount Value', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="number" name="discount_value" step="0.01" min="0" value="<?php echo esc_attr($editing_coupon->discount_value); ?>" class="regular-text" required>
                            <p class="description">
                                <?php _e('Fixed amount in', 'yenolx-cargo'); ?> <?php echo $currency_symbol; ?> 
                                <?php _e('or percentage (e.g., 10 for 10%)', 'yenolx-cargo'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Minimum Order Value', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="number" name="min_order_value" step="0.01" min="0" value="<?php echo esc_attr($editing_coupon->min_order_value); ?>" class="regular-text">
                            <p class="description"><?php _e('Minimum order value required to use this coupon.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Usage Limit', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="number" name="max_uses" min="0" value="<?php echo esc_attr($editing_coupon->max_uses); ?>" class="regular-text">
                            <p class="description"><?php _e('Maximum number of times this coupon can be used. Leave empty for unlimited.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Used Count', 'yenolx-cargo'); ?></th>
                        <td>
                            <strong><?php echo number_format($editing_coupon->used_count); ?></strong>
                            <p class="description"><?php _e('Number of times this coupon has been used.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Start Date', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="date" name="start_date" value="<?php echo esc_attr($editing_coupon->start_date); ?>" class="regular-text">
                            <p class="description"><?php _e('Date when coupon becomes valid. Leave empty for immediate availability.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('End Date', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="date" name="end_date" value="<?php echo esc_attr($editing_coupon->end_date); ?>" class="regular-text">
                            <p class="description"><?php _e('Date when coupon expires. Leave empty for no expiration.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Status', 'yenolx-cargo'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="status" value="1" <?php checked($editing_coupon->status, 1); ?>>
                                <?php _e('Active', 'yenolx-cargo'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="yenolx_edit_coupon" class="button button-primary" value="<?php _e('Update Coupon', 'yenolx-cargo'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=yenolx-coupons'); ?>" class="button"><?php _e('Cancel', 'yenolx-cargo'); ?></a>
                </p>
            </form>
        </div>
        
    <?php else: ?>
        <!-- Add Coupon Form -->
        <div class="yenolx-form-container">
            <h2><?php _e('Add New Coupon', 'yenolx-cargo'); ?></h2>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Coupon Code', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="code" class="regular-text" required>
                            <p class="description"><?php _e('Unique code for the coupon.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Discount Type', 'yenolx-cargo'); ?></th>
                        <td>
                            <select name="discount_type" class="regular-text">
                                <option value="fixed"><?php _e('Fixed Amount', 'yenolx-cargo'); ?></option>
                                <option value="percentage"><?php _e('Percentage', 'yenolx-cargo'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Discount Value', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="number" name="discount_value" step="0.01" min="0" class="regular-text" required>
                            <p class="description">
                                <?php _e('Fixed amount in', 'yenolx-cargo'); ?> <?php echo $currency_symbol; ?> 
                                <?php _e('or percentage (e.g., 10 for 10%)', 'yenolx-cargo'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Minimum Order Value', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="number" name="min_order_value" step="0.01" min="0" value="0" class="regular-text">
                            <p class="description"><?php _e('Minimum order value required to use this coupon.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Usage Limit', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="number" name="max_uses" min="0" class="regular-text">
                            <p class="description"><?php _e('Maximum number of times this coupon can be used. Leave empty for unlimited.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Start Date', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="date" name="start_date" class="regular-text">
                            <p class="description"><?php _e('Date when coupon becomes valid. Leave empty for immediate availability.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('End Date', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="date" name="end_date" class="regular-text">
                            <p class="description"><?php _e('Date when coupon expires. Leave empty for no expiration.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Status', 'yenolx-cargo'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="status" value="1" checked>
                                <?php _e('Active', 'yenolx-cargo'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="yenolx_add_coupon" class="button button-primary" value="<?php _e('Add Coupon', 'yenolx-cargo'); ?>">
                </p>
            </form>
        </div>
        
        <!-- Filters -->
        <div class="yenolx-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="yenolx-coupons">
                
                <div class="yenolx-filter-group">
                    <label><?php _e('Status:', 'yenolx-cargo'); ?></label>
                    <select name="status">
                        <option value=""><?php _e('All Coupons', 'yenolx-cargo'); ?></option>
                        <option value="1" <?php selected($filter_status, '1'); ?>><?php _e('Active', 'yenolx-cargo'); ?></option>
                        <option value="0" <?php selected($filter_status, '0'); ?>><?php _e('Inactive', 'yenolx-cargo'); ?></option>
                    </select>
                </div>
                
                <div class="yenolx-filter-group">
                    <input type="submit" class="button" value="<?php _e('Filter', 'yenolx-cargo'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=yenolx-coupons'); ?>" class="button"><?php _e('Reset', 'yenolx-cargo'); ?></a>
                </div>
            </form>
        </div>
        
        <!-- Coupons Table -->
        <div class="yenolx-table-container">
            <h2><?php _e('Coupons', 'yenolx-cargo'); ?> (<?php echo number_format($total_coupons); ?>)</h2>
            
            <?php if (!empty($coupons)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Code', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Discount', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Usage', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Valid Period', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Status', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Actions', 'yenolx-cargo'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $coupon): ?>
                            <tr>
                                <td><strong><?php echo esc_html($coupon->code); ?></strong></td>
                                <td>
                                    <?php if ($coupon->discount_type === 'fixed'): ?>
                                        <?php echo $currency_symbol . number_format($coupon->discount_value, 2); ?>
                                    <?php else: ?>
                                        <?php echo number_format($coupon->discount_value, 0); ?>%
                                    <?php endif; ?>
                                    <?php if ($coupon->min_order_value > 0): ?>
                                        <br><small><?php _e('Min:', 'yenolx-cargo'); ?> <?php echo $currency_symbol . number_format($coupon->min_order_value, 2); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo number_format($coupon->used_count); ?>
                                    <?php if ($coupon->max_uses): ?>
                                        / <?php echo number_format($coupon->max_uses); ?>
                                    <?php else: ?>
                                        <?php _e('(unlimited)', 'yenolx-cargo'); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($coupon->start_date): ?>
                                        <?php echo date('Y-m-d', strtotime($coupon->start_date)); ?>
                                    <?php else: ?>
                                        <?php _e('Now', 'yenolx-cargo'); ?>
                                    <?php endif; ?>
                                    <?php _e('to', 'yenolx-cargo'); ?>
                                    <?php if ($coupon->end_date): ?>
                                        <?php echo date('Y-m-d', strtotime($coupon->end_date)); ?>
                                    <?php else: ?>
                                        <?php _e('Never', 'yenolx-cargo'); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $coupon->status ? '<span class="status-active">' . __('Active', 'yenolx-cargo') . '</span>' : '<span class="status-inactive">' . __('Inactive', 'yenolx-cargo') . '</span>'; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=yenolx-coupons&edit_coupon=' . $coupon->id); ?>" class="button button-small"><?php _e('Edit', 'yenolx-cargo'); ?></a>
                                    <form method="post" action="" style="display: inline;" onsubmit="return confirm('<?php _e('Are you sure you want to delete this coupon?', 'yenolx-cargo'); ?>');">
                                        <input type="hidden" name="coupon_id" value="<?php echo esc_attr($coupon->id); ?>">
                                        <input type="submit" name="yenolx_delete_coupon" class="button button-small" value="<?php _e('Delete', 'yenolx-cargo'); ?>">
                                    </form>
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
                <p><?php _e('No coupons found.', 'yenolx-cargo'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>