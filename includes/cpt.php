<?php
defined( 'ABSPATH' ) || exit;

/**
 * Register the poc_card Custom Post Type
 */
add_action( 'init', function () {

    $labels = [
        'name'               => 'Aktionskarten',
        'singular_name'      => 'Aktionskarte',
        'add_new'            => 'Neue Aktionskarte',
        'add_new_item'       => 'Neue Aktionskarte hinzufügen',
        'edit_item'          => 'Aktionskarte bearbeiten',
        'new_item'           => 'Neue Aktionskarte',
        'view_item'          => 'Aktionskarte ansehen',
        'search_items'       => 'Aktionskarten suchen',
        'not_found'          => 'Keine Aktionskarten gefunden',
        'not_found_in_trash' => 'Keine Aktionskarten im Papierkorb',
        'menu_name'          => 'Aktionskarten',
    ];

    register_post_type( 'poc_card', [
        'labels'       => $labels,
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-index-card',
        'supports'     => [ 'title' ],
        'rewrite'      => false,
    ] );

} );

/**
 * Register the card_category taxonomy
 */
add_action( 'init', function () {

    $labels = [
        'name'              => 'Kategorien',
        'singular_name'     => 'Kategorie',
        'search_items'      => 'Kategorien suchen',
        'all_items'         => 'Alle Kategorien',
        'edit_item'         => 'Kategorie bearbeiten',
        'update_item'       => 'Kategorie aktualisieren',
        'add_new_item'      => 'Neue Kategorie hinzufügen',
        'new_item_name'     => 'Neuer Kategoriename',
        'menu_name'         => 'Kategorien',
    ];

    register_taxonomy( 'card_category', 'poc_card', [
        'labels'       => $labels,
        'hierarchical' => false,
        'show_ui'      => true,
        'rewrite'      => false,
    ] );

} );
