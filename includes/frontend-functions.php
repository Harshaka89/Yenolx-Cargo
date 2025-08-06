<?php
/**
 * Frontend functions for Yenolx Cargo Service
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'includes/utility-functions.php';

/**
 * Get countries for frontend
 */
function yenolx_frontend_get_countries() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_countries';
    $countries = $wpdb->get_results("SELECT id, name_en, name_si, name_ta FROM $table_name WHERE status = 1 ORDER BY name_en ASC");
    
    return $countries;
}

/**
 * Get country name by ID and language
 */
function yenolx_get_country_name($country_id, $lang = 'en') {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yenolx_countries';
    $country = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $country_id));
    
    if (!$country) {
        return '';
    }
    
    switch ($lang) {
        case 'si':
            return $country->name_si;
        case 'ta':
            return $country->name_ta;
        default:
            return $country->name_en;
    }
}

/**
 * Get weight options
 */
function yenolx_get_weight_options() {
    return array(
        '5' => '5 kg',
        '10' => '10 kg',
        '15' => '15 kg',
        '20' => '20 kg',
        '25' => '25 kg',
        '30' => '30 kg',
        '35' => '35 kg',
        '40' => '40 kg',
        '45' => '45 kg',
        '50' => '50 kg',
    );
}

/**
 * Get order statuses
 */
function yenolx_get_order_statuses() {
    return array(
        'Order Confirmed' => __('Order Confirmed', 'yenolx-cargo'),
        'Ready for Pickup' => __('Ready for Pickup', 'yenolx-cargo'),
        'Picked Up' => __('Picked Up', 'yenolx-cargo'),
        'In Transit to Italy' => __('In Transit to Italy', 'yenolx-cargo'),
        'In Transit to Sri Lanka' => __('In Transit to Sri Lanka', 'yenolx-cargo'),
        'At Sri Lanka Office' => __('At Sri Lanka Office', 'yenolx-cargo'),
        'In Transit to Home' => __('In Transit to Home', 'yenolx-cargo'),
        'Delivered' => __('Delivered', 'yenolx-cargo'),
    );
}

/**
 * Format price
 */
function yenolx_format_price($price) {
    $currency_symbol = get_option('yenolx_currency_symbol', '€');
    return $currency_symbol . number_format($price, 2);
}

/**
 * Get current language
 */
function yenolx_get_current_language() {
    // This is a simple implementation. In a real multilingual setup,
    // you would integrate with WPML, TranslatePress, or similar.
    if (isset($_GET['lang'])) {
        return sanitize_text_field($_GET['lang']);
    }
    
    // Default to English
    return 'en';
}

/**
 * Language switcher
 */
function yenolx_language_switcher() {
    $current_lang = yenolx_get_current_language();
    $current_url = remove_query_arg('lang');
    
    $languages = array(
        'en' => __('English', 'yenolx-cargo'),
        'si' => __('සිංහල', 'yenolx-cargo'),
        'ta' => __('தமிழ்', 'yenolx-cargo'),
    );
    
    echo '<div class="yenolx-language-switcher">';
    foreach ($languages as $code => $name) {
        $active = $current_lang === $code ? 'active' : '';
        echo '<a href="' . add_query_arg('lang', $code, $current_url) . '" class="' . esc_attr($active) . '">' . esc_html($name) . '</a>';
    }
    echo '</div>';
}

/**
 * Translate text based on current language
 */
function yenolx_translate($en_text, $si_text = '', $ta_text = '') {
    $current_lang = yenolx_get_current_language();
    
    switch ($current_lang) {
        case 'si':
            return !empty($si_text) ? $si_text : $en_text;
        case 'ta':
            return !empty($ta_text) ? $ta_text : $en_text;
        default:
            return $en_text;
    }
}

/**
 * Get translated text by key
 */
