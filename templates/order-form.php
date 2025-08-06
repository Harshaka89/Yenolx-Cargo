<?php
/**
 * Order Form Template
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
    <div class="yenolx-order-form">
        <h1><?php _e('Place Your Order', 'yenolx-cargo'); ?></h1>
        
        <div class="yenolx-order-steps">
            <div class="yenolx-step active">
                <div class="yenolx-step-number">1</div>
                <div class="yenolx-step-title"><?php _e('Calculate Price', 'yenolx-cargo'); ?></div>
            </div>
            <div class="yenolx-step">
                <div class="yenolx-step-number">2</div>
                <div class="yenolx-step-title"><?php _e('Enter Details', 'yenolx-cargo'); ?></div>
            </div>
            <div class="yenolx-step">
                <div class="yenolx-step-number">3</div>
                <div class="yenolx-step-title"><?php _e('Confirm Order', 'yenolx-cargo'); ?></div>
            </div>
        </div>
        
        <?php yenolx_display_order_form_step_1(); ?>
        <?php yenolx_display_order_form_step_2(); ?>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Store order data
    let orderData = {};
    
    // Calculate order price
    $('#calculate_order_price').on('click', function() {
        const countryId = $('#order_country').val();
        const weightKg = $('#order_weight').val();
        const slDelivery = $('#order_sl_delivery').is(':checked');
        const couponCode = $('#order_coupon').val();
        
        if (!countryId || !weightKg) {
            alert('<?php _e('Please select country and weight.', 'yenolx-cargo'); ?>');
            return;
        }
        
        // Show loading
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<span class="yenolx-loading"></span> <?php _e('Calculating...', 'yenolx-cargo'); ?>');
        $btn.prop('disabled', true);
        
        $.ajax({
            url: yenolx_cargo_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'yenolx_calculate_price',
                nonce: yenolx_cargo_ajax.nonce,
                country_id: countryId,
                weight_kg: weightKg,
                sl_delivery: slDelivery,
                coupon_code: couponCode
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    orderData = {
                        country_id: countryId,
                        weight_kg: weightKg,
                        sl_delivery: slDelivery,
                        coupon_code: couponCode,
                        price_data: data
                    };
                    
                    let resultHtml = '<h3><?php _e('Price Breakdown', 'yenolx-cargo'); ?></h3>';
                    resultHtml += '<div class="yenolx-price-breakdown">';
                    resultHtml += '<div class="yenolx-price-row">';
                    resultHtml += '<span class="yenolx-price-label"><?php _e('Country to Milan:', 'yenolx-cargo'); ?></span>';
                    resultHtml += '<span class="yenolx-price-value">' + data.currency_symbol + data.country_to_milan.toFixed(2) + '</span>';
                    resultHtml += '</div>';
                    resultHtml += '<div class="yenolx-price-row">';
                    resultHtml += '<span class="yenolx-price-label"><?php _e('Milan to Sri Lanka:', 'yenolx-cargo'); ?></span>';
                    resultHtml += '<span class="yenolx-price-value">' + data.currency_symbol + data.milan_to_sl.toFixed(2) + '</span>';
                    resultHtml += '</div>';
                    
                    if (data.sl_delivery_cost > 0) {
                        resultHtml += '<div class="yenolx-price-row">';
                        resultHtml += '<span class="yenolx-price-label"><?php _e('Sri Lanka Delivery:', 'yenolx-cargo'); ?></span>';
                        resultHtml += '<span class="yenolx-price-value">' + data.currency_symbol + data.sl_delivery_cost.toFixed(2) + '</span>';
                        resultHtml += '</div>';
                    }
                    
                    resultHtml += '<div class="yenolx-price-row yenolx-subtotal">';
                    resultHtml += '<span class="yenolx-price-label"><?php _e('Subtotal:', 'yenolx-cargo'); ?></span>';
                    resultHtml += '<span class="yenolx-price-value">' + data.currency_symbol + data.subtotal.toFixed(2) + '</span>';
                    resultHtml += '</div>';
                    
                    if (data.discount > 0) {
                        resultHtml += '<div class="yenolx-price-row yenolx-discount">';
                        resultHtml += '<span class="yenolx-price-label"><?php _e('Discount:', 'yenolx-cargo'); ?></span>';
                        resultHtml += '<span class="yenolx-price-value">-' + data.currency_symbol + data.discount.toFixed(2) + '</span>';
                        resultHtml += '</div>';
                    }
                    
                    resultHtml += '<div class="yenolx-price-row yenolx-total">';
                    resultHtml += '<span class="yenolx-price-label"><strong><?php _e('Total:', 'yenolx-cargo'); ?></strong></span>';
                    resultHtml += '<span class="yenolx-price-value"><strong>' + data.currency_symbol + data.total.toFixed(2) + '</strong></span>';
                    resultHtml += '</div>';
                    resultHtml += '</div>';
                    
                    resultHtml += '<div class="yenolx-effective-cost">';
                    resultHtml += '<h4><?php _e('Effective Cost per kg', 'yenolx-cargo'); ?></h4>';
                    resultHtml += '<div class="yenolx-big-number">' + data.currency_symbol + data.effective_cost_per_kg.toFixed(2) + '</div>';
                    resultHtml += '</div>';
                    
                    if (data.delivery_time_ranges.length > 0) {
                        resultHtml += '<div class="yenolx-delivery-time">';
                        resultHtml += '<h4><?php _e('Estimated Delivery Time', 'yenolx-cargo'); ?></h4>';
                        resultHtml += '<ul>';
                        $.each(data.delivery_time_ranges, function(index, range) {
                            resultHtml += '<li>' + range + '</li>';
                        });
                        resultHtml += '</ul>';
                        resultHtml += '</div>';
                    }
                    
                    if (data.coupon_message) {
                        resultHtml += '<div class="yenolx-notice ' + (data.discount > 0 ? 'success' : 'warning') + '">';
                        resultHtml += '<p>' + data.coupon_message + '</p>';
                        resultHtml += '</div>';
                    }
                    
                    resultHtml += '<div class="yenolx-form-actions">';
                    resultHtml += '<button type="button" id="proceed_to_step_2" class="yenolx-button yenolx-button-primary"><?php echo yenolx_get_translated_text('next_step'); ?></button>';
                    resultHtml += '</div>';
                    
                    $('#order_price_result').html(resultHtml).show().addClass('yenolx-fade-in');
                } else {
                    alert('<?php _e('Error calculating price.', 'yenolx-cargo'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error calculating price.', 'yenolx-cargo'); ?>');
            },
            complete: function() {
                // Hide loading
                $btn.html(originalText);
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Proceed to step 2
    $(document).on('click', '#proceed_to_step_2', function() {
        $('.yenolx-step-1').fadeOut(300, function() {
            $('.yenolx-step-2').fadeIn(300);
            $('.yenolx-step').removeClass('active');
            $('.yenolx-step:nth-child(2)').addClass('active');
        });
    });
    
    // Back to step 1
    $('#back_to_step_1').on('click', function() {
        $('.yenolx-step-2').fadeOut(300, function() {
            $('.yenolx-step-1').fadeIn(300);
            $('.yenolx-step').removeClass('active');
            $('.yenolx-step:nth-child(1)').addClass('active');
        });
    });
    
    // Submit order
    $('#submit_order').on('click', function() {
        // Validate form
        const senderFirstName = $('#sender_first_name').val();
        const senderLastName = $('#sender_last_name').val();
        const senderEmail = $('#sender_email').val();
        const senderPhone = $('#sender_phone').val();
        const senderAddress = $('#sender_address').val();
        const senderCity = $('#sender_city').val();
        const senderPostalCode = $('#sender_postal_code').val();
        const senderCountry = $('#sender_country').val();
        const receiverFirstName = $('#receiver_first_name').val();
        const receiverLastName = $('#receiver_last_name').val();
        const receiverPhone = $('#receiver_phone').val();
        const receiverAddress = $('#receiver_address').val();
        const receiverCity = $('#receiver_city').val();
        const receiverPostalCode = $('#receiver_postal_code').val();
        const acceptTerms = $('#accept_terms').is(':checked');
        
        if (!senderFirstName || !senderLastName || !senderEmail || !senderPhone || !senderAddress || !senderCity || !senderPostalCode || !senderCountry || !receiverFirstName || !receiverLastName || !receiverPhone || !receiverAddress || !receiverCity || !receiverPostalCode || !acceptTerms) {
            alert('<?php _e('Please fill in all required fields and accept the terms.', 'yenolx-cargo'); ?>');
            return;
        }
        
        // Show loading
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<span class="yenolx-loading"></span> <?php _e('Submitting...', 'yenolx-cargo'); ?>');
        $btn.prop('disabled', true);
        
        // Submit order
        $.ajax({
            url: yenolx_cargo_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'yenolx_submit_order',
                nonce: yenolx_cargo_ajax.nonce,
                country_id: orderData.country_id,
                weight_kg: orderData.weight_kg,
                sl_delivery: orderData.sl_delivery,
                coupon_code: orderData.coupon_code,
                sender_name: senderFirstName + ' ' + senderLastName,
                sender_email: senderEmail,
                sender_phone: senderPhone,
                sender_address: senderAddress,
                sender_city: senderCity,
                sender_postal_code: senderPostalCode,
                sender_country: senderCountry,
                receiver_name: receiverFirstName + ' ' + receiverLastName,
                receiver_phone: receiverPhone,
                receiver_address: receiverAddress,
                receiver_city: receiverCity,
                receiver_postal_code: receiverPostalCode,
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Order placed successfully!', 'yenolx-cargo'); ?>');
                    if (response.data.redirect_url) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        location.reload();
                    }
                } else {
                    alert(response.data.message || '<?php _e('Error placing order.', 'yenolx-cargo'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error placing order.', 'yenolx-cargo'); ?>');
            },
            complete: function() {
                // Hide loading
                $btn.html(originalText);
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>