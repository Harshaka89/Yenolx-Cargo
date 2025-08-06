<?php
/**
 * Status Update Email Template
 *
 * @var object $order
 * @var array $tracking_history
 * @var string $currency_symbol
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the latest tracking entry
$latest_tracking = !empty($tracking_history) ? $tracking_history[0] : null;

echo yenolx_get_email_header(__('Order Status Update', 'yenolx-cargo'));

?>
<p><?php printf(__('Your order status has been updated. Here are the details:', 'yenolx-cargo')); ?></p>

<div class="order-details">
    <h3><?php _e('Order Information', 'yenolx-cargo'); ?></h3>
    <table>
        <tr>
            <td><?php _e('Tracking ID:', 'yenolx-cargo'); ?></td>
            <td><strong><?php echo esc_html($order->tracking_id); ?></strong></td>
        </tr>
        <tr>
            <td><?php _e('Current Status:', 'yenolx-cargo'); ?></td>
            <td><?php echo yenolx_get_email_status_badge($order->status); ?></td>
        </tr>
        <tr>
            <td><?php _e('Weight:', 'yenolx-cargo'); ?></td>
            <td><?php echo esc_html($order->weight_kg); ?> kg</td>
        </tr>
        <tr>
            <td><?php _e('Total Amount:', 'yenolx-cargo'); ?></td>
            <td><?php echo yenolx_format_email_currency($order->final_price_eur); ?></td>
        </tr>
        <tr>
            <td><?php _e('Last Updated:', 'yenolx-cargo'); ?></td>
            <td><?php echo yenolx_format_date($order->updated_at); ?></td>
        </tr>
    </table>
</div>

<?php if ($latest_tracking): ?>
<div class="order-details">
    <h3><?php _e('Status Update Details', 'yenolx-cargo'); ?></h3>
    <p><strong><?php echo esc_html($latest_tracking->status); ?></strong></p>
    <?php if ($latest_tracking->notes): ?>
        <p><?php echo nl2br(esc_html($latest_tracking->notes)); ?></p>
    <?php endif; ?>
    <p><em><?php echo yenolx_format_date($latest_tracking->created_at); ?></em></p>
</div>
<?php endif; ?>

<div class="order-details">
    <h3><?php _e('What This Means', 'yenolx-cargo'); ?></h3>
    <?php
    $status_explanations = array(
        'Order Confirmed' => __('Your order has been received and confirmed in our system.', 'yenolx-cargo'),
        'Ready for Pickup' => __('Your package is ready for pickup from the specified location.', 'yenolx-cargo'),
        'Picked Up' => __('Your package has been collected and is in transit to our facility.', 'yenolx-cargo'),
        'In Transit to Italy' => __('Your package is on its way to our sorting facility in Italy.', 'yenolx-cargo'),
        'In Transit to Sri Lanka' => __('Your package has left Italy and is en route to Sri Lanka.', 'yenolx-cargo'),
        'At Sri Lanka Office' => __('Your package has arrived at our Sri Lanka facility and is being processed.', 'yenolx-cargo'),
        'In Transit to Home' => __('Your package is out for delivery to the recipient\'s address.', 'yenolx-cargo'),
        'Delivered' => __('Your package has been successfully delivered to the recipient.', 'yenolx-cargo'),
    );
    
    if (isset($status_explanations[$order->status])) {
        echo '<p>' . esc_html($status_explanations[$order->status]) . '</p>';
    }
    ?>
</div>

<div class="order-details">
    <h3><?php _e('Tracking History', 'yenolx-cargo'); ?></h3>
    <?php if (!empty($tracking_history)): ?>
        <table>
            <tr>
                <th><?php _e('Status', 'yenolx-cargo'); ?></th>
                <th><?php _e('Date', 'yenolx-cargo'); ?></th>
                <th><?php _e('Notes', 'yenolx-cargo'); ?></th>
            </tr>
            <?php foreach ($tracking_history as $history): ?>
                <tr>
                    <td><?php echo esc_html($history->status); ?></td>
                    <td><?php echo yenolx_format_date($history->created_at); ?></td>
                    <td><?php echo nl2br(esc_html($history->notes)); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p><?php _e('No tracking history available.', 'yenolx-cargo'); ?></p>
    <?php endif; ?>
</div>

<?php if ($order->special_notes): ?>
<div class="order-details">
    <h3><?php _e('Special Notes', 'yenolx-cargo'); ?></h3>
    <p><?php echo nl2br(esc_html($order->special_notes)); ?></p>
</div>
<?php endif; ?>

<p><?php _e('You can track your order anytime using our tracking system with your tracking ID.', 'yenolx-cargo'); ?></p>

<p><?php _e('If you have any questions or concerns about your order, please contact our customer support team.', 'yenolx-cargo'); ?></p>

<p><?php _e('Thank you for choosing Yenolx Cargo Service!', 'yenolx-cargo'); ?></p>

<?php echo yenolx_get_email_footer(); ?>