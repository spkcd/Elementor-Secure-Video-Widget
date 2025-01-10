<?php
/**
 * Plugin Name: Elementor Secure Video Widget
 * Plugin URI:  https://sparkwebstudio.com/
 * Description: Provides an Elementor widget to embed self-hosted videos with expiring URLs (secure), now with a settings page to set default width & height.
 * Version:     1.1
 * Author:      SPARKWEB Studio
 * Author URI:  https://sparkwebstudio.com/
 * License:     GPL2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * 1. DEFINE PLUGIN CONSTANTS
 */
define( 'ESV_PLUGIN_VERSION', '1.1' );
define( 'ESV_PLUGIN_SLUG',    'elementor-secure-video-widget' );

/**
 * 2. LOAD THE WIDGET CLASS
 */
function esv_register_elementor_widget( $widgets_manager ) {
    require_once( __DIR__ . '/widgets/class-elementor-secure-video.php' );
    $widgets_manager->register( new \Elementor_Secure_Video() );
}
add_action( 'elementor/widgets/register', 'esv_register_elementor_widget' );

/**
 * 3. SETTINGS PAGE - CREATE A SIMPLE ADMIN MENU
 */
function esv_add_settings_page() {
    add_options_page(
        'Secure Video Widget Settings',    // Page title
        'Secure Video Widget',             // Menu title
        'manage_options',                  // Capability required
        'esv_settings',                    // Menu slug
        'esv_render_settings_page'         // Callback to render the page
    );
}
add_action( 'admin_menu', 'esv_add_settings_page' );

/**
 * 4. REGISTER SETTINGS
 *    We'll store default width/height in 'esv_default_width' and 'esv_default_height'.
 */
function esv_register_settings() {
    register_setting( 'esv_settings_group', 'esv_default_width', [
        'type'              => 'integer',
        'sanitize_callback' => 'absint',
        'default'           => 500,
    ] );
    register_setting( 'esv_settings_group', 'esv_default_height', [
        'type'              => 'integer',
        'sanitize_callback' => 'absint',
        'default'           => 260,
    ] );
}
add_action( 'admin_init', 'esv_register_settings' );

/**
 * 5. RENDER SETTINGS PAGE
 */
function esv_render_settings_page() {
    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    // Handle messages
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Secure Video Widget Settings', 'esv-widget' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'esv_settings_group' ); ?>
            <?php do_settings_sections( 'esv_settings_group' ); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Default Video Width', 'esv-widget' ); ?></th>
                    <td>
                        <input 
                            type="number" 
                            name="esv_default_width" 
                            value="<?php echo esc_attr( get_option( 'esv_default_width', 500 ) ); ?>"
                            min="1"
                        />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Default Video Height', 'esv-widget' ); ?></th>
                    <td>
                        <input 
                            type="number" 
                            name="esv_default_height" 
                            value="<?php echo esc_attr( get_option( 'esv_default_height', 260 ) ); ?>"
                            min="1"
                        />
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}