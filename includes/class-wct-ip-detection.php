<?php
/**
 * Clase para detección de moneda por IP
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCT_IP_Detection {
    
    private static $country_currency_map = array(
        'US' => 'USD', 'CA' => 'CAD', 'MX' => 'MXN',
        'GB' => 'GBP', 'IE' => 'EUR', 'FR' => 'EUR', 'DE' => 'EUR', 'ES' => 'EUR', 'IT' => 'EUR', 'NL' => 'EUR', 'BE' => 'EUR', 'AT' => 'EUR', 'PT' => 'EUR', 'FI' => 'EUR', 'GR' => 'EUR', 'LU' => 'EUR', 'MT' => 'EUR', 'CY' => 'EUR', 'SI' => 'EUR', 'SK' => 'EUR', 'EE' => 'EUR', 'LV' => 'EUR', 'LT' => 'EUR',
        'JP' => 'JPY', 'CN' => 'CNY', 'KR' => 'KRW', 'IN' => 'INR', 'AU' => 'AUD', 'NZ' => 'NZD',
        'BR' => 'BRL', 'AR' => 'ARS', 'CL' => 'CLP', 'CO' => 'COP', 'PE' => 'PEN', 'UY' => 'UYU',
        'RU' => 'RUB', 'TR' => 'TRY', 'ZA' => 'ZAR', 'EG' => 'EGP', 'NG' => 'NGN',
        'CH' => 'CHF', 'SE' => 'SEK', 'NO' => 'NOK', 'DK' => 'DKK', 'PL' => 'PLN', 'CZ' => 'CZK', 'HU' => 'HUF'
    );
    
    public function __construct() {
        add_action('wp_ajax_wct_test_ip_detection', array($this, 'test_ip_detection'));
        add_action('wp_ajax_nopriv_wct_test_ip_detection', array($this, 'test_ip_detection'));
    }
    
    public static function get_currency_by_ip() {
        if (!get_option('wct_enable_ip_detection', 'no') === 'yes') {
            return false;
        }
        
        $ip = self::get_user_ip();
        if (!$ip) {
            return false;
        }
        
        $country = self::get_country_by_ip($ip);
        if (!$country) {
            return false;
        }
        
        $currency = self::get_currency_by_country($country);
        if (!$currency) {
            return false;
        }
        
        // Verificar si la moneda está habilitada
        $currencies = get_option('wct_currencies', array());
        if (!isset($currencies[$currency]) || !$currencies[$currency]['enabled']) {
            return false;
        }
        
        return $currency;
    }
    
    private static function get_user_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }
    
    private static function get_country_by_ip($ip) {
        // Cache para evitar múltiples llamadas API
        $cache_key = 'wct_country_' . md5($ip);
        $cached_country = get_transient($cache_key);
        
        if ($cached_country !== false) {
            return $cached_country;
        }
        
        $country = false;
        
        // Intentar con ipapi.co (gratuito)
        $response = wp_remote_get("http://ipapi.co/{$ip}/country/", array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'WooCommerce Currency Switcher'
            )
        ));
        
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            if (strlen($body) === 2) { // Código de país de 2 letras
                $country = strtoupper(trim($body));
            }
        }
        
        // Fallback a ip-api.com si ipapi.co falla
        if (!$country) {
            $response = wp_remote_get("http://ip-api.com/json/{$ip}", array(
                'timeout' => 10,
                'headers' => array(
                    'User-Agent' => 'WooCommerce Currency Switcher'
                )
            ));
            
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (isset($data['countryCode'])) {
                    $country = $data['countryCode'];
                }
            }
        }
        
        // Cache por 24 horas
        if ($country) {
            set_transient($cache_key, $country, 24 * HOUR_IN_SECONDS);
        }
        
        return $country;
    }
    
    private static function get_currency_by_country($country_code) {
        if (isset(self::$country_currency_map[$country_code])) {
            return self::$country_currency_map[$country_code];
        }
        
        // Mapeo adicional para países no incluidos
        $additional_mapping = get_option('wct_country_currency_mapping', array());
        if (isset($additional_mapping[$country_code])) {
            return $additional_mapping[$country_code];
        }
        
        return false;
    }
    
    public function test_ip_detection() {
        check_ajax_referer('wct_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('No tienes permisos para realizar esta acción', 'wct-currency-toolbox'));
        }
        
        $ip = self::get_user_ip();
        $country = self::get_country_by_ip($ip);
        $currency = self::get_currency_by_country($country);
        
        $result = array(
            'ip' => $ip,
            'country' => $country,
            'currency' => $currency,
            'success' => !empty($currency)
        );
        
        if (!$result['success']) {
            $result['message'] = __('No se pudo detectar la moneda para esta ubicación', 'wct-currency-toolbox');
        } else {
            $currencies = get_option('wct_currencies', array());
            $currency_name = isset($currencies[$currency]) ? $currencies[$currency]['name'] : $currency;
            $result['message'] = sprintf(__('Moneda detectada: %s (%s) para %s', 'wct-currency-toolbox'), $currency_name, $currency, $country);
        }
        
        wp_send_json($result);
    }
    
    public static function get_available_countries() {
        return array_keys(self::$country_currency_map);
    }
    
    public static function add_country_currency_mapping($country_code, $currency_code) {
        $mapping = get_option('wct_country_currency_mapping', array());
        $mapping[$country_code] = $currency_code;
        update_option('wct_country_currency_mapping', $mapping);
    }
    
    public static function remove_country_currency_mapping($country_code) {
        $mapping = get_option('wct_country_currency_mapping', array());
        unset($mapping[$country_code]);
        update_option('wct_country_currency_mapping', $mapping);
    }
    
    public static function get_country_currency_mapping() {
        return get_option('wct_country_currency_mapping', array());
    }
}
