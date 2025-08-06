# Yenolx Cargo Service Plugin

A complete WordPress plugin for managing cargo services from EU countries to Sri Lanka.

## Features

- **Multilingual Support**: Supports English, Sinhala, and Tamil languages
- **Country Management**: Add/edit countries with multilingual names and delivery time ranges
- **Pricing Management**: Set fixed pricing by country and weight, with configurable rates
- **Order Management**: Complete order tracking system with multiple status updates
- **Coupon System**: Create and manage discount coupons
- **Frontend Forms**: Order forms, tracking forms, and price calculator
- **Email Notifications**: Automated emails for order confirmations and status updates
- **Admin Dashboard**: Comprehensive dashboard with statistics and management tools

## Installation

1. Upload the `yenolx-cargo` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings through the Yenolx Cargo menu in WordPress admin

## Usage

### Shortcodes

Use the following shortcodes to display forms on your pages:

- `[yenolx_order_form]` - Displays the complete order form with price calculator
- `[yenolx_tracking_form]` - Displays the tracking form for customers to check their order status
- `[yenolx_price_calculator]` - Displays a standalone price calculator

### Admin Panel

Access the admin panel through WordPress admin menu:

- **Dashboard**: View statistics and manage general settings
- **Countries**: Add/edit countries and manage country-specific pricing
- **Pricing**: Overview and manage pricing configurations
- **Orders**: View, edit, and manage customer orders
- **Coupons**: Create and manage discount coupons
- **Settings**: Configure plugin settings and view shortcode information

## Configuration

### 1. Set Up Countries

1. Go to Yenolx Cargo → Countries
2. Add countries with names in English, Sinhala, and Tamil
3. Set delivery time ranges (e.g., "Germany to Milan: 7-10 days")
4. Configure country-specific pricing tiers

### 2. Configure Base Rates

1. Go to Yenolx Cargo → Settings
2. Set the Milan to Sri Lanka rate (per kg)
3. Set the Sri Lanka local delivery rate (per kg)
4. Configure currency symbol and email settings

### 3. Create Thank You Page

1. Create a new WordPress page
2. Add the tracking form shortcode: `[yenolx_tracking_form]`
3. Go to Yenolx Cargo → Settings and select this page as the Thank You Page

## Order Process

1. **Customer selects country and weight** - Price is calculated automatically
2. **Customer fills sender and receiver details** - Complete address information
3. **Order is placed** - Unique tracking ID is generated
4. **Email confirmation is sent** - Customer receives order details
5. **Admin manages order status** - Status updates trigger email notifications
6. **Customer tracks order** - Using the tracking form with their tracking ID

## Order Statuses

The system supports the following order statuses:
- Order Confirmed
- Ready for Pickup
- Picked Up
- In Transit to Italy
- In Transit to Sri Lanka
- At Sri Lanka Office
- In Transit to Home
- Delivered

## Email Notifications

The plugin automatically sends emails for:
- **Order Confirmation**: When a customer places an order
- **Status Updates**: When admin updates order status
- **Admin Notifications**: When new orders are received

## Multilingual Support

The plugin supports three languages:
- **English**: Default language
- **Sinhala**: සිංහල
- **Tamil**: தமிழ்

Language switcher is automatically displayed on all frontend forms.

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Support

For support and documentation, please visit the plugin's official website or contact the development team.

## License

This plugin is licensed under the GPLv2 or later.