<?php
/**
 * Admin Notification Email Template
 *
 * @var object $order
 * @var object $country
 * @var string $currency_symbol
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

echo yenolx_get_email_header(__('New Order Received', 'yenolx-cargo'));

?>
<p><?php _e('A new order has been placed on your Yenolx Cargo Service website. Please review the details below:', 'yenolx-cargo'); ?></p>

<div class="order-details">
    <h3><?php _e('Order Information', 'yenolx-cargo'); ?></h3>
    <table>
        <tr>
            <td><?php _e('Tracking ID:', 'yenolx-cargo'); ?></td>
            <td><strong><?php echo esc_html($order->tracking_id); ?></strong></td>
        </tr>
        <tr>
            <td><?php _e('Status:', 'yenolx-cargo'); ?></td>
            <td><?php echo yenolx_get_email_status_badge($order->status); ?></td>
        </tr>
        <tr>
            <td><?php _e('Weight:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->weight_kg); ?> kg</td>
        </tr>
        <tr>
            <td><?php _e('Sri Lanka Delivery:', 'yenolx-cargo'); ?></td>
            <td><?php echo $order->sl_delivery ? __('Yes', 'yenolx-cargo') : __('No', 'yenolx-cargo'); ?></td>
        </tr>
        <?php if ($order->coupon_code): ?>
        <tr>
            <td><?php _e('Coupon Code:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->coupon_code); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td><?php _e('Subtotal:', 'yenolx-cargo'); ?></td>
            <td><?php echo yenolx_format_email_currency($order->price_eur); ?></td>
        </tr>
        <?php if ($order->discount_eur > 0): ?>
        <tr>
            <td><?php _e('Discount:', 'yenolx-cargo'); ?></td>
            <td>-<?php echo yenolx_format_email_currency($order->discount_eur); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td><strong><?php _e('Total Amount:', 'yenolx-cargo'); ?></strong></td>
            <td><strong><?php echo yenolx_format_email_currency($order->final_price_eur); ?></strong></td>
        </tr>
        <tr>
            <td><?php _e('Order Date:', 'yenolx-cargo'); ?></td>
            <td><?php echo yenolx_format_date($order->created_at); ?></td>
        </tr>
    </table>
</div>

<div class="order-details">
    <h3><?php _e('Sender Information', 'yenolx-cargo'); ?></h3>
    <table>
        <tr>
            <td><?php _e('Name:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->sender_name); ?></td>
        </tr>
        <tr>
            <td><?php _e('Email:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->sender_email); ?></td>
        </tr>
        <tr>
            <td><?php _e('Phone:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->sender_phone); ?></td>
        </tr>
        <tr>
            <td><?php _e('Address:', 'yenolx-cargo'); ?></td>
            <td><?php echo nl2br(esc_html($order->sender_address)); ?></td>
        </tr>
        <tr>
            <td><?php _e('City:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->sender_city); ?></td>
        </tr>
        <tr>
            <td><?php _e('Postal Code:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->sender_postal_code); ?></td>
        </tr>
        <tr>
            <td><?php _e('Country:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->sender_country); ?></td>
        </tr>
    </table>
</div>

<div class="order-details">
    <h3><?php _e('Receiver Information', 'yenolx-cargo'); ?></h3>
    <table>
        <tr>
            <td><?php _e('Name:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->receiver_name); ?></td>
        </tr>
        <tr>
            <td><?php _e('Phone:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->receiver_phone); ?></td>
        </tr>
        <tr>
            <td><?php _e('Address:', 'yenolx-cargo'); ?></td>
            <td><?php echo nl2br(esc_html($order->receiver_address)); ?></td>
        </tr>
        <tr>
            <td><?php _e('City:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->receiver_city); ?></td>
        </tr>
        <tr>
            <td><?php _e('Postal Code:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->receiver_postal_code); ?></td>
        </tr>
    </table>
</div>

<div class="order-details">
    <h3><?php _e('Required Actions', 'yenolx-cargo'); ?></h3>
    <ol>
        <li><?php _e('Review the order details and confirm all information is correct.', 'yenolx-cargo'); ?></li>
        <li><?php _e('Update the order status as it progresses through the shipping process.', 'yenolx-cargo'); ?></li>
        <li><?php _e('Contact the sender if any additional information is required.', 'yenolx-cargo'); ?></li>
        <li><?php _e('Ensure proper tracking updates are provided to the customer.', 'yenolx-cargo'); ?></li>
    </ol>
</div>

<p>
    <a href="<?php echo admin_url('admin.php?page=yenolx-orders&edit_order=' . $order->id); ?>" class="button">
        <?php _e('View Order in Admin', 'yenolx-cargo'); ?>
    </a>
</p>

<p><?php _e('This is an automated notification. Please log in to the admin panel to manage this order.', 'yenolx-cargo'); ?></p>

<?php echo yenolx_get_email_footer(); ?>