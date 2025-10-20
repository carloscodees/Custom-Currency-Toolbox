<?php
/**
 * Clase para funcionalidad del frontend
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCT_Frontend {
    
    private $converting = false;
    private $getting_currency = false;
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render_currency_switcher'));
        
        // Solo hooks esenciales para evitar recursión
        // Los precios se convertirán a nivel de producto
        add_filter('woocommerce_product_get_price', array($this, 'convert_product_price'), 10, 2);
        add_filter('woocommerce_product_get_regular_price', array($this, 'convert_product_price'), 10, 2);
        add_filter('woocommerce_product_get_sale_price', array($this, 'convert_product_price'), 10, 2);
        
        // Variaciones
        add_filter('woocommerce_product_variation_get_price', array($this, 'convert_product_price'), 10, 2);
        add_filter('woocommerce_product_variation_get_regular_price', array($this, 'convert_product_price'), 10, 2);
        add_filter('woocommerce_product_variation_get_sale_price', array($this, 'convert_product_price'), 10, 2);
        
        // Hooks para checkout
        add_filter('woocommerce_checkout_fields', array($this, 'modify_checkout_fields'));
        add_action('woocommerce_checkout_process', array($this, 'validate_checkout_currency'));
        
        // Hooks para emails
        add_filter('woocommerce_email_order_meta_fields', array($this, 'add_currency_to_email'), 10, 3);
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('wct-frontend', WCT_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), WCT_VERSION, true);
        wp_enqueue_style('wct-frontend', WCT_PLUGIN_URL . 'assets/css/frontend.css', array(), WCT_VERSION);
        
        wp_localize_script('wct-frontend', 'wct_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wct_nonce'),
            'current_currency' => $this->get_current_currency(),
            'loading_text' => __('Cambiando moneda...', 'wct-currency-toolbox'),
            'currency_switched_text' => __('Moneda cambiada exitosamente', 'wct-currency-toolbox')
        ));
    }
    
    public function render_currency_switcher() {
        $position = get_option('wct_widget_position', 'header');
        $style = get_option('wct_widget_style', 'dropdown');
        
        if ($position === 'widget') {
            return; // Solo mostrar en widget
        }
        
        $currencies = get_option('wct_currencies', array());
        $current_currency = $this->get_current_currency();
        
        if (empty($currencies)) {
            return;
        }
        
        $enabled_currencies = array_filter($currencies, function($currency) {
            return $currency['enabled'];
        });
        
        if (empty($enabled_currencies)) {
            return;
        }
        
        $enable_flags = get_option('wct_enable_flags', 'yes') === 'yes';
        
        echo '<div id="wct-currency-switcher" class="wct-currency-switcher wct-position-' . esc_attr($position) . ' wct-style-' . esc_attr($style) . '">';
        
        switch ($style) {
            case 'dropdown':
                $this->render_dropdown($enabled_currencies, $current_currency, $enable_flags);
                break;
            case 'buttons':
                $this->render_buttons($enabled_currencies, $current_currency, $enable_flags);
                break;
            case 'flags':
                $this->render_flags($enabled_currencies, $current_currency);
                break;
        }
        
        echo '</div>';
    }
    
    private function render_dropdown($currencies, $current_currency, $show_flags) {
        echo '<select id="wct-currency-select" class="wct-currency-dropdown">';
        
        foreach ($currencies as $code => $currency) {
            $selected = selected($current_currency, $code, false);
            $display_text = $code;
            
            if (!empty($currency['symbol'])) {
                $display_text = $currency['symbol'] . ' ' . $code;
            }
            
            echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($display_text) . '</option>';
        }
        
        echo '</select>';
    }
    
    private function render_buttons($currencies, $current_currency, $show_flags) {
        echo '<div class="wct-currency-buttons">';
        
        foreach ($currencies as $code => $currency) {
            $active = $current_currency === $code ? 'active' : '';
            $display_text = $code;
            
            if (!empty($currency['symbol'])) {
                $display_text = $currency['symbol'] . ' ' . $code;
            }
            
            echo '<button class="wct-currency-button ' . $active . '" data-currency="' . esc_attr($code) . '">';
            
            if ($show_flags && !empty($currency['flag'])) {
                echo '<span class="wct-flag wct-flag-' . esc_attr($currency['flag']) . '"></span>';
            }
            
            echo '<span class="wct-currency-text">' . esc_html($display_text) . '</span>';
            echo '</button>';
        }
        
        echo '</div>';
    }
    
    private function render_flags($currencies, $current_currency) {
        echo '<div class="wct-currency-flags">';
        
        foreach ($currencies as $code => $currency) {
            if (empty($currency['flag'])) {
                continue;
            }
            
            $active = $current_currency === $code ? 'active' : '';
            
            echo '<button class="wct-flag-button ' . $active . '" data-currency="' . esc_attr($code) . '" title="' . esc_attr($currency['name']) . '">';
            echo '<span class="wct-flag wct-flag-' . esc_attr($currency['flag']) . '"></span>';
            echo '</button>';
        }
        
        echo '</div>';
    }
    
    public function convert_product_price($price, $product) {
        // Evitar recursión
        if ($this->converting || empty($price) || !is_numeric($price)) {
            return $price;
        }
        
        $this->converting = true;
        
        $current_currency = $this->get_current_currency();
        $base_currency = get_option('wct_default_currency', get_woocommerce_currency());
        
        if ($current_currency === $base_currency) {
            $this->converting = false;
            return $price;
        }
        
        $converted_price = WCT_Exchange_Rates::convert_price($price, $base_currency, $current_currency);
        
        $this->converting = false;
        
        return $converted_price;
    }
    
    public function modify_checkout_fields($fields) {
        $current_currency = $this->get_current_currency();
        $base_currency = get_option('wct_default_currency', get_woocommerce_currency());
        
        if ($current_currency !== $base_currency) {
            $fields['billing']['billing_currency'] = array(
                'type' => 'hidden',
                'default' => $current_currency
            );
        }
        
        return $fields;
    }
    
    public function validate_checkout_currency() {
        // Verificar si WooCommerce está disponible
        if (!function_exists('WC') || !WC()->session) {
            return;
        }
        
        $current_currency = $this->get_current_currency();
        $base_currency = get_option('wct_default_currency', get_woocommerce_currency());
        
        if ($current_currency !== $base_currency) {
            $currencies = get_option('wct_currencies', array());
            
            if (!isset($currencies[$current_currency]) || !$currencies[$current_currency]['enabled']) {
                wc_add_notice(__('La moneda seleccionada no está disponible', 'wct-currency-toolbox'), 'error');
                return;
            }
            
            // Verificar métodos de pago permitidos
            $allowed_gateways = isset($currencies[$current_currency]['gateways']) ? $currencies[$current_currency]['gateways'] : array();
            $chosen_payment_method = WC()->session->get('chosen_payment_method');
            
            if (!empty($allowed_gateways) && !in_array($chosen_payment_method, $allowed_gateways)) {
                wc_add_notice(__('El método de pago seleccionado no está disponible para esta moneda', 'wct-currency-toolbox'), 'error');
            }
        }
    }
    
    public function add_currency_to_email($fields, $sent_to_admin, $order) {
        $currency = $order->get_meta('_order_currency');
        
        if ($currency) {
            $fields['order_currency'] = array(
                'label' => __('Moneda', 'wct-currency-toolbox'),
                'value' => $currency
            );
        }
        
        return $fields;
    }
    
    private function get_current_currency() {
        // Evitar recursión
        if ($this->getting_currency) {
            return get_option('wct_default_currency', 'USD');
        }
        
        $this->getting_currency = true;
        
        // Verificar si WooCommerce y su sesión están disponibles
        if (!function_exists('WC') || !WC()->session) {
            $this->getting_currency = false;
            return get_option('wct_default_currency', get_option('woocommerce_currency', 'USD'));
        }
        
        $session_currency = WC()->session->get('wct_currency');
        if ($session_currency) {
            $this->getting_currency = false;
            return $session_currency;
        }
        
        // Detección por IP si está habilitada
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
}
