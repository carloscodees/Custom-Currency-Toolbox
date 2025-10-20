<?php
/**
 * Widget para cambio de moneda
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCT_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'wct_currency_switcher',
            __('Currency Switcher', 'wct-currency-toolbox'),
            array(
                'description' => __('Permite a los usuarios cambiar la moneda de la tienda', 'wct-currency-toolbox'),
                'classname' => 'wct-currency-switcher-widget'
            )
        );
    }
    
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $style = isset($instance['style']) ? $instance['style'] : 'dropdown';
        $show_flags = isset($instance['show_flags']) ? $instance['show_flags'] : true;
        $show_symbols = isset($instance['show_symbols']) ? $instance['show_symbols'] : true;
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        $this->render_switcher($style, $show_flags, $show_symbols);
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = isset($instance['title']) ? $instance['title'] : __('Seleccionar Moneda', 'wct-currency-toolbox');
        $style = isset($instance['style']) ? $instance['style'] : 'dropdown';
        $show_flags = isset($instance['show_flags']) ? $instance['show_flags'] : true;
        $show_symbols = isset($instance['show_symbols']) ? $instance['show_symbols'] : true;
        ?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Título:', 'wct-currency-toolbox'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('style'); ?>"><?php _e('Estilo:', 'wct-currency-toolbox'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('style'); ?>" name="<?php echo $this->get_field_name('style'); ?>">
                <option value="dropdown" <?php selected($style, 'dropdown'); ?>><?php _e('Lista Desplegable', 'wct-currency-toolbox'); ?></option>
                <option value="buttons" <?php selected($style, 'buttons'); ?>><?php _e('Botones', 'wct-currency-toolbox'); ?></option>
                <option value="flags" <?php selected($style, 'flags'); ?>><?php _e('Solo Banderas', 'wct-currency-toolbox'); ?></option>
            </select>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_flags); ?> id="<?php echo $this->get_field_id('show_flags'); ?>" name="<?php echo $this->get_field_name('show_flags'); ?>">
            <label for="<?php echo $this->get_field_id('show_flags'); ?>"><?php _e('Mostrar Banderas', 'wct-currency-toolbox'); ?></label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_symbols); ?> id="<?php echo $this->get_field_id('show_symbols'); ?>" name="<?php echo $this->get_field_name('show_symbols'); ?>">
            <label for="<?php echo $this->get_field_id('show_symbols'); ?>"><?php _e('Mostrar Símbolos', 'wct-currency-toolbox'); ?></label>
        </p>
        
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['style'] = (!empty($new_instance['style'])) ? strip_tags($new_instance['style']) : 'dropdown';
        $instance['show_flags'] = isset($new_instance['show_flags']) ? true : false;
        $instance['show_symbols'] = isset($new_instance['show_symbols']) ? true : false;
        
        return $instance;
    }
    
    private function render_switcher($style, $show_flags, $show_symbols) {
        $currencies = get_option('wct_currencies', array());
        $current_currency = WC()->session->get('wct_currency', get_option('wct_default_currency', 'USD'));
        $enable_flags = get_option('wct_enable_flags', 'yes') === 'yes' && $show_flags;
        
        if (empty($currencies)) {
            return;
        }
        
        $enabled_currencies = array_filter($currencies, function($currency) {
            return $currency['enabled'];
        });
        
        if (empty($enabled_currencies)) {
            return;
        }
        
        echo '<div class="wct-currency-switcher" data-style="' . esc_attr($style) . '">';
        
        switch ($style) {
            case 'dropdown':
                $this->render_dropdown($enabled_currencies, $current_currency, $enable_flags, $show_symbols);
                break;
            case 'buttons':
                $this->render_buttons($enabled_currencies, $current_currency, $enable_flags, $show_symbols);
                break;
            case 'flags':
                $this->render_flags($enabled_currencies, $current_currency);
                break;
        }
        
        echo '</div>';
    }
    
    private function render_dropdown($currencies, $current_currency, $show_flags, $show_symbols) {
        echo '<select id="wct-currency-select" class="wct-currency-dropdown">';
        
        foreach ($currencies as $code => $currency) {
            $selected = selected($current_currency, $code, false);
            $display_text = $code;
            
            if ($show_symbols && !empty($currency['symbol'])) {
                $display_text = $currency['symbol'] . ' ' . $code;
            }
            
            echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($display_text) . '</option>';
        }
        
        echo '</select>';
    }
    
    private function render_buttons($currencies, $current_currency, $show_flags, $show_symbols) {
        echo '<div class="wct-currency-buttons">';
        
        foreach ($currencies as $code => $currency) {
            $active = $current_currency === $code ? 'active' : '';
            $display_text = $code;
            
            if ($show_symbols && !empty($currency['symbol'])) {
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
}

// Registrar el widget
add_action('widgets_init', function() {
    register_widget('WCT_Widget');
});
