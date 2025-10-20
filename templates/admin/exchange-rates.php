<?php
/**
 * Plantilla para la página de tipos de cambio
 */

if (!defined('ABSPATH')) {
    exit;
}

$exchange_rate_api = get_option('wct_exchange_rate_api', 'manual');
$api_key = get_option('wct_api_key', '');
$currencies = get_option('wct_currencies', array());
$base_currency = get_option('wct_default_currency', 'USD');
?>

<div class="wrap wct-exchange-rates-page">
    <h1><?php _e('Tipos de Cambio', 'wct-currency-toolbox'); ?></h1>
    
    <?php if (isset($_GET['message']) && $_GET['message'] === 'updated'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Tipos de cambio actualizados exitosamente', 'wct-currency-toolbox'); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="wct-admin-container">
        <div class="wct-form-section">
            <h2><?php _e('Configuración de API', 'wct-currency-toolbox'); ?></h2>
            
            <div class="wct-form-row">
                <label for="wct_exchange_rate_api"><?php _e('Fuente de Tipos de Cambio', 'wct-currency-toolbox'); ?></label>
                <select id="wct_exchange_rate_api" name="wct_exchange_rate_api">
                    <option value="manual" <?php selected($exchange_rate_api, 'manual'); ?>><?php _e('Manual', 'wct-currency-toolbox'); ?></option>
                    <option value="fixer" <?php selected($exchange_rate_api, 'fixer'); ?>><?php _e('Fixer.io', 'wct-currency-toolbox'); ?></option>
                    <option value="exchangerate" <?php selected($exchange_rate_api, 'exchangerate'); ?>><?php _e('ExchangeRate-API', 'wct-currency-toolbox'); ?></option>
                </select>
            </div>
            
            <div id="api_key_row" style="<?php echo $exchange_rate_api === 'manual' ? 'display:none;' : ''; ?>">
                <div class="wct-form-row">
                    <label for="wct_api_key"><?php _e('Clave API', 'wct-currency-toolbox'); ?></label>
                    <input type="text" id="wct_api_key" name="wct_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                    <button type="button" id="test-api-connection" class="button"><?php _e('Probar Conexión', 'wct-currency-toolbox'); ?></button>
                </div>
            </div>
            
            <div class="wct-form-row">
                <button type="button" id="update-exchange-rates" class="button button-primary"><?php _e('Actualizar Tipos de Cambio', 'wct-currency-toolbox'); ?></button>
            </div>
        </div>
        
        <div class="wct-form-section">
            <h2><?php _e('Tipos de Cambio Actuales', 'wct-currency-toolbox'); ?></h2>
            
            <?php if ($exchange_rate_api === 'manual'): ?>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('wct_update_rates_manual'); ?>
                    <input type="hidden" name="action" value="wct_update_rates_manual">
                    
                    <table class="wct-table">
                        <thead>
                            <tr>
                                <th><?php _e('Moneda', 'wct-currency-toolbox'); ?></th>
                                <th><?php _e('Tipo de Cambio', 'wct-currency-toolbox'); ?></th>
                                <th><?php _e('Última Actualización', 'wct-currency-toolbox'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($currencies as $code => $currency): ?>
                                <?php if ($code === $base_currency || !$currency['enabled']): continue; endif; ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($currency['name']); ?></strong><br>
                                        <small><?php echo esc_html($code); ?></small>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="rates[<?php echo esc_attr($code); ?>]" 
                                               value="<?php echo esc_attr($currency['rate']); ?>" 
                                               step="0.000001" 
                                               min="0" 
                                               class="regular-text manual-rate-input"
                                               data-currency="<?php echo esc_attr($code); ?>">
                                        <small><?php echo sprintf(__('1 %s = %s %s', 'wct-currency-toolbox'), $base_currency, $currency['rate'], $code); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        global $wpdb;
                                        $table_name = $wpdb->prefix . 'wct_exchange_rates';
                                        $last_update = $wpdb->get_var($wpdb->prepare(
                                            "SELECT date FROM {$table_name} WHERE from_currency = %s AND to_currency = %s ORDER BY date DESC LIMIT 1",
                                            $base_currency,
                                            $code
                                        ));
                                        
                                        if ($last_update) {
                                            echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_update)));
                                        } else {
                                            echo __('Nunca', 'wct-currency-toolbox');
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="wct-form-row">
                        <button type="submit" class="button button-primary"><?php _e('Guardar Tipos de Cambio', 'wct-currency-toolbox'); ?></button>
                    </div>
                </form>
            <?php else: ?>
                <div class="wct-notice">
                    <p><?php _e('Los tipos de cambio se actualizan automáticamente según la configuración de la API.', 'wct-currency-toolbox'); ?></p>
                </div>
                
                <table class="wct-table">
                    <thead>
                        <tr>
                            <th><?php _e('Moneda', 'wct-currency-toolbox'); ?></th>
                            <th><?php _e('Tipo de Cambio', 'wct-currency-toolbox'); ?></th>
                            <th><?php _e('Última Actualización', 'wct-currency-toolbox'); ?></th>
                            <th><?php _e('Acciones', 'wct-currency-toolbox'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($currencies as $code => $currency): ?>
                            <?php if ($code === $base_currency || !$currency['enabled']): continue; endif; ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($currency['name']); ?></strong><br>
                                    <small><?php echo esc_html($code); ?></small>
                                </td>
                                <td>
                                    <span class="rate-value"><?php echo esc_html($currency['rate']); ?></span>
                                    <small><?php echo sprintf(__('1 %s = %s %s', 'wct-currency-toolbox'), $base_currency, $currency['rate'], $code); ?></small>
                                </td>
                                <td>
                                    <?php
                                    global $wpdb;
                                    $table_name = $wpdb->prefix . 'wct_exchange_rates';
                                    $last_update = $wpdb->get_var($wpdb->prepare(
                                        "SELECT date FROM {$table_name} WHERE from_currency = %s AND to_currency = %s ORDER BY date DESC LIMIT 1",
                                        $base_currency,
                                        $code
                                    ));
                                    
                                    if ($last_update) {
                                        echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_update)));
                                    } else {
                                        echo __('Nunca', 'wct-currency-toolbox');
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button type="button" class="button update-single-rate" data-currency="<?php echo esc_attr($code); ?>">
                                        <?php _e('Actualizar', 'wct-currency-toolbox'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="wct-form-section">
            <h2><?php _e('Historial de Tipos de Cambio', 'wct-currency-toolbox'); ?></h2>
            
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'wct_exchange_rates';
            $history = $wpdb->get_results(
                "SELECT * FROM {$table_name} ORDER BY date DESC LIMIT 50"
            );
            ?>
            
            <?php if (!empty($history)): ?>
                <table class="wct-table">
                    <thead>
                        <tr>
                            <th><?php _e('Fecha', 'wct-currency-toolbox'); ?></th>
                            <th><?php _e('De', 'wct-currency-toolbox'); ?></th>
                            <th><?php _e('A', 'wct-currency-toolbox'); ?></th>
                            <th><?php _e('Tipo de Cambio', 'wct-currency-toolbox'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $rate): ?>
                            <tr>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($rate->date))); ?></td>
                                <td><?php echo esc_html($rate->from_currency); ?></td>
                                <td><?php echo esc_html($rate->to_currency); ?></td>
                                <td><?php echo esc_html($rate->rate); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="wct-notice">
                    <p><?php _e('No hay historial de tipos de cambio disponible.', 'wct-currency-toolbox'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Probar conexión API
    $('#test-api-connection').click(function() {
        var apiType = $('#wct_exchange_rate_api').val();
        var apiKey = $('#wct_api_key').val();
        var baseCurrency = '<?php echo esc_js($base_currency); ?>';
        
        if (!apiKey) {
            alert('<?php _e('Por favor, ingresa una clave API', 'wct-currency-toolbox'); ?>');
            return;
        }
        
        $(this).prop('disabled', true).text('<?php _e('Probando...', 'wct-currency-toolbox'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wct_test_api',
                api_type: apiType,
                api_key: apiKey,
                base_currency: baseCurrency,
                nonce: '<?php echo wp_create_nonce('wct_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Conexión exitosa:', 'wct-currency-toolbox'); ?> ' + response.data.message);
                } else {
                    alert('<?php _e('Error:', 'wct-currency-toolbox'); ?> ' + response.data.message);
                }
            },
            error: function() {
                alert('<?php _e('Error de conexión', 'wct-currency-toolbox'); ?>');
            },
            complete: function() {
                $('#test-api-connection').prop('disabled', false).text('<?php _e('Probar Conexión', 'wct-currency-toolbox'); ?>');
            }
        });
    });
    
    // Actualizar tipos de cambio
    $('#update-exchange-rates').click(function() {
        $(this).prop('disabled', true).text('<?php _e('Actualizando...', 'wct-currency-toolbox'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wct_update_exchange_rates',
                nonce: '<?php echo wp_create_nonce('wct_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Tipos de cambio actualizados exitosamente', 'wct-currency-toolbox'); ?>');
                    location.reload();
                } else {
                    alert('<?php _e('Error al actualizar los tipos de cambio', 'wct-currency-toolbox'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error de conexión', 'wct-currency-toolbox'); ?>');
            },
            complete: function() {
                $('#update-exchange-rates').prop('disabled', false).text('<?php _e('Actualizar Tipos de Cambio', 'wct-currency-toolbox'); ?>');
            }
        });
    });
});
</script>
