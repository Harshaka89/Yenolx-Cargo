# Yenolx Cargo Service - English Language Implementation

## Overview

The Yenolx Cargo Service WordPress plugin includes comprehensive English language support as the primary language. This implementation ensures that all user-facing content, admin interfaces, and system communications are available in clear, professional English.

## Features

### 1. Complete English Translation Coverage
- **Frontend Forms**: All order forms, tracking forms, and price calculators display English text
- **Admin Panel**: Complete WordPress admin interface translated to English
- **Email Notifications**: All automated emails sent in English
- **System Messages**: Error messages, success notifications, and status updates in English
- **Country Names**: All countries can have English names configured
- **Form Labels**: All form fields and buttons labeled in English

### 2. Language Switcher
- **Automatic Inclusion**: Language switcher automatically added to all frontend forms
- **Three Language Options**: English, Sinhala (සිංහල), and Tamil (தமிழ்)
- **URL-based Switching**: Language selection maintained via URL parameters
- **Visual Feedback**: Active language highlighted in the switcher
- **Responsive Design**: Works on all device sizes

### 3. Translation Files
- **POT Template**: `languages/yenolx-cargo.pot` - Translation template file
- **English PO**: `languages/en_US.po` - English translation source
- **English MO**: `languages/en_US.mo` - Compiled English translation
- **134 Translations**: Complete coverage of all user-facing strings

## Implementation Details

### File Structure
```
languages/
├── yenolx-cargo.pot          # Translation template
├── en_US.po                  # English translation source
├── en_US.mo                  # Compiled English translation
├── compile-mo.py             # PO to MO compiler
└── compile-mo.php            # PO to MO compiler (PHP)

includes/
├── frontend-functions.php    # Language detection and display functions
└── utility-functions.php      # Translation utility functions

templates/
├── order-form.php            # Order form with language switcher
├── tracking-form.php         # Tracking form with language switcher
└── price-calculator.php      # Price calculator with language switcher

assets/css/
└── frontend.css              # Language switcher styling
```

### Key Functions

#### Language Detection
```php
function yenolx_get_current_language() {
    // Check URL parameter first
    if (isset($_GET['lang'])) {
        return sanitize_text_field($_GET['lang']);
    }
    
    // Default to English
    return 'en';
}
```

#### Language Switcher Display
```php
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
```

#### Translated Text Retrieval
```php
function yenolx_get_translated_text($key) {
    $translations = array(
        'select_country' => array(
            'en' => 'Select Country',
            'si' => 'රට තෝරන්න',
            'ta' => 'நாட்டைத் தேர்ந்தெடுக்கவும்',
        ),
        // More translations...
    );
    
    $current_lang = yenolx_get_current_language();
    
    if (isset($translations[$key][$current_lang])) {
        return $translations[$key][$current_lang];
    }
    
    return isset($translations[$key]['en']) ? $translations[$key]['en'] : $key;
}
```

### CSS Styling
```css
/* Language Switcher Styles */
.yenolx-language-switcher {
    text-align: right;
    margin-bottom: 20px;
}

.yenolx-language-switcher a {
    display: inline-block;
    padding: 8px 16px;
    margin: 0 4px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    text-decoration: none;
    color: #495057;
    font-size: 14px;
    transition: all 0.3s ease;
}

.yenolx-language-switcher a:hover {
    background-color: #e9ecef;
    color: #212529;
}

.yenolx-language-switcher a.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}
```

## Usage

### 1. Frontend Forms
All frontend forms automatically include the language switcher and display content in the appropriate language:

```php
// Order Form
[yenolx_order_form]

// Tracking Form
[yenolx_tracking_form]

// Price Calculator
[yenolx_price_calculator]
```

### 2. Language Switching
Users can switch languages by clicking on the language switcher links. The language preference is maintained via URL parameters:

```
https://example.com/order-form/?lang=en    // English
https://example.com/order-form/?lang=si    // Sinhala
https://example.com/order-form/?lang=ta    // Tamil
```

### 3. Admin Panel
The WordPress admin panel is fully translated to English:

- **Dashboard**: Order statistics and recent orders
- **Countries**: Add/edit countries with multilingual names
- **Pricing**: Configure pricing by country and weight
- **Orders**: Manage and track orders
- **Coupons**: Create and manage discount coupons
- **Settings**: Configure system options

### 4. Email Notifications
All email notifications are sent in English when English is selected:

- **Order Confirmation**: Sent to customers after placing an order
- **Status Updates**: Sent when order status changes
- **Admin Notifications**: Sent to administrators for new orders

## Translation Examples

