<?php
/*
Plugin Name: Coming Soon by Foxnice
Description: Un plugin simple para activar o desactivar el modo Coming Soon.
Version: 1.5
Author: Met El Idrissi
Author URI: https://www.foxnice.com/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
    exit; // Evita el acceso directo
}

// Agrega la página del menú en el dashboard
add_action('admin_menu', 'foxnice_coming_soon_menu');
function foxnice_coming_soon_menu() {
    add_menu_page(
        'Coming Soon',
        'Coming Soon',
        'manage_options',
        'foxnice-coming-soon',
        'foxnice_coming_soon_settings_page',
        'dashicons-welcome-view-site',
        999999999
    );
}

// Página de configuración del plugin
function foxnice_coming_soon_settings_page() {
    if (isset($_POST['submit'])) {
        // Verificación de nonce
        $nonce = isset($_POST['foxnice_coming_soon_nonce']) ? sanitize_text_field(wp_unslash($_POST['foxnice_coming_soon_nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'foxnice_coming_soon_update')) {
            wp_die('Nonce inválido. Actualiza la página e inténtalo de nuevo.');
        }

        // Procesar datos del formulario de manera segura
        $status = isset($_POST['foxnice_coming_soon_status']) ? '1' : '0';
        update_option('foxnice_coming_soon_status', sanitize_text_field($status));

        // Guardar el texto personalizado
        $coming_soon_text = isset($_POST['foxnice_coming_soon_text']) ? wp_kses_post(wp_unslash($_POST['foxnice_coming_soon_text'])) : '';
        update_option('foxnice_coming_soon_text', $coming_soon_text);
    }

    $status = get_option('foxnice_coming_soon_status', '0');
    $coming_soon_text = get_option('foxnice_coming_soon_text', '<h1>Coming Soon</h1>');
    ?>
    <div class="wrap">
        <h1>Coming Soon Settings</h1>
        <form method="post">
            <?php wp_nonce_field('foxnice_coming_soon_update', 'foxnice_coming_soon_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Activar Coming Soon</th>
                    <td>
                        <input type="checkbox" name="foxnice_coming_soon_status" value="1" <?php checked($status, '1'); ?> />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Texto para Coming Soon</th>
                    <td>
                        <?php
                        // Editor de texto enriquecido para que el usuario pueda modificar el texto de Coming Soon
                        wp_editor($coming_soon_text, 'foxnice_coming_soon_text', array(
                            'textarea_name' => 'foxnice_coming_soon_text',
                            'textarea_rows' => 10,
                            'media_buttons' => false
                        ));
                        ?>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Mostrar la página "Coming Soon" cuando esté activada
add_action('template_redirect', 'foxnice_coming_soon_template');
function foxnice_coming_soon_template() {
    if (!current_user_can('manage_options') && get_option('foxnice_coming_soon_status') === '1') {
        // Asegura que no se cargue nada del tema ni contenido adicional
        remove_all_actions('wp_head');
        remove_all_actions('wp_footer');

        // Obtener el texto personalizado de Coming Soon
        $coming_soon_text = get_option('foxnice_coming_soon_text', '<h1>Coming Soon</h1>');

        // Evitar que se cargue el resto del contenido del sitio y mostrar solo lo necesario
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Coming Soon</title>
            <style>
                body {
                    background-color: #fdf6f0; /* Blanco roto */
                    font-family: "Montserrat", sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    text-align: center;
                }
                .coming-soon-content {
                    display: block;
                    width: 100%;
                    max-width: 800px;
                    padding: 20px;
                    box-sizing: border-box;
                }
            </style>
        </head>
        <body>
        <div class="coming-soon-content">
        ';

        // Mostrar el texto personalizado
        echo wp_kses_post($coming_soon_text);

        echo '
        </div>
        </body>
        </html>';

        exit; // Detenemos el resto de la carga de la página
    }
}


// Agregar enlace de "Settings" en la página de plugins
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'foxnice_add_settings_link');

function foxnice_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=foxnice-coming-soon">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}
?>
