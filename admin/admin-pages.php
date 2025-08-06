<?php
/**
 * Admin pages handler
 * 
 * This file handles the admin pages for the Yenolx Cargo Service plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure required functions are available
require_once YENOLX_CARGO_PATH . 'admin/admin-functions.php';

/**
 * Admin pages class
 */
class Yenolx_Cargo_Admin_Pages {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add admin menu pages
        add_action('admin_menu', array($this, 'add_admin_menu_pages'));
        
        // Add admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu_pages() {
        // Main menu
        add_menu_page(
            __('Yenolx Cargo Service', 'yenolx-cargo'),
            __('Yenolx Cargo', 'yenolx-cargo'),
            'manage_options',
            'yenolx-cargo',
            array($this, 'dashboard_page'),
            'dashicons-archive',
            25
        );
        
        // Submenu pages
        add_submenu_page(
            'yenolx-cargo',
            __('Dashboard', 'yenolx-cargo'),
            __('Dashboard', 'yenolx-cargo'),
            'manage_options',
            'yenolx-cargo',
            array($this, 'dashboard_page')
        );
        
        add_submenu_page(
            'yenolx-cargo',
            __('Countries', 'yenolx-cargo'),
            __('Countries', 'yenolx-cargo'),
            'manage_options',
            'yenolx-countries',
            array($this, 'countries_page')
        );
        
        add_submenu_page(
            'yenolx-cargo',
            __('Pricing', 'yenolx-cargo'),
            __('Pricing', 'yenolx-cargo'),
            'manage_options',
            'yenolx-pricing',
            array($this, 'pricing_page')
        );
        
        add_submenu_page(
            'yenolx-cargo',
            __('Orders', 'yenolx-cargo'),
            __('Orders', 'yenolx-cargo'),
            'manage_options',
            'yenolx-orders',
            array($this, 'orders_page')
        );
        
        add_submenu_page(
            'yenolx-cargo',
            __('Coupons', 'yenolx-cargo'),
            __('Coupons', 'yenolx-cargo'),
            'manage_options',
            'yenolx-coupons',
            array($this, 'coupons_page')
        );
        
        add_submenu_page(
            'yenolx-cargo',
            __('Settings', 'yenolx-cargo'),
            __('Settings', 'yenolx-cargo'),
            'manage_options',
            'yenolx-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        include YENOLX_CARGO_PATH . 'admin/pages/dashboard.php';
    }
    
    /**
     * Countries page
     */
    public function countries_page() {
        include YENOLX_CARGO_PATH . 'admin/pages/countries.php';
    }
    
    /**
     * Pricing page
     */
    public function pricing_page() {
        include YENOLX_CARGO_PATH . 'admin/pages/pricing.php';
    }
    
    /**
     * Orders page
     */
    public function orders_page() {
        include YENOLX_CARGO_PATH . 'admin/pages/orders.php';
    }
    
    /**
     * Coupons page
     */
    public function coupons_page() {
        include YENOLX_CARGO_PATH . 'admin/pages/coupons.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        include YENOLX_CARGO_PATH . 'admin/pages/settings.php';
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        // Check for success messages
        if (isset($_GET['message']) && !empty($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
            
            echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible">';
            echo '<p>' . esc_html($message) . '</p>';
            echo '</div>';
        }
    }
}

// Initialize admin pages
new Yenolx_Cargo_Admin_Pages();