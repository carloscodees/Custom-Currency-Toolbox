<?php
/**
 * Clase para integración con WPML
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCT_WPML_Integration {
    
    public function __construct() {
        // Verificar si WPML está activo
        if (!$this->is_wpml_active()) {
            return;
        }
        
        add_action('wpml_loaded', array($this, 'init_wpml_integration'));
    }
    
    public function init_wpml_integration() {
        add_filter('wct_get_currency_by_language', array($this, 'get_currency_by_language'), 10, 1);
        add_action('wpml_switch_language', array($this, 'switch_currency_on_language_change'));
        add_filter('wct_currency_switcher_currencies', array($this, 'filter_currencies_by_language'), 10, 1);
    }
    
    private function is_wpml_active() {
        return class_exists('SitePress') && function_exists('icl_get_languages');
    }
    
    public function get_currency_by_language($default_currency) {
        if (!$this->is_wpml_active()) {
            return $default_currency;
        }
        
        $current_language = apply_filters('wpml_current_language', null);
        $language_currencies = get_option('wct_language_currencies', array());
        
        if (isset($language_currencies[$current_language])) {
            return $language_currencies[$current_language];
        }
        
        return $default_currency;
    }
    
    public function switch_currency_on_language_change() {
        $current_language = apply_filters('wpml_current_language', null);
        $language_currencies = get_option('wct_language_currencies', array());
        
        if (isset($language_currencies[$current_language])) {
            $currency = $language_currencies[$current_language];
            
            // Verificar si la moneda está habilitada
            $currencies = get_option('wct_currencies', array());
            if (isset($currencies[$currency]) && $currencies[$currency]['enabled']) {
                WC()->session->set('wct_currency', $currency);
            }
        }
    }
    
    public function filter_currencies_by_language($currencies) {
        if (!$this->is_wpml_active()) {
            return $currencies;
        }
        
        $current_language = apply_filters('wpml_current_language', null);
        $language_currencies = get_option('wct_language_currencies', array());
        
        if (empty($language_currencies)) {
            return $currencies;
        }
        
        // Filtrar monedas basadas en el idioma actual
        $filtered_currencies = array();
        
        foreach ($currencies as $code => $currency) {
            // Si hay configuración específica para este idioma
            if (isset($language_currencies[$current_language])) {
                if (in_array($code, $language_currencies[$current_language])) {
                    $filtered_currencies[$code] = $currency;
                }
            } else {
                // Si no hay configuración específica, mostrar todas las monedas habilitadas
                $filtered_currencies[$code] = $currency;
            }
        }
        
        return $filtered_currencies;
    }
    
    public static function set_language_currency($language, $currency) {
        $language_currencies = get_option('wct_language_currencies', array());
        $language_currencies[$language] = $currency;
        update_option('wct_language_currencies', $language_currencies);
    }
    
    public static function get_language_currency($language) {
        $language_currencies = get_option('wct_language_currencies', array());
        return isset($language_currencies[$language]) ? $language_currencies[$language] : false;
    }
    
    public static function get_all_language_currencies() {
        return get_option('wct_language_currencies', array());
    }
    
    public static function remove_language_currency($language) {
        $language_currencies = get_option('wct_language_currencies', array());
        unset($language_currencies[$language]);
        update_option('wct_language_currencies', $language_currencies);
    }
    
    public static function get_available_languages() {
        if (!function_exists('icl_get_languages')) {
            return array();
        }
        
        $languages = icl_get_languages('skip_missing=0&orderby=code');
        $available_languages = array();
        
        foreach ($languages as $lang) {
            $available_languages[$lang['language_code']] = $lang['native_name'];
        }
        
        return $available_languages;
    }
}
