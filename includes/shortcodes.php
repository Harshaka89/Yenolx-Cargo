<?php
/**
 * Shortcodes for Yenolx Cargo Service
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'includes/frontend-functions.php';
require_once YENOLX_CARGO_PATH . 'includes/utility-functions.php';

/**
 * Order form shortcode
 */
function yenolx_order_form_shortcode($atts) {
    ob_start();
    
    // Add language switcher
    yenolx_language_switcher();
    
    ?>
    <div class="yenolx-container">
        <div class="yenolx-order-form">
            <h1><?php _e('Place Your Order', 'yenolx-cargo'); ?></h1>
            
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
                        resultHtml += '<table class="yenolx-price-table">';
                        resultHtml += '<tr><td><?php _e('Country to Milan:', 'yenolx-cargo'); ?></td><td>' + data.currency_symbol + data.country_to_milan.toFixed(2) + '</td></tr>';
                        resultHtml += '<tr><td><?php _e('Milan to Sri Lanka:', 'yenolx-cargo'); ?></td><td>' + data.currency_symbol + data.milan_to_sl.toFixed(2) + '</td></tr>';
                        
                        if (data.sl_delivery_cost > 0) {
                            resultHtml += '<tr><td><?php _e('Sri Lanka Delivery:', 'yenolx-cargo'); ?></td><td>' + data.currency_symbol + data.sl_delivery_cost.toFixed(2) + '</td></tr>';
                        }
                        
                        resultHtml += '<tr><td><?php _e('Subtotal:', 'yenolx-cargo'); ?></td><td>' + data.currency_symbol + data.subtotal.toFixed(2) + '</td></tr>';
                        
                        if (data.discount > 0) {
                            resultHtml += '<tr><td><?php _e('Discount:', 'yenolx-cargo'); ?></td><td>-' + data.currency_symbol + data.discount.toFixed(2) + '</td></tr>';
                        }
                        
                        resultHtml += '<tr class="yenolx-total-row"><td><strong><?php _e('Total:', 'yenolx-cargo'); ?></strong></td><td><strong>' + data.currency_symbol + data.total.toFixed(2) + '</strong></td></tr>';
                        resultHtml += '</table>';
                        
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
                        
                        resultHtml += '<button type="button" id="proceed_to_step_2" class="yenolx-button yenolx-button-primary"><?php echo yenolx_get_translated_text('next_step'); ?></button>';
                        
                        $('#order_price_result').html(resultHtml).show();
                    } else {
                        alert('<?php _e('Error calculating price.', 'yenolx-cargo'); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e('Error calculating price.', 'yenolx-cargo'); ?>');
                }
            });
        });
        
        // Proceed to step 2
        $(document).on('click', '#proceed_to_step_2', function() {
            $('.yenolx-step-1').hide();
            $('.yenolx-step-2').show();
        });
        
        // Back to step 1
        $('#back_to_step_1').on('click', function() {
            $('.yenolx-step-2').hide();
            $('.yenolx-step-1').show();
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
                }
            });
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * Tracking form shortcode
 */
