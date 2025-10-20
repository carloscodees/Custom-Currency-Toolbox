<?php
/**
 * Plugin Name: WooCommerce Currency Toolbox
 * Plugin URI: https://snowscode.com/woocommerce-currency-toolbox
 * Description: Complete multi-currency management tool for WooCommerce with automatic exchange rates, IP detection, price zones, and more.
 * Version: 1.0.1
 * Author: Snows
 * Author URI: https://snowscode.com
 * Text Domain: wct-currency-toolbox
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', 'wct_woocommerce_missing_notice');
    return;
}

function wct_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>WooCommerce Currency Toolbox</strong> requiere que WooCommerce esté instalado y activo.</p></div>';
}

define('WCT_PLUGIN_FILE', __FILE__);
define('WCT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WCT_VERSION', '1.0.1');

require_once WCT_PLUGIN_DIR . 'includes/class-wct-main.php';
require_once WCT_PLUGIN_DIR . 'includes/class-wct-admin.php';
require_once WCT_PLUGIN_DIR . 'includes/class-wct-frontend.php';
require_once WCT_PLUGIN_DIR . 'includes/class-wct-currency-manager.php';
require_once WCT_PLUGIN_DIR . 'includes/class-wct-exchange-rates.php';
require_once WCT_PLUGIN_DIR . 'includes/class-wct-widget.php';
require_once WCT_PLUGIN_DIR . 'includes/class-wct-ip-detection.php';
require_once WCT_PLUGIN_DIR . 'includes/class-wct-zone-pricing.php';
require_once WCT_PLUGIN_DIR . 'includes/class-wct-coupons.php';
require_once WCT_PLUGIN_DIR . 'includes/class-wct-wpml-integration.php';
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

function wct_init() {
    new WCT_Main();
}
add_action('plugins_loaded', 'wct_init');

register_activation_hook(__FILE__, 'wct_activate');
function wct_activate() {
    WCT_Main::create_tables();
    WCT_Main::set_default_options();
}

register_deactivation_hook(__FILE__, 'wct_deactivate');
function wct_deactivate() {
    wp_clear_scheduled_hook('wct_update_exchange_rates');
}
