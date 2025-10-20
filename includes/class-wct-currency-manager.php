<?php
/**
 * Clase para gestión de monedas
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCT_Currency_Manager {
    
    public function __construct() {
        add_action('woocommerce_checkout_order_processed', array($this, 'save_order_currency'));
        add_action('woocommerce_order_status_changed', array($this, 'handle_order_status_change'), 10, 3);
    }
    
    public function save_order_currency($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $current_currency = WC()->session->get('wct_currency');
        if ($current_currency) {
            $order->update_meta_data('_order_currency', $current_currency);
            $order->save();
        }
    }
    
    public function handle_order_status_change($order_id, $old_status, $new_status) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $order_currency = $order->get_meta('_order_currency');
        if (!$order_currency) {
            return;
        }
        
        // Si el pedido se completa, procesar el pago en la moneda correcta
        if ($new_status === 'completed' && $old_status !== 'completed') {
            $this->process_currency_payment($order, $order_currency);
        }
    }
    
    private function process_currency_payment($order, $currency) {
        // Aquí se implementaría la lógica para procesar el pago en la moneda específica
        // Esto dependería de la pasarela de pago utilizada
        
        $payment_method = $order->get_payment_method();
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        
        if (isset($gateways[$payment_method])) {
            $gateway = $gateways[$payment_method];
            
            // Verificar si la pasarela soporta múltiples monedas
            if (method_exists($gateway, 'supports_currency')) {
                if (!$gateway->supports_currency($currency)) {
                    // Convertir a moneda base si la pasarela no soporta la moneda
                    $this->convert_order_to_base_currency($order);
                }
            }
        }
    }
    
    private function convert_order_to_base_currency($order) {
        $base_currency = get_option('wct_default_currency', get_woocommerce_currency());
        $order_currency = $order->get_meta('_order_currency');
        
        if ($order_currency === $base_currency) {
            return;
        }
        
        $rate = WCT_Exchange_Rates::get_rate($order_currency, $base_currency);
        
        // Actualizar totales del pedido
        $total = $order->get_total();
        $converted_total = $total * $rate;
        
        $order->set_total($converted_total);
        $order->set_currency($base_currency);
        $order->update_meta_data('_original_currency', $order_currency);
        $order->update_meta_data('_exchange_rate', $rate);
        $order->save();
    }
    
    public static function get_available_currencies() {
        $currencies = get_option('wct_currencies', array());
        return array_filter($currencies, function($currency) {
            return $currency['enabled'];
        });
    }
    
    public static function is_currency_enabled($currency_code) {
        $currencies = get_option('wct_currencies', array());
        return isset($currencies[$currency_code]) && $currencies[$currency_code]['enabled'];
    }
    
    public static function get_currency_info($currency_code) {
        $currencies = get_option('wct_currencies', array());
        return isset($currencies[$currency_code]) ? $currencies[$currency_code] : false;
    }
    
    public static function format_price($price, $currency_code) {
        $currency_info = self::get_currency_info($currency_code);
        
        if (!$currency_info) {
            return wc_price($price);
        }
        
        $symbol = $currency_info['symbol'];
        $format = $currency_info['format'];
        
        $formatted_price = sprintf($format, $symbol, number_format($price, 2));
        
        return $formatted_price;
    }
}