### Frontend Form Labels
```php
// English
'Select Country' => 'Select Country'
'Select Package Weight' => 'Select Package Weight'
'Calculate Price' => 'Calculate Price'
'Place Order' => 'Place Order'

// Order Statuses
'Order Confirmed' => 'Order Confirmed'
'In Transit to Italy' => 'In Transit to Italy'
'In Transit to Sri Lanka' => 'In Transit to Sri Lanka'
'Delivered' => 'Delivered'
```

### Admin Panel Text
```php
// Menu Items
'Dashboard' => 'Dashboard'
'Countries' => 'Countries'
'Pricing' => 'Pricing'
'Orders' => 'Orders'
'Settings' => 'Settings'

// Form Labels
'Country Name (English)' => 'Country Name (English)'
'Weight (kg)' => 'Weight (kg)'
'Price (EUR)' => 'Price (EUR)'
'Status' => 'Status'
```

### Email Content
```php
// Email Subjects
'Order Confirmation' => 'Order Confirmation'
'Order Status Update' => 'Order Status Update'

// Email Body
'Thank you for your order with Yenolx Cargo Service.' => 'Thank you for your order with Yenolx Cargo Service.'
'Your order details are as follows:' => 'Your order details are as follows:'
'Your order status has been updated.' => 'Your order status has been updated.'
```

## Customization

### Adding New Translations
1. Edit `languages/en_US.po` file
2. Add new translation entries:
   ```
   msgid "New Text to Translate"
   msgstr "Translated Text in English"
   ```
3. Compile to .mo file using the provided compiler
4. The new translations will be automatically available

### Modifying Language Switcher Styling
Edit the CSS in `assets/css/frontend.css`:

```css
.yenolx-language-switcher {
    /* Custom styles */
}

.yenolx-language-switcher a {
    /* Custom button styles */
}

.yenolx-language-switcher a.active {
    /* Custom active state styles */
}
```

### Adding New Languages
1. Create new .po file (e.g., `fr_FR.po` for French)
2. Copy content from `en_US.po` and translate
3. Compile to .mo file
4. Add language option to `yenolx_language_switcher()` function

## Integration with WordPress

### Text Domain
The plugin uses the text domain `yenolx-cargo` for all translations:

```php
__('Text to translate', 'yenolx-cargo')
_e('Text to translate', 'yenolx-cargo')
```

### Load Text Domain
The plugin automatically loads translations:

```php
public function load_textdomain() {
    load_plugin_textdomain('yenolx-cargo', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
```

### WordPress Multilingual Plugins
The plugin is compatible with popular WordPress multilingual plugins:
- **WPML**: Fully compatible with WPML translation management
- **TranslatePress**: Can be integrated with TranslatePress
- **Polylang**: Works with Polylang language switching

## Testing

### Testing Language Switching
1. Create a page with `[yenolx_order_form]` shortcode
2. View the page and verify language switcher appears
3. Click different language options
4. Verify form text changes accordingly
5. Check URL parameters update correctly

### Testing Email Translations
1. Place a test order with English selected
2. Verify order confirmation email is in English
3. Update order status in admin panel
4. Verify status update email is in English

### Testing Admin Panel
1. Navigate to Yenolx Cargo menu in WordPress admin
2. Verify all menu items are in English
3. Check form labels and buttons in all admin pages
4. Test adding/editing countries, pricing, and orders

## Troubleshooting

### Common Issues

**Language switcher not appearing**
- Verify `yenolx_language_switcher()` is called in templates
- Check CSS is properly loaded
- Ensure template files are not overridden by theme

**Translations not updating**
- Recompile .mo file after editing .po file
- Clear WordPress cache
- Check file permissions on language files

**Emails not in English**
- Verify language is set correctly when order is placed
- Check email template files
- Ensure translation files are properly loaded

**Admin panel not translated**
- Verify WordPress admin language is set to English
- Check text domain is correctly used in all strings
- Ensure translation files are in the correct location

## Best Practices

1. **Always use translation functions**: Use `__()` and `_e()` for all user-facing text
2. **Keep translations updated**: Regularly review and update translation files
3. **Test thoroughly**: Test all languages after making changes
4. **Use clear, simple English**: Keep translations straightforward and professional
5. **Document customizations**: Keep track of any custom translation modifications

## Conclusion

The English language implementation in Yenolx Cargo Service provides comprehensive multilingual support with English as the primary language. The implementation ensures that all user interactions, admin functions, and system communications are available in clear, professional English, making the plugin accessible to a global audience while maintaining support for local languages.

The language system is designed to be extensible, making it easy to add new languages or customize existing translations. The automatic language switcher and URL-based language selection provide a seamless user experience across all supported languages.