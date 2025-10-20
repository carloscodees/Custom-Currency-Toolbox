<?php
/**
 * Plantilla para la página de configuración general
 */

if (!defined('ABSPATH')) {
    exit;
}

$default_currency = get_option('wct_default_currency', 'USD');
$enable_flags = get_option('wct_enable_flags', 'yes');
$enable_ip_detection = get_option('wct_enable_ip_detection', 'no');
$allow_purchase = get_option('wct_allow_purchase_in_selected_currency', 'yes');
$exchange_rate_api = get_option('wct_exchange_rate_api', 'manual');
$api_key = get_option('wct_api_key', '');
$update_interval = get_option('wct_update_interval', 'daily');
$widget_position = get_option('wct_widget_position', 'header');
$widget_style = get_option('wct_widget_style', 'dropdown');
?>

<div class="wrap">
    <h1><?php _e('Configuración General - Currency Switcher', 'wct-currency-toolbox'); ?></h1>
    
    <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Configuración guardada exitosamente', 'wct-currency-toolbox'); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="options.php">
        <?php settings_fields('wct_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="wct_default_currency"><?php _e('Moneda Principal', 'wct-currency-toolbox'); ?></label>
                </th>
                <td>
                    <select name="wct_default_currency" id="wct_default_currency">
                        <?php
                        $currencies = get_option('wct_currencies', array());
                        foreach ($currencies as $code => $currency) {
                            if ($currency['enabled']) {
                                $selected = selected($default_currency, $code, false);
                                echo "<option value='{$code}' {$selected}>{$currency['name']} ({$code})</option>";
                            }
                        }
                        ?>
                    </select>
                    <p class="description"><?php _e('Moneda base para todos los cálculos de tipos de cambio', 'wct-currency-toolbox'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wct_enable_flags"><?php _e('Mostrar Banderas', 'wct-currency-toolbox'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="wct_enable_flags" id="wct_enable_flags" value="yes" <?php checked($enable_flags, 'yes'); ?>>
                    <label for="wct_enable_flags"><?php _e('Mostrar banderas de países junto a las monedas', 'wct-currency-toolbox'); ?></label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wct_enable_ip_detection"><?php _e('Detección por IP', 'wct-currency-toolbox'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="wct_enable_ip_detection" id="wct_enable_ip_detection" value="yes" <?php checked($enable_ip_detection, 'yes'); ?>>
                    <label for="wct_enable_ip_detection"><?php _e('Detectar moneda automáticamente basada en la ubicación del usuario', 'wct-currency-toolbox'); ?></label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wct_allow_purchase_in_selected_currency"><?php _e('Permitir Compra en Moneda Seleccionada', 'wct-currency-toolbox'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="wct_allow_purchase_in_selected_currency" id="wct_allow_purchase_in_selected_currency" value="yes" <?php checked($allow_purchase, 'yes'); ?>>
                    <label for="wct_allow_purchase_in_selected_currency"><?php _e('Permitir que los clientes paguen en la moneda seleccionada', 'wct-currency-toolbox'); ?></label>
                    <p class="description"><?php _e('Si está desactivado, todos los pagos se procesarán en la moneda principal', 'wct-currency-toolbox'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wct_exchange_rate_api"><?php _e('Fuente de Tipos de Cambio', 'wct-currency-toolbox'); ?></label>
                </th>
                <td>
                    <select name="wct_exchange_rate_api" id="wct_exchange_rate_api">
                        <option value="manual" <?php selected($exchange_rate_api, 'manual'); ?>><?php _e('Manual', 'wct-currency-toolbox'); ?></option>
                        <option value="fixer" <?php selected($exchange_rate_api, 'fixer'); ?>><?php _e('Fixer.io', 'wct-currency-toolbox'); ?></option>
                        <option value="exchangerate" <?php selected($exchange_rate_api, 'exchangerate'); ?>><?php _e('ExchangeRate-API', 'wct-currency-toolbox'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr id="api_key_row" style="<?php echo $exchange_rate_api === 'manual' ? 'display:none;' : ''; ?>">
                <th scope="row">
                    <label for="wct_api_key"><?php _e('Clave API', 'wct-currency-toolbox'); ?></label>
                </th>
                <td>
                    <input type="text" name="wct_api_key" id="wct_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                    <p class="description"><?php _e('Ingresa tu clave API para el servicio de tipos de cambio seleccionado', 'wct-currency-toolbox'); ?></p>
                </td>
            </tr>
            
            <tr id="update_interval_row" style="<?php echo $exchange_rate_api === 'manual' ? 'display:none;' : ''; ?>">
                <th scope="row">
                    <label for="wct_update_interval"><?php _e('Intervalo de Actualización', 'wct-currency-toolbox'); ?></label>
                </th>
                <td>
                    <select name="wct_update_interval" id="wct_update_interval">
                        <option value="hourly" <?php selected($update_interval, 'hourly'); ?>><?php _e('Cada hora', 'wct-currency-toolbox'); ?></option>
                        <option value="daily" <?php selected($update_interval, 'daily'); ?>><?php _e('Diariamente', 'wct-currency-toolbox'); ?></option>
                        <option value="weekly" <?php selected($update_interval, 'weekly'); ?>><?php _e('Semanalmente', 'wct-currency-toolbox'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wct_widget_position"><?php _e('Posición del Widget', 'wct-currency-toolbox'); ?></label>
                </th>
                <td>
                    <select name="wct_widget_position" id="wct_widget_position">
                        <option value="header" <?php selected($widget_position, 'header'); ?>><?php _e('Header', 'wct-currency-toolbox'); ?></option>
                        <option value="footer" <?php selected($widget_position, 'footer'); ?>><?php _e('Footer', 'wct-currency-toolbox'); ?></option>
                        <option value="shop" <?php selected($widget_position, 'shop'); ?>><?php _e('Página de Tienda', 'wct-currency-toolbox'); ?></option>
                        <option value="cart" <?php selected($widget_position, 'cart'); ?>><?php _e('Carrito', 'wct-currency-toolbox'); ?></option>
                        <option value="checkout" <?php selected($widget_position, 'checkout'); ?>><?php _e('Checkout', 'wct-currency-toolbox'); ?></option>
                        <option value="widget" <?php selected($widget_position, 'widget'); ?>><?php _e('Solo Widget', 'wct-currency-toolbox'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="wct_widget_style"><?php _e('Estilo del Widget', 'wct-currency-toolbox'); ?></label>
                </th>
                <td>
                    <select name="wct_widget_style" id="wct_widget_style">
                        <option value="dropdown" <?php selected($widget_style, 'dropdown'); ?>><?php _e('Lista Desplegable', 'wct-currency-toolbox'); ?></option>
                        <option value="buttons" <?php selected($widget_style, 'buttons'); ?>><?php _e('Botones', 'wct-currency-toolbox'); ?></option>
                        <option value="flags" <?php selected($widget_style, 'flags'); ?>><?php _e('Solo Banderas', 'wct-currency-toolbox'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#wct_exchange_rate_api').change(function() {
        if ($(this).val() === 'manual') {
            $('#api_key_row, #update_interval_row').hide();
        } else {
            $('#api_key_row, #update_interval_row').show();
        }
    });
});
</script>
