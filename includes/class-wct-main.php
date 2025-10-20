<?php
/**
 * Clase principal del plugin WooCommerce Currency Switcher
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCT_Main {
    
    private static $instance = null;
    private $getting_currency = false;
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        add_filter('woocommerce_currency', array($this, 'filter_woocommerce_currency'), 10, 1);
        add_filter('woocommerce_currency_symbol', array($this, 'get_currency_symbol'), 10, 2);
        
        add_action('wp_ajax_wct_switch_currency', array($this, 'ajax_switch_currency'));
        add_action('wp_ajax_nopriv_wct_switch_currency', array($this, 'ajax_switch_currency'));
        
        add_action('wct_update_exchange_rates', array($this, 'update_exchange_rates'));
        
        $this->init_components();
    }
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function init() {
        load_plugin_textdomain('wct-currency-toolbox', false, dirname(plugin_basename(WCT_PLUGIN_FILE)) . '/languages');
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('wct-frontend', WCT_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), WCT_VERSION, true);
        wp_enqueue_style('wct-frontend', WCT_PLUGIN_URL . 'assets/css/frontend.css', array(), WCT_VERSION);
        
        wp_localize_script('wct-frontend', 'wct_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wct_nonce'),
            'current_currency' => $this->get_current_currency(),
            'loading_text' => __('Cambiando moneda...', 'wct-currency-toolbox')
        ));
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'wct-') !== false) {
            wp_enqueue_script('wct-admin', WCT_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), WCT_VERSION, true);
            wp_enqueue_style('wct-admin', WCT_PLUGIN_URL . 'assets/css/admin.css', array(), WCT_VERSION);
        }
    }
    
    private function init_components() {
        new WCT_Admin();
        new WCT_Frontend();
        new WCT_Currency_Manager();
        new WCT_Exchange_Rates();
        new WCT_IP_Detection();
        new WCT_Zone_Pricing();
        new WCT_Coupons();
        new WCT_WPML_Integration();
    }
    
    public function filter_woocommerce_currency($currency) {
        if ($this->getting_currency) {
            return $currency;
        }
        
        $this->getting_currency = true;
        
        if (!function_exists('WC') || !WC()->session) {
            $this->getting_currency = false;
            return get_option('wct_default_currency', $currency);
        }
        
        $session_currency = WC()->session->get('wct_currency');
        if ($session_currency) {
            $this->getting_currency = false;
            return $session_currency;
        }
        
        if (get_option('wct_enable_ip_detection', 'no') === 'yes') {
            $ip_currency = WCT_IP_Detection::get_currency_by_ip();
            if ($ip_currency) {
                $this->getting_currency = false;
                return $ip_currency;
            }
        }
        
        $this->getting_currency = false;
        return get_option('wct_default_currency', $currency);
    }
    
    public function get_current_currency() {
        if ($this->getting_currency) {
            return get_option('wct_default_currency', 'USD');
        }
        
        $this->getting_currency = true;
        
        if (!function_exists('WC') || !WC()->session) {
            $this->getting_currency = false;
            return get_option('wct_default_currency', get_option('woocommerce_currency', 'USD'));
        }
        
        $session_currency = WC()->session->get('wct_currency');
        if ($session_currency) {
            $this->getting_currency = false;
            return $session_currency;
        }
        
        if (get_option('wct_enable_ip_detection', 'no') === 'yes') {
            $ip_currency = WCT_IP_Detection::get_currency_by_ip();
            if ($ip_currency) {
                $this->getting_currency = false;
                return $ip_currency;
            }
        }
        
        $this->getting_currency = false;
        return get_option('wct_default_currency', get_option('woocommerce_currency', 'USD'));
    }
    
    public function get_currency_symbol($currency_symbol, $currency) {
        $currencies = get_option('wct_currencies', array());
        if (isset($currencies[$currency]['symbol'])) {
            return $currencies[$currency]['symbol'];
        }
        return $currency_symbol;
    }
    
    public function price_format($format) {
        $currency = $this->get_current_currency();
        $currencies = get_option('wct_currencies', array());
        
        if (isset($currencies[$currency]['format'])) {
            return $currencies[$currency]['format'];
        }
        
        return $format;
    }
    
    public function ajax_switch_currency() {
        check_ajax_referer('wct_nonce', 'nonce');
        
        $currency = sanitize_text_field($_POST['currency']);
        $currencies = get_option('wct_currencies', array());
        
        if (!isset($currencies[$currency])) {
            wp_send_json_error(array('message' => __('Moneda no válida', 'wct-currency-toolbox')));
        }
        
        if (!function_exists('WC') || !WC()->session) {
            wp_send_json_error(array('message' => __('La sesión de WooCommerce no está disponible', 'wct-currency-toolbox')));
        }
        
        if (get_option('wct_allow_purchase_in_selected_currency', 'yes') === 'no') {
            $currency = get_option('wct_default_currency', get_woocommerce_currency());
        }
        
        WC()->session->set('wct_currency', $currency);
        
        wp_send_json_success(array(
            'currency' => $currency,
            'message' => __('Moneda cambiada exitosamente', 'wct-currency-toolbox')
        ));
    }
    
    public function update_exchange_rates() {
        WCT_Exchange_Rates::update_all_rates();
    }
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'wct_exchange_rates';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            from_currency varchar(3) NOT NULL,
            to_currency varchar(3) NOT NULL,
            rate decimal(10,6) NOT NULL,
            date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY from_currency (from_currency),
            KEY to_currency (to_currency)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public static function set_default_options() {
        $default_currencies = array(
            'USD' => array(
                'name' => 'Dólar Estadounidense',
                'symbol' => '$',
                'format' => '%1$s%2$s',
                'flag' => 'us',
                'enabled' => true,
                'rate' => 1.0,
                'gateways' => array()
            ),
            'EUR' => array(
                'name' => 'Euro',
                'symbol' => '€',
                'format' => '%1$s%2$s',
                'flag' => 'eu',
                'enabled' => true,
                'rate' => 0.85,
                'gateways' => array()
            ),
            'GBP' => array(
                'name' => 'Libra Esterlina',
                'symbol' => '£',
                'format' => '%1$s%2$s',
                'flag' => 'gb',
                'enabled' => true,
                'rate' => 0.73,
                'gateways' => array()
            )
        );
        
        if (!get_option('wct_currencies')) {
            update_option('wct_currencies', $default_currencies);
        }
        
        if (!get_option('wct_default_currency')) {
            update_option('wct_default_currency', 'USD');
        }
        
        if (!get_option('wct_enable_flags')) {
            update_option('wct_enable_flags', 'yes');
        }
        
        if (!get_option('wct_enable_ip_detection')) {
            update_option('wct_enable_ip_detection', 'no');
        }
        
        if (!get_option('wct_allow_purchase_in_selected_currency')) {
            update_option('wct_allow_purchase_in_selected_currency', 'yes');
        }
    }
}
