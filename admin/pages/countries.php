<?php
/**
 * Countries admin page for Yenolx Cargo Service
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'admin/admin-functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['yenolx_add_country'])) {
        // Add new country
        $result = yenolx_add_country($_POST);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Country added successfully.', 'yenolx-cargo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to add country.', 'yenolx-cargo') . '</p></div>';
        }
    } elseif (isset($_POST['yenolx_edit_country'])) {
        // Edit country
        $country_id = intval($_POST['country_id']);
        $result = yenolx_update_country($country_id, $_POST);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Country updated successfully.', 'yenolx-cargo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to update country.', 'yenolx-cargo') . '</p></div>';
        }
    } elseif (isset($_POST['yenolx_delete_country'])) {
        // Delete country
        $country_id = intval($_POST['country_id']);
        $result = yenolx_delete_country($country_id);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Country deleted successfully.', 'yenolx-cargo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to delete country. It may have existing orders.', 'yenolx-cargo') . '</p></div>';
        }
    } elseif (isset($_POST['yenolx_add_pricing'])) {
        // Add pricing
        $country_id = intval($_POST['country_id']);
        $weight_kg = floatval($_POST['weight_kg']);
        $price_eur = floatval($_POST['price_eur']);
        
        $result = yenolx_add_pricing($country_id, $weight_kg, $price_eur);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Pricing added successfully.', 'yenolx-cargo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to add pricing. It may already exist.', 'yenolx-cargo') . '</p></div>';
        }
    } elseif (isset($_POST['yenolx_update_pricing'])) {
        // Update pricing
        $pricing_id = intval($_POST['pricing_id']);
        $price_eur = floatval($_POST['price_eur']);
        
        $result = yenolx_update_pricing($pricing_id, $price_eur);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Pricing updated successfully.', 'yenolx-cargo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to update pricing.', 'yenolx-cargo') . '</p></div>';
        }
    } elseif (isset($_POST['yenolx_delete_pricing'])) {
        // Delete pricing
        $pricing_id = intval($_POST['pricing_id']);
        $result = yenolx_delete_pricing($pricing_id);
        
        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Pricing deleted successfully.', 'yenolx-cargo') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to delete pricing.', 'yenolx-cargo') . '</p></div>';
        }
    }
}

// Get countries
$countries = yenolx_get_countries();

// Get editing country if any
$editing_country = null;
if (isset($_GET['edit_country'])) {
    $editing_country = yenolx_get_country(intval($_GET['edit_country']));
}

// Get country for pricing if any
$pricing_country = null;
if (isset($_GET['pricing_country'])) {
    $pricing_country = yenolx_get_country(intval($_GET['pricing_country']));
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if ($editing_country): ?>
        <!-- Edit Country Form -->
        <div class="yenolx-form-container">
            <h2><?php _e('Edit Country', 'yenolx-cargo'); ?></h2>
            <form method="post" action="">
                <input type="hidden" name="country_id" value="<?php echo esc_attr($editing_country->id); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Country Name (English)', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="name_en" value="<?php echo esc_attr($editing_country->name_en); ?>" class="regular-text" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Country Name (Sinhala)', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="name_si" value="<?php echo esc_attr($editing_country->name_si); ?>" class="regular-text" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Country Name (Tamil)', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="name_ta" value="<?php echo esc_attr($editing_country->name_ta); ?>" class="regular-text" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Delivery Time Range 1', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="delivery_time_range_1" value="<?php echo esc_attr($editing_country->delivery_time_range_1); ?>" class="regular-text">
                            <p class="description"><?php _e('e.g., "Germany to Milan: 7-10 days"', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Delivery Time Range 2', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="delivery_time_range_2" value="<?php echo esc_attr($editing_country->delivery_time_range_2); ?>" class="regular-text">
                            <p class="description"><?php _e('e.g., "Milan to Sri Lanka: 7-10 days"', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Delivery Time Range 3', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="delivery_time_range_3" value="<?php echo esc_attr($editing_country->delivery_time_range_3); ?>" class="regular-text">
                            <p class="description"><?php _e('e.g., "Office to Home: 7-10 days"', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Status', 'yenolx-cargo'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="status" value="1" <?php checked($editing_country->status, 1); ?>>
                                <?php _e('Active', 'yenolx-cargo'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="yenolx_edit_country" class="button button-primary" value="<?php _e('Update Country', 'yenolx-cargo'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=yenolx-countries'); ?>" class="button"><?php _e('Cancel', 'yenolx-cargo'); ?></a>
                </p>
            </form>
        </div>
        
    <?php elseif ($pricing_country): ?>
        <!-- Pricing Management -->
        <div class="yenolx-form-container">
            <h2><?php printf(__('Pricing for %s', 'yenolx-cargo'), esc_html($pricing_country->name_en)); ?></h2>
            
            <!-- Add Pricing Form -->
            <h3><?php _e('Add New Pricing', 'yenolx-cargo'); ?></h3>
            <form method="post" action="">
                <input type="hidden" name="country_id" value="<?php echo esc_attr($pricing_country->id); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Weight (kg)', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="number" name="weight_kg" step="0.1" min="0" class="regular-text" required>
                            <p class="description"><?php _e('Maximum weight for this price tier.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Price (EUR)', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="number" name="price_eur" step="0.01" min="0" class="regular-text" required>
                            <p class="description"><?php _e('Fixed price for shipments up to this weight.', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="yenolx_add_pricing" class="button button-primary" value="<?php _e('Add Pricing', 'yenolx-cargo'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=yenolx-countries'); ?>" class="button"><?php _e('Back to Countries', 'yenolx-cargo'); ?></a>
                </p>
            </form>
            
            <!-- Existing Pricing -->
            <h3><?php _e('Existing Pricing', 'yenolx-cargo'); ?></h3>
            <?php $pricing = yenolx_get_pricing($pricing_country->id); ?>
            
            <?php if (!empty($pricing)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Weight (kg)', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Price (EUR)', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Actions', 'yenolx-cargo'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pricing as $price): ?>
                            <tr>
                                <td><?php echo esc_html($price->weight_kg); ?></td>
                                <td><?php echo esc_html($price->price_eur); ?></td>
                                <td>
                                    <form method="post" action="" style="display: inline;">
                                        <input type="hidden" name="pricing_id" value="<?php echo esc_attr($price->id); ?>">
                                        <input type="number" name="price_eur" step="0.01" min="0" value="<?php echo esc_attr($price->price_eur); ?>" style="width: 80px;">
                                        <input type="submit" name="yenolx_update_pricing" class="button button-small" value="<?php _e('Update', 'yenolx-cargo'); ?>">
                                    </form>
                                    <form method="post" action="" style="display: inline;" onsubmit="return confirm('<?php _e('Are you sure you want to delete this pricing?', 'yenolx-cargo'); ?>');">
                                        <input type="hidden" name="pricing_id" value="<?php echo esc_attr($price->id); ?>">
                                        <input type="submit" name="yenolx_delete_pricing" class="button button-small" value="<?php _e('Delete', 'yenolx-cargo'); ?>">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No pricing found for this country.', 'yenolx-cargo'); ?></p>
            <?php endif; ?>
        </div>
        
    <?php else: ?>
        <!-- Countries List -->
        <div class="yenolx-form-container">
            <h2><?php _e('Add New Country', 'yenolx-cargo'); ?></h2>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Country Name (English)', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="name_en" class="regular-text" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Country Name (Sinhala)', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="name_si" class="regular-text" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Country Name (Tamil)', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="name_ta" class="regular-text" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Delivery Time Range 1', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="delivery_time_range_1" class="regular-text">
                            <p class="description"><?php _e('e.g., "Germany to Milan: 7-10 days"', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Delivery Time Range 2', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="delivery_time_range_2" class="regular-text">
                            <p class="description"><?php _e('e.g., "Milan to Sri Lanka: 7-10 days"', 'yenolx-cargo'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Delivery Time Range 3', 'yenolx-cargo'); ?></th>
                        <td>
                            <input type="text" name="delivery_time_range_3" class="regular-text">
                            <p class="description"><?php _e('e.g., "Office to Home: 7-10 days"', 'yenolx-cargo'); ?></p>
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
                    <input type="submit" name="yenolx_add_country" class="button button-primary" value="<?php _e('Add Country', 'yenolx-cargo'); ?>">
                </p>
            </form>
        </div>
        
        <!-- Countries Table -->
        <div class="yenolx-table-container">
            <h2><?php _e('Countries', 'yenolx-cargo'); ?></h2>
            
            <?php if (!empty($countries)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Country Name', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Delivery Time Ranges', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Status', 'yenolx-cargo'); ?></th>
                            <th><?php _e('Actions', 'yenolx-cargo'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($countries as $country): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($country->name_en); ?></strong><br>
                                    <small><?php echo esc_html($country->name_si); ?> / <?php echo esc_html($country->name_ta); ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($country->delivery_time_range_1)): ?>
                                        <div><?php echo esc_html($country->delivery_time_range_1); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($country->delivery_time_range_2)): ?>
                                        <div><?php echo esc_html($country->delivery_time_range_2); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($country->delivery_time_range_3)): ?>
                                        <div><?php echo esc_html($country->delivery_time_range_3); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $country->status ? '<span class="status-active">' . __('Active', 'yenolx-cargo') . '</span>' : '<span class="status-inactive">' . __('Inactive', 'yenolx-cargo') . '</span>'; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=yenolx-countries&edit_country=' . $country->id); ?>" class="button button-small"><?php _e('Edit', 'yenolx-cargo'); ?></a>
                                    <a href="<?php echo admin_url('admin.php?page=yenolx-countries&pricing_country=' . $country->id); ?>" class="button button-small"><?php _e('Pricing', 'yenolx-cargo'); ?></a>
                                    <form method="post" action="" style="display: inline;" onsubmit="return confirm('<?php _e('Are you sure you want to delete this country?', 'yenolx-cargo'); ?>');">
                                        <input type="hidden" name="country_id" value="<?php echo esc_attr($country->id); ?>">
                                        <input type="submit" name="yenolx_delete_country" class="button button-small" value="<?php _e('Delete', 'yenolx-cargo'); ?>">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No countries found.', 'yenolx-cargo'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>