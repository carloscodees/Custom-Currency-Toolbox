<?php
/**
 * Clase para el panel de administración del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCT_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_wct_save_currencies', array($this, 'save_currencies'));
        add_action('admin_post_wct_update_exchange_rates', array($this, 'update_exchange_rates'));
        add_action('admin_post_wct_update_rates_manual', array($this, 'update_rates_manual'));
        add_action('admin_post_wct_save_pricing_zones', array($this, 'save_pricing_zones'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Currency Toolbox', 'wct-currency-toolbox'),
            __('Currency Toolbox', 'wct-currency-toolbox'),
            'manage_woocommerce',
            'wct-settings',
            array($this, 'settings_page'),
            'dashicons-money-alt',
            56
        );
        
        add_submenu_page(
            'wct-settings',
            __('Configuración General', 'wct-currency-toolbox'),
            __('Configuración General', 'wct-currency-toolbox'),
            'manage_woocommerce',
            'wct-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'wct-settings',
            __('Monedas', 'wct-currency-toolbox'),
            __('Monedas', 'wct-currency-toolbox'),
            'manage_woocommerce',
            'wct-currencies',
            array($this, 'currencies_page')
        );
        
        add_submenu_page(
            'wct-settings',
            __('Tipos de Cambio', 'wct-currency-toolbox'),
            __('Tipos de Cambio', 'wct-currency-toolbox'),
            'manage_woocommerce',
            'wct-exchange-rates',
            array($this, 'exchange_rates_page')
        );
        
        add_submenu_page(
            'wct-settings',
            __('Zonas de Precios', 'wct-currency-toolbox'),
            __('Zonas de Precios', 'wct-currency-toolbox'),
            'manage_woocommerce',
            'wct-zone-pricing',
            array($this, 'zone_pricing_page')
        );
        
        add_submenu_page(
            'wct-settings',
            __('Widget', 'wct-currency-toolbox'),
            __('Widget', 'wct-currency-toolbox'),
            'manage_woocommerce',
            'wct-widget',
            array($this, 'widget_page')
        );
    }
    
    public function register_settings() {
        // Configuración general
        register_setting('wct_settings', 'wct_default_currency');
        register_setting('wct_settings', 'wct_enable_flags');
        register_setting('wct_settings', 'wct_enable_ip_detection');
        register_setting('wct_settings', 'wct_allow_purchase_in_selected_currency');
        register_setting('wct_settings', 'wct_exchange_rate_api');
        register_setting('wct_settings', 'wct_api_key');
        register_setting('wct_settings', 'wct_update_interval');
        register_setting('wct_settings', 'wct_widget_position');
        register_setting('wct_settings', 'wct_widget_style');
    }
    
    public function settings_page() {
        include WCT_PLUGIN_DIR . 'templates/admin/settings.php';
    }
    
    public function currencies_page() {
        include WCT_PLUGIN_DIR . 'templates/admin/currencies.php';
    }
    
    public function exchange_rates_page() {
        include WCT_PLUGIN_DIR . 'templates/admin/exchange-rates.php';
    }
    
    public function zone_pricing_page() {
        include WCT_PLUGIN_DIR . 'templates/admin/zone-pricing.php';
    }
    
    public function widget_page() {
        include WCT_PLUGIN_DIR . 'templates/admin/widget.php';
    }
    
    public function save_currencies() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'wct-currency-toolbox'));
        }
        
        check_admin_referer('wct_save_currencies');
        
        $currencies = array();
        
        if (isset($_POST['currencies'])) {
            foreach ($_POST['currencies'] as $code => $data) {
                $currencies[$code] = array(
                    'name' => sanitize_text_field($data['name']),
                    'symbol' => sanitize_text_field($data['symbol']),
                    'format' => sanitize_text_field($data['format']),
                    'flag' => sanitize_text_field($data['flag']),
                    'enabled' => isset($data['enabled']) ? true : false,
                    'rate' => floatval($data['rate']),
                    'gateways' => isset($data['gateways']) ? array_map('sanitize_text_field', $data['gateways']) : array()
                );
            }
        }
        
        update_option('wct_currencies', $currencies);
        
        wp_redirect(admin_url('admin.php?page=wct-currencies&message=saved'));
        exit;
    }
    
    public function update_exchange_rates() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'wct-currency-toolbox'));
        }
        
        check_admin_referer('wct_update_exchange_rates');
        
        WCT_Exchange_Rates::update_all_rates();
        
        wp_redirect(admin_url('admin.php?page=wct-exchange-rates&message=updated'));
        exit;
    }
    
    public function update_rates_manual() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'wct-currency-toolbox'));
        }
        
        check_admin_referer('wct_update_rates_manual');
        
        if (isset($_POST['rates'])) {
            $currencies = get_option('wct_currencies', array());
            
            foreach ($_POST['rates'] as $code => $rate) {
                if (isset($currencies[$code])) {
                    $currencies[$code]['rate'] = floatval($rate);
                }
            }
            
            update_option('wct_currencies', $currencies);
        }
        
        wp_redirect(admin_url('admin.php?page=wct-exchange-rates&message=updated'));
        exit;
    }
    
    public function save_pricing_zones() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'wct-currency-toolbox'));
        }
        
        check_admin_referer('wct_save_pricing_zones');
        
        $pricing_zones = array();
        
        if (isset($_POST['pricing_zones'])) {
            foreach ($_POST['pricing_zones'] as $zone_id => $zone_data) {
                // Sanitizar el zone_id
                $clean_zone_id = sanitize_title($zone_data['name']);
                
                $pricing_zones[$clean_zone_id] = array(
                    'name' => sanitize_text_field($zone_data['name']),
                    'countries' => isset($zone_data['countries']) ? array_map('sanitize_text_field', $zone_data['countries']) : array(),
                    'currency' => sanitize_text_field($zone_data['currency'])
                );
            }
        }
        
        update_option('wct_pricing_zones', $pricing_zones);
        
        wp_redirect(admin_url('admin.php?page=wct-zone-pricing&message=saved'));
        exit;
    }
}
