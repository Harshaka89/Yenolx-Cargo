<?php
/**
 * Pricing admin page for Yenolx Cargo Service
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'admin/admin-functions.php';

// Get countries
$countries = yenolx_get_countries();
$currency_symbol = get_option('yenolx_currency_symbol', '€');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="yenolx-pricing-overview">
        <h2><?php _e('Pricing Overview', 'yenolx-cargo'); ?></h2>
        
        <div class="yenolx-pricing-rates">
            <h3><?php _e('Base Rates', 'yenolx-cargo'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Milan to Sri Lanka Rate', 'yenolx-cargo'); ?></th>
                    <td><?php echo $currency_symbol; ?><?php echo number_format(get_option('yenolx_milan_to_sl_rate', 3.50), 2); ?> <?php _e('per kg', 'yenolx-cargo'); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Sri Lanka Local Delivery Rate', 'yenolx-cargo'); ?></th>
                    <td><?php echo $currency_symbol; ?><?php echo number_format(get_option('yenolx_sl_delivery_rate', 1.00), 2); ?> <?php _e('per kg', 'yenolx-cargo'); ?></td>
                </tr>
            </table>
            
            <p class="description">
                <?php _e('To edit base rates, go to', 'yenolx-cargo'); ?> 
                <a href="<?php echo admin_url('admin.php?page=yenolx-cargo'); ?>"><?php _e('Dashboard &rarr; Settings', 'yenolx-cargo'); ?></a>
            </p>
        </div>
        
        <div class="yenolx-country-pricing">
            <h3><?php _e('Country-Specific Pricing', 'yenolx-cargo'); ?></h3>
            
            <?php if (!empty($countries)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Country', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Pricing Tiers', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Actions', 'yenolx-cargo'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($countries as $country): ?>
                            <?php $pricing = yenolx_get_pricing($country->id); ?>
                            <tr>
                                <td><strong><?php echo esc_html($country->name_en); ?></strong></td>
                                <td>
                                    <?php if (!empty($pricing)): ?>
                                        <div class="yenolx-pricing-tiers">
                                            <?php foreach ($pricing as $price): ?>
                                                <div class="yenolx-pricing-tier">
                                                    <?php echo esc_html($price->weight_kg); ?> kg = <?php echo $currency_symbol . number_format($price->price_eur, 2); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="description"><?php _e('No pricing set', 'yenolx-cargo'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=yenolx-countries&pricing_country=' . $country->id); ?>" class="button button-small">
                                        <?php _e('Manage Pricing', 'yenolx-cargo'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No countries found. Please add countries first.', 'yenolx-cargo'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=yenolx-countries'); ?>" class="button button-primary">
                    <?php _e('Add Countries', 'yenolx-cargo'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="yenolx-pricing-calculator">
        <h2><?php _e('Price Calculator', 'yenolx-cargo'); ?></h2>
        <p class="description"><?php _e('Use this calculator to test your pricing configuration.', 'yenolx-cargo'); ?></p>
        
        <div class="yenolx-calculator-form">
            <div class="yenolx-form-group">
                <label for="calc_country"><?php _e('Country:', 'yenolx-cargo'); ?></label>
                <select id="calc_country" name="country">
                    <option value=""><?php _e('Select Country', 'yenolx-cargo'); ?></option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?php echo esc_attr($country->id); ?>"><?php echo esc_html($country->name_en); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="yenolx-form-group">
                <label for="calc_weight"><?php _e('Weight (kg):', 'yenolx-cargo'); ?></label>
                <input type="number" id="calc_weight" name="weight" step="0.1" min="0" placeholder="e.g., 5">
            </div>
            
            <div class="yenolx-form-group">
                <label>
                    <input type="checkbox" id="calc_sl_delivery" name="sl_delivery">
                    <?php _e('Deliver to home in Sri Lanka', 'yenolx-cargo'); ?>
                </label>
            </div>
            
            <div class="yenolx-form-group">
                <label for="calc_coupon"><?php _e('Coupon Code (optional):', 'yenolx-cargo'); ?></label>
                <input type="text" id="calc_coupon" name="coupon_code" placeholder="<?php _e('Enter coupon code', 'yenolx-cargo'); ?>">
            </div>
            
            <div class="yenolx-form-group">
                <button type="button" id="calculate_price" class="button button-primary">
                    <?php _e('Calculate Price', 'yenolx-cargo'); ?>
                </button>
            </div>
        </div>
        
        <div id="calculation_result" class="yenolx-calculation-result" style="display: none;">
            <!-- Results will be displayed here -->
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#calculate_price').on('click', function() {
        var country_id = $('#calc_country').val();
        var weight_kg = $('#calc_weight').val();
        var sl_delivery = $('#calc_sl_delivery').is(':checked');
        var coupon_code = $('#calc_coupon').val();
        
        if (!country_id || !weight_kg) {
            alert('<?php _e('Please select country and enter weight.', 'yenolx-cargo'); ?>');
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yenolx_calculate_price',
                nonce: '<?php echo wp_create_nonce('yenolx-cargo-nonce'); ?>',
                country_id: country_id,
                weight_kg: weight_kg,
                sl_delivery: sl_delivery,
                coupon_code: coupon_code
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var result_html = '';
                    
                    result_html += '<h3><?php _e('Price Breakdown', 'yenolx-cargo'); ?></h3>';
                    result_html += '<table class="form-table">';
                    result_html += '<tr><th><?php _e('Country to Milan:', 'yenolx-cargo'); ?></th><td>' + data.currency_symbol + data.country_to_milan.toFixed(2) + '</td></tr>';
                    result_html += '<tr><th><?php _e('Milan to Sri Lanka:', 'yenolx-cargo'); ?></th><td>' + data.currency_symbol + data.milan_to_sl.toFixed(2) + '</td></tr>';
                    
                    if (data.sl_delivery_cost > 0) {
                        result_html += '<tr><th><?php _e('Sri Lanka Delivery:', 'yenolx-cargo'); ?></th><td>' + data.currency_symbol + data.sl_delivery_cost.toFixed(2) + '</td></tr>';
                    }
                    
                    result_html += '<tr><th><?php _e('Subtotal:', 'yenolx-cargo'); ?></th><td>' + data.currency_symbol + data.subtotal.toFixed(2) + '</td></tr>';
                    
                    if (data.discount > 0) {
                        result_html += '<tr><th><?php _e('Discount:', 'yenolx-cargo'); ?></th><td>-' + data.currency_symbol + data.discount.toFixed(2) + '</td></tr>';
                    }
                    
                    result_html += '<tr><th><strong><?php _e('Total:', 'yenolx-cargo'); ?></strong></th><td><strong>' + data.currency_symbol + data.total.toFixed(2) + '</strong></td></tr>';
                    result_html += '</table>';
                    
                    result_html += '<div class="yenolx-effective-cost">';
                    result_html += '<h4><?php _e('Effective Cost per kg', 'yenolx-cargo'); ?></h4>';
                    result_html += '<div class="yenolx-big-number">' + data.currency_symbol + data.effective_cost_per_kg.toFixed(2) + '</div>';
                    result_html += '</div>';
                    
                    if (data.delivery_time_ranges.length > 0) {
                        result_html += '<div class="yenolx-delivery-time">';
                        result_html += '<h4><?php _e('Estimated Delivery Time', 'yenolx-cargo'); ?></h4>';
                        result_html += '<ul>';
                        $.each(data.delivery_time_ranges, function(index, range) {
                            result_html += '<li>' + range + '</li>';
                        });
                        result_html += '</ul>';
                        result_html += '</div>';
                    }
                    
                    if (data.coupon_message) {
                        result_html += '<div class="notice notice-' + (data.discount > 0 ? 'success' : 'warning') + '">';
                        result_html += '<p>' + data.coupon_message + '</p>';
                        result_html += '</div>';
                    }
                    
                    $('#calculation_result').html(result_html).show();
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