function yenolx_get_translated_text($key) {
    $translations = array(
        'select_country' => array(
            'en' => 'Select Country',
            'si' => 'රට තෝරන්න',
            'ta' => 'நாட்டைத் தேர்ந்தெடுக்கவும்',
        ),
        'select_weight' => array(
            'en' => 'Select Package Weight',
            'si' => 'පැකේජයේ බර තෝරන්න',
            'ta' => 'தொகுப்பின் எடையைத் தேர்ந்தெடுக்கவும்',
        ),
        'deliver_to_home' => array(
            'en' => 'Deliver to home in Sri Lanka',
            'si' => 'ශ්‍රී ලංකාවේ නිවසට බෙදා හරින්න',
            'ta' => 'இலங்கையில் வீட்டிற்கு வழங்கவும்',
        ),
        'coupon_code' => array(
            'en' => 'Coupon Code (optional)',
            'si' => 'කූපන් කේතය (අත්‍යවශ්‍ය නොවේ)',
            'ta' => 'கூப்பன் குறியீடு (விரும்பினால்)',
        ),
        'calculate_price' => array(
            'en' => 'Calculate Price',
            'si' => 'මිල ගණන් කරන්න',
            'ta' => 'விலையைக் கணக்கிடுங்கள்',
        ),
        'next_step' => array(
            'en' => 'Next Step',
            'si' => 'ඊළඟ පියවර',
            'ta' => 'அடுத்த படி',
        ),
        'sender_details' => array(
            'en' => 'Sender Details',
            'si' => 'එවන්නාගේ විස්තර',
            'ta' => 'அனுப்புபவரின் விவரங்கள்',
        ),
        'receiver_details' => array(
            'en' => 'Receiver Details',
            'si' => 'ලබන්නාගේ විස්තර',
            'ta' => 'பெறுபவரின் விவரங்கள்',
        ),
        'first_name' => array(
            'en' => 'First Name',
            'si' => 'මුල් නම',
            'ta' => 'முதல் பெயர்',
        ),
        'last_name' => array(
            'en' => 'Last Name',
            'si' => 'අවසාන නම',
            'ta' => 'கடைசி பெயர்',
        ),
        'email' => array(
            'en' => 'Email',
            'si' => 'විද්‍යුත් තැපෑල',
            'ta' => 'மின்னஞ்சல்',
        ),
        'phone' => array(
            'en' => 'Phone',
            'si' => 'දුරකථන',
            'ta' => 'தொலைபேசி',
        ),
        'address' => array(
            'en' => 'Address',
            'si' => 'ලිපිනය',
            'ta' => 'முகவரி',
        ),
        'city' => array(
            'en' => 'City',
            'si' => 'නගරය',
            'ta' => 'நகரம்',
        ),
        'postal_code' => array(
            'en' => 'Postal Code',
            'si' => 'තැපැල් කේතය',
            'ta' => 'அஞ்சல் குறியீடு',
        ),
        'country' => array(
            'en' => 'Country',
            'si' => 'රට',
            'ta' => 'நாடு',
        ),
        'submit_order' => array(
            'en' => 'Submit Order',
            'si' => 'ඇණවුම ඉදිරිපත් කරන්න',
            'ta' => 'ஆர்டரைச் சமர்ப்பிக்கவும்',
        ),
        'tracking_id' => array(
            'en' => 'Tracking ID',
            'si' => 'ලුහුබැඳීමේ හැඳුනුම්පත',
            'ta' => 'கண்காணிப்பு ஐடி',
        ),
        'track_order' => array(
            'en' => 'Track Order',
            'si' => 'ඇණවුම ලුහුබැඳීම',
            'ta' => 'ஆர்டரைக் கண்காணிக்கவும்',
        ),
        'current_status' => array(
            'en' => 'Current Status',
            'si' => 'වත්මන් තත්ත්වය',
            'ta' => 'தற்போதைய நிலை',
        ),
        'tracking_history' => array(
            'en' => 'Tracking History',
            'si' => 'ලුහුබැඳීමේ ඉතිහාසය',
            'ta' => 'கண்காணிப்பு வரலாறு',
        ),
        'special_notes' => array(
            'en' => 'Special Notes',
            'si' => 'විශේෂ සටහන්',
            'ta' => 'சிறப்பு குறிப்புகள்',
        ),
    );
    
    $current_lang = yenolx_get_current_language();
    
    if (isset($translations[$key][$current_lang])) {
        return $translations[$key][$current_lang];
    }
    
    return isset($translations[$key]['en']) ? $translations[$key]['en'] : $key;
}

/**
 * Display order form step 1
 */
