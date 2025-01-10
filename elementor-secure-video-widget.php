<?php
/**
 * Plugin Name: Elementor Secure Video Widget
 * Description: An Elementor widget to embed self-hosted videos with expiring URLs for security.
 * Version:     1.0
 * Author:      Your Name
 * Text Domain: esv-widget
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// 1. Require the custom widget class
function esv_register_elementor_widget( $widgets_manager ) {
    require_once( __DIR__ . '/widgets/class-elementor-secure-video.php' );
    // Register the widget with Elementor
    $widgets_manager->register( new \Elementor_Secure_Video() );
}
add_action( 'elementor/widgets/register', 'esv_register_elementor_widget' );