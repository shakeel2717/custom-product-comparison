<?php

/**
 * Plugin Name: Custom Product Comparison
 * Description: A custom Elementor widget for product comparison.
 * Version: 1.0
 * Author: Shakeel Ahmad
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Register the widget
function register_custom_product_comparison_widget($widgets_manager)
{
    require_once(__DIR__ . '/widgets/custom-product-comparison-widget.php');
    $widgets_manager->register(new \Elementor_Custom_Product_Comparison_Widget());
}
add_action('elementor/widgets/register', 'register_custom_product_comparison_widget');

// Enqueue necessary scripts and styles
function custom_product_comparison_scripts() {
    wp_enqueue_style( 'custom-product-comparison', plugins_url( 'assets/css/style.css', __FILE__ ) );
    wp_enqueue_script( 'custom-product-comparison', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'jquery' ), false, true );
}
add_action( 'wp_enqueue_scripts', 'custom_product_comparison_scripts' );

