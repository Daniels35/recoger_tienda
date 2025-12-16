# 📍 Custom Local Pickup (WooCommerce)

**Método de envío "Recoger en Tienda" con restricción geográfica inteligente.**

Este plugin para WooCommerce añade una opción de envío personalizada que **solo se activa** cuando el cliente selecciona una ciudad específica dentro del área de cobertura (por defecto, el Valle de Aburrá, Colombia). Además, muestra avisos condicionales en el checkout para informar al usuario sobre los tiempos de entrega y requisitos para retirar su pedido.

## 📋 Características Principales

### 🚚 Logística Condicional
* **Geolocalización por Ciudad:** El método de envío "Recoger en tienda" permanece oculto por defecto. Solo se hace visible si la ciudad de envío ingresada por el cliente coincide con la lista permitida (Medellín, Envigado, Bello, Itagüí, etc.).
* **Costo Cero:** Configura automáticamente el costo de envío a `$0` cuando se selecciona esta opción.

### 💻 Experiencia de Usuario (Checkout)
* **Actualización en Tiempo Real:** Incluye un script de jQuery que detecta cambios en el campo "Ciudad" (`#billing_city`) y fuerza la actualización del checkout (`update_checkout`) al instante para recalcular los métodos de envío disponibles.
* **Mensajes Informativos:** Si la opción está disponible, despliega un aviso visual (`#local-pickup-message`) explicando las condiciones: tiempo de espera (5 días hábiles), requisito de presentar cédula y enlace directo a WhatsApp para dudas.

### 🛠️ Gestión Técnica
* **Clase de Envío Nativa:** Extiende la clase `WC_Shipping_Method` para integrarse limpiamente con el núcleo de WooCommerce, asegurando compatibilidad con otros plugins de pagos y envíos.
* **Gestión de Sesiones:** Utiliza `WC()->session` para controlar cuándo mostrar u ocultar los mensajes de aviso, evitando duplicados visuales.

## 📂 Estructura del Plugin

* `recoger_tienda_woocommerce.php`: Archivo único que contiene:
    * Definición de la clase `WC_Custom_Local_Pickup_Method`.
    * Lógica de filtrado `woocommerce_package_rates`.
    * Scripts JS para el refresco del checkout (`custom_refresh_checkout_script`).
    * HTML del aviso de recogida.

## 🚀 Instalación

1.  Sube el archivo `recoger_tienda_woocommerce.php` (o su carpeta) a `/wp-content/plugins/`.
2.  Activa el plugin desde el panel de WordPress.
3.  Verifica que las ciudades configuradas en el código coincidan con las que usa tu tienda WooCommerce.

## ⚙️ Configuración (Hardcoded)

Este plugin no tiene panel de administración. La configuración se realiza directamente en el código fuente.

**1. Editar el Mensaje de Aviso:**
Busca la función `custom_display_local_pickup_message` para cambiar el texto o el número de WhatsApp.

**2. Definir Ciudades Permitidas:**
Busca la función `custom_toggle_shipping_methods_based_on_city` y edita el array `$valle_de_aburra_cities`. Debes usar el formato exacto que WooCommerce espera (nombre o código):

```php
$valle_de_aburra_cities = array(
    'MEDELLIN (ANT) (05001000)',
    'ENVIGADO (ANT) (05266000)',
    // Añade tus ciudades aquí...
);
