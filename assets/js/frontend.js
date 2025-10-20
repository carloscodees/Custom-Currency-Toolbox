/**
 * JavaScript para el frontend del Currency Switcher
 */

(function($) {
    'use strict';
    
    var WCT = {
        init: function() {
            this.bindEvents();
            this.initializeSwitcher();
        },
        
        bindEvents: function() {
            // Evento para cambio de moneda
            $(document).on('change', '#wct-currency-select', this.handleCurrencyChange);
            $(document).on('click', '.wct-currency-button, .wct-flag-button', this.handleCurrencyClick);
            
            // Evento para actualizar precios
            $(document.body).on('updated_cart_totals', this.updatePrices);
            $(document.body).on('updated_checkout', this.updatePrices);
        },
        
        initializeSwitcher: function() {
            // Inicializar el switcher si existe
            if ($('#wct-currency-switcher').length) {
                this.setCurrentCurrency();
            }
        },
        
        handleCurrencyChange: function(e) {
            var currency = $(this).val();
            WCT.switchCurrency(currency);
        },
        
        handleCurrencyClick: function(e) {
            e.preventDefault();
            var currency = $(this).data('currency');
            WCT.switchCurrency(currency);
        },
        
        switchCurrency: function(currency) {
            if (!currency) {
                return;
            }
            
            // Mostrar estado de carga
            WCT.showLoading();
            
            // Enviar petición AJAX
            $.ajax({
                url: wct_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wct_switch_currency',
                    currency: currency,
                    nonce: wct_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WCT.updateCurrencySwitcher(currency);
                        WCT.updatePrices();
                        WCT.showMessage(response.data.message, 'success');
                    } else {
                        WCT.showMessage('Error al cambiar la moneda', 'error');
                    }
                },
                error: function() {
                    WCT.showMessage('Error de conexión', 'error');
                },
                complete: function() {
                    WCT.hideLoading();
                }
            });
        },
        
        updateCurrencySwitcher: function(currency) {
            // Actualizar dropdown
            $('#wct-currency-select').val(currency);
            
            // Actualizar botones
            $('.wct-currency-button, .wct-flag-button').removeClass('active');
            $('.wct-currency-button[data-currency="' + currency + '"], .wct-flag-button[data-currency="' + currency + '"]').addClass('active');
        },
        
        updatePrices: function() {
            // Recargar la página para actualizar todos los precios
            // Esto es necesario porque WooCommerce no tiene hooks para actualizar todos los precios dinámicamente
            if (typeof wc_cart_fragments_params !== 'undefined') {
                $(document.body).trigger('wc_fragment_refresh');
            } else {
                location.reload();
            }
        },
        
        setCurrentCurrency: function() {
            var currentCurrency = wct_ajax.current_currency;
            
            // Establecer moneda actual en el switcher
            $('#wct-currency-select').val(currentCurrency);
            $('.wct-currency-button[data-currency="' + currentCurrency + '"], .wct-flag-button[data-currency="' + currentCurrency + '"]').addClass('active');
        },
        
        showLoading: function() {
            $('.wct-currency-switcher').addClass('wct-loading');
        },
        
        hideLoading: function() {
            $('.wct-currency-switcher').removeClass('wct-loading');
        },
        
        showMessage: function(message, type) {
            // Remover mensajes existentes
            $('.wct-message').remove();
            
            // Crear nuevo mensaje
            var messageHtml = '<div class="wct-message ' + type + '">' + message + '</div>';
            
            // Insertar mensaje
            if ($('#wct-currency-switcher').length) {
                $('#wct-currency-switcher').after(messageHtml);
            } else {
                $('body').prepend(messageHtml);
            }
            
            // Auto-ocultar mensaje después de 3 segundos
            setTimeout(function() {
                $('.wct-message').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        },
        
        convertPrice: function(price, fromCurrency, toCurrency) {
            // Esta función se puede usar para conversiones del lado del cliente
            // aunque la conversión real se hace en el servidor
            return price;
        }
    };
    
    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        WCT.init();
    });
    
    // Exponer WCS globalmente para uso externo
    window.WCT = WCS;
    
})(jQuery);

/**
 * Funciones adicionales para integración con WooCommerce
 */

// Actualizar mini cart cuando cambie la moneda
jQuery(document).ready(function($) {
    // Hook para actualizar mini cart
    $(document.body).on('wc_fragment_refresh', function() {
        // El mini cart se actualizará automáticamente
    });
    
    // Hook para actualizar checkout
    $(document.body).on('checkout_error', function() {
        // Manejar errores de checkout relacionados con moneda
    });
});

/**
 * Funciones para detección automática de moneda
 */
jQuery(document).ready(function($) {
    // Detectar moneda por IP si está habilitado
    if (typeof wct_ajax !== 'undefined' && wct_ajax.enable_ip_detection) {
        $.ajax({
            url: wct_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wct_detect_currency_by_ip',
                nonce: wct_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.currency) {
                    // Cambiar automáticamente a la moneda detectada
                    WCT.switchCurrency(response.data.currency);
                }
            }
        });
    }
});

/**
 * Funciones para zonas de precios
 */
jQuery(document).ready(function($) {
    // Detectar zona de precios basada en ubicación
    if (typeof wct_ajax !== 'undefined' && wct_ajax.enable_zone_pricing) {
        $.ajax({
            url: wct_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wct_detect_pricing_zone',
                nonce: wct_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.zone) {
                    // Aplicar precios de zona
                    WCT.applyZonePricing(response.data.zone);
                }
            }
        });
    }
});

// Función para aplicar precios de zona
WCT.applyZonePricing = function(zone) {
    // Esta función se implementaría para aplicar precios específicos de zona
    // Los precios se actualizarían dinámicamente basados en la zona detectada
    console.log('Aplicando precios de zona:', zone);
};
