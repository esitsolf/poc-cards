<?php
/**
 * Plugin Name: Action Cards
 * Plugin URI:  https://pioneersofchange.org
 * Description: Action cards for Pioneers of Change — Custom Post Type, ACF fields, and shortcode renderer.
 * Version:     beta 1.0.4
 * Author:      Pioneers of Change
 * Text Domain: actionsakrten
 */

defined( 'ABSPATH' ) || exit;

define( 'POC_CARDS_PATH', plugin_dir_path( __FILE__ ) );
define( 'POC_CARDS_URL',  plugin_dir_url( __FILE__ ) );

require_once POC_CARDS_PATH . 'includes/cpt.php';
require_once POC_CARDS_PATH . 'includes/acf-fields.php';
require_once POC_CARDS_PATH . 'includes/shortcode.php';

/**
 * Enqueue front-end assets
 */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'poc-cards',
        POC_CARDS_URL . 'assets/poc-cards.css',
        [],
        '1.0.0'
    );
    wp_enqueue_script(
        'poc-cards',
        POC_CARDS_URL . 'assets/poc-cards.js',
        [],
        '1.0.0',
        true
    );
} );
