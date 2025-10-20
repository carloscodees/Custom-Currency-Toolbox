/**
 * JavaScript para el panel de administración del Currency Switcher
 */

(function($) {
    'use strict';
    
    var WCSAdmin = {
        init: function() {
            this.bindEvents();
            this.initializeComponents();
        },
        
        bindEvents: function() {
            // Eventos para gestión de monedas
            $(document).on('click', '#add-currency', this.addCurrency);
            $(document).on('click', '.remove-currency', this.removeCurrency);
            
            // Eventos para tipos de cambio
            $(document).on('click', '#test-api-connection', this.testApiConnection);
            $(document).on('click', '#update-exchange-rates', this.updateExchangeRates);
            $(document).on('click', '#save-manual-rates', this.saveManualRates);
            
            // Eventos para zonas de precios
            $(document).on('click', '#add-pricing-zone', this.addPricingZone);
            $(document).on('click', '.remove-pricing-zone', this.removePricingZone);
            
            // Eventos para configuración
            $(document).on('change', '#wct_exchange_rate_api', this.toggleApiFields);
            $(document).on('change', '#wct_enable_ip_detection', this.toggleIpDetection);
            
            // Eventos para validación
            $(document).on('submit', 'form', this.validateForm);
        },
        
        initializeComponents: function() {
            // Inicializar componentes específicos de cada página
            this.initializeCurrenciesPage();
            this.initializeExchangeRatesPage();
            this.initializeZonePricingPage();
            this.initializeWidgetPage();
        },
        
        initializeCurrenciesPage: function() {
            // Inicializar funcionalidad específica de la página de monedas
            if ($('.wct-currencies-page').length) {
                this.setupCurrencyValidation();
            }
        },
        
        initializeExchangeRatesPage: function() {
            // Inicializar funcionalidad específica de la página de tipos de cambio
            if ($('.wct-exchange-rates-page').length) {
                this.setupExchangeRatesValidation();
            }
        },
        
        initializeZonePricingPage: function() {
            // Inicializar funcionalidad específica de la página de zonas de precios
            if ($('.wct-zone-pricing-page').length) {
                this.setupZonePricingValidation();
            }
        },
        
        initializeWidgetPage: function() {
            // Inicializar funcionalidad específica de la página de widget
            if ($('.wct-widget-page').length) {
                this.setupWidgetPreview();
            }
        },
        
        addCurrency: function(e) {
            e.preventDefault();
            
            var currencyTemplate = WCSAdmin.getCurrencyTemplate();
            var newIndex = $('#currencies-list .currency-item').length;
            var newTemplate = currencyTemplate.replace(/NEW/g, 'NEW_' + newIndex);
            
            $('#currencies-list').append(newTemplate);
            
            // Animar la nueva moneda
            $('.currency-item').last().hide().slideDown(300);
        },
        
        removeCurrency: function(e) {
            e.preventDefault();
            
            if (confirm('¿Estás seguro de que quieres eliminar esta moneda?')) {
                $(this).closest('.currency-item').slideUp(300, function() {
                    $(this).remove();
                });
            }
        },
        
        getCurrencyTemplate: function() {
            return `
                <div class="currency-item" data-currency="">
                    <div class="currency-header">
                        <h3>Nueva Moneda</h3>
                        <button type="button" class="button remove-currency">Eliminar</button>
                    </div>
                    
                    <div class="currency-fields">
                        <div class="field-group">
                            <label>Código de Moneda</label>
                            <input type="text" name="currencies[NEW][code]" value="" required>
                        </div>
                        
                        <div class="field-group">
                            <label>Nombre</label>
                            <input type="text" name="currencies[NEW][name]" value="" required>
                        </div>
                        
                        <div class="field-group">
                            <label>Símbolo</label>
                            <input type="text" name="currencies[NEW][symbol]" value="" required>
                        </div>
                        
                        <div class="field-group">
                            <label>Formato de Precio</label>
                            <input type="text" name="currencies[NEW][format]" value="%1$s%2$s" placeholder="%1$s%2$s">
                        </div>
                        
                        <div class="field-group">
                            <label>Código de Bandera</label>
                            <input type="text" name="currencies[NEW][flag]" value="" placeholder="us, eu, gb">
                        </div>
                        
                        <div class="field-group">
                            <label>
                                <input type="checkbox" name="currencies[NEW][enabled]" value="1" checked>
                                Habilitada
                            </label>
                        </div>
                        
                        <div class="field-group">
                            <label>Tipo de Cambio Manual</label>
                            <input type="number" name="currencies[NEW][rate]" value="1" step="0.000001" min="0">
                        </div>
                    </div>
                </div>
            `;
        },
        
        testApiConnection: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var apiType = $('#wct_exchange_rate_api').val();
            var apiKey = $('#wct_api_key').val();
            var baseCurrency = $('#wct_default_currency').val();
            
            if (!apiKey) {
                alert('Por favor, ingresa una clave API');
                return;
            }
            
            $button.prop('disabled', true).text('Probando...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wct_test_api',
                    api_type: apiType,
                    api_key: apiKey,
                    base_currency: baseCurrency,
                    nonce: $('#wct_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('Conexión exitosa: ' + response.data.message);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Error de conexión');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Probar Conexión');
                }
            });
        },
        
        updateExchangeRates: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            $button.prop('disabled', true).text('Actualizando...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wct_update_exchange_rates',
                    nonce: $('#wct_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('Tipos de cambio actualizados exitosamente');
                        location.reload();
                    } else {
                        alert('Error al actualizar los tipos de cambio');
                    }
                },
                error: function() {
                    alert('Error de conexión');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Actualizar Tipos de Cambio');
                }
            });
        },
        
        saveManualRates: function(e) {
            e.preventDefault();
            
            var rates = {};
            $('.manual-rate-input').each(function() {
                var currency = $(this).data('currency');
                var rate = $(this).val();
                if (rate) {
                    rates[currency] = rate;
                }
            });
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wct_update_rates_manual',
                    rates: rates,
                    nonce: $('#wct_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('Tipos de cambio guardados exitosamente');
                    } else {
                        alert('Error al guardar los tipos de cambio');
                    }
                },
                error: function() {
                    alert('Error de conexión');
                }
            });
        },
        
        addPricingZone: function(e) {
            e.preventDefault();
            
            var zoneTemplate = WCSAdmin.getZoneTemplate();
            var newIndex = $('#pricing-zones-list .zone-item').length;
            var newTemplate = zoneTemplate.replace(/NEW/g, 'NEW_' + newIndex);
            
            $('#pricing-zones-list').append(newTemplate);
            
            // Animar la nueva zona
            $('.zone-item').last().hide().slideDown(300);
        },
        
        removePricingZone: function(e) {
            e.preventDefault();
            
            if (confirm('¿Estás seguro de que quieres eliminar esta zona de precios?')) {
                $(this).closest('.zone-item').slideUp(300, function() {
                    $(this).remove();
                });
            }
        },
        
        getZoneTemplate: function() {
            return `
                <div class="zone-item" data-zone="">
                    <div class="zone-header">
                        <h3>Nueva Zona de Precios</h3>
                        <button type="button" class="button remove-pricing-zone">Eliminar</button>
                    </div>
                    
                    <div class="zone-fields">
                        <div class="field-group">
                            <label>Nombre de la Zona</label>
                            <input type="text" name="pricing_zones[NEW][name]" value="" required>
                        </div>
                        
                        <div class="field-group">
                            <label>Países</label>
                            <select name="pricing_zones[NEW][countries][]" multiple>
                                <option value="US">Estados Unidos</option>
                                <option value="CA">Canadá</option>
                                <option value="MX">México</option>
                                <option value="GB">Reino Unido</option>
                                <option value="DE">Alemania</option>
                                <option value="FR">Francia</option>
                                <option value="ES">España</option>
                                <option value="IT">Italia</option>
                                <option value="AU">Australia</option>
                                <option value="JP">Japón</option>
                            </select>
                        </div>
                        
                        <div class="field-group">
                            <label>Moneda</label>
                            <select name="pricing_zones[NEW][currency]">
                                <option value="USD">USD - Dólar Estadounidense</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="GBP">GBP - Libra Esterlina</option>
                                <option value="CAD">CAD - Dólar Canadiense</option>
                                <option value="AUD">AUD - Dólar Australiano</option>
                                <option value="JPY">JPY - Yen Japonés</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
        },
        
        toggleApiFields: function() {
            var apiType = $(this).val();
            var $apiKeyRow = $('#api_key_row');
            var $updateIntervalRow = $('#update_interval_row');
            
            if (apiType === 'manual') {
                $apiKeyRow.hide();
                $updateIntervalRow.hide();
            } else {
                $apiKeyRow.show();
                $updateIntervalRow.show();
            }
        },
        
        toggleIpDetection: function() {
            var enabled = $(this).is(':checked');
            var $ipSettings = $('.ip-detection-settings');
            
            if (enabled) {
                $ipSettings.show();
            } else {
                $ipSettings.hide();
            }
        },
        
        setupCurrencyValidation: function() {
            // Validar códigos de moneda únicos
            $(document).on('blur', 'input[name*="[code]"]', function() {
                var code = $(this).val().toUpperCase();
                var $input = $(this);
                var $currencyItem = $input.closest('.currency-item');
                
                // Verificar duplicados
                var duplicates = $('input[name*="[code]"]').not($input).filter(function() {
                    return $(this).val().toUpperCase() === code;
                });
                
                if (duplicates.length > 0) {
                    $input.addClass('error');
                    $input.after('<span class="error-message">Código de moneda duplicado</span>');
                } else {
                    $input.removeClass('error');
                    $input.next('.error-message').remove();
                }
            });
        },
        
        setupExchangeRatesValidation: function() {
            // Validar tipos de cambio
            $(document).on('blur', '.rate-input', function() {
                var rate = parseFloat($(this).val());
                var $input = $(this);
                
                if (rate < 0) {
                    $input.addClass('error');
                    $input.after('<span class="error-message">El tipo de cambio no puede ser negativo</span>');
                } else {
                    $input.removeClass('error');
                    $input.next('.error-message').remove();
                }
            });
        },
        
        setupZonePricingValidation: function() {
            // Validar nombres de zona únicos
            $(document).on('blur', 'input[name*="[name]"]', function() {
                var name = $(this).val();
                var $input = $(this);
                
                // Verificar duplicados
                var duplicates = $('input[name*="[name]"]').not($input).filter(function() {
                    return $(this).val() === name;
                });
                
                if (duplicates.length > 0) {
                    $input.addClass('error');
                    $input.after('<span class="error-message">Nombre de zona duplicado</span>');
                } else {
                    $input.removeClass('error');
                    $input.next('.error-message').remove();
                }
            });
        },
        
        setupWidgetPreview: function() {
            // Actualizar vista previa del widget
            $(document).on('change', '#wct_widget_style', function() {
                var style = $(this).val();
                $('.widget-preview .wct-currency-switcher').removeClass().addClass('wct-currency-switcher wct-style-' + style);
            });
        },
        
        validateForm: function(e) {
            var $form = $(this);
            var hasErrors = false;
            
            // Limpiar errores anteriores
            $form.find('.error').removeClass('error');
            $form.find('.error-message').remove();
            
            // Validar campos requeridos
            $form.find('input[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('error');
                    $(this).after('<span class="error-message">Este campo es requerido</span>');
                    hasErrors = true;
                }
            });
            
            if (hasErrors) {
                e.preventDefault();
                alert('Por favor, corrige los errores antes de continuar');
            }
        }
    };
    
    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        WCSAdmin.init();
    });
    
    // Exponer WCSAdmin globalmente
    window.WCTAdmin = WCSAdmin;
    
})(jQuery);
