<?php
/**
 * Clase para manejo de tipos de cambio
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCT_Exchange_Rates {
    
    public function __construct() {
        add_action('wp_ajax_wct_test_api', array($this, 'test_api_connection'));
        add_action('wp_ajax_wct_update_rates_manual', array($this, 'update_rates_manual'));
    }
    
    public static function update_all_rates() {
        $api_type = get_option('wct_exchange_rate_api', 'manual');
        
        if ($api_type === 'manual') {
            return false;
        }
        
        $api_key = get_option('wct_api_key', '');
        if (empty($api_key)) {
            return false;
        }
        
        $base_currency = get_option('wct_default_currency', 'USD');
        $currencies = get_option('wct_currencies', array());
        
        $rates = array();
        
        switch ($api_type) {
            case 'fixer':
                $rates = self::get_fixer_rates($api_key, $base_currency);
                break;
            case 'exchangerate':
                $rates = self::get_exchangerate_rates($api_key, $base_currency);
                break;
        }
        
        if (!empty($rates)) {
            self::save_rates($rates);
            return true;
        }
        
        return false;
    }
    
    private static function get_fixer_rates($api_key, $base_currency) {
        $url = "http://data.fixer.io/api/latest?access_key={$api_key}&base={$base_currency}";
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'WooCommerce Currency Switcher'
            )
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || !$data['success']) {
            return false;
        }
        
        return $data['rates'];
    }
    
    private static function get_exchangerate_rates($api_key, $base_currency) {
        $url = "https://v6.exchangerate-api.com/v6/{$api_key}/latest/{$base_currency}";
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'WooCommerce Currency Switcher'
            )
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data || $data['result'] !== 'success') {
            return false;
        }
        
        return $data['conversion_rates'];
    }
    
    private static function save_rates($rates) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wct_exchange_rates';
        $base_currency = get_option('wct_default_currency', 'USD');
        $currencies = get_option('wct_currencies', array());
        
        foreach ($currencies as $code => $currency) {
            if ($code === $base_currency || !$currency['enabled']) {
                continue;
            }
            
            if (isset($rates[$code])) {
                $rate = floatval($rates[$code]);
                
                $wpdb->insert(
                    $table_name,
                    array(
                        'from_currency' => $base_currency,
                        'to_currency' => $code,
                        'rate' => $rate,
                        'date' => current_time('mysql')
                    ),
                    array('%s', '%s', '%f', '%s')
                );
                
                // Actualizar la tasa en la configuración
                $currencies[$code]['rate'] = $rate;
            }
        }
        
        update_option('wct_currencies', $currencies);
    }
    
    public static function get_rate($from_currency, $to_currency) {
        if ($from_currency === $to_currency) {
            return 1.0;
        }
        
        $currencies = get_option('wct_currencies', array());
        
        // Buscar tasa directa
        if (isset($currencies[$to_currency]['rate'])) {
            return floatval($currencies[$to_currency]['rate']);
        }
        
        // Buscar en base de datos
        global $wpdb;
        $table_name = $wpdb->prefix . 'wct_exchange_rates';
        
        $rate = $wpdb->get_var($wpdb->prepare(
            "SELECT rate FROM {$table_name} WHERE from_currency = %s AND to_currency = %s ORDER BY date DESC LIMIT 1",
            $from_currency,
            $to_currency
        ));
        
        if ($rate) {
            return floatval($rate);
        }
        
        // Tasa por defecto si no se encuentra
        return 1.0;
    }
    
    public static function convert_price($price, $from_currency, $to_currency) {
        if ($from_currency === $to_currency) {
            return $price;
        }
        
        $rate = self::get_rate($from_currency, $to_currency);
        return $price * $rate;
    }
    
    public function test_api_connection() {
        check_ajax_referer('wct_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'wct-currency-toolbox'));
        }
        
        $api_type = sanitize_text_field($_POST['api_type']);
        $api_key = sanitize_text_field($_POST['api_key']);
        $base_currency = sanitize_text_field($_POST['base_currency']);
        
        $success = false;
        $message = '';
        
        switch ($api_type) {
            case 'fixer':
                $url = "http://data.fixer.io/api/latest?access_key={$api_key}&base={$base_currency}";
                $response = wp_remote_get($url, array('timeout' => 30));
                
                if (!is_wp_error($response)) {
                    $body = wp_remote_retrieve_body($response);
                    $data = json_decode($body, true);
                    
                    if ($data && $data['success']) {
                        $success = true;
                        $message = __('Conexión exitosa con Fixer.io', 'wct-currency-toolbox');
                    } else {
                        $message = __('Error en la API: ' . (isset($data['error']['info']) ? $data['error']['info'] : 'Desconocido'), 'wct-currency-toolbox');
                    }
                } else {
                    $message = __('Error de conexión: ' . $response->get_error_message(), 'wct-currency-toolbox');
                }
                break;
                
            case 'exchangerate':
                $url = "https://v6.exchangerate-api.com/v6/{$api_key}/latest/{$base_currency}";
                $response = wp_remote_get($url, array('timeout' => 30));
                
                if (!is_wp_error($response)) {
                    $body = wp_remote_retrieve_body($response);
                    $data = json_decode($body, true);
                    
                    if ($data && $data['result'] === 'success') {
                        $success = true;
                        $message = __('Conexión exitosa con ExchangeRate-API', 'wct-currency-toolbox');
                    } else {
                        $message = __('Error en la API: ' . (isset($data['error-type']) ? $data['error-type'] : 'Desconocido'), 'wct-currency-toolbox');
                    }
                } else {
                    $message = __('Error de conexión: ' . $response->get_error_message(), 'wct-currency-toolbox');
                }
                break;
        }
        
        wp_send_json(array(
            'success' => $success,
            'message' => $message
        ));
    }
    
    public function update_rates_manual() {
        check_ajax_referer('wct_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'wct-currency-toolbox'));
        }
        
        $currencies = get_option('wct_currencies', array());
        $base_currency = get_option('wct_default_currency', 'USD');
        
        foreach ($_POST['rates'] as $code => $rate) {
            if (isset($currencies[$code])) {
                $currencies[$code]['rate'] = floatval($rate);
            }
        }
        
        update_option('wct_currencies', $currencies);
        
        wp_send_json_success(array(
            'message' => __('Tipos de cambio actualizados exitosamente', 'wct-currency-toolbox')
        ));
    }
    
    public static function schedule_updates() {
        $api_type = get_option('wct_exchange_rate_api', 'manual');
        
        if ($api_type === 'manual') {
            wp_clear_scheduled_hook('wct_update_exchange_rates');
            return;
        }
        
        $interval = get_option('wct_update_interval', 'daily');
        
        // Limpiar eventos existentes
        wp_clear_scheduled_hook('wct_update_exchange_rates');
        
        // Programar nuevo evento
        switch ($interval) {
            case 'hourly':
                wp_schedule_event(time(), 'hourly', 'wct_update_exchange_rates');
                break;
            case 'daily':
                wp_schedule_event(time(), 'daily', 'wct_update_exchange_rates');
                break;
            case 'weekly':
                wp_schedule_event(time(), 'weekly', 'wct_update_exchange_rates');
                break;
        }
    }
}