function yenolx_display_order_form_step_1() {
    $countries = yenolx_frontend_get_countries();
    $weight_options = yenolx_get_weight_options();
    $current_lang = yenolx_get_current_language();
    
    ?>
    <div class="yenolx-order-form-step yenolx-step-1">
        <h2><?php echo yenolx_get_translated_text('select_country'); ?></h2>
        
        <div class="yenolx-form-group">
            <select id="order_country" name="country" class="yenolx-select" required>
                <option value=""><?php echo yenolx_get_translated_text('select_country'); ?></option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo esc_attr($country->id); ?>">
                        <?php 
                        switch ($current_lang) {
                            case 'si':
                                echo esc_html($country->name_si);
                                break;
                            case 'ta':
                                echo esc_html($country->name_ta);
                                break;
                            default:
                                echo esc_html($country->name_en);
                        }
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <h2><?php echo yenolx_get_translated_text('select_weight'); ?></h2>
        
        <div class="yenolx-form-group">
            <select id="order_weight" name="weight" class="yenolx-select" required>
                <option value=""><?php echo yenolx_get_translated_text('select_weight'); ?></option>
                <?php foreach ($weight_options as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="yenolx-form-group">
            <label>
                <input type="checkbox" id="order_sl_delivery" name="sl_delivery">
                <?php echo yenolx_get_translated_text('deliver_to_home'); ?>
            </label>
        </div>
        
        <div class="yenolx-form-group">
            <input type="text" id="order_coupon" name="coupon_code" placeholder="<?php echo yenolx_get_translated_text('coupon_code'); ?>">
        </div>
        
        <div class="yenolx-form-group">
            <button type="button" id="calculate_order_price" class="yenolx-button yenolx-button-primary">
                <?php echo yenolx_get_translated_text('calculate_price'); ?>
            </button>
        </div>
        
        <div id="order_price_result" class="yenolx-price-result" style="display: none;">
            <!-- Price calculation results will be displayed here -->
        </div>
    </div>
    <?php
}

/**
 * Display order form step 2
 */
function yenolx_display_order_form_step_2() {
    $current_lang = yenolx_get_current_language();
    
    ?>
    <div class="yenolx-order-form-step yenolx-step-2" style="display: none;">
        <h2><?php echo yenolx_get_translated_text('sender_details'); ?></h2>
        
        <div class="yenolx-form-row">
            <div class="yenolx-form-group">
                <label for="sender_first_name"><?php echo yenolx_get_translated_text('first_name'); ?> *</label>
                <input type="text" id="sender_first_name" name="sender_first_name" required>
            </div>
            
            <div class="yenolx-form-group">
                <label for="sender_last_name"><?php echo yenolx_get_translated_text('last_name'); ?> *</label>
                <input type="text" id="sender_last_name" name="sender_last_name" required>
            </div>
        </div>
        
        <div class="yenolx-form-group">
            <label for="sender_email"><?php echo yenolx_get_translated_text('email'); ?> *</label>
            <input type="email" id="sender_email" name="sender_email" required>
        </div>
        
        <div class="yenolx-form-group">
            <label for="sender_phone"><?php echo yenolx_get_translated_text('phone'); ?> *</label>
            <input type="tel" id="sender_phone" name="sender_phone" required>
        </div>
        
        <div class="yenolx-form-group">
            <label for="sender_address"><?php echo yenolx_get_translated_text('address'); ?> *</label>
            <textarea id="sender_address" name="sender_address" rows="3" required></textarea>
        </div>
        
        <div class="yenolx-form-row">
            <div class="yenolx-form-group">
                <label for="sender_city"><?php echo yenolx_get_translated_text('city'); ?> *</label>
                <input type="text" id="sender_city" name="sender_city" required>
            </div>
            
            <div class="yenolx-form-group">
                <label for="sender_postal_code"><?php echo yenolx_get_translated_text('postal_code'); ?> *</label>
                <input type="text" id="sender_postal_code" name="sender_postal_code" required>
            </div>
        </div>
        
        <div class="yenolx-form-group">
            <label for="sender_country"><?php echo yenolx_get_translated_text('country'); ?> *</label>
            <input type="text" id="sender_country" name="sender_country" required>
        </div>
        
        <h2><?php echo yenolx_get_translated_text('receiver_details'); ?></h2>
        
        <div class="yenolx-form-row">
            <div class="yenolx-form-group">
                <label for="receiver_first_name"><?php echo yenolx_get_translated_text('first_name'); ?> *</label>
                <input type="text" id="receiver_first_name" name="receiver_first_name" required>
            </div>
            
            <div class="yenolx-form-group">
                <label for="receiver_last_name"><?php echo yenolx_get_translated_text('last_name'); ?> *</label>
                <input type="text" id="receiver_last_name" name="receiver_last_name" required>
            </div>
        </div>
        
        <div class="yenolx-form-group">
            <label for="receiver_phone"><?php echo yenolx_get_translated_text('phone'); ?> *</label>
            <input type="tel" id="receiver_phone" name="receiver_phone" required>
        </div>
        
        <div class="yenolx-form-group">
            <label for="receiver_address"><?php echo yenolx_get_translated_text('address'); ?> *</label>
            <textarea id="receiver_address" name="receiver_address" rows="3" required></textarea>
        </div>
        
        <div class="yenolx-form-row">
            <div class="yenolx-form-group">
                <label for="receiver_city"><?php echo yenolx_get_translated_text('city'); ?> *</label>
                <input type="text" id="receiver_city" name="receiver_city" required>
            </div>
            
            <div class="yenolx-form-group">
                <label for="receiver_postal_code"><?php echo yenolx_get_translated_text('postal_code'); ?> *</label>
                <input type="text" id="receiver_postal_code" name="receiver_postal_code" required>
            </div>
        </div>
        
        <div class="yenolx-form-group">
            <label>
                <input type="checkbox" id="accept_terms" name="accept_terms" required>
                <?php _e('I accept the terms and conditions', 'yenolx-cargo'); ?> *
            </label>
        </div>
        
        <div class="yenolx-form-group">
            <button type="button" id="back_to_step_1" class="yenolx-button"><?php _e('Back', 'yenolx-cargo'); ?></button>
            <button type="button" id="submit_order" class="yenolx-button yenolx-button-primary">
                <?php echo yenolx_get_translated_text('submit_order'); ?>
            </button>
        </div>
    </div>
    <?php
}

/**
 * Display tracking form
 */
function yenolx_display_tracking_form() {
    ?>
    <div class="yenolx-tracking-form">
        <h2><?php echo yenolx_get_translated_text('track_order'); ?></h2>
        
        <div class="yenolx-form-group">
            <input type="text" id="tracking_id_input" placeholder="<?php echo yenolx_get_translated_text('tracking_id'); ?>">
        </div>
        
        <div class="yenolx-form-group">
            <button type="button" id="track_order_button" class="yenolx-button yenolx-button-primary">
                <?php echo yenolx_get_translated_text('track_order'); ?>
            </button>
        </div>
        
        <div id="tracking_result" class="yenolx-tracking-result" style="display: none;">
            <!-- Tracking results will be displayed here -->
        </div>
    </div>
    <?php
}

/**
 * Display price calculator
 */
function yenolx_display_price_calculator() {
    $countries = yenolx_frontend_get_countries();
    $weight_options = yenolx_get_weight_options();
    $current_lang = yenolx_get_current_language();
    
    ?>
    <div class="yenolx-price-calculator">
        <h2><?php _e('Price Calculator', 'yenolx-cargo'); ?></h2>
        
        <div class="yenolx-form-group">
            <label for="calc_country"><?php echo yenolx_get_translated_text('select_country'); ?></label>
            <select id="calc_country" name="country" class="yenolx-select">
                <option value=""><?php echo yenolx_get_translated_text('select_country'); ?></option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo esc_attr($country->id); ?>">
                        <?php 
                        switch ($current_lang) {
                            case 'si':
                                echo esc_html($country->name_si);
                                break;
                            case 'ta':
                                echo esc_html($country->name_ta);
                                break;
                            default:
                                echo esc_html($country->name_en);
                        }
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="yenolx-form-group">
            <label for="calc_weight"><?php echo yenolx_get_translated_text('select_weight'); ?></label>
            <select id="calc_weight" name="weight" class="yenolx-select">
                <option value=""><?php echo yenolx_get_translated_text('select_weight'); ?></option>
                <?php foreach ($weight_options as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="yenolx-form-group">
            <label>
                <input type="checkbox" id="calc_sl_delivery" name="sl_delivery">
                <?php echo yenolx_get_translated_text('deliver_to_home'); ?>
            </label>
        </div>
        
        <div class="yenolx-form-group">
            <input type="text" id="calc_coupon" name="coupon_code" placeholder="<?php echo yenolx_get_translated_text('coupon_code'); ?>">
        </div>
        
        <div class="yenolx-form-group">
            <button type="button" id="calculate_price_button" class="yenolx-button yenolx-button-primary">
                <?php echo yenolx_get_translated_text('calculate_price'); ?>
            </button>
        </div>
        
        <div id="calc_price_result" class="yenolx-price-result" style="display: none;">
            <!-- Price calculation results will be displayed here -->
        </div>
    </div>
    <?php
}