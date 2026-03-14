<?php
defined( 'ABSPATH' ) || exit;

/**
 * Register ACF field group for POC Cards
 * Requires ACF Pro or ACF Free (5.0+)
 */
add_action( 'acf/init', function () {

    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [
        'key'    => 'group_poc_card',
        'title'  => 'Karteninhalte',
        'fields' => [

            // ── Reihenfolge ──────────────────────────────────────────

            [
                'key'          => 'field_poc_order',
                'label'        => 'Reihenfolge',
                'name'         => 'poc_order',
                'type'         => 'number',
                'min'          => 0,
                'default_value'=> 0,
                'instructions' => 'Sortierung der Karten (aufsteigend, 0 = keine bestimmte Reihenfolge).',
            ],

            // ── General ─────────────────────────────────────────────

            [
                'key'          => 'field_poc_description',
                'label'        => 'Beschreibung',
                'name'         => 'poc_description',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Kurze Beschreibung der Aktion (Vorderseite der Karte).',
            ],

            // ── Fußabdruck (Silver) ──────────────────────────────────

            [
                'key'   => 'field_poc_tab_fuss',
                'label' => 'Fußabdruck · Silber',
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'          => 'field_poc_fuss_text',
                'label'        => 'Aktionstext',
                'name'         => 'poc_fuss_text',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Text der Silber-Aktion ("Du buchst...").',
            ],
            [
                'key'          => 'field_poc_fuss_stars',
                'label'        => 'Aufwand (Sterne)',
                'name'         => 'poc_fuss_stars',
                'type'         => 'number',
                'min'          => 1,
                'max'          => 5,
                'default_value'=> 3,
                'instructions' => 'Anzahl der ausgefüllten Fußabdruck-Icons (1–5).',
            ],
            [
                'key'          => 'field_poc_fuss_ulike_id',
                'label'        => 'WP ULike Post-ID',
                'name'         => 'poc_fuss_ulike_id',
                'type'         => 'number',
                'instructions' => 'Post-ID für den WP ULike Shortcode (Silber).',
            ],

            // ── Handabdruck (Gold) ───────────────────────────────────

            [
                'key'   => 'field_poc_tab_hand',
                'label' => 'Handabdruck · Gold',
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'          => 'field_poc_hand_text',
                'label'        => 'Aktionstext',
                'name'         => 'poc_hand_text',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Text der Gold-Aktion ("Du engagierst dich...").',
            ],
            [
                'key'          => 'field_poc_hand_stars',
                'label'        => 'Aufwand (Sterne)',
                'name'         => 'poc_hand_stars',
                'type'         => 'number',
                'min'          => 1,
                'max'          => 5,
                'default_value'=> 3,
                'instructions' => 'Anzahl der ausgefüllten Handabdruck-Icons (1–5).',
            ],
            [
                'key'          => 'field_poc_hand_ulike_id',
                'label'        => 'WP ULike Post-ID',
                'name'         => 'poc_hand_ulike_id',
                'type'         => 'number',
                'instructions' => 'Post-ID für den WP ULike Shortcode (Gold).',
            ],

            // ── Umsetzungsschritte ────────────────────────────────────────────

            [
                'key'   => 'field_poc_tab_back',
                'label' => 'Umsetzungsschritte',
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'          => 'field_poc_step1',
                'label'        => 'Step 1',
                'name'         => 'poc_step1',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Schritte zur Umsetzung (Rückseite der Karte).',
            ],
            [
                'key'          => 'field_poc_step2',
                'label'        => 'Step 2',
                'name'         => 'poc_step2',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Schritte zur Umsetzung (Rückseite der Karte).',
            ],
            [
                'key'          => 'field_poc_step3',
                'label'        => 'Step 3',
                'name'         => 'poc_step3',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Schritte zur Umsetzung (Rückseite der Karte).',
            ],

            // ── Links & Testimonials────────────────────────────────────────────

            [
                'key'   => 'field_poc_tab_links',
                'label' => 'Links & Anbieter',
                'name'  => '',
                'type'  => 'tab',
            ],
            [
                'key'          => 'field_poc_link_de',
                'label'        => 'Link DE',
                'name'         => 'poc_link_de',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Link zu einem Anbieter in Deutschland.',

            ],
            [
                'key'          => 'field_poc_link_at',
                'label'        => 'Link AT',
                'name'         => 'poc_link_at',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Link zu einem Anbieter in Österreich.',
            ],                        
            [
                'key'          => 'field_poc_link_ch',
                'label'        => 'Link CH',
                'name'         => 'poc_link_ch',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Link zu einem Anbieter in der Schweiz.',
            ],

            [
                'key'          => 'field_poc_testimonial',
                'label'        => 'Testimonial',
                'name'         => 'poc_testimonial',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'Optionales Testimonial auf der Rückseite.',
            ],

        ],
        'location' => [
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'poc_card' ] ],
        ],
    ] );

} );