function yenolx_tracking_form_shortcode($atts) {
    ob_start();
    
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
                        
                        resultHtml += '<h3><?php echo yenolx_get_translated_text('current_status'); ?></h3>';
                        resultHtml += '<div class="yenolx-status-badge">' + data.status + '</div>';
                        
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
                        
                        if (data.special_notes) {
                            resultHtml += '<h3><?php echo yenolx_get_translated_text('special_notes'); ?></h3>';
                            resultHtml += '<div class="yenolx-special-notes">' + data.special_notes + '</div>';
                        }
                        
                        resultHtml += '<h3><?php _e('Order Details', 'yenolx-cargo'); ?></h3>';
                        resultHtml += '<table class="yenolx-order-details-table">';
                        resultHtml += '<tr><td><?php _e('Tracking ID:', 'yenolx-cargo'); ?></td><td><strong>' + data.tracking_id + '</strong></td></tr>';
                        resultHtml += '<tr><td><?php _e('Weight:', 'yenolx-cargo'); ?></td><td>' + data.weight_kg + ' kg</td></tr>';
                        resultHtml += '<tr><td><?php _e('Total Price:', 'yenolx-cargo'); ?></td><td>' + data.currency_symbol + data.final_price_eur + '</td></tr>';
                        resultHtml += '<tr><td><?php _e('Sender:', 'yenolx-cargo'); ?></td><td>' + data.sender_name + '</td></tr>';
                        resultHtml += '<tr><td><?php _e('Receiver:', 'yenolx-cargo'); ?></td><td>' + data.receiver_name + '</td></tr>';
                        resultHtml += '<tr><td><?php _e('Created:', 'yenolx-cargo'); ?></td><td>' + data.created_at + '</td></tr>';
                        resultHtml += '</table>';
                        
                        $('#tracking_result').html(resultHtml).show();
                    } else {
                        alert(response.data.message || '<?php _e('Error tracking order.', 'yenolx-cargo'); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e('Error tracking order.', 'yenolx-cargo'); ?>');
                }
            });
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

/**
 * Price calculator shortcode
 */
function yenolx_price_calculator_shortcode($atts) {
    ob_start();
    
    // Add language switcher
    yenolx_language_switcher();
    
    ?>
    <div class="yenolx-container">
        <div class="yenolx-calculator-page">
            <h1><?php _e('Price Calculator', 'yenolx-cargo'); ?></h1>
            
            <?php yenolx_display_price_calculator(); ?>
        </div>
    </div>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Calculate price
        $('#calculate_price_button').on('click', function() {
            const countryId = $('#calc_country').val();
            const weightKg = $('#calc_weight').val();
            const slDelivery = $('#calc_sl_delivery').is(':checked');
            const couponCode = $('#calc_coupon').val();
            
            if (!countryId || !weightKg) {
                alert('<?php _e('Please select country and weight.', 'yenolx-cargo'); ?>');
                return;
            }
            
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
                        let resultHtml = '<h3><?php _e('Price Breakdown', 'yenolx-cargo'); ?></h3>';
                        resultHtml += '<table class="yenolx-price-table">';
                        resultHtml += '<tr><td><?php _e('Country to Milan:', 'yenolx-cargo'); ?></td><td>' + data.currency_symbol + data.country_to_milan.toFixed(2) + '</td></tr>';
                        resultHtml += '<tr><td><?php _e('Milan to Sri Lanka:', 'yenolx-cargo'); ?></td><td>' + data.currency_symbol + data.milan_to_sl.toFixed(2) + '</td></tr>';
                        
                        if (data.sl_delivery_cost > 0) {
                            resultHtml += '<tr><td><?php _e('Sri Lanka Delivery:', 'yenolx-cargo'); ?></td><td>' + data.currency_symbol + data.sl_delivery_cost.toFixed(2) + '</td></tr>';
                        }
                        
                        resultHtml += '<tr><td><?php _e('Subtotal:', 'yenolx-cargo'); ?></td><td>' + data.currency_symbol + data.subtotal.toFixed(2) + '</td></tr>';
                        
                        if (data.discount > 0) {
                            resultHtml += '<tr><td><?php _e('Discount:', 'yenolx-cargo'); ?></td><td>-' + data.currency_symbol + data.discount.toFixed(2) + '</td></tr>';
                        }
                        
                        resultHtml += '<tr class="yenolx-total-row"><td><strong><?php _e('Total:', 'yenolx-cargo'); ?></strong></td><td><strong>' + data.currency_symbol + data.total.toFixed(2) + '</strong></td></tr>';
                        resultHtml += '</table>';
                        
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
                        
                        $('#calc_price_result').html(resultHtml).show();
                    } else {
                        alert('<?php _e('Error calculating price.', 'yenolx-cargo'); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e('Error calculating price.', 'yenolx-cargo'); ?>');
                }
            });
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}