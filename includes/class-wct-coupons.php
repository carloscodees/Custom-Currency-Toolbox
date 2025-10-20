<?php
/**
 * Clase para manejo de cupones con monedas específicas
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCT_Coupons {
    
    public function __construct() {
        add_action('woocommerce_coupon_options', array($this, 'add_currency_fields'));
        add_action('woocommerce_coupon_options_save', array($this, 'save_currency_fields'));
        
        add_filter('woocommerce_coupon_is_valid', array($this, 'validate_currency_coupon'), 10, 3);
        add_filter('woocommerce_coupon_get_discount_amount', array($this, 'convert_coupon_discount'), 10, 5);
    }
    
    public function add_currency_fields() {
        global $post;
        
        $coupon_currencies = get_post_meta($post->ID, '_wct_coupon_currencies', true);
        $coupon_currencies = is_array($coupon_currencies) ? $coupon_currencies : array();
        
        $min_amount_currencies = get_post_meta($post->ID, '_wct_min_amount_currencies', true);
        $min_amount_currencies = is_array($min_amount_currencies) ? $min_amount_currencies : array();
        
        $max_amount_currencies = get_post_meta($post->ID, '_wct_max_amount_currencies', true);
        $max_amount_currencies = is_array($max_amount_currencies) ? $max_amount_currencies : array();
        
        $currencies = get_option('wct_currencies', array());
        $enabled_currencies = array_filter($currencies, function($currency) {
            return $currency['enabled'];
        });
        
        echo '<div class="options_group wct-coupon-currencies">';
        echo '<h4>' . __('Configuración de Monedas', 'wct-currency-toolbox') . '</h4>';
        
        // Monedas permitidas
        echo '<p class="form-field">';
        echo '<label for="wct_coupon_currencies">' . __('Monedas Permitidas', 'wct-currency-toolbox') . '</label>';
        echo '<select id="wct_coupon_currencies" name="wct_coupon_currencies[]" multiple="multiple" style="width: 100%;">';
        
        foreach ($enabled_currencies as $code => $currency) {
            $selected = in_array($code, $coupon_currencies) ? 'selected' : '';
            echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($currency['name'] . ' (' . $code . ')') . '</option>';
        }
        
        echo '</select>';
        echo '<span class="description">' . __('Dejar vacío para permitir todas las monedas', 'wct-currency-toolbox') . '</span>';
        echo '</p>';
        
        // Montos mínimos por moneda
        echo '<h5>' . __('Montos Mínimos por Moneda', 'wct-currency-toolbox') . '</h5>';
        foreach ($enabled_currencies as $code => $currency) {
            $min_amount = isset($min_amount_currencies[$code]) ? $min_amount_currencies[$code] : '';
            
            echo '<p class="form-field">';
            echo '<label for="wct_min_amount_' . $code . '">' . sprintf(__('Monto Mínimo (%s)', 'wct-currency-toolbox'), $code) . '</label>';
            echo '<input type="number" id="wct_min_amount_' . $code . '" name="wct_min_amount_currencies[' . $code . ']" value="' . esc_attr($min_amount) . '" step="0.01" min="0">';
            echo '</p>';
        }
        
        // Montos máximos por moneda
        echo '<h5>' . __('Montos Máximos por Moneda', 'wct-currency-toolbox') . '</h5>';
        foreach ($enabled_currencies as $code => $currency) {
            $max_amount = isset($max_amount_currencies[$code]) ? $max_amount_currencies[$code] : '';
            
            echo '<p class="form-field">';
            echo '<label for="wct_max_amount_' . $code . '">' . sprintf(__('Monto Máximo (%s)', 'wct-currency-toolbox'), $code) . '</label>';
            echo '<input type="number" id="wct_max_amount_' . $code . '" name="wct_max_amount_currencies[' . $code . ']" value="' . esc_attr($max_amount) . '" step="0.01" min="0">';
            echo '</p>';
        }
        
        echo '</div>';
    }
    
    public function save_currency_fields($post_id) {
        // Guardar monedas permitidas
        $coupon_currencies = isset($_POST['wct_coupon_currencies']) ? array_map('sanitize_text_field', $_POST['wct_coupon_currencies']) : array();
        update_post_meta($post_id, '_wct_coupon_currencies', $coupon_currencies);
        
        // Guardar montos mínimos
        $min_amount_currencies = array();
        if (isset($_POST['wct_min_amount_currencies'])) {
            foreach ($_POST['wct_min_amount_currencies'] as $code => $amount) {
                $min_amount_currencies[sanitize_text_field($code)] = floatval($amount);
            }
        }
        update_post_meta($post_id, '_wct_min_amount_currencies', $min_amount_currencies);
        
        // Guardar montos máximos
        $max_amount_currencies = array();
        if (isset($_POST['wct_max_amount_currencies'])) {
            foreach ($_POST['wct_max_amount_currencies'] as $code => $amount) {
                $max_amount_currencies[sanitize_text_field($code)] = floatval($amount);
            }
        }
        update_post_meta($post_id, '_wct_max_amount_currencies', $max_amount_currencies);
    }
    
    public function validate_currency_coupon($is_valid, $coupon, $discount_obj) {
        if (!$is_valid) {
            return $is_valid;
        }
        
        $coupon_currencies = get_post_meta($coupon->get_id(), '_wct_coupon_currencies', true);
        
        // Si no hay monedas específicas configuradas, permitir todas
        if (empty($coupon_currencies)) {
            return $is_valid;
        }
        
        // Verificar si WooCommerce está disponible
        if (!function_exists('WC') || !WC()->session) {
            return $is_valid;
        }
        
        $current_currency = WC()->session->get('wct_currency', get_option('wct_default_currency', 'USD'));
        
        if (!in_array($current_currency, $coupon_currencies)) {
            throw new Exception(__('Este cupón no es válido para la moneda seleccionada', 'wct-currency-toolbox'));
        }
        
        return $is_valid;
    }
    
    public function convert_coupon_discount($discount, $discounting_amount, $cart_item, $single, $coupon) {
        // Verificar si WooCommerce está disponible
        if (!function_exists('WC') || !WC()->session) {
            return $discount;
        }
        
        $current_currency = WC()->session->get('wct_currency', get_option('wct_default_currency', 'USD'));
        $base_currency = get_option('wct_default_currency', 'USD');
        
        if ($current_currency === $base_currency) {
            return $discount;
        }
        
        // Convertir el descuento a la moneda actual
        $converted_discount = WCT_Exchange_Rates::convert_price($discount, $base_currency, $current_currency);
        
        return $converted_discount;
    }
    
    public static function validate_coupon_amounts($coupon_id, $current_currency) {
        $min_amount_currencies = get_post_meta($coupon_id, '_wct_min_amount_currencies', true);
        $max_amount_currencies = get_post_meta($coupon_id, '_wct_max_amount_currencies', true);
        
        $cart_total = WC()->cart->get_cart_contents_total();
        
        // Validar monto mínimo
        if (isset($min_amount_currencies[$current_currency]) && $min_amount_currencies[$current_currency] > 0) {
            if ($cart_total < $min_amount_currencies[$current_currency]) {
                return false;
            }
        }
        
        // Validar monto máximo
        if (isset($max_amount_currencies[$current_currency]) && $max_amount_currencies[$current_currency] > 0) {
            if ($cart_total > $max_amount_currencies[$current_currency]) {
                return false;
            }
        }
        
        return true;
    }
    
    public static function get_coupon_currencies($coupon_id) {
        return get_post_meta($coupon_id, '_wct_coupon_currencies', true);
    }
    
    public static function is_coupon_valid_for_currency($coupon_id, $currency) {
        $coupon_currencies = self::get_coupon_currencies($coupon_id);
        
        // Si no hay monedas específicas, es válido para todas
        if (empty($coupon_currencies)) {
            return true;
        }
        
        return in_array($currency, $coupon_currencies);
    }
}
