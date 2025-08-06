<?php
/**
 * Plugin Name: Yenolx Cargo Service
 * Plugin URI: https://yenolx.com/
 * Description: A complete cargo service management system for shipments from EU countries to Sri Lanka
 * Version: 1.5.0
 * Author: Yenolx Team
 * Author URI: https://yenolx.com/
 * Text Domain: yenolx-cargo
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('YENOLX_CARGO_VERSION', '1.5.0');
define('YENOLX_CARGO_PATH', plugin_dir_path(__FILE__));
define('YENOLX_CARGO_URL', plugin_dir_url(__FILE__));
define('YENOLX_CARGO_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Yenolx_Cargo_Service {

    /**
     * Constructor
     */
    public function __construct() {
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize plugin components
        add_action('plugins_loaded', array($this, 'init'));
        
        // Register text domain for translation
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create required tables
        $this->create_tables();
        
        // Register custom post types
        $this->register_post_types();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default options
        $this->set_default_options();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Initialize plugin components
     */
    public function init() {
        // Include required files
        $this->includes();
        
        // Register custom post types
        add_action('init', array($this, 'register_post_types'));
        
        // Register taxonomies
        add_action('init', array($this, 'register_taxonomies'));
        
        // Register shortcodes
        add_action('init', array($this, 'register_shortcodes'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Register AJAX handlers
        add_action('wp_ajax_yenolx_calculate_price', array($this, 'ajax_calculate_price'));
        add_action('wp_ajax_nopriv_yenolx_calculate_price', array($this, 'ajax_calculate_price'));
        add_action('wp_ajax_yenolx_submit_order', array($this, 'ajax_submit_order'));
        add_action('wp_ajax_nopriv_yenolx_submit_order', array($this, 'ajax_submit_order'));
        add_action('wp_ajax_yenolx_track_order', array($this, 'ajax_track_order'));
        add_action('wp_ajax_nopriv_yenolx_track_order', array($this, 'ajax_track_order'));
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('yenolx-cargo', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Include utility functions
        require_once YENOLX_CARGO_PATH . 'includes/utility-functions.php';
        
        // Include email functions
        require_once YENOLX_CARGO_PATH . 'includes/email-functions.php';
        
        // Include admin functions
        if (is_admin()) {
            require_once YENOLX_CARGO_PATH . 'admin/admin-functions.php';
            require_once YENOLX_CARGO_PATH . 'admin/admin-pages.php';
            require_once YENOLX_CARGO_PATH . 'admin/admin-ajax.php';
        }
        
        // Include frontend functions
        require_once YENOLX_CARGO_PATH . 'includes/frontend-functions.php';
        require_once YENOLX_CARGO_PATH . 'includes/shortcodes.php';
    }
    
    /**
     * Create required database tables
     */
    private function create_tables() {
        global $wpdb;
        
        // Make sure dbDelta function is available
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Countries table
        $table_name = $wpdb->prefix . 'yenolx_countries';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name_en varchar(100) NOT NULL,
            name_si varchar(100) NOT NULL,
            name_ta varchar(100) NOT NULL,
            delivery_time_range_1 varchar(100) NOT NULL,
            delivery_time_range_2 varchar(100) NOT NULL,
            delivery_time_range_3 varchar(100) NOT NULL,
            status tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Pricing table
        $table_name = $wpdb->prefix . 'yenolx_pricing';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            country_id mediumint(9) NOT NULL,
            weight_kg decimal(5,2) NOT NULL,
            price_eur decimal(10,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY country_id (country_id)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Orders table
        $table_name = $wpdb->prefix . 'yenolx_orders';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            tracking_id varchar(50) NOT NULL,
            country_id mediumint(9) NOT NULL,
            weight_kg decimal(5,2) NOT NULL,
            price_eur decimal(10,2) NOT NULL,
            sl_delivery tinyint(1) NOT NULL DEFAULT 0,
            coupon_code varchar(50) DEFAULT NULL,
            discount_eur decimal(10,2) DEFAULT 0,
            final_price_eur decimal(10,2) NOT NULL,
            sender_name varchar(100) NOT NULL,
            sender_email varchar(100) NOT NULL,
            sender_phone varchar(20) NOT NULL,
            sender_address text NOT NULL,
            sender_city varchar(100) NOT NULL,
            sender_postal_code varchar(20) NOT NULL,
            sender_country varchar(100) NOT NULL,
            receiver_name varchar(100) NOT NULL,
            receiver_phone varchar(20) NOT NULL,
            receiver_address text NOT NULL,
            receiver_city varchar(100) NOT NULL,
            receiver_postal_code varchar(20) NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'Order Confirmed',
            special_notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY tracking_id (tracking_id),
            KEY country_id (country_id),
            KEY status (status)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Order tracking history table
        $table_name = $wpdb->prefix . 'yenolx_order_tracking';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            order_id mediumint(9) NOT NULL,
            status varchar(50) NOT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY order_id (order_id)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Coupon codes table
        $table_name = $wpdb->prefix . 'yenolx_coupons';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            code varchar(50) NOT NULL,
            discount_type varchar(20) NOT NULL DEFAULT 'fixed',
            discount_value decimal(10,2) NOT NULL,
            min_order_value decimal(10,2) DEFAULT 0,
            max_uses int(11) DEFAULT NULL,
            used_count int(11) DEFAULT 0,
            start_date date DEFAULT NULL,
            end_date date DEFAULT NULL,
            status tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code)
        ) $charset_collate;";
        
        dbDelta($sql);
        
        // Reviews table
        $table_name = $wpdb->prefix . 'yenolx_reviews';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            order_id mediumint(9) NOT NULL,
            rating tinyint(1) NOT NULL,
            review_text text DEFAULT NULL,
            customer_name varchar(100) DEFAULT NULL,
            status tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY order_id (order_id)
        ) $charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Set default options
     */
    private function set_default_options() {
        // Set default settings
        $default_options = array(
            'milan_to_sl_rate' => 3.50,
            'sl_delivery_rate' => 1.00,
            'currency_symbol' => '€',
            'email_from_name' => get_bloginfo('name'),
            'email_from_address' => get_option('admin_email'),
            'tracking_id_prefix' => 'YCS',
            'tracking_id_length' => 10,
        );
        
        foreach ($default_options as $option_name => $option_value) {
            if (get_option('yenolx_' . $option_name) === false) {
                update_option('yenolx_' . $option_name, $option_value);
            }
        }
    }
    
    /**
     * Register custom post types
     */
    public function register_post_types() {
        // Country post type
        $labels = array(
            'name' => __('Countries', 'yenolx-cargo'),
            'singular_name' => __('Country', 'yenolx-cargo'),
            'menu_name' => __('Countries', 'yenolx-cargo'),
            'name_admin_bar' => __('Country', 'yenolx-cargo'),
            'add_new' => __('Add New', 'yenolx-cargo'),
            'add_new_item' => __('Add New Country', 'yenolx-cargo'),
            'new_item' => __('New Country', 'yenolx-cargo'),
            'edit_item' => __('Edit Country', 'yenolx-cargo'),
            'view_item' => __('View Country', 'yenolx-cargo'),
            'all_items' => __('All Countries', 'yenolx-cargo'),
            'search_items' => __('Search Countries', 'yenolx-cargo'),
            'parent_item_colon' => __('Parent Countries:', 'yenolx-cargo'),
            'not_found' => __('No countries found.', 'yenolx-cargo'),
            'not_found_in_trash' => __('No countries found in Trash.', 'yenolx-cargo'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => 'yenolx-cargo',
            'query_var' => false,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title'),
        );
        
        register_post_type('yenolx_country', $args);
        
        // Order post type
        $labels = array(
            'name' => __('Orders', 'yenolx-cargo'),
            'singular_name' => __('Order', 'yenolx-cargo'),
            'menu_name' => __('Orders', 'yenolx-cargo'),
            'name_admin_bar' => __('Order', 'yenolx-cargo'),
            'add_new' => __('Add New', 'yenolx-cargo'),
            'add_new_item' => __('Add New Order', 'yenolx-cargo'),
            'new_item' => __('New Order', 'yenolx-cargo'),
            'edit_item' => __('Edit Order', 'yenolx-cargo'),
            'view_item' => __('View Order', 'yenolx-cargo'),
            'all_items' => __('All Orders', 'yenolx-cargo'),
            'search_items' => __('Search Orders', 'yenolx-cargo'),
            'parent_item_colon' => __('Parent Orders:', 'yenolx-cargo'),
            'not_found' => __('No orders found.', 'yenolx-cargo'),
            'not_found_in_trash' => __('No orders found in Trash.', 'yenolx-cargo'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => 'yenolx-cargo',
            'query_var' => false,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title'),
        );
        
        register_post_type('yenolx_order', $args);
    }
    
    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // Order status taxonomy
        $labels = array(
            'name' => __('Order Statuses', 'yenolx-cargo'),
            'singular_name' => __('Order Status', 'yenolx-cargo'),
            'search_items' => __('Search Order Statuses', 'yenolx-cargo'),
            'all_items' => __('All Order Statuses', 'yenolx-cargo'),
            'parent_item' => __('Parent Order Status', 'yenolx-cargo'),
            'parent_item_colon' => __('Parent Order Status:', 'yenolx-cargo'),
            'edit_item' => __('Edit Order Status', 'yenolx-cargo'),
            'update_item' => __('Update Order Status', 'yenolx-cargo'),
            'add_new_item' => __('Add New Order Status', 'yenolx-cargo'),
            'new_item_name' => __('New Order Status Name', 'yenolx-cargo'),
            'menu_name' => __('Order Status', 'yenolx-cargo'),
        );
        
        $args = array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => false,
            'rewrite' => false,
        );
        
        register_taxonomy('yenolx_order_status', array('yenolx_order'), $args);
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('yenolx_order_form', array($this, 'order_form_shortcode'));
        add_shortcode('yenolx_tracking_form', array($this, 'tracking_form_shortcode'));
        add_shortcode('yenolx_price_calculator', array($this, 'price_calculator_shortcode'));
    }
    
    /**
     * Order form shortcode
     */
    public function order_form_shortcode($atts) {
        ob_start();
        include YENOLX_CARGO_PATH . 'templates/order-form.php';
        return ob_get_clean();
    }
    
    /**
     * Tracking form shortcode
     */
    public function tracking_form_shortcode($atts) {
        ob_start();
        include YENOLX_CARGO_PATH . 'templates/tracking-form.php';
        return ob_get_clean();
    }
    
    /**
     * Price calculator shortcode
     */
    public function price_calculator_shortcode($atts) {
        ob_start();
        include YENOLX_CARGO_PATH . 'templates/price-calculator.php';
        return ob_get_clean();
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        // Enqueue styles
        wp_enqueue_style('yenolx-cargo-frontend', YENOLX_CARGO_URL . 'assets/css/frontend.css', array(), YENOLX_CARGO_VERSION);
        
        // Enqueue scripts
        wp_enqueue_script('yenolx-cargo-frontend', YENOLX_CARGO_URL . 'assets/js/frontend.js', array('jquery'), YENOLX_CARGO_VERSION, true);
        
        // Localize script
        wp_localize_script('yenolx-cargo-frontend', 'yenolx_cargo_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yenolx-cargo-nonce'),
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'yenolx-cargo') === false) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style('yenolx-cargo-admin', YENOLX_CARGO_URL . 'assets/css/admin.css', array(), YENOLX_CARGO_VERSION);
        
        // Enqueue scripts
        wp_enqueue_script('yenolx-cargo-admin', YENOLX_CARGO_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-datepicker'), YENOLX_CARGO_VERSION, true);
        
        // Localize script
        wp_localize_script('yenolx-cargo-admin', 'yenolx_cargo_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yenolx-cargo-admin-nonce'),
        ));
    }
    
  
    /**
     * AJAX calculate price
     */
    public function ajax_calculate_price() {
        // Verify nonce
        check_ajax_referer('yenolx-cargo-nonce', 'nonce');
        
        // Get and sanitize input
        $country_id = isset($_POST['country_id']) ? intval($_POST['country_id']) : 0;
        $weight_kg = isset($_POST['weight_kg']) ? floatval($_POST['weight_kg']) : 0;
        $sl_delivery = isset($_POST['sl_delivery']) ? (bool) $_POST['sl_delivery'] : false;
        $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';
        
        // Calculate price
        $price_data = $this->calculate_price($country_id, $weight_kg, $sl_delivery, $coupon_code);
        
        // Return JSON response
        wp_send_json_success($price_data);
    }
    
    /**
     * Calculate price
     */
    private function calculate_price($country_id, $weight_kg, $sl_delivery = false, $coupon_code = '') {
        global $wpdb;
        
        // Get country to Milan fixed price
        $table_name = $wpdb->prefix . 'yenolx_pricing';
        $country_price = $wpdb->get_row($wpdb->prepare(
            "SELECT price_eur FROM $table_name WHERE country_id = %d AND weight_kg >= %d ORDER BY weight_kg ASC LIMIT 1",
            $country_id,
            $weight_kg
        ));
        
        $country_to_milan = $country_price ? floatval($country_price->price_eur) : 0;
        
        // Get Milan to Sri Lanka rate
        $milan_to_sl_rate = floatval(get_option('yenolx_milan_to_sl_rate', 3.50));
        $milan_to_sl = $weight_kg * $milan_to_sl_rate;
        
        // Get Sri Lanka delivery rate if selected
        $sl_delivery_cost = 0;
        if ($sl_delivery) {
            $sl_delivery_rate = floatval(get_option('yenolx_sl_delivery_rate', 1.00));
            $sl_delivery_cost = $weight_kg * $sl_delivery_rate;
        }
        
        // Calculate subtotal
        $subtotal = $country_to_milan + $milan_to_sl + $sl_delivery_cost;
        
        // Apply coupon discount if valid
        $discount = 0;
        $coupon_message = '';
        
        if (!empty($coupon_code)) {
            $coupon = $this->validate_coupon($coupon_code, $subtotal);
            
            if ($coupon) {
                if ($coupon->discount_type === 'fixed') {
                    $discount = floatval($coupon->discount_value);
                } elseif ($coupon->discount_type === 'percentage') {
                    $discount = $subtotal * (floatval($coupon->discount_value) / 100);
                }
                
                // Update coupon usage count
                $this->update_coupon_usage($coupon->id);
                
                $coupon_message = sprintf(__('Coupon "%s" applied successfully.', 'yenolx-cargo'), $coupon->code);
            } else {
                $coupon_message = __('Invalid or expired coupon code.', 'yenolx-cargo');
            }
        }
        
        // Calculate total
        $total = max(0, $subtotal - $discount);
        
        // Calculate effective cost per kg
        $effective_cost_per_kg = $weight_kg > 0 ? $total / $weight_kg : 0;
        
        // Get delivery time ranges
        $country = $wpdb->get_row($wpdb->prepare(
            "SELECT delivery_time_range_1, delivery_time_range_2, delivery_time_range_3 FROM {$wpdb->prefix}yenolx_countries WHERE id = %d",
            $country_id
        ));
        
        $delivery_time_ranges = array();
        if ($country) {
            if (!empty($country->delivery_time_range_1)) {
                $delivery_time_ranges[] = $country->delivery_time_range_1;
            }
            if (!empty($country->delivery_time_range_2)) {
                $delivery_time_ranges[] = $country->delivery_time_range_2;
            }
            if (!empty($country->delivery_time_range_3)) {
                $delivery_time_ranges[] = $country->delivery_time_range_3;
            }
        }
        
        // Get currency symbol
        $currency_symbol = get_option('yenolx_currency_symbol', '€');
        
        return array(
            'country_to_milan' => $country_to_milan,
            'milan_to_sl' => $milan_to_sl,
            'sl_delivery_cost' => $sl_delivery_cost,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'effective_cost_per_kg' => $effective_cost_per_kg,
            'delivery_time_ranges' => $delivery_time_ranges,
            'currency_symbol' => $currency_symbol,
            'coupon_message' => $coupon_message,
        );
    }
    
    /**
     * Validate coupon
     */
    private function validate_coupon($coupon_code, $order_total) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'yenolx_coupons';
        $coupon = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE code = %s AND status = 1",
            $coupon_code
        ));
        
        if (!$coupon) {
            return false;
        }
        
        // Check if coupon has expired
        $current_date = current_time('Y-m-d');
        if (!empty($coupon->end_date) && $current_date > $coupon->end_date) {
            return false;
        }
        
        // Check if coupon has started
        if (!empty($coupon->start_date) && $current_date < $coupon->start_date) {
            return false;
        }
        
        // Check minimum order value
        if ($order_total < floatval($coupon->min_order_value)) {
            return false;
        }
        
        // Check maximum uses
        if (!is_null($coupon->max_uses) && intval($coupon->used_count) >= intval($coupon->max_uses)) {
            return false;
        }
        
        return $coupon;
    }
    
    /**
     * Update coupon usage count
     */
    private function update_coupon_usage($coupon_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'yenolx_coupons';
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET used_count = used_count + 1 WHERE id = %d",
            $coupon_id
        ));
    }
    
    /**
     * AJAX submit order
     */
    public function ajax_submit_order() {
        // Verify nonce
        check_ajax_referer('yenolx-cargo-nonce', 'nonce');
        
        // Get and sanitize input
        $country_id = isset($_POST['country_id']) ? intval($_POST['country_id']) : 0;
        $weight_kg = isset($_POST['weight_kg']) ? floatval($_POST['weight_kg']) : 0;
        $sl_delivery = isset($_POST['sl_delivery']) ? (bool) $_POST['sl_delivery'] : false;
        $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';
        
        $sender_name = isset($_POST['sender_name']) ? sanitize_text_field($_POST['sender_name']) : '';
        $sender_email = isset($_POST['sender_email']) ? sanitize_email($_POST['sender_email']) : '';
        $sender_phone = isset($_POST['sender_phone']) ? sanitize_text_field($_POST['sender_phone']) : '';
        $sender_address = isset($_POST['sender_address']) ? sanitize_textarea_field($_POST['sender_address']) : '';
        $sender_city = isset($_POST['sender_city']) ? sanitize_text_field($_POST['sender_city']) : '';
        $sender_postal_code = isset($_POST['sender_postal_code']) ? sanitize_text_field($_POST['sender_postal_code']) : '';
        $sender_country = isset($_POST['sender_country']) ? sanitize_text_field($_POST['sender_country']) : '';
        
        $receiver_name = isset($_POST['receiver_name']) ? sanitize_text_field($_POST['receiver_name']) : '';
        $receiver_phone = isset($_POST['receiver_phone']) ? sanitize_text_field($_POST['receiver_phone']) : '';
        $receiver_address = isset($_POST['receiver_address']) ? sanitize_textarea_field($_POST['receiver_address']) : '';
        $receiver_city = isset($_POST['receiver_city']) ? sanitize_text_field($_POST['receiver_city']) : '';
        $receiver_postal_code = isset($_POST['receiver_postal_code']) ? sanitize_text_field($_POST['receiver_postal_code']) : '';
        
        // Validate required fields
        if (empty($country_id) || empty($weight_kg) || empty($sender_name) || empty($sender_email) || 
            empty($sender_phone) || empty($sender_address) || empty($sender_city) || empty($sender_postal_code) || 
            empty($sender_country) || empty($receiver_name) || empty($receiver_phone) || 
            empty($receiver_address) || empty($receiver_city) || empty($receiver_postal_code)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'yenolx-cargo')));
        }
        
        // Calculate price
        $price_data = $this->calculate_price($country_id, $weight_kg, $sl_delivery, $coupon_code);
        
        // Generate unique tracking ID
        $tracking_id = $this->generate_tracking_id();
        
        // Save order to database
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'yenolx_orders';
        $result = $wpdb->insert(
            $table_name,
            array(
                'tracking_id' => $tracking_id,
                'country_id' => $country_id,
                'weight_kg' => $weight_kg,
                'price_eur' => $price_data['subtotal'],
                'sl_delivery' => $sl_delivery ? 1 : 0,
                'coupon_code' => $coupon_code,
                'discount_eur' => $price_data['discount'],
                'final_price_eur' => $price_data['total'],
                'sender_name' => $sender_name,
                'sender_email' => $sender_email,
                'sender_phone' => $sender_phone,
                'sender_address' => $sender_address,
                'sender_city' => $sender_city,
                'sender_postal_code' => $sender_postal_code,
                'sender_country' => $sender_country,
                'receiver_name' => $receiver_name,
                'receiver_phone' => $receiver_phone,
                'receiver_address' => $receiver_address,
                'receiver_city' => $receiver_city,
                'receiver_postal_code' => $receiver_postal_code,
                'status' => 'Order Confirmed',
            ),
            array('%s', '%d', '%f', '%f', '%d', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to save order. Please try again.', 'yenolx-cargo')));
        }
        
        $order_id = $wpdb->insert_id;
        
        // Add initial tracking entry
        $table_name = $wpdb->prefix . 'yenolx_order_tracking';
        $wpdb->insert(
            $table_name,
            array(
                'order_id' => $order_id,
                'status' => 'Order Confirmed',
                'notes' => __('Order placed successfully.', 'yenolx-cargo'),
            ),
            array('%d', '%s', '%s')
        );
        
        // Send order confirmation email
        $this->send_order_confirmation_email($order_id);
        
        // Return success response
        wp_send_json_success(array(
            'message' => __('Order placed successfully!', 'yenolx-cargo'),
            'tracking_id' => $tracking_id,
            'redirect_url' => add_query_arg('tracking_id', $tracking_id, get_permalink(get_option('yenolx_thank_you_page_id'))),
        ));
    }
    
    /**
     * Generate unique tracking ID
     */
    private function generate_tracking_id() {
        global $wpdb;
        
        $prefix = get_option('yenolx_tracking_id_prefix', 'YCS');
        $length = intval(get_option('yenolx_tracking_id_length', 10));
        $max_attempts = 100;
        $attempt = 0;
        
        while ($attempt < $max_attempts) {
            // Generate random string
            $random_string = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
            $tracking_id = $prefix . $random_string;
            
            // Check if tracking ID already exists
            $table_name = $wpdb->prefix . 'yenolx_orders';
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE tracking_id = %s",
                $tracking_id
            ));
            
            if ($existing == 0) {
                return $tracking_id;
            }
            
            $attempt++;
        }
        
        // If we couldn't generate a unique ID after max attempts, use timestamp
        return $prefix . time();
    }
    
    /**
     * Send order confirmation email
     */
    private function send_order_confirmation_email($order_id) {
        global $wpdb;
        
        // Get order details
        $table_name = $wpdb->prefix . 'yenolx_orders';
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $order_id
        ));
        
        if (!$order) {
            return false;
        }
        
        // Get country details
        $country_table = $wpdb->prefix . 'yenolx_countries';
        $country = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $country_table WHERE id = %d",
            $order->country_id
        ));
        
        // Get email settings
        $from_name = get_option('yenolx_email_from_name', get_bloginfo('name'));
        $from_address = get_option('yenolx_email_from_address', get_option('admin_email'));
        
        // Set email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_address . '>',
        );
        
        // Get email template
        $subject = __('Order Confirmation - ' . $order->tracking_id, 'yenolx-cargo');
        
        // Prepare email content
        ob_start();
        include YENOLX_CARGO_PATH . 'templates/emails/order-confirmation.php';
        $message = ob_get_clean();
        
        // Send email
        wp_mail($order->sender_email, $subject, $message, $headers);
        
        return true;
    }
    
    /**
     * AJAX track order
     */
    public function ajax_track_order() {
        // Verify nonce
        check_ajax_referer('yenolx-cargo-nonce', 'nonce');
        
        // Get and sanitize input
        $tracking_id = isset($_POST['tracking_id']) ? sanitize_text_field($_POST['tracking_id']) : '';
        
        if (empty($tracking_id)) {
            wp_send_json_error(array('message' => __('Please enter a tracking ID.', 'yenolx-cargo')));
        }
        
        // Get order details
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'yenolx_orders';
        $order = $wpdb->get_row($wpdb->prepare(
            "SELECT o.*, c.name_en, c.name_si, c.name_ta FROM $table_name o 
            LEFT JOIN {$wpdb->prefix}yenolx_countries c ON o.country_id = c.id 
            WHERE o.tracking_id = %s",
            $tracking_id
        ));
        
        if (!$order) {
            wp_send_json_error(array('message' => __('Invalid tracking ID. Please check and try again.', 'yenolx-cargo')));
        }
        
        // Get tracking history
        $tracking_table = $wpdb->prefix . 'yenolx_order_tracking';
        $tracking_history = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $tracking_table WHERE order_id = %d ORDER BY created_at ASC",
            $order->id
        ));
        
        // Get currency symbol
        $currency_symbol = get_option('yenolx_currency_symbol', '€');
        
        // Prepare response data
        $order_data = array(
            'tracking_id' => $order->tracking_id,
            'status' => $order->status,
            'weight_kg' => $order->weight_kg,
            'price_eur' => $order->price_eur,
            'discount_eur' => $order->discount_eur,
            'final_price_eur' => $order->final_price_eur,
            'sender_name' => $order->sender_name,
            'sender_email' => $order->sender_email,
            'sender_phone' => $order->sender_phone,
            'sender_address' => $order->sender_address,
            'sender_city' => $order->sender_city,
            'sender_postal_code' => $order->sender_postal_code,
            'sender_country' => $order->sender_country,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'receiver_address' => $order->receiver_address,
            'receiver_city' => $order->receiver_city,
            'receiver_postal_code' => $order->receiver_postal_code,
            'special_notes' => $order->special_notes,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'country_name' => $order->name_en,
            'tracking_history' => $tracking_history,
            'currency_symbol' => $currency_symbol,
        );
        
        // Return success response
        wp_send_json_success($order_data);
    }
}

// Initialize the plugin
$yenolx_cargo_service = new Yenolx_Cargo_Service();