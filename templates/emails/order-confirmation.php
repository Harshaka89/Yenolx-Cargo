<?php
/**
 * Order Confirmation Email Template
 *
 * @var object $order
 * @var object $country
 * @var string $currency_symbol
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

echo yenolx_get_email_header(__('Order Confirmation', 'yenolx-cargo'));

?>
<p><?php _e('Thank you for placing your order with Yenolx Cargo Service!', 'yenolx-cargo'); ?></p>
<p><?php _e('Your order has been received and is being processed. Below are your order details:', 'yenolx-cargo'); ?></p>

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

<?php if ($country): ?>
<div class="order-details">
    <h3><?php _e('Estimated Delivery Time', 'yenolx-cargo'); ?></h3>
    <ul>
        <?php if (!empty($country->delivery_time_range_1)): ?>
            <li><?php echo esc_html($country->delivery_time_range_1); ?></li>
        <?php endif; ?>
        <?php if (!empty($country->delivery_time_range_2)): ?>
            <li><?php echo esc_html($country->delivery_time_range_2); ?></li>
        <?php endif; ?>
        <?php if (!empty($country->delivery_time_range_3)): ?>
            <li><?php echo esc_html($country->delivery_time_range_3); ?></li>
        <?php endif; ?>
    </ul>
</div>
<?php endif; ?>

<div class="order-details">
    <h3><?php _e('Next Steps', 'yenolx-cargo'); ?></h3>
    <ol>
        <li><?php _e('Keep your tracking ID safe for future reference.', 'yenolx-cargo'); ?></li>
        <li><?php _e('Pack your items securely and prepare for pickup.', 'yenolx-cargo'); ?></li>
        <li><?php _e('You will receive notifications as your order progresses through each stage.', 'yenolx-cargo'); ?></li>
        <li><?php _e('Track your order status anytime using our tracking system.', 'yenolx-cargo'); ?></li>
    </ol>
</div>

<p><?php _e('If you have any questions about your order, please don\'t hesitate to contact us.', 'yenolx-cargo'); ?></p>

<p><?php _e('Thank you for choosing Yenolx Cargo Service!', 'yenolx-cargo'); ?></p>

<?php echo yenolx_get_email_footer(); ?>