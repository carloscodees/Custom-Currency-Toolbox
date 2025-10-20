<?php
/**
 * Clase para zonas de precios
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCT_Zone_Pricing {
    
    public function __construct() {
        add_action('woocommerce_product_options_pricing', array($this, 'add_zone_pricing_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'save_zone_pricing'));
        add_action('woocommerce_variation_options_pricing', array($this, 'add_variation_zone_pricing'), 10, 3);
        add_action('woocommerce_save_product_variation', array($this, 'save_variation_zone_pricing'), 10, 2);
        
        // NOTA: Los hooks de precios por zona están desactivados temporalmente para evitar conflictos
        // con los hooks de conversión de moneda. Estos se pueden activar con mayor prioridad si es necesario.
        // add_filter('woocommerce_product_get_price', array($this, 'get_zone_price'), 5, 2);
        // add_filter('woocommerce_product_get_regular_price', array($this, 'get_zone_regular_price'), 5, 2);
        // add_filter('woocommerce_product_get_sale_price', array($this, 'get_zone_sale_price'), 5, 2);
    }
    
    public function add_zone_pricing_fields() {
        global $post;
        
        $zones = get_option('wct_pricing_zones', array());
        if (empty($zones)) {
            return;
        }
        
        echo '<div class="options_group wct-zone-pricing">';
        echo '<h4>' . __('Precios por Zona', 'wct-currency-toolbox') . '</h4>';
        
        foreach ($zones as $zone_id => $zone) {
            $zone_prices = get_post_meta($post->ID, '_wct_zone_prices_' . $zone_id, true);
            
            echo '<div class="zone-pricing-group">';
            echo '<h5>' . esc_html($zone['name']) . '</h5>';
            
            echo '<p class="form-field">';
            echo '<label for="wct_zone_regular_price_' . $zone_id . '">' . __('Precio Regular', 'wct-currency-toolbox') . '</label>';
            echo '<input type="number" id="wct_zone_regular_price_' . $zone_id . '" name="wct_zone_regular_price[' . $zone_id . ']" value="' . esc_attr($zone_prices['regular'] ?? '') . '" step="0.01" min="0">';
            echo '</p>';
            
            echo '<p class="form-field">';
            echo '<label for="wct_zone_sale_price_' . $zone_id . '">' . __('Precio de Oferta', 'wct-currency-toolbox') . '</label>';
            echo '<input type="number" id="wct_zone_sale_price_' . $zone_id . '" name="wct_zone_sale_price[' . $zone_id . ']" value="' . esc_attr($zone_prices['sale'] ?? '') . '" step="0.01" min="0">';
            echo '</p>';
            
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    public function save_zone_pricing($post_id) {
        $zones = get_option('wct_pricing_zones', array());
        
        foreach ($zones as $zone_id => $zone) {
            $regular_price = isset($_POST['wct_zone_regular_price'][$zone_id]) ? floatval($_POST['wct_zone_regular_price'][$zone_id]) : '';
            $sale_price = isset($_POST['wct_zone_sale_price'][$zone_id]) ? floatval($_POST['wct_zone_sale_price'][$zone_id]) : '';
            
            $zone_prices = array(
                'regular' => $regular_price,
                'sale' => $sale_price
            );
            
            update_post_meta($post_id, '_wct_zone_prices_' . $zone_id, $zone_prices);
        }
    }
    
    public function add_variation_zone_pricing($loop, $variation_data, $variation) {
        $zones = get_option('wct_pricing_zones', array());
        if (empty($zones)) {
            return;
        }
        
        echo '<div class="wct-variation-zone-pricing">';
        echo '<h4>' . __('Precios por Zona', 'wct-currency-toolbox') . '</h4>';
        
        foreach ($zones as $zone_id => $zone) {
            $zone_prices = get_post_meta($variation->ID, '_wct_zone_prices_' . $zone_id, true);
            
            echo '<div class="zone-pricing-group">';
            echo '<h5>' . esc_html($zone['name']) . '</h5>';
            
            echo '<p class="form-row form-row-first">';
            echo '<label>' . __('Precio Regular', 'wct-currency-toolbox') . '</label>';
            echo '<input type="number" name="wct_variation_zone_regular_price[' . $loop . '][' . $zone_id . ']" value="' . esc_attr($zone_prices['regular'] ?? '') . '" step="0.01" min="0">';
            echo '</p>';
            
            echo '<p class="form-row form-row-last">';
            echo '<label>' . __('Precio de Oferta', 'wct-currency-toolbox') . '</label>';
            echo '<input type="number" name="wct_variation_zone_sale_price[' . $loop . '][' . $zone_id . ']" value="' . esc_attr($zone_prices['sale'] ?? '') . '" step="0.01" min="0">';
            echo '</p>';
            
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    public function save_variation_zone_pricing($variation_id, $loop) {
        $zones = get_option('wct_pricing_zones', array());
        
        foreach ($zones as $zone_id => $zone) {
            $regular_price = isset($_POST['wct_variation_zone_regular_price'][$loop][$zone_id]) ? floatval($_POST['wct_variation_zone_regular_price'][$loop][$zone_id]) : '';
            $sale_price = isset($_POST['wct_variation_zone_sale_price'][$loop][$zone_id]) ? floatval($_POST['wct_variation_zone_sale_price'][$loop][$zone_id]) : '';
            
            $zone_prices = array(
                'regular' => $regular_price,
                'sale' => $sale_price
            );
            
            update_post_meta($variation_id, '_wct_zone_prices_' . $zone_id, $zone_prices);
        }
    }
    
    public function get_zone_price($price, $product) {
        $current_zone = $this->get_current_zone();
        if (!$current_zone) {
            return $price;
        }
        
        $zone_price = $this->get_zone_price_for_product($product, $current_zone);
        return $zone_price ? $zone_price : $price;
    }
    
    public function get_zone_regular_price($price, $product) {
        $current_zone = $this->get_current_zone();
        if (!$current_zone) {
            return $price;
        }
        
        $zone_prices = get_post_meta($product->get_id(), '_wct_zone_prices_' . $current_zone, true);
        return isset($zone_prices['regular']) && $zone_prices['regular'] ? $zone_prices['regular'] : $price;
    }
    
    public function get_zone_sale_price($price, $product) {
        $current_zone = $this->get_current_zone();
        if (!$current_zone) {
            return $price;
        }
        
        $zone_prices = get_post_meta($product->get_id(), '_wct_zone_prices_' . $current_zone, true);
        return isset($zone_prices['sale']) && $zone_prices['sale'] ? $zone_prices['sale'] : $price;
    }
    
    private function get_current_zone() {
        // Determinar zona basada en ubicación del usuario
        $user_country = $this->get_user_country();
        if (!$user_country) {
            return false;
        }
        
        $zones = get_option('wct_pricing_zones', array());
        foreach ($zones as $zone_id => $zone) {
            if (in_array($user_country, $zone['countries'])) {
                return $zone_id;
            }
        }
        
        return false;
    }
    
    private function get_user_country() {
        // Usar la misma lógica de detección de IP que la clase WCT_IP_Detection
        if (get_option('wct_enable_ip_detection', 'no') === 'yes') {
            return WCT_IP_Detection::get_country_by_ip(WCT_IP_Detection::get_user_ip());
        }
        
        return false;
    }
    
    private function get_zone_price_for_product($product, $zone_id) {
        $zone_prices = get_post_meta($product->get_id(), '_wct_zone_prices_' . $zone_id, true);
        
        if (empty($zone_prices)) {
            return false;
        }
        
        // Si hay precio de oferta, usarlo, sino usar precio regular
        if (!empty($zone_prices['sale']) && $zone_prices['sale'] > 0) {
            return $zone_prices['sale'];
        } elseif (!empty($zone_prices['regular']) && $zone_prices['regular'] > 0) {
            return $zone_prices['regular'];
        }
        
        return false;
    }
    
    public static function create_pricing_zone($name, $countries, $currency) {
        $zones = get_option('wct_pricing_zones', array());
        $zone_id = sanitize_title($name);
        
        $zones[$zone_id] = array(
            'name' => $name,
            'countries' => $countries,
            'currency' => $currency
        );
        
        update_option('wct_pricing_zones', $zones);
        return $zone_id;
    }
    
    public static function update_pricing_zone($zone_id, $name, $countries, $currency) {
        $zones = get_option('wct_pricing_zones', array());
        
        if (isset($zones[$zone_id])) {
            $zones[$zone_id] = array(
                'name' => $name,
                'countries' => $countries,
                'currency' => $currency
            );
            
            update_option('wct_pricing_zones', $zones);
            return true;
        }
        
        return false;
    }
    
    public static function delete_pricing_zone($zone_id) {
        $zones = get_option('wct_pricing_zones', array());
        
        if (isset($zones[$zone_id])) {
            unset($zones[$zone_id]);
            update_option('wct_pricing_zones', $zones);
            return true;
        }
        
        return false;
    }
    
    public static function get_pricing_zones() {
        return get_option('wct_pricing_zones', array());
    }
}
