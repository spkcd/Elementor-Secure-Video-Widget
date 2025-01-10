<?php
/**
 * Plugin Name: Elementor Secure Video Widget
 * Plugin URI:  https://sparkwebstudio.com/
 * Description: Provides an Elementor widget to embed self-hosted videos with expiring URLs, no-download controls, a poster image, and a button to regenerate the secret key in settings.
 * Version:     1.3
 * Author:      SPARKWEB Studio
 * Author URI:  https://sparkwebstudio.com/
 * License:     GPL2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define version
define( 'ESVW_VERSION', '1.3' );
define( 'ESVW_SLUG',    'elementor-secure-video-widget' );

/**
 * 1) Load the Elementor Widget class
 */
function esvw_register_elementor_widget( $widgets_manager ) {
    require_once( __DIR__ . '/widgets/class-elementor-secure-video.php' );
    $widgets_manager->register( new \Elementor_Secure_Video() );
}
add_action( 'elementor/widgets/register', 'esvw_register_elementor_widget' );

/**
 * 2) Add a Settings Page (under Settings → Secure Video Widget)
 */
function esvw_add_settings_page() {
    add_options_page(
        'Secure Video Widget Settings',
        'Secure Video Widget',
        'manage_options',
        'esvw_settings',
        'esvw_render_settings_page'
    );
}
add_action( 'admin_menu', 'esvw_add_settings_page' );

/**
 * 3) Register Settings (default size + expiring URL options)
 */
function esvw_register_settings() {
    // Default video dimensions
    register_setting( 'esvw_settings_group', 'esvw_default_width', [
        'type'              => 'integer',
        'sanitize_callback' => 'absint',
        'default'           => 500,
    ] );
    register_setting( 'esvw_settings_group', 'esvw_default_height', [
        'type'              => 'integer',
        'sanitize_callback' => 'absint',
        'default'           => 260,
    ] );

    // Expiring URL: Secret key, Base URL, Expiry time
    register_setting( 'esvw_settings_group', 'esvw_secret_key', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => 'mySuperSecretKey123',
    ] );
    register_setting( 'esvw_settings_group', 'esvw_protected_base_url', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default'           => 'https://contactcustody.kcdev.site/ppv/',
    ] );
    register_setting( 'esvw_settings_group', 'esvw_expiry_seconds', [
        'type'              => 'integer',
        'sanitize_callback' => 'absint',
        'default'           => 3600, // 1 hour
    ] );
}
add_action( 'admin_init', 'esvw_register_settings' );

/**
 * 4) Render the Settings Page (including "Regenerate Secret Key" button)
 */
function esvw_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Check if "Regenerate Secret Key" button was pressed
    if ( isset( $_POST['esvw_regenerate_key'] ) && check_admin_referer( 'esvw_regenerate_key_action', 'esvw_regenerate_key_nonce' ) ) {
        // Generate a random 32-char secret key (letters+numbers)
        $new_secret = wp_generate_password( 32, false, false );
        update_option( 'esvw_secret_key', $new_secret );
        // Possibly show an admin notice or just let them see the new key in the field
        add_settings_error( 'esvw_settings', 'esvw_key_regenerated', 'Secret key regenerated successfully!', 'updated' );
    }

    // Show any settings errors
    settings_errors( 'esvw_settings' );

    ?>
    <div class="wrap">
        <h1>Secure Video Widget Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'esvw_settings_group' ); ?>
            <?php do_settings_sections( 'esvw_settings_group' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">Default Video Width</th>
                    <td>
                        <input type="number" name="esvw_default_width"
                               value="<?php echo esc_attr( get_option( 'esvw_default_width', 500 ) ); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Default Video Height</th>
                    <td>
                        <input type="number" name="esvw_default_height"
                               value="<?php echo esc_attr( get_option( 'esvw_default_height', 260 ) ); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Secret Key</th>
                    <td>
                        <input type="text" name="esvw_secret_key"
                               value="<?php echo esc_attr( get_option( 'esvw_secret_key', 'mySuperSecretKey123' ) ); ?>"
                               style="width: 320px;" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <?php 
                        // Nonce for regenerating key
                        wp_nonce_field( 'esvw_regenerate_key_action', 'esvw_regenerate_key_nonce' ); 
                        ?>
                        <button type="submit" name="esvw_regenerate_key" class="button">
                            Regenerate Secret Key
                        </button>
                        <p class="description">
                            Click to automatically generate a new random key. This may affect any external scripts relying on the old key.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Protected Base URL</th>
                    <td>
                        <input type="url" name="esvw_protected_base_url"
                               value="<?php echo esc_attr( get_option( 'esvw_protected_base_url', 'https://contactcustody.kcdev.site/ppv/' ) ); ?>"
                               style="width: 320px;" />
                        <p class="description">
                            E.g. https://contactcustody.kcdev.site/ppv/
                            <br>Ensure your .htaccess or check_access.php enforces token checks here.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Expiry Time (seconds)</th>
                    <td>
                        <input type="number" name="esvw_expiry_seconds"
                               value="<?php echo esc_attr( get_option( 'esvw_expiry_seconds', 3600 ) ); ?>" />
                        <p class="description">E.g. 3600 = 1 hour</p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}