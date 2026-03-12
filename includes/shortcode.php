<?php
defined( 'ABSPATH' ) || exit;

/**
 * [poc_cards] shortcode
 *
 * Usage:
 *   [poc_cards]                        — all cards
 *   [poc_cards category="mobilitaet"]  — filtered by slug
 *   [poc_cards limit="10"]             — limit number of cards
 */
add_shortcode( 'poc_cards', function ( $atts ) {

    $atts = shortcode_atts( [
        'category' => '',
        'limit'    => -1,
    ], $atts, 'poc_cards' );

    // ── Query ────────────────────────────────────────────────────
    $args = [
        'post_type'      => 'poc_card',
        'posts_per_page' => intval( $atts['limit'] ),
        'post_status'    => 'publish',
        'meta_query'     => [
            'relation'      => 'OR',
            'order_clause'  => [ 'key' => 'poc_order', 'compare' => 'EXISTS',     'type' => 'NUMERIC' ],
            'order_missing' => [ 'key' => 'poc_order', 'compare' => 'NOT EXISTS' ],
        ],
        'orderby'        => [ 'order_clause' => 'ASC', 'title' => 'ASC' ],
    ];

    if ( ! empty( $atts['category'] ) ) {
        $args['tax_query'] = [ [
            'taxonomy' => 'card_category',
            'field'    => 'slug',
            'terms'    => sanitize_text_field( $atts['category'] ),
        ] ];
    }

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '<p>Keine Karten gefunden.</p>';
    }

    // ── SVG icons ────────────────────────────────────────────────
    $svg_fuss = poc_cards_svg_fuss();
    $svg_hand = poc_cards_svg_hand();

    // ── Render ───────────────────────────────────────────────────
    ob_start();

    ?>
    <div class="poc-nav-wrapper">
        <button class="poc-nav-btn poc-nav-prev" aria-label="Vorherige Karte">← Vorherige</button>
        <span class="poc-nav-counter">Karte 1 von <?php echo intval( $query->post_count ); ?></span>
        <button class="poc-nav-btn poc-nav-next" aria-label="Nächste Karte">Nächste →</button>
    </div>

    <?php // Cards viewport ?>
    <div class="poc-cards-viewport">
        <div class="poc-cards-track">

        <?php
        while ( $query->have_posts() ) :
            $query->the_post();

            $post_id     = get_the_ID();
            $title       = get_the_title();
            $terms       = get_the_terms( $post_id, 'card_category' );
            $cat_slug    = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : '';
            $cat_name    = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';

            // ── ACF fields ───────────────────────────────────────
            $description  = get_field( 'poc_description',      $post_id );
            $fuss_text    = get_field( 'poc_fuss_text',         $post_id );
            $fuss_stars   = intval( get_field( 'poc_fuss_stars',    $post_id ) );
            $fuss_ulike   = intval( get_field( 'poc_fuss_ulike_id', $post_id ) );
            $hand_text    = get_field( 'poc_hand_text',         $post_id );
            $hand_stars   = intval( get_field( 'poc_hand_stars',    $post_id ) );
            $hand_ulike   = intval( get_field( 'poc_hand_ulike_id', $post_id ) );
            $testimonial  = get_field( 'poc_testimonial',       $post_id );

            // Steps — individual fields
            $step1 = get_field( 'poc_step1', $post_id );
            $step2 = get_field( 'poc_step2', $post_id );
            $step3 = get_field( 'poc_step3', $post_id );
            $steps = array_values( array_filter( [ $step1, $step2, $step3 ] ) );

            // Links — individual fields per country
            $link_de = trim( (string) get_field( 'poc_link_de', $post_id ) );
            $link_at = trim( (string) get_field( 'poc_link_at', $post_id ) );
            $link_ch = trim( (string) get_field( 'poc_link_ch', $post_id ) );

            $poc_id_silver = 'card_' . $post_id . '_silver';
            $poc_id_gold   = 'card_' . $post_id . '_gold';
        ?>

        <div class="flip-card" id="card-<?php echo esc_attr( $post_id ); ?>" data-cat="<?php echo esc_attr( $cat_slug ); ?>">
        <div class="flip-card-inner">

            <?php // ── FRONT (actions) ─────────────────────────── ?>
            <div class="flip-card-front">

                <?php // Category badge & title ?>
                <div class="poc-card-header">
                    <span class="poc-category-badge"><?php echo esc_html( $cat_name ); ?></span>
                    <h3 class="poc-card-title"><?php echo esc_html( $title ); ?></h3>
                    <?php if ( $description ) : ?>
                        <p class="poc-card-description"><?php echo esc_html( $description ); ?></p>
                    <?php endif; ?>
                </div>

                <?php // ── Silver block ── ?>
                <div class="poc-action-block silver" data-poc-id="<?php echo esc_attr( $poc_id_silver ); ?>" data-ulike-id="<?php echo esc_attr( $fuss_ulike ); ?>">
                    <div class="poc-block-header">
                        <div class="poc-block-icon"><?php echo $svg_fuss; ?></div>
                        <h4 class="poc-block-title">Fußabdruck · Silber</h4>
                        <div class="poc-toggle-area">
                            <span class="toggle-label-off">offen</span>
                            <button class="poc-toggle-btn poc-btn-silver" aria-label="Als erledigt markieren">
                                <span class="poc-toggle-track"><span class="poc-toggle-thumb"></span></span>
                            </button>
                            <span class="toggle-label-on">erledigt</span>
                        </div>
                    </div>
                    <?php if ( $fuss_text ) : ?>
                        <p class="poc-action-text"><?php echo esc_html( $fuss_text ); ?></p>
                    <?php endif; ?>
                    <div class="poc-block-footer">
                        <div class="poc-stars">
                            <span class="poc-stars-label">Aufwand:</span>
                            <?php echo poc_cards_render_stars( $svg_fuss, $fuss_stars, 5 ); ?>
                        </div>
                        <?php if ( $fuss_ulike ) : ?>
                        <div class="poc-ulike">
                            👥 <?php echo do_shortcode( '[wp_ulike id="' . $fuss_ulike . '"]' ); ?>
                            <span class="poc-ulike-label">haben das schon gemacht</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php // ── Gold block ── ?>
                <div class="poc-action-block gold" data-poc-id="<?php echo esc_attr( $poc_id_gold ); ?>" data-ulike-id="<?php echo esc_attr( $hand_ulike ); ?>">
                    <div class="poc-block-header">
                        <div class="poc-block-icon"><?php echo $svg_hand; ?></div>
                        <h4 class="poc-block-title">Handabdruck · Gold</h4>
                        <div class="poc-toggle-area">
                            <span class="toggle-label-off">offen</span>
                            <button class="poc-toggle-btn poc-btn-gold" aria-label="Als erledigt markieren">
                                <span class="poc-toggle-track"><span class="poc-toggle-thumb"></span></span>
                            </button>
                            <span class="toggle-label-on">erledigt</span>
                        </div>
                    </div>
                    <?php if ( $hand_text ) : ?>
                        <p class="poc-action-text"><?php echo esc_html( $hand_text ); ?></p>
                    <?php endif; ?>
                    <div class="poc-block-footer">
                        <div class="poc-stars">
                            <span class="poc-stars-label">Aufwand:</span>
                            <?php echo poc_cards_render_stars( $svg_hand, $hand_stars, 5 ); ?>
                        </div>
                        <?php if ( $hand_ulike ) : ?>
                        <div class="poc-ulike">
                            👥 <?php echo do_shortcode( '[wp_ulike id="' . $hand_ulike . '"]' ); ?>
                            <span class="poc-ulike-label">haben das schon gemacht</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php // Testimonial ?>
                <?php if ( $testimonial ) : ?>
                <div class="poc-testimonial">
                    <p><?php echo esc_html( $testimonial ); ?></p>
                </div>
                <?php endif; ?>

                <button class="flip-button poc-flip-btn" data-flip-trigger="1">↩️ Mini-Anleitung, Links &amp; Teilen – Rückseite anzeigen</button>

            </div><?php // end .flip-card-front ?>

            <?php // ── BACK (steps/links) ────────────────────── ?>
            <div class="flip-card-back">

                <h3 class="poc-card-title"><?php echo esc_html( $title ); ?></h3>
                <h3 class="poc-back-subtitle">Ideen zur Umsetzung (als Impuls -  du kannst es auch anders machen!)</h3>
                
                <?php // Steps ?>
                <?php if ( ! empty( $steps ) ) : ?>
                <ol class="poc-steps">
                    <?php foreach ( $steps as $i => $step ) : ?>
                    <li class="poc-step">
                        <span class="poc-step-number"><?php echo intval( $i + 1 ); ?></span>
                        <span class="poc-step-text"><?php echo esc_html( $step ); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ol>
                <?php endif; ?>

                <?php // Links per country ?>
                <?php
                $country_links = [];
                if ( $link_de ) $country_links[] = [ 'label' => '🇩🇪', 'url' => $link_de ];
                if ( $link_at ) $country_links[] = [ 'label' => '🇦🇹',   'url' => $link_at ];
                if ( $link_ch ) $country_links[] = [ 'label' => '🇨🇭',      'url' => $link_ch ];
                ?>
                <?php if ( ! empty( $country_links ) ) : ?>
                <h3 class="poc-back-subtitle">🔗 Links zur Inspiration und für nächste Schritte</h3>
                <div class="poc-links">
                    <?php foreach ( $country_links as $country_link ) : ?>
                        <a class="poc-link-btn" href="<?php echo esc_url( $country_link['url'] ); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html( $country_link['label'] ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                

                <?php // Share buttons ?>
                <?php
                $share_text = urlencode( 'Ich habe gerade die silberne/goldene Karte des Fußabdrucks/Handabdrucks zum Thema "' . $title . '" bei dem Pioneers of Change Aktionskartenspiel freigeschaltet. Interessiert an einer Challenge? Dann finde heraus, wie du wirksam nachhaltig handeln kannst!' );
                ?>
                <h3 class="poc-back-subtitle">💬 Dein Schritt inspiriert andere</h3>
                <p class="poc-share-intro">Beim Teilen erscheint automatisch ein Text mit der Aktionskarte – du kannst ihn vor dem Senden noch anpassen.</p>
                <div class="poc-share-btns">
                    <a class="poc-share-btn email" href="mailto:?body=<?php echo $share_text; ?>"><svg aria-hidden="true" class="poc-share-icon e-font-icon-svg e-far-envelope" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm0 48v40.805c-22.422 18.259-58.168 46.651-134.587 106.49-16.841 13.247-50.201 45.072-73.413 44.701-23.208.375-56.579-31.459-73.413-44.701C106.18 199.465 70.425 171.067 48 152.805V112h416zM48 400V214.398c22.914 18.251 55.409 43.862 104.938 82.646 21.857 17.205 60.134 55.186 103.062 54.955 42.717.231 80.509-37.199 103.053-54.947 49.528-38.783 82.032-64.401 104.947-82.653V400H48z"></path></svg> Email</a>
                    <a class="poc-share-btn whatsapp" href="https://api.whatsapp.com/send?text=<?php echo $share_text; ?>" target="_blank" rel="noopener noreferrer"><svg aria-hidden="true" class="poc-share-icon e-font-icon-svg e-fab-whatsapp" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"></path></svg> WhatsApp</a>
                    <a class="poc-share-btn telegram" href="https://t.me/share/url?text=<?php echo $share_text; ?>" target="_blank" rel="noopener noreferrer"><svg aria-hidden="true" class="poc-share-icon e-font-icon-svg e-fab-telegram-plane" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M446.7 98.6l-67.6 318.8c-5.1 22.5-18.4 28.1-37.3 17.5l-103-75.9-49.7 47.8c-5.5 5.5-10.1 10.1-20.7 10.1l7.4-104.9 190.9-172.5c8.3-7.4-1.8-11.5-12.9-4.1L117.8 284 16.2 252.2c-22.1-6.9-22.5-22.1 4.6-32.7L418.2 66.4c18.4-6.9 34.5 4.1 28.5 32.2z"></path></svg> Telegram</a>
                    <a class="poc-share-btn facebook" href="https://www.facebook.com/sharer/sharer.php?quote=<?php echo $share_text; ?>" target="_blank" rel="noopener noreferrer"><svg aria-hidden="true" class="poc-share-icon e-font-icon-svg e-fab-facebook-f" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"></path></svg> Facebook</a>
                    <a class="poc-share-btn linkedin" href="https://www.linkedin.com/sharing/share-offsite/?summary=<?php echo $share_text; ?>" target="_blank" rel="noopener noreferrer"><svg aria-hidden="true" class=" poc-share-icon e-font-icon-svg e-fab-linkedin-in" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"></path></svg> LinkedIn</a>
                </div>

                <button class="flip-button poc-flip-btn poc-flip-back" data-flip-trigger="1">← Vorderseite</button>

            </div><?php // end .flip-card-back ?>

        </div><?php // end .flip-card-inner ?>
        </div><?php // end .flip-card ?>

        <?php endwhile; wp_reset_postdata(); ?>

        </div><?php // end .poc-cards-track ?>
    </div><?php // end .poc-cards-viewport ?>

    <?php
    return ob_get_clean();
} );

