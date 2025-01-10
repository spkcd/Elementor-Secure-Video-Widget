<?php
/**
 * Plugin Name: Elementor Secure Video Widget
 * Plugin URI:  https://sparkwebstudio.com/
 * Description: Provides an Elementor widget to embed self-hosted videos with expiring URLs (secure), now with settings for default size & expiring URL configuration, plus title & description controls.
 * Version:     1.1
 * Author:      SPARKWEB Studio
 * Author URI:  https://sparkwebstudio.com/
 * License:     GPL2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'ESV_PLUGIN_VERSION', '1.1' );
define( 'ESV_PLUGIN_SLUG',    'elementor-secure-video-widget' );

/**
 * 1. REGISTER THE ELEMENTOR WIDGET
 */
function esv_register_elementor_widget( $widgets_manager ) {
    require_once( __DIR__ . '/widgets/class-elementor-secure-video.php' );
    $widgets_manager->register( new \Elementor_Secure_Video() );
}
add_action( 'elementor/widgets/register', 'esv_register_elementor_widget' );

/**
 * 2. ADD A SETTINGS PAGE FOR EXPIRING URL + DEFAULT VIDEO DIMENSIONS
 */
function esv_add_settings_page() {
    add_options_page(
        'Secure Video Widget Settings',    // Page title
        'Secure Video Widget',             // Menu title
        'manage_options',                  // Capability
        'esv_settings',                    // Menu slug
        'esv_render_settings_page'         // Callback
    );
}
add_action( 'admin_menu', 'esv_add_settings_page' );

/**
 * 3. REGISTER SETTINGS FIELDS
 */
function esv_register_settings() {
    // Default video size
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

    // Expiring URL Options
    register_setting( 'esv_settings_group', 'esv_secret_key', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => 'mySuperSecretKey123',
    ] );
    register_setting( 'esv_settings_group', 'esv_protected_base_url', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default'           => 'https://contactcustody.kcdev.site/ppv/',
    ] );
    register_setting( 'esv_settings_group', 'esv_expiry_seconds', [
        'type'              => 'integer',
        'sanitize_callback' => 'absint',
        'default'           => 3600, // 1 hour
    ] );
}
add_action( 'admin_init', 'esv_register_settings' );

/**
 * 4. RENDER THE SETTINGS PAGE
 */
function esv_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Secure Video Widget Settings', 'esv-widget' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'esv_settings_group' ); ?>
            <?php do_settings_sections( 'esv_settings_group' ); ?>

            <table class="form-table">
                <!-- Default Video Dimensions -->
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

                <!-- Expiring URL Settings -->
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Secret Key', 'esv-widget' ); ?></th>
                    <td>
                        <input
                            type="text"
                            name="esv_secret_key"
                            value="<?php echo esc_attr( get_option( 'esv_secret_key', 'mySuperSecretKey123' ) ); ?>"
                            style="width: 300px;"
                        />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Protected Base URL', 'esv-widget' ); ?></th>
                    <td>
                        <input
                            type="url"
                            name="esv_protected_base_url"
                            value="<?php echo esc_attr( get_option( 'esv_protected_base_url', 'https://contactcustody.kcdev.site/ppv/' ) ); ?>"
                            style="width: 300px;"
                        />
                        <p class="description">
                            <?php esc_html_e( 'e.g. https://yourdomain.com/ppv/', 'esv-widget' ); ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Expiry Time (seconds)', 'esv-widget' ); ?></th>
                    <td>
                        <input
                            type="number"
                            name="esv_expiry_seconds"
                            value="<?php echo esc_attr( get_option( 'esv_expiry_seconds', 3600 ) ); ?>"
                            min="1"
                        />
                        <p class="description">
                            <?php esc_html_e( 'Number of seconds before the video link expires (e.g., 3600 = 1 hour).', 'esv-widget' ); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}