# WooCommerce Currency Toolbox

Un plugin completo para WooCommerce que permite a los usuarios cambiar la moneda de la tienda con soporte para tipos de cambio automáticos, detección por IP y múltiples zonas de precios.

## Características Principales

### Para Administradores
- ✅ Configuración de múltiples monedas desde el backend
- ✅ Tipos de cambio manuales y automáticos vía API
- ✅ Integración con Fixer.io y ExchangeRate-API
- ✅ Opción para mostrar banderas de países
- ✅ Control de compras en moneda seleccionada
- ✅ Widget de cambio de moneda configurable
- ✅ Zonas de precios por ubicación
- ✅ Sistema de cupones con monedas específicas
- ✅ Integración con WPML para múltiples idiomas

### Para Usuarios Frontend
- ✅ Cambio de moneda en página de tienda
- ✅ Cambio de moneda en carrito
- ✅ Cambio de moneda en checkout
- ✅ Detección automática por IP
- ✅ Múltiples estilos de widget (dropdown, botones, banderas)
- ✅ Conversión automática de precios
- ✅ Soporte para productos simples y variables

## Instalación

1. Sube la carpeta `woocommerce-currency-toolbox` al directorio `/wp-content/plugins/`
2. Activa el plugin desde el panel de administración de WordPress
3. Ve a **Currency Switcher** en el menú de administración
4. Configura las monedas y tipos de cambio

## Configuración

### 1. Configuración General
- **Moneda Principal**: Selecciona la moneda base para todos los cálculos
- **Mostrar Banderas**: Habilita/deshabilita las banderas de países
- **Detección por IP**: Detecta automáticamente la moneda del usuario
- **Permitir Compra en Moneda Seleccionada**: Controla si los pagos se procesan en la moneda seleccionada

### 2. Gestión de Monedas
- Agrega, edita y elimina monedas
- Configura símbolos y formatos de precio
- Establece tipos de cambio manuales
- Asigna métodos de pago permitidos por moneda

### 3. Tipos de Cambio
- **Manual**: Establece tipos de cambio fijos
- **API**: Integración con servicios externos
  - Fixer.io
  - ExchangeRate-API
- Actualización automática programada

### 4. Zonas de Precios
- Crea zonas de precios por ubicación geográfica
- Asigna monedas específicas a cada zona
- Configura precios individuales por producto y zona

### 5. Widget y Shortcode
- **Posición**: Header, Footer, Tienda, Carrito, Checkout
- **Estilo**: Dropdown, Botones, Banderas
- **Shortcode**: `[wct_currency_switcher]`

## Uso

### Shortcode
```php
// Básico
[wct_currency_switcher]

// Con parámetros
[wct_currency_switcher style="buttons" show_flags="yes" currencies="USD,EUR,GBP"]
```

### PHP
```php
// Verificar si el plugin está activo
if (function_exists('wct_currency_switcher')) {
    wct_currency_switcher();
}

// Obtener moneda actual
$current_currency = WC()->session->get('wct_currency');

// Convertir precio
$converted_price = WCT_Exchange_Rates::convert_price($price, 'USD', 'EUR');
```

### Widget de WordPress
El plugin incluye un widget que puedes agregar a cualquier área de widgets de tu tema.

## Hooks y Filtros

### Acciones
```php
// Después de cambiar moneda
do_action('wct_currency_changed', $new_currency, $old_currency);

// Después de actualizar tipos de cambio
do_action('wct_exchange_rates_updated', $rates);
```

### Filtros
```php
// Filtrar monedas disponibles
apply_filters('wct_available_currencies', $currencies);

// Filtrar precio convertido
apply_filters('wct_converted_price', $price, $from_currency, $to_currency);

// Filtrar zona de precios
apply_filters('wct_pricing_zone', $zone, $user_location);
```

## Integración con WPML

El plugin incluye soporte completo para WPML:
- Asignación de monedas por idioma
- Cambio automático de moneda al cambiar idioma
- Filtrado de monedas por idioma actual

## Zonas de Precios

### Crear una Zona
1. Ve a **Currency Switcher > Zonas de Precios**
2. Haz clic en **Agregar Zona de Precios**
3. Configura nombre, países y moneda
4. Guarda la zona

### Configurar Precios por Zona
1. Edita un producto
2. En la sección **Precios por Zona**
3. Establece precios específicos para cada zona

## Cupones con Monedas

### Configurar Cupón
1. Edita un cupón
2. En la sección **Configuración de Monedas**
3. Selecciona monedas permitidas
4. Establece montos mínimos/máximos por moneda

## API de Tipos de Cambio

### Fixer.io
1. Regístrate en [Fixer.io](https://fixer.io)
2. Obtén tu clave API
3. Configura en **Currency Switcher > Tipos de Cambio**

### ExchangeRate-API
1. Regístrate en [ExchangeRate-API](https://exchangerate-api.com)
2. Obtén tu clave API
3. Configura en **Currency Switcher > Tipos de Cambio**

## Personalización

### CSS
```css
/* Personalizar estilo del switcher */
.wcs-currency-switcher {
    /* Tus estilos aquí */
}

/* Personalizar botones */
.wcs-currency-button {
    /* Tus estilos aquí */
}
```

### JavaScript
```javascript
// Evento personalizado al cambiar moneda
jQuery(document).on('wct_currency_changed', function(event, currency) {
    // Tu código aquí
});
```

## Solución de Problemas

### Los precios no se convierten
- Verifica que WooCommerce esté activo
- Asegúrate de que las monedas estén habilitadas
- Revisa los tipos de cambio

### La detección por IP no funciona
- Verifica que la detección esté habilitada
- Revisa la configuración de la API de geolocalización
- Comprueba los logs de errores

### Los widgets no aparecen
- Verifica la posición del widget
- Asegúrate de que el tema soporte widgets
- Revisa la configuración del widget

## Soporte

Para soporte técnico o reportar bugs, contacta al desarrollador.

## Changelog

### 1.0.0
- Lanzamiento inicial
- Soporte completo para múltiples monedas
- Integración con APIs de tipos de cambio
- Zonas de precios
- Soporte para WPML
- Sistema de cupones con monedas
- Widget y shortcode
- Detección por IP

## Traducciones / Translations

El plugin incluye traducciones en:
- 🇪🇸 Español (España)
- 🇺🇸 English (US)

Para agregar más idiomas, consulta la carpeta `/languages/`

## Licencia

GPL v2 o posterior
