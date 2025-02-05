<?php
/**
 * Plugin Name: Custom Local Pickup
 * Plugin URI: https://daniels35.com/
 * Description: Agrega un método de envío personalizado "Recoger en tienda" y gestiona su visibilidad según la ciudad seleccionada en el checkout.
 * Version: 1.0
 * Author: Daniel Diaz Tag Marketing
 * Author URI: https://www.tagdigital.com.co/
 * License: GPL2
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Registrar el método de envío personalizado
add_action('woocommerce_shipping_init', 'custom_register_local_pickup_method');

function custom_register_local_pickup_method() {
    class WC_Custom_Local_Pickup_Method extends WC_Shipping_Method {
        public function __construct() {
            $this->id                 = 'custom_local_pickup';
            $this->method_title       = 'Recoger en tienda';
            $this->method_description = 'Método para recoger el producto directamente en la tienda.';
            $this->enabled            = 'yes';
            $this->title              = 'Recoger en tienda';
        }

        public function calculate_shipping($package = array()) {
            $rate = array(
                'id'    => $this->id,
                'label' => $this->title,
                'cost'  => 0,
            );
            $this->add_rate($rate);
        }
    }
}

// Agregar el método a WooCommerce
add_filter('woocommerce_shipping_methods', 'custom_add_local_pickup_method');

function custom_add_local_pickup_method($methods) {
    $methods['custom_local_pickup'] = 'WC_Custom_Local_Pickup_Method';
    return $methods;
}

// Mostrar u ocultar métodos de envío según la ciudad
add_filter('woocommerce_package_rates', 'custom_toggle_shipping_methods_based_on_city', 20, 2);

function custom_toggle_shipping_methods_based_on_city($rates, $package) {
    $valle_de_aburra_cities = array(
        'BELLO (ANT) (05088000)', 'MEDELLIN (ANT) (05001000)', 'ENVIGADO (ANT) (05266000)', 
        'ITAGUI (ANT) (05360000)', 'SABANETA (ANT) (05631000)', 'COPACABANA (ANT) (05212000)', 
        'GIRARDOTA (ANT) (05308000)', 'CALDAS (ANT) (05129000)', 'LA ESTRELLA (ANT) (05380000)'
    );

    $customer_city = WC()->customer->get_shipping_city();

    if (in_array(strtoupper($customer_city), array_map('strtoupper', $valle_de_aburra_cities))) {
        if (!isset($rates['custom_local_pickup'])) {
            $rates['custom_local_pickup'] = new WC_Shipping_Rate(
                'custom_local_pickup',
                'Recoger en tienda',
                0,
                array(),
                'custom_local_pickup'
            );
        }
        WC()->session->set('show_local_pickup_message', true);
    } else {
        unset($rates['custom_local_pickup']);
        WC()->session->set('show_local_pickup_message', false);
        WC()->session->set('local_pickup_message_shown', false);
    }

    return $rates;
}

// Actualizar sesión al cambiar dirección
add_action('woocommerce_checkout_update_order_review', function () {
    if (!empty($_POST['billing_city']) && !empty($_POST['billing_state'])) {
        WC()->customer->set_shipping_city(sanitize_text_field($_POST['billing_city']));
        WC()->customer->set_shipping_state(sanitize_text_field($_POST['billing_state']));
        WC()->customer->save();

        WC()->session->set('local_pickup_message_shown', false);
    }
});

// Mostrar mensaje si la ciudad está en la lista
add_action('woocommerce_review_order_after_shipping', 'custom_display_local_pickup_message');

function custom_display_local_pickup_message() {
    if (WC()->session->get('show_local_pickup_message') && !WC()->session->get('local_pickup_message_shown')) {
        echo '<div id="local-pickup-message" style="padding: 15px; background: #f8f9fa; border: 1px solid #ddd; margin-top: 10px;">
            <strong>📍 Recoger en tienda:</strong>
            <p>Si seleccionas "Recoger en tienda", Nos pondremos en contacto contigo en los siguientes 5 días hábiles para que recojas tu pedido en nuestro punto de venta presentando tu cédula. Si tienes alguna duda acerca de tu pedido comunícate con nosotros a nuestra línea de <a href="https://wa.me/573228267516" target="_blank">WhatsApp</a>.</p>
        </div>';
        
        WC()->session->set('local_pickup_message_shown', true);
    }
}

// Script para actualizar el checkout y eliminar mensajes duplicados
add_action('wp_footer', 'custom_refresh_checkout_script');

function custom_refresh_checkout_script() {
    if (is_checkout()) {
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                // Actualizar el checkout al cambiar la ciudad
                $('body').on('change', '#billing_city', function() {
                    $('body').trigger('update_checkout');
                });

                // Al actualizar el checkout...
                $(document).on('updated_checkout', function() {
                    // Eliminar duplicados dejando solo el primero
                    $('#local-pickup-message').not(':first').remove();
                    
                    // Verificar si existe el método de envío "custom_local_pickup"
                    if (!$('input.shipping_method[value="custom_local_pickup"]').length) {
                        $('#local-pickup-message').remove();
                    }
                });
            });
        </script>
        <?php
    }
}
