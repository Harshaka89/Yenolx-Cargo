<?php
/**
 * Price Calculator Template
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
                    
                    resultHtml += '<div class="yenolx-calculator-actions">';
                    resultHtml += '<button type="button" id="recalculate_price" class="yenolx-button"><?php _e('Recalculate', 'yenolx-cargo'); ?></button>';
                    resultHtml += '<button type="button" id="place_order" class="yenolx-button yenolx-button-primary"><?php _e('Place Order', 'yenolx-cargo'); ?></button>';
                    resultHtml += '</div>';
                    
                    $('#calc_price_result').html(resultHtml).show().addClass('yenolx-fade-in');
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
    
    // Recalculate price
    $(document).on('click', '#recalculate_price', function() {
        $('#calc_price_result').hide();
        $('#calc_country').val('');
        $('#calc_weight').val('');
        $('#calc_sl_delivery').prop('checked', false);
        $('#calc_coupon').val('');
    });
    
    // Place order from calculator
    $(document).on('click', '#place_order', function() {
        // Redirect to order form with pre-filled data
        const countryId = $('#calc_country').val();
        const weightKg = $('#calc_weight').val();
        const slDelivery = $('#calc_sl_delivery').is(':checked');
        const couponCode = $('#calc_coupon').val();
        
        if (countryId && weightKg) {
            // Find order form page and redirect with parameters
            const orderFormUrl = '<?php echo get_permalink(get_option('yenolx_order_form_page_id')); ?>';
            const redirectUrl = orderFormUrl + '?country=' + countryId + '&weight=' + weightKg + '&sl_delivery=' + (slDelivery ? '1' : '0') + '&coupon=' + encodeURIComponent(couponCode);
            
            window.location.href = redirectUrl;
        }
    });
});
</script>