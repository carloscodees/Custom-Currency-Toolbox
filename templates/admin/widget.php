<?php
/**
 * Plantilla para la página de configuración del widget
 */

if (!defined('ABSPATH')) {
    exit;
}

$widget_position = get_option('wct_widget_position', 'header');
$widget_style = get_option('wct_widget_style', 'dropdown');
$enable_flags = get_option('wct_enable_flags', 'yes');
$currencies = get_option('wct_currencies', array());
$enabled_currencies = array_filter($currencies, function($currency) {
    return $currency['enabled'];
});
?>

<div class="wrap wct-widget-page">
    <h1><?php _e('Configuración del Widget', 'wct-currency-toolbox'); ?></h1>
    
    <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Configuración del widget guardada exitosamente', 'wct-currency-toolbox'); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="wct-admin-container">
        <div class="wct-form-section">
            <h2><?php _e('Configuración General del Widget', 'wct-currency-toolbox'); ?></h2>
            
            <form method="post" action="options.php">
                <?php settings_fields('wct_settings'); ?>
                
                <div class="wct-form-row">
                    <label for="wct_widget_position"><?php _e('Posición del Widget', 'wct-currency-toolbox'); ?></label>
                    <select id="wct_widget_position" name="wct_widget_position">
                        <option value="header" <?php selected($widget_position, 'header'); ?>><?php _e('Header', 'wct-currency-toolbox'); ?></option>
                        <option value="footer" <?php selected($widget_position, 'footer'); ?>><?php _e('Footer', 'wct-currency-toolbox'); ?></option>
                        <option value="shop" <?php selected($widget_position, 'shop'); ?>><?php _e('Página de Tienda', 'wct-currency-toolbox'); ?></option>
                        <option value="cart" <?php selected($widget_position, 'cart'); ?>><?php _e('Carrito', 'wct-currency-toolbox'); ?></option>
                        <option value="checkout" <?php selected($widget_position, 'checkout'); ?>><?php _e('Checkout', 'wct-currency-toolbox'); ?></option>
                        <option value="widget" <?php selected($widget_position, 'widget'); ?>><?php _e('Solo Widget', 'wct-currency-toolbox'); ?></option>
                    </select>
                </div>
                
                <div class="wct-form-row">
                    <label for="wct_widget_style"><?php _e('Estilo del Widget', 'wct-currency-toolbox'); ?></label>
                    <select id="wct_widget_style" name="wct_widget_style">
                        <option value="dropdown" <?php selected($widget_style, 'dropdown'); ?>><?php _e('Lista Desplegable', 'wct-currency-toolbox'); ?></option>
                        <option value="buttons" <?php selected($widget_style, 'buttons'); ?>><?php _e('Botones', 'wct-currency-toolbox'); ?></option>
                        <option value="flags" <?php selected($widget_style, 'flags'); ?>><?php _e('Solo Banderas', 'wct-currency-toolbox'); ?></option>
                    </select>
                </div>
                
                <div class="wct-form-row">
                    <label>
                        <input type="checkbox" name="wct_enable_flags" value="yes" <?php checked($enable_flags, 'yes'); ?>>
                        <?php _e('Mostrar Banderas', 'wct-currency-toolbox'); ?>
                    </label>
                </div>
                
                <div class="wct-form-row">
                    <?php submit_button(__('Guardar Configuración', 'wct-currency-toolbox')); ?>
                </div>
            </form>
        </div>
        
        <div class="wct-form-section">
            <h2><?php _e('Vista Previa del Widget', 'wct-currency-toolbox'); ?></h2>
            
            <div class="widget-preview">
                <h4><?php _e('Vista Previa', 'wct-currency-toolbox'); ?></h4>
                <div class="wct-currency-switcher wct-style-<?php echo esc_attr($widget_style); ?>">
                    <?php if ($widget_style === 'dropdown'): ?>
                        <select id="wct-currency-select-preview" class="wct-currency-dropdown">
                            <?php foreach ($enabled_currencies as $code => $currency): ?>
                                <option value="<?php echo esc_attr($code); ?>">
                                    <?php echo esc_html($currency['symbol'] . ' ' . $code); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($widget_style === 'buttons'): ?>
                        <div class="wct-currency-buttons">
                            <?php foreach ($enabled_currencies as $code => $currency): ?>
                                <button class="wct-currency-button" data-currency="<?php echo esc_attr($code); ?>">
                                    <?php if ($enable_flags === 'yes' && !empty($currency['flag'])): ?>
                                        <span class="wct-flag wct-flag-<?php echo esc_attr($currency['flag']); ?>"></span>
                                    <?php endif; ?>
                                    <span class="wct-currency-text"><?php echo esc_html($currency['symbol'] . ' ' . $code); ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($widget_style === 'flags'): ?>
                        <div class="wct-currency-flags">
                            <?php foreach ($enabled_currencies as $code => $currency): ?>
                                <?php if (!empty($currency['flag'])): ?>
                                    <button class="wct-flag-button" data-currency="<?php echo esc_attr($code); ?>" title="<?php echo esc_attr($currency['name']); ?>">
                                        <span class="wct-flag wct-flag-<?php echo esc_attr($currency['flag']); ?>"></span>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="wct-form-section">
            <h2><?php _e('Widget de WordPress', 'wct-currency-toolbox'); ?></h2>
            
            <div class="wct-notice">
                <p><?php _e('También puedes agregar el Currency Switcher como un widget de WordPress en cualquier área de widgets de tu tema.', 'wct-currency-toolbox'); ?></p>
            </div>
            
            <div class="wct-form-row">
                <a href="<?php echo admin_url('widgets.php'); ?>" class="button"><?php _e('Gestionar Widgets', 'wct-currency-toolbox'); ?></a>
            </div>
        </div>
        
        <div class="wct-form-section">
            <h2><?php _e('Shortcode', 'wct-currency-toolbox'); ?></h2>
            
            <div class="wct-notice">
                <p><?php _e('También puedes usar el shortcode para mostrar el Currency Switcher en cualquier lugar:', 'wct-currency-toolbox'); ?></p>
            </div>
            
            <div class="wct-form-row">
                <label><?php _e('Shortcode Básico:', 'wct-currency-toolbox'); ?></label>
                <input type="text" value="[wct_currency_switcher]" readonly class="regular-text">
            </div>
            
            <div class="wct-form-row">
                <label><?php _e('Shortcode con Estilo:', 'wct-currency-toolbox'); ?></label>
                <input type="text" value="[wct_currency_switcher style='buttons' show_flags='yes']" readonly class="regular-text">
            </div>
            
            <div class="wct-form-row">
                <label><?php _e('Parámetros disponibles:', 'wct-currency-toolbox'); ?></label>
                <ul>
                    <li><code>style</code> - dropdown, buttons, flags</li>
                    <li><code>show_flags</code> - yes, no</li>
                    <li><code>show_symbols</code> - yes, no</li>
                    <li><code>currencies</code> - Lista de códigos de moneda separados por comas</li>
                </ul>
            </div>
        </div>
        
        <div class="wct-form-section">
            <h2><?php _e('Integración con Temas', 'wct-currency-toolbox'); ?></h2>
            
            <div class="wct-notice">
                <p><?php _e('Para integrar el Currency Switcher en tu tema, puedes usar las siguientes funciones PHP:', 'wct-currency-toolbox'); ?></p>
            </div>
            
            <div class="wct-form-row">
                <label><?php _e('Mostrar en cualquier lugar:', 'wct-currency-toolbox'); ?></label>
                <textarea readonly class="large-text" rows="3"><?php echo esc_html('<?php echo do_shortcode(\'[wct_currency_switcher]\'); ?>'); ?></textarea>
            </div>
            
            <div class="wct-form-row">
                <label><?php _e('Verificar si está habilitado:', 'wct-currency-toolbox'); ?></label>
                <textarea readonly class="large-text" rows="3"><?php echo esc_html('<?php if (function_exists(\'wct_currency_switcher\')) { wct_currency_switcher(); } ?>'); ?></textarea>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Actualizar vista previa cuando cambie el estilo
    $('#wct_widget_style').change(function() {
        var style = $(this).val();
        $('.widget-preview .wct-currency-switcher').removeClass().addClass('wct-currency-switcher wct-style-' + style);
        
        // Actualizar contenido de la vista previa
        updatePreview(style);
    });
    
    function updatePreview(style) {
        var previewHtml = '';
        
        if (style === 'dropdown') {
            previewHtml = '<select id="wct-currency-select-preview" class="wct-currency-dropdown">';
            <?php foreach ($enabled_currencies as $code => $currency): ?>
                previewHtml += '<option value="<?php echo esc_js($code); ?>"><?php echo esc_js($currency['symbol'] . ' ' . $code); ?></option>';
            <?php endforeach; ?>
            previewHtml += '</select>';
        } else if (style === 'buttons') {
            previewHtml = '<div class="wct-currency-buttons">';
            <?php foreach ($enabled_currencies as $code => $currency): ?>
                previewHtml += '<button class="wct-currency-button" data-currency="<?php echo esc_js($code); ?>">';
                <?php if ($enable_flags === 'yes' && !empty($currency['flag'])): ?>
                    previewHtml += '<span class="wct-flag wct-flag-<?php echo esc_js($currency['flag']); ?>"></span>';
                <?php endif; ?>
                previewHtml += '<span class="wct-currency-text"><?php echo esc_js($currency['symbol'] . ' ' . $code); ?></span>';
                previewHtml += '</button>';
            <?php endforeach; ?>
            previewHtml += '</div>';
        } else if (style === 'flags') {
            previewHtml = '<div class="wct-currency-flags">';
            <?php foreach ($enabled_currencies as $code => $currency): ?>
                <?php if (!empty($currency['flag'])): ?>
                    previewHtml += '<button class="wct-flag-button" data-currency="<?php echo esc_js($code); ?>" title="<?php echo esc_js($currency['name']); ?>">';
                    previewHtml += '<span class="wct-flag wct-flag-<?php echo esc_js($currency['flag']); ?>"></span>';
                    previewHtml += '</button>';
                <?php endif; ?>
            <?php endforeach; ?>
            previewHtml += '</div>';
        }
        
        $('.widget-preview .wct-currency-switcher').html(previewHtml);
    }
});
</script>

<style>
.widget-preview {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    background: #f9f9f9;
    margin: 20px 0;
}

.widget-preview h4 {
    margin: 0 0 15px 0;
    color: #23282d;
}

.widget-preview .wct-currency-switcher {
    margin: 0;
}

.wct-form-row textarea {
    font-family: monospace;
    font-size: 12px;
}

.wct-form-row ul {
    margin: 10px 0;
    padding-left: 20px;
}

.wct-form-row li {
    margin: 5px 0;
}

.wct-form-row code {
    background: #f1f1f1;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
}
</style>
