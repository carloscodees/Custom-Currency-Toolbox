<?php
/**
 * Plantilla para la página de gestión de monedas
 */

if (!defined('ABSPATH')) {
    exit;
}

$currencies = get_option('wct_currencies', array());
$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
?>

<div class="wrap">
    <h1><?php _e('Gestión de Monedas', 'wct-currency-toolbox'); ?></h1>
    
    <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Monedas guardadas exitosamente', 'wct-currency-toolbox'); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('wct_save_currencies'); ?>
        <input type="hidden" name="action" value="wct_save_currencies">
        
        <div class="wct-currencies-container">
            <div class="wct-currencies-header">
                <button type="button" class="button button-primary" id="add-currency"><?php _e('Agregar Moneda', 'wct-currency-toolbox'); ?></button>
            </div>
            
            <div id="currencies-list">
                <?php foreach ($currencies as $code => $currency): ?>
                    <div class="currency-item" data-currency="<?php echo esc_attr($code); ?>">
                        <div class="currency-header">
                            <h3><?php echo esc_html($currency['name']); ?> (<?php echo esc_html($code); ?>)</h3>
                            <button type="button" class="button remove-currency"><?php _e('Eliminar', 'wct-currency-toolbox'); ?></button>
                        </div>
                        
                        <div class="currency-fields">
                            <div class="field-group">
                                <label><?php _e('Código de Moneda', 'wct-currency-toolbox'); ?></label>
                                <input type="text" name="currencies[<?php echo esc_attr($code); ?>][code]" value="<?php echo esc_attr($code); ?>" readonly>
                            </div>
                            
                            <div class="field-group">
                                <label><?php _e('Nombre', 'wct-currency-toolbox'); ?></label>
                                <input type="text" name="currencies[<?php echo esc_attr($code); ?>][name]" value="<?php echo esc_attr($currency['name']); ?>" required>
                            </div>
                            
                            <div class="field-group">
                                <label><?php _e('Símbolo', 'wct-currency-toolbox'); ?></label>
                                <input type="text" name="currencies[<?php echo esc_attr($code); ?>][symbol]" value="<?php echo esc_attr($currency['symbol']); ?>" required>
                            </div>
                            
                            <div class="field-group">
                                <label><?php _e('Formato de Precio', 'wct-currency-toolbox'); ?></label>
                                <input type="text" name="currencies[<?php echo esc_attr($code); ?>][format]" value="<?php echo esc_attr($currency['format']); ?>" placeholder="%1$s%2$s">
                                <p class="description"><?php _e('%1$s = símbolo, %2$s = precio', 'wct-currency-toolbox'); ?></p>
                            </div>
                            
                            <div class="field-group">
                                <label><?php _e('Código de Bandera', 'wct-currency-toolbox'); ?></label>
                                <select class="wct-country-select" name="currencies[<?php echo esc_attr($code); ?>][flag]">
                                    <option value=""><?php _e('Seleccionar país', 'wct-currency-toolbox'); ?></option>
                                    <?php
                                    $countries = array(
                                        'af' => __('Afganistán', 'wct-currency-toolbox'),
                                        'al' => __('Albania', 'wct-currency-toolbox'),
                                        'de' => __('Alemania', 'wct-currency-toolbox'),
                                        'ad' => __('Andorra', 'wct-currency-toolbox'),
                                        'ao' => __('Angola', 'wct-currency-toolbox'),
                                        'sa' => __('Arabia Saudita', 'wct-currency-toolbox'),
                                        'dz' => __('Argelia', 'wct-currency-toolbox'),
                                        'ar' => __('Argentina', 'wct-currency-toolbox'),
                                        'am' => __('Armenia', 'wct-currency-toolbox'),
                                        'au' => __('Australia', 'wct-currency-toolbox'),
                                        'at' => __('Austria', 'wct-currency-toolbox'),
                                        'az' => __('Azerbaiyán', 'wct-currency-toolbox'),
                                        'bs' => __('Bahamas', 'wct-currency-toolbox'),
                                        'bd' => __('Bangladesh', 'wct-currency-toolbox'),
                                        'bb' => __('Barbados', 'wct-currency-toolbox'),
                                        'be' => __('Bélgica', 'wct-currency-toolbox'),
                                        'bz' => __('Belice', 'wct-currency-toolbox'),
                                        'bo' => __('Bolivia', 'wct-currency-toolbox'),
                                        'br' => __('Brasil', 'wct-currency-toolbox'),
                                        'bg' => __('Bulgaria', 'wct-currency-toolbox'),
                                        'ca' => __('Canadá', 'wct-currency-toolbox'),
                                        'cl' => __('Chile', 'wct-currency-toolbox'),
                                        'cn' => __('China', 'wct-currency-toolbox'),
                                        'co' => __('Colombia', 'wct-currency-toolbox'),
                                        'kr' => __('Corea del Sur', 'wct-currency-toolbox'),
                                        'cr' => __('Costa Rica', 'wct-currency-toolbox'),
                                        'hr' => __('Croacia', 'wct-currency-toolbox'),
                                        'cu' => __('Cuba', 'wct-currency-toolbox'),
                                        'dk' => __('Dinamarca', 'wct-currency-toolbox'),
                                        'ec' => __('Ecuador', 'wct-currency-toolbox'),
                                        'eg' => __('Egipto', 'wct-currency-toolbox'),
                                        'sv' => __('El Salvador', 'wct-currency-toolbox'),
                                        'ae' => __('Emiratos Árabes Unidos', 'wct-currency-toolbox'),
                                        'es' => __('España', 'wct-currency-toolbox'),
                                        'us' => __('Estados Unidos', 'wct-currency-toolbox'),
                                        'ee' => __('Estonia', 'wct-currency-toolbox'),
                                        'ph' => __('Filipinas', 'wct-currency-toolbox'),
                                        'fi' => __('Finlandia', 'wct-currency-toolbox'),
                                        'fr' => __('Francia', 'wct-currency-toolbox'),
                                        'gr' => __('Grecia', 'wct-currency-toolbox'),
                                        'gt' => __('Guatemala', 'wct-currency-toolbox'),
                                        'hn' => __('Honduras', 'wct-currency-toolbox'),
                                        'hk' => __('Hong Kong', 'wct-currency-toolbox'),
                                        'hu' => __('Hungría', 'wct-currency-toolbox'),
                                        'in' => __('India', 'wct-currency-toolbox'),
                                        'id' => __('Indonesia', 'wct-currency-toolbox'),
                                        'iq' => __('Irak', 'wct-currency-toolbox'),
                                        'ir' => __('Irán', 'wct-currency-toolbox'),
                                        'ie' => __('Irlanda', 'wct-currency-toolbox'),
                                        'is' => __('Islandia', 'wct-currency-toolbox'),
                                        'il' => __('Israel', 'wct-currency-toolbox'),
                                        'it' => __('Italia', 'wct-currency-toolbox'),
                                        'jm' => __('Jamaica', 'wct-currency-toolbox'),
                                        'jp' => __('Japón', 'wct-currency-toolbox'),
                                        'jo' => __('Jordania', 'wct-currency-toolbox'),
                                        'kz' => __('Kazajistán', 'wct-currency-toolbox'),
                                        'ke' => __('Kenia', 'wct-currency-toolbox'),
                                        'kw' => __('Kuwait', 'wct-currency-toolbox'),
                                        'lv' => __('Letonia', 'wct-currency-toolbox'),
                                        'lb' => __('Líbano', 'wct-currency-toolbox'),
                                        'lt' => __('Lituania', 'wct-currency-toolbox'),
                                        'lu' => __('Luxemburgo', 'wct-currency-toolbox'),
                                        'my' => __('Malasia', 'wct-currency-toolbox'),
                                        'mt' => __('Malta', 'wct-currency-toolbox'),
                                        'ma' => __('Marruecos', 'wct-currency-toolbox'),
                                        'mx' => __('México', 'wct-currency-toolbox'),
                                        'ni' => __('Nicaragua', 'wct-currency-toolbox'),
                                        'ng' => __('Nigeria', 'wct-currency-toolbox'),
                                        'no' => __('Noruega', 'wct-currency-toolbox'),
                                        'nz' => __('Nueva Zelanda', 'wct-currency-toolbox'),
                                        'nl' => __('Países Bajos', 'wct-currency-toolbox'),
                                        'pk' => __('Pakistán', 'wct-currency-toolbox'),
                                        'pa' => __('Panamá', 'wct-currency-toolbox'),
                                        'py' => __('Paraguay', 'wct-currency-toolbox'),
                                        'pe' => __('Perú', 'wct-currency-toolbox'),
                                        'pl' => __('Polonia', 'wct-currency-toolbox'),
                                        'pt' => __('Portugal', 'wct-currency-toolbox'),
                                        'pr' => __('Puerto Rico', 'wct-currency-toolbox'),
                                        'gb' => __('Reino Unido', 'wct-currency-toolbox'),
                                        'cz' => __('República Checa', 'wct-currency-toolbox'),
                                        'do' => __('República Dominicana', 'wct-currency-toolbox'),
                                        'ro' => __('Rumania', 'wct-currency-toolbox'),
                                        'ru' => __('Rusia', 'wct-currency-toolbox'),
                                        'sg' => __('Singapur', 'wct-currency-toolbox'),
                                        'za' => __('Sudáfrica', 'wct-currency-toolbox'),
                                        'se' => __('Suecia', 'wct-currency-toolbox'),
                                        'ch' => __('Suiza', 'wct-currency-toolbox'),
                                        'th' => __('Tailandia', 'wct-currency-toolbox'),
                                        'tw' => __('Taiwán', 'wct-currency-toolbox'),
                                        'tr' => __('Turquía', 'wct-currency-toolbox'),
                                        'ua' => __('Ucrania', 'wct-currency-toolbox'),
                                        'uy' => __('Uruguay', 'wct-currency-toolbox'),
                                        've' => __('Venezuela', 'wct-currency-toolbox'),
                                        'vn' => __('Vietnam', 'wct-currency-toolbox'),
                                        'eu' => __('Unión Europea', 'wct-currency-toolbox'),
                                    );
                                    foreach ($countries as $code_country => $name) {
                                        $selected = ($currency['flag'] == $code_country) ? 'selected' : '';
                                        echo '<option value="' . esc_attr($code_country) . '" ' . $selected . '>' . esc_html($name) . ' (' . strtoupper($code_country) . ')</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="field-group">
                                <label>
                                    <input type="checkbox" name="currencies[<?php echo esc_attr($code); ?>][enabled]" value="1" <?php checked($currency['enabled']); ?>>
                                    <?php _e('Habilitada', 'wct-currency-toolbox'); ?>
                                </label>
                            </div>
                            
                            <div class="field-group">
                                <label><?php _e('Tipo de Cambio Manual', 'wct-currency-toolbox'); ?></label>
                                <input type="number" name="currencies[<?php echo esc_attr($code); ?>][rate]" value="<?php echo esc_attr($currency['rate']); ?>" step="0.000001" min="0">
                                <p class="description"><?php _e('Tipo de cambio respecto a la moneda principal', 'wct-currency-toolbox'); ?></p>
                            </div>
                            
                            <div class="field-group">
                                <label><?php _e('Métodos de Pago Permitidos', 'wct-currency-toolbox'); ?></label>
                                <div class="gateways-list">
                                    <?php foreach ($available_gateways as $gateway_id => $gateway): ?>
                                        <label>
                                            <input type="checkbox" name="currencies[<?php echo esc_attr($code); ?>][gateways][]" value="<?php echo esc_attr($gateway_id); ?>" 
                                                   <?php checked(in_array($gateway_id, $currency['gateways'])); ?>>
                                            <?php echo esc_html($gateway->get_title()); ?>
                                        </label><br>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php submit_button(__('Guardar Monedas', 'wct-currency-toolbox')); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    var currencyTemplate = `
        <div class="currency-item" data-currency="">
            <div class="currency-header">
                <h3><?php _e('Nueva Moneda', 'wct-currency-toolbox'); ?></h3>
                <button type="button" class="button remove-currency"><?php _e('Eliminar', 'wct-currency-toolbox'); ?></button>
            </div>
            
            <div class="currency-fields">
                <div class="field-group">
                    <label><?php _e('Código de Moneda', 'wct-currency-toolbox'); ?></label>
                    <input type="text" name="currencies[NEW][code]" value="" required>
                </div>
                
                <div class="field-group">
                    <label><?php _e('Nombre', 'wct-currency-toolbox'); ?></label>
                    <input type="text" name="currencies[NEW][name]" value="" required>
                </div>
                
                <div class="field-group">
                    <label><?php _e('Símbolo', 'wct-currency-toolbox'); ?></label>
                    <input type="text" name="currencies[NEW][symbol]" value="" required>
                </div>
                
                <div class="field-group">
                    <label><?php _e('Formato de Precio', 'wct-currency-toolbox'); ?></label>
                    <input type="text" name="currencies[NEW][format]" value="%1$s%2$s" placeholder="%1$s%2$s">
                </div>
                
                <div class="field-group">
                    <label><?php _e('Código de Bandera', 'wct-currency-toolbox'); ?></label>
                    <select class="wct-country-select" name="currencies[NEW][flag]">
                        <option value=""><?php _e('Seleccionar país', 'wct-currency-toolbox'); ?></option>
                        <?php
                        foreach ($countries as $code_country => $name) {
                            echo '<option value="' . esc_attr($code_country) . '">' . esc_html($name) . ' (' . strtoupper($code_country) . ')</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="field-group">
                    <label>
                        <input type="checkbox" name="currencies[NEW][enabled]" value="1" checked>
                        <?php _e('Habilitada', 'wct-currency-toolbox'); ?>
                    </label>
                </div>
                
                <div class="field-group">
                    <label><?php _e('Tipo de Cambio Manual', 'wct-currency-toolbox'); ?></label>
                    <input type="number" name="currencies[NEW][rate]" value="1" step="0.000001" min="0">
                </div>
                
                <div class="field-group">
                    <label><?php _e('Métodos de Pago Permitidos', 'wct-currency-toolbox'); ?></label>
                    <div class="gateways-list">
                        <?php foreach ($available_gateways as $gateway_id => $gateway): ?>
                            <label>
                                <input type="checkbox" name="currencies[NEW][gateways][]" value="<?php echo esc_attr($gateway_id); ?>">
                                <?php echo esc_html($gateway->get_title()); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#add-currency').click(function() {
        var newIndex = $('#currencies-list .currency-item').length;
        var newTemplate = currencyTemplate.replace(/NEW/g, 'NEW_' + newIndex);
        $('#currencies-list').append(newTemplate);
    });
    
    $(document).on('click', '.remove-currency', function() {
        if (confirm('<?php _e('¿Estás seguro de que quieres eliminar esta moneda?', 'wct-currency-toolbox'); ?>')) {
            $(this).closest('.currency-item').remove();
        }
    });
});
</script>

<style>
.wct-currencies-container {
    max-width: 1000px;
}

.currency-item {
    border: 1px solid #ddd;
    margin-bottom: 20px;
    padding: 20px;
    background: #fff;
}

.currency-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.currency-fields {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.field-group {
    display: flex;
    flex-direction: column;
}

.field-group label {
    font-weight: bold;
    margin-bottom: 5px;
}

.field-group input,
.field-group select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 100%;
}

.field-group select.wct-country-select {
    max-height: 200px;
}

.gateways-list {
    max-height: 150px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    background: #f9f9f9;
}

.gateways-list label {
    display: block;
    margin-bottom: 5px;
    font-weight: normal;
}
</style>