/**
 * [poc_progress] shortcode — Fußabdruck & Handabdruck progress bars
 *
 * Usage:
 *   [poc_progress]
 */
add_shortcode( 'poc_progress', function () {

    $query = new WP_Query( [
        'post_type'      => 'poc_card',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ] );

    $total    = count( $query->posts );
    $post_ids = array_map( 'intval', $query->posts );

    if ( $total === 0 ) return '';

    $svg_fuss = poc_cards_svg_fuss();
    $svg_hand = poc_cards_svg_hand();

    ob_start();
    ?>
    <div class="poc-progress-wrap"
         data-post-ids="<?php echo esc_attr( wp_json_encode( $post_ids ) ); ?>"
         data-total="<?php echo esc_attr( $total ); ?>">

        <div class="poc-progress-rows">

            <div class="poc-progress-row">
                <div class="poc-pr-label">
                    <span class="poc-pr-icon-silver"><?php echo $svg_fuss; ?></span>
                    <span>Fußabdruck</span>
                </div>
                <div class="poc-progress-track">
                    <div class="poc-progress-fill poc-fill-silver" style="width:0%"></div>
                </div>
                <div class="poc-pr-count poc-pr-count-silver">0 von <?php echo esc_html( $total ); ?> erledigt</div>
            </div>

            <div class="poc-progress-row">
                <div class="poc-pr-label">
                    <span class="poc-pr-icon-gold"><?php echo $svg_hand; ?></span>
                    <span>Handabdruck</span>
                </div>
                <div class="poc-progress-track">
                    <div class="poc-progress-fill poc-fill-gold" style="width:0%"></div>
                </div>
                <div class="poc-pr-count poc-pr-count-gold">0 von <?php echo esc_html( $total ); ?> erledigt</div>
            </div>

        </div>
    </div>
    <?php
    return ob_get_clean();
} );

