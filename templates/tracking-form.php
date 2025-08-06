<?php
/**
 * Tracking Form Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'includes/frontend-functions.php';

// Add language switcher
yenolx_language_switcher();
?>

<div class="yenolx-container">
    <div class="yenolx-tracking-page">
        <h1><?php _e('Track Your Order', 'yenolx-cargo'); ?></h1>
        
        <?php yenolx_display_tracking_form(); ?>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Track order
    $('#track_order_button').on('click', function() {
        const trackingId = $('#tracking_id_input').val();
        
        if (!trackingId) {
            alert('<?php _e('Please enter a tracking ID.', 'yenolx-cargo'); ?>');
            return;
        }
        
        // Show loading
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<span class="yenolx-loading"></span> <?php _e('Tracking...', 'yenolx-cargo'); ?>');
        $btn.prop('disabled', true);
        
        $.ajax({
            url: yenolx_cargo_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'yenolx_track_order',
                nonce: yenolx_cargo_ajax.nonce,
                tracking_id: trackingId
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    let resultHtml = '';
                    
                    resultHtml += '<div class="yenolx-tracking-header">';
                    resultHtml += '<h3><?php echo yenolx_get_translated_text('current_status'); ?></h3>';
                    resultHtml += '<div class="yenolx-status-badge status-' + data.status.toLowerCase().replace(/\s+/g, '-') + '">' + data.status + '</div>';
                    resultHtml += '</div>';
                    
                    resultHtml += '<div class="yenolx-tracking-section">';
                    resultHtml += '<h3><?php echo yenolx_get_translated_text('tracking_history'); ?></h3>';
                    
                    if (data.tracking_history && data.tracking_history.length > 0) {
                        resultHtml += '<div class="yenolx-tracking-timeline">';
                        $.each(data.tracking_history, function(index, history) {
                            resultHtml += '<div class="yenolx-timeline-item">';
                            resultHtml += '<div class="yenolx-timeline-date">' + history.created_at + '</div>';
                            resultHtml += '<div class="yenolx-timeline-status">' + history.status + '</div>';
                            if (history.notes) {
                                resultHtml += '<div class="yenolx-timeline-notes">' + history.notes + '</div>';
                            }
                            resultHtml += '</div>';
                        });
                        resultHtml += '</div>';
                    } else {
                        resultHtml += '<p><?php _e('No tracking history available.', 'yenolx-cargo'); ?></p>';
                    }
                    resultHtml += '</div>';
                    
                    if (data.special_notes) {
                        resultHtml += '<div class="yenolx-tracking-section">';
                        resultHtml += '<h3><?php echo yenolx_get_translated_text('special_notes'); ?></h3>';
                        resultHtml += '<div class="yenolx-special-notes">' + data.special_notes + '</div>';
                        resultHtml += '</div>';
                    }
                    
                    resultHtml += '<div class="yenolx-tracking-section">';
                    resultHtml += '<h3><?php _e('Order Details', 'yenolx-cargo'); ?></h3>';
                    resultHtml += '<div class="yenolx-order-details-grid">';
                    resultHtml += '<div class="yenolx-detail-row">';
                    resultHtml += '<span class="yenolx-detail-label"><?php _e('Tracking ID:', 'yenolx-cargo'); ?></span>';
                    resultHtml += '<span class="yenolx-detail-value"><strong>' + data.tracking_id + '</strong></span>';
                    resultHtml += '</div>';
                    resultHtml += '<div class="yenolx-detail-row">';
                    resultHtml += '<span class="yenolx-detail-label"><?php _e('Weight:', 'yenolx-cargo'); ?></span>';
                    resultHtml += '<span class="yenolx-detail-value">' + data.weight_kg + ' kg</span>';
                    resultHtml += '</div>';
                    resultHtml += '<div class="yenolx-detail-row">';
                    resultHtml += '<span class="yenolx-detail-label"><?php _e('Total Price:', 'yenolx-cargo'); ?></span>';
                    resultHtml += '<span class="yenolx-detail-value">' + data.currency_symbol + data.final_price_eur + '</span>';
                    resultHtml += '</div>';
                    resultHtml += '<div class="yenolx-detail-row">';
                    resultHtml += '<span class="yenolx-detail-label"><?php _e('Sender:', 'yenolx-cargo'); ?></span>';
                    resultHtml += '<span class="yenolx-detail-value">' + data.sender_name + '</span>';
                    resultHtml += '</div>';
                    resultHtml += '<div class="yenolx-detail-row">';
                    resultHtml += '<span class="yenolx-detail-label"><?php _e('Receiver:', 'yenolx-cargo'); ?></span>';
                    resultHtml += '<span class="yenolx-detail-value">' + data.receiver_name + '</span>';
                    resultHtml += '</div>';
                    resultHtml += '<div class="yenolx-detail-row">';
                    resultHtml += '<span class="yenolx-detail-label"><?php _e('Created:', 'yenolx-cargo'); ?></span>';
                    resultHtml += '<span class="yenolx-detail-value">' + data.created_at + '</span>';
                    resultHtml += '</div>';
                    resultHtml += '</div>';
                    resultHtml += '</div>';
                    
                    $('#tracking_result').html(resultHtml).show().addClass('yenolx-fade-in');
                } else {
                    alert(response.data.message || '<?php _e('Error tracking order.', 'yenolx-cargo'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error tracking order.', 'yenolx-cargo'); ?>');
            },
            complete: function() {
                // Hide loading
                $btn.html(originalText);
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Allow Enter key to trigger tracking
    $('#tracking_id_input').on('keypress', function(e) {
        if (e.which === 13) {
            $('#track_order_button').trigger('click');
        }
    });
});
</script>