<?php
/**
 * Plantilla para la página de zonas de precios
 */

if (!defined('ABSPATH')) {
    exit;
}

$pricing_zones = get_option('wct_pricing_zones', array());
$available_countries = array(
    'US' => 'Estados Unidos',
    'CA' => 'Canadá',
    'MX' => 'México',
    'GB' => 'Reino Unido',
    'DE' => 'Alemania',
    'FR' => 'Francia',
    'ES' => 'España',
    'IT' => 'Italia',
    'NL' => 'Países Bajos',
    'BE' => 'Bélgica',
    'AT' => 'Austria',
    'CH' => 'Suiza',
    'SE' => 'Suecia',
    'NO' => 'Noruega',
    'DK' => 'Dinamarca',
    'FI' => 'Finlandia',
    'PL' => 'Polonia',
    'CZ' => 'República Checa',
    'HU' => 'Hungría',
    'AU' => 'Australia',
    'NZ' => 'Nueva Zelanda',
    'JP' => 'Japón',
    'CN' => 'China',
    'KR' => 'Corea del Sur',
    'IN' => 'India',
    'BR' => 'Brasil',
    'AR' => 'Argentina',
    'CL' => 'Chile',
    'CO' => 'Colombia',
    'PE' => 'Perú',
    'UY' => 'Uruguay',
    'RU' => 'Rusia',
    'TR' => 'Turquía',
    'ZA' => 'Sudáfrica',
    'EG' => 'Egipto',
    'NG' => 'Nigeria'
);
?>

<div class="wrap wct-zone-pricing-page">
    <h1><?php _e('Zonas de Precios', 'wct-currency-toolbox'); ?></h1>
    
    <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Zonas de precios guardadas exitosamente', 'wct-currency-toolbox'); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="wct-admin-container">
        <div class="wct-form-section">
            <h2><?php _e('Gestión de Zonas de Precios', 'wct-currency-toolbox'); ?></h2>
            
            <div class="wct-form-row">
                <button type="button" id="add-pricing-zone" class="button button-primary"><?php _e('Agregar Zona de Precios', 'wct-currency-toolbox'); ?></button>
            </div>
            
            <div id="pricing-zones-list">
                <?php foreach ($pricing_zones as $zone_id => $zone): ?>
                    <div class="zone-item" data-zone="<?php echo esc_attr($zone_id); ?>">
                        <div class="zone-header">
                            <h3><?php echo esc_html($zone['name']); ?></h3>
                            <button type="button" class="button remove-pricing-zone"><?php _e('Eliminar', 'wct-currency-toolbox'); ?></button>
                        </div>
                        
                        <div class="zone-fields">
                            <div class="field-group">
                                <label><?php _e('Nombre de la Zona', 'wct-currency-toolbox'); ?></label>
                                <input type="text" name="pricing_zones[<?php echo esc_attr($zone_id); ?>][name]" value="<?php echo esc_attr($zone['name']); ?>" required>
                            </div>
                            
                            <div class="field-group">
                                <label><?php _e('Países', 'wct-currency-toolbox'); ?></label>
                                <select name="pricing_zones[<?php echo esc_attr($zone_id); ?>][countries][]" multiple style="height: 150px;">
                                    <?php foreach ($available_countries as $code => $name): ?>
                                        <option value="<?php echo esc_attr($code); ?>" <?php selected(in_array($code, $zone['countries'])); ?>>
                                            <?php echo esc_html($name . ' (' . $code . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="field-group">
                                <label><?php _e('Moneda', 'wct-currency-toolbox'); ?></label>
                                <select name="pricing_zones[<?php echo esc_attr($zone_id); ?>][currency]">
                                    <?php
                                    $currencies = get_option('wct_currencies', array());
                                    foreach ($currencies as $code => $currency) {
                                        if ($currency['enabled']) {
                                            $selected = selected($zone['currency'], $code, false);
                                            echo "<option value='{$code}' {$selected}>{$currency['name']} ({$code})</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('wct_save_pricing_zones'); ?>
                <input type="hidden" name="action" value="wct_save_pricing_zones">
                
                <div class="wct-form-row">
                    <button type="submit" class="button button-primary"><?php _e('Guardar Zonas de Precios', 'wct-currency-toolbox'); ?></button>
                </div>
            </form>
        </div>
        
        <div class="wct-form-section">
            <h2><?php _e('Configuración de Productos', 'wct-currency-toolbox'); ?></h2>
            
            <div class="wct-notice">
                <p><?php _e('Para configurar precios específicos por zona, edita cada producto individualmente. Los campos de precios por zona aparecerán en la sección de precios de cada producto.', 'wct-currency-toolbox'); ?></p>
            </div>
            
            <div class="wct-form-row">
                <a href="<?php echo admin_url('edit.php?post_type=product'); ?>" class="button"><?php _e('Gestionar Productos', 'wct-currency-toolbox'); ?></a>
            </div>
        </div>
        
        <div class="wct-form-section">
            <h2><?php _e('Estadísticas de Zonas', 'wct-currency-toolbox'); ?></h2>
            
            <?php if (!empty($pricing_zones)): ?>
                <table class="wct-table">
                    <thead>
                        <tr>
                            <th><?php _e('Zona', 'wct-currency-toolbox'); ?></th>
                            <th><?php _e('Países', 'wct-currency-toolbox'); ?></th>
                            <th><?php _e('Moneda', 'wct-currency-toolbox'); ?></th>
                            <th><?php _e('Productos con Precios', 'wct-currency-toolbox'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pricing_zones as $zone_id => $zone): ?>
                            <?php
                            // Contar productos con precios para esta zona
                            $products_with_prices = 0;
                            $args = array(
                                'post_type' => 'product',
                                'posts_per_page' => -1,
                                'meta_query' => array(
                                    array(
                                        'key' => '_wct_zone_prices_' . $zone_id,
                                        'compare' => 'EXISTS'
                                    )
                                )
                            );
                            $products = get_posts($args);
                            $products_with_prices = count($products);
                            ?>
                            <tr>
                                <td><?php echo esc_html($zone['name']); ?></td>
                                <td><?php echo count($zone['countries']); ?> <?php _e('países', 'wct-currency-toolbox'); ?></td>
                                <td><?php echo esc_html($zone['currency']); ?></td>
                                <td><?php echo $products_with_prices; ?> <?php _e('productos', 'wct-currency-toolbox'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="wct-notice">
                    <p><?php _e('No hay zonas de precios configuradas.', 'wct-currency-toolbox'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var zoneTemplate = `
        <div class="zone-item" data-zone="">
            <div class="zone-header">
                <h3><?php _e('Nueva Zona de Precios', 'wct-currency-toolbox'); ?></h3>
                <button type="button" class="button remove-pricing-zone"><?php _e('Eliminar', 'wct-currency-toolbox'); ?></button>
            </div>
            
            <div class="zone-fields">
                <div class="field-group">
                    <label><?php _e('Nombre de la Zona', 'wct-currency-toolbox'); ?></label>
                    <input type="text" name="pricing_zones[NEW][name]" value="" required>
                </div>
                
                <div class="field-group">
                    <label><?php _e('Países', 'wct-currency-toolbox'); ?></label>
                    <select name="pricing_zones[NEW][countries][]" multiple style="height: 150px;">
                        <?php foreach ($available_countries as $code => $name): ?>
                            <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name . ' (' . $code . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="field-group">
                    <label><?php _e('Moneda', 'wct-currency-toolbox'); ?></label>
                    <select name="pricing_zones[NEW][currency]">
                        <?php
                        $currencies = get_option('wct_currencies', array());
                        foreach ($currencies as $code => $currency) {
                            if ($currency['enabled']) {
                                echo "<option value='{$code}'>{$currency['name']} ({$code})</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    `;
    
    $('#add-pricing-zone').click(function() {
        var newIndex = $('#pricing-zones-list .zone-item').length;
        var newTemplate = zoneTemplate.replace(/NEW/g, 'NEW_' + newIndex);
        $('#pricing-zones-list').append(newTemplate);
        
        // Animar la nueva zona
        $('.zone-item').last().hide().slideDown(300);
    });
    
    $(document).on('click', '.remove-pricing-zone', function() {
        if (confirm('<?php _e('¿Estás seguro de que quieres eliminar esta zona de precios?', 'wct-currency-toolbox'); ?>')) {
            $(this).closest('.zone-item').slideUp(300, function() {
                $(this).remove();
            });
        }
    });
});
</script>

<style>
.zone-item {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
    background: #fafafa;
}

.zone-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.zone-fields {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.field-group {
    display: flex;
    flex-direction: column;
}

.field-group label {
    font-weight: 600;
    margin-bottom: 5px;
    color: #23282d;
}

.field-group input,
.field-group select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.field-group select[multiple] {
    min-height: 150px;
}

@media (max-width: 768px) {
    .zone-fields {
        grid-template-columns: 1fr;
    }
    
    .zone-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>