/**
 * [poc_cards_list] shortcode — simplified card list
 *
 * Usage:
 *   [poc_cards_list]                          — all cards
 *   [poc_cards_list category="mobilitaet"]    — filtered by slug
 *   [poc_cards_list full_tab=".my-selector"]  — CSS selector of the tab trigger that shows [poc_cards]
 */
add_shortcode( 'poc_cards_list', function ( $atts ) {

    $atts = shortcode_atts( [
        'category' => '',
        'limit'    => -1,
        'full_tab' => '',
    ], $atts, 'poc_cards_list' );

    $args = [
        'post_type'      => 'poc_card',
        'posts_per_page' => intval( $atts['limit'] ),
        'post_status'    => 'publish',
        'meta_query'     => [
            'relation'      => 'OR',
            'order_clause'  => [ 'key' => 'poc_order', 'compare' => 'EXISTS',     'type' => 'NUMERIC' ],
            'order_missing' => [ 'key' => 'poc_order', 'compare' => 'NOT EXISTS' ],
        ],
        'orderby'        => [ 'order_clause' => 'ASC', 'title' => 'ASC' ],
    ];

    if ( ! empty( $atts['category'] ) ) {
        $args['tax_query'] = [ [
            'taxonomy' => 'card_category',
            'field'    => 'slug',
            'terms'    => sanitize_text_field( $atts['category'] ),
        ] ];
    }

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '<p>Keine Karten gefunden.</p>';
    }

    $svg_fuss = poc_cards_svg_fuss();
    $svg_hand = poc_cards_svg_hand();

    // ── Collect all category terms for filter bar ────────────────
    $all_terms = get_terms( [
        'taxonomy'   => 'card_category',
        'hide_empty' => true,
    ] );

    ob_start();
    ?>
    <div class="poc-list-wrap">
    <?php if ( ! empty( $all_terms ) && empty( $atts['category'] ) ) : ?>
    <div class="poc-filter-bar poc-list-filter-bar">
        <button class="poc-filter-btn poc-filter-active" data-filter="all">Alle</button>
        <?php foreach ( $all_terms as $term ) : ?>
            <button class="poc-filter-btn" data-filter="<?php echo esc_attr( $term->slug ); ?>">
                <?php echo esc_html( $term->name ); ?>
            </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="poc-list" data-full-tab="<?php echo esc_attr( $atts['full_tab'] ); ?>">
    <div class="poc-list-grid">

    <?php while ( $query->have_posts() ) :
        $query->the_post();
        $post_id  = get_the_ID();
        $title    = get_the_title();
        $terms    = get_the_terms( $post_id, 'card_category' );
        $cat_slug = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : '';
        $cat_name = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';

        $fuss_ulike = intval( get_field( 'poc_fuss_ulike_id', $post_id ) );
        $hand_ulike = intval( get_field( 'poc_hand_ulike_id', $post_id ) );

        $poc_id_silver = 'card_' . $post_id . '_silver';
        $poc_id_gold   = 'card_' . $post_id . '_gold';
    ?>

    <div class="poc-list-card" data-post-id="<?php echo esc_attr( $post_id ); ?>" data-cat="<?php echo esc_attr( $cat_slug ); ?>">

        <div class="poc-list-card-header">
            <?php if ( $cat_name ) : ?>
            <span class="poc-category-badge"><?php echo esc_html( $cat_name ); ?></span>
            <?php endif; ?>
            <span class="poc-list-card-title"><?php echo esc_html( $title ); ?></span>
            <!-- <button class="poc-list-goto-btn" aria-label="Vollversion anzeigen">→</button> -->
        </div>

        <div class="poc-action-block silver poc-list-card-action"
             data-poc-id="<?php echo esc_attr( $poc_id_silver ); ?>"
             data-ulike-id="<?php echo esc_attr( $fuss_ulike ); ?>">
            <button class="poc-toggle-btn poc-list-action-btn poc-btn-silver" aria-label="Fußabdruck als erledigt markieren">
                <span class="poc-lbtn-off">Fußabdruck offen</span>
                <span class="poc-lbtn-on">Fußabdruck erledigt</span>
            </button>
            <div class="poc-list-action-meta">
                <span class="poc-list-icon silver"><?php echo $svg_fuss; ?></span>
                <?php if ( $fuss_ulike ) : ?>
                <span class="poc-list-ulike"><?php echo do_shortcode( '[wp_ulike id="' . $fuss_ulike . '"]' ); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="poc-action-block gold poc-list-card-action"
             data-poc-id="<?php echo esc_attr( $poc_id_gold ); ?>"
             data-ulike-id="<?php echo esc_attr( $hand_ulike ); ?>">
            <button class="poc-toggle-btn poc-list-action-btn poc-btn-gold" aria-label="Handabdruck als erledigt markieren">
                <span class="poc-lbtn-off">Handabdruck offen</span>
                <span class="poc-lbtn-on">Handabdruck erledigt</span>
            </button>
            <div class="poc-list-action-meta">
                <span class="poc-list-icon gold"><?php echo $svg_hand; ?></span>
                <?php if ( $hand_ulike ) : ?>
                <span class="poc-list-ulike"><?php echo do_shortcode( '[wp_ulike id="' . $hand_ulike . '"]' ); ?></span>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <?php endwhile; wp_reset_postdata(); ?>

    </div>
    </div>
    </div>
    <?php
    return ob_get_clean();
} );

// ── Helper: render star icons ────────────────────────────────────────────────
function poc_cards_render_stars( string $svg, int $filled, int $total ): string {
    $out = '';
    for ( $i = 1; $i <= $total; $i++ ) {
        $class = $i <= $filled ? 'poc-star poc-star-on' : 'poc-star poc-star-off';
        $out  .= '<span class="' . $class . '">' . $svg . '</span>';
    }
    return $out;
}

// ── Helper: Fußabdruck SVG ───────────────────────────────────────────────────
function poc_cards_svg_fuss(): string {
    $file = POC_CARDS_PATH . 'assets/icons/foot.svg';
    return file_exists( $file ) ? file_get_contents( $file ) : '';
}

// ── Helper: Handabdruck SVG ──────────────────────────────────────────────────
function poc_cards_svg_hand(): string {
    $file = POC_CARDS_PATH . 'assets/icons/hand.svg';
    return file_exists( $file ) ? file_get_contents( $file ) : '';
}
