<?php
/**
 * Enamel Reviews Feed — Location Registry (PHP defaults + DB overrides)
 *
 * STRUCTURE:
 *   - Place IDs and slug→label mapping live in this file as PHP defaults.
 *     These are technical setup data — edit in PHP when adding/removing studios.
 *   - Booking URLs, Google listing URLs, headlines, ledes, and button text
 *     are merchant-editable copy. They have PHP defaults but can be overridden
 *     from Settings → Enamel Reviews → Locations. Overrides live in wp_options
 *     under 'erf_locations' (per-studio) and 'erf_generic' (network-wide).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Per-studio defaults.  TODO: replace the placeholder Place IDs before
 * activating the plugin in production.
 */
function erf_get_location_defaults() {
    return [

        'south-lamar' => [
            'label'       => 'South Lamar',
            'address'     => '2717 S Lamar Blvd #1086, Austin, TX 78704',
            'place_id'    => '__PLACE_ID_SOUTH_LAMAR__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+south+lamar+reviews',
            'headline'    => 'South Lamar patients keep <em>telling on us.</em>',
            'lede'        => 'Our South Lamar patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at South Lamar',
        ],
        'parmer-park' => [
            'label'       => 'Parmer Park',
            'address'     => '1606 E Parmer Ln #125, Austin, TX 78753',
            'place_id'    => '__PLACE_ID_PARMER_PARK__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+parmer+park+reviews',
            'headline'    => 'Parmer Park patients keep <em>telling on us.</em>',
            'lede'        => 'Our Parmer Park patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Parmer Park',
        ],
        'lantana' => [
            'label'       => 'Lantana',
            'address'     => '7415 Southwest Parkway Building 6 #200, Austin, TX 78735',
            'place_id'    => '__PLACE_ID_LANTANA__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+lantana+reviews',
            'headline'    => 'Lantana patients keep <em>telling on us.</em>',
            'lede'        => 'Our Lantana patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Lantana',
        ],
        'saltillo' => [
            'label'       => 'Saltillo',
            'address'     => '901 E 5th St Suite 170, Austin, TX 78702',
            'place_id'    => '__PLACE_ID_SALTILLO__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+saltillo+reviews',
            'headline'    => 'Saltillo patients keep <em>telling on us.</em>',
            'lede'        => 'Our Saltillo patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Saltillo',
        ],
        'domain' => [
            'label'       => 'Domain',
            'address'     => '11005 Burnet Rd #100, Austin, TX 78758',
            'place_id'    => '__PLACE_ID_DOMAIN__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+domain+reviews',
            'headline'    => 'Domain patients keep <em>telling on us.</em>',
            'lede'        => 'Our Domain patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Domain',
        ],
        'the-grove' => [
            'label'       => 'The Grove',
            'address'     => '4301 Bull Creek Road, Suite 190, Austin, TX 78731',
            'place_id'    => '__PLACE_ID_THE_GROVE__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+the+grove+austin+reviews',
            'headline'    => 'The Grove patients keep <em>telling on us.</em>',
            'lede'        => 'Our Grove patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at The Grove',
        ],
        'mckinney' => [
            'label'       => 'McKinney',
            'address'     => '6700 Alma Rd, Suite 400, McKinney, TX 75070',
            'place_id'    => '__PLACE_ID_MCKINNEY__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+mckinney+reviews',
            'headline'    => 'McKinney patients keep <em>telling on us.</em>',
            'lede'        => 'Our McKinney patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at McKinney',
        ],
        'manor' => [
            'label'       => 'Manor',
            'address'     => '14008 Shadow Glen Blvd STE 203, Manor, TX 78653',
            'place_id'    => '__PLACE_ID_MANOR__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+manor+reviews',
            'headline'    => 'Manor patients keep <em>telling on us.</em>',
            'lede'        => 'Our Manor patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Manor',
        ],
        'easton-park' => [
            'label'       => 'Easton Park',
            'address'     => '7101 E William Cannon Dr, Building 4 Suite 430, Austin, TX 78744',
            'place_id'    => '__PLACE_ID_EASTON_PARK__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+easton+park+reviews',
            'headline'    => 'Easton Park patients keep <em>telling on us.</em>',
            'lede'        => 'Our Easton Park patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Easton Park',
        ],
        'leander' => [
            'label'       => 'Leander',
            'address'     => '128 South Brook Drive, Suite 120, Leander, TX 78641',
            'place_id'    => '__PLACE_ID_LEANDER__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+leander+reviews',
            'headline'    => 'Leander patients keep <em>telling on us.</em>',
            'lede'        => 'Our Leander patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Leander',
        ],

    ];
}

/** Generic / network-wide (homepage, service pages, "all studios" contexts). */
function erf_get_generic_defaults() {
    return [
        'label'       => 'All Studios (Generic)',
        'booking_url' => 'https://enamel.subscribili.com/appointments',
        'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+austin+reviews',
        'headline'    => 'Patients keep <em>telling on us.</em>',
        'lede'        => 'Across <strong data-role="agg-studios">10</strong> Enamel studios in the Austin area &amp; McKinney, patients keep saying about the same thing. We won&rsquo;t argue with them.',
        'button_text' => 'Book your visit',
    ];
}

/** Editable fields (the keys present in admin form + override option). */
function erf_editable_fields() {
    return [ 'booking_url', 'google_url', 'headline', 'lede', 'button_text' ];
}

/**
 * Per-studio config merged with admin overrides.
 * Returns the same shape as defaults; overrides only touch editable fields.
 */
function erf_get_locations() {
    $defaults  = erf_get_location_defaults();
    $overrides = get_option( 'erf_locations', [] );
    $place_ids = get_option( 'erf_place_ids', [] );

    foreach ( $defaults as $slug => &$loc ) {
        // Copy overrides (booking URL, headline, lede, etc.)
        if ( ! empty( $overrides[ $slug ] ) && is_array( $overrides[ $slug ] ) ) {
            foreach ( erf_editable_fields() as $field ) {
                if ( isset( $overrides[ $slug ][ $field ] ) && $overrides[ $slug ][ $field ] !== '' ) {
                    $loc[ $field ] = $overrides[ $slug ][ $field ];
                }
            }
        }
        // Place ID override (entered in WP Admin → Settings → Enamel Reviews).
        // Replaces the PHP placeholder so no code edit is ever needed.
        if ( ! empty( $place_ids[ $slug ] ) ) {
            $loc['place_id'] = $place_ids[ $slug ];
        }
    }
    unset( $loc );
    return $defaults;
}

/** Place IDs as saved in admin (slug => id). Empty array if never set. */
function erf_get_place_ids() {
    $saved = get_option( 'erf_place_ids', [] );
    return is_array( $saved ) ? $saved : [];
}

/** Sanitize the Place IDs form: trim, drop blanks/placeholders/unknown slugs. */
function erf_sanitize_place_ids( $input ) {
    $clean = [];
    $known = erf_get_location_defaults();
    if ( is_array( $input ) ) {
        foreach ( $input as $slug => $id ) {
            $slug = sanitize_key( $slug );
            $id   = trim( sanitize_text_field( $id ) );
            // Skip blanks, leftover placeholders, and slugs no longer in the
            // location list (keeps the option clean after a location rename).
            if ( $id === '' || strpos( $id, '__' ) !== false || ! array_key_exists( $slug, $known ) ) {
                continue;
            }
            $clean[ $slug ] = $id;
        }
    }
    return $clean;
}

/** Generic config merged with admin overrides. */
function erf_get_generic() {
    $defaults = erf_get_generic_defaults();
    $override = get_option( 'erf_generic', [] );
    if ( is_array( $override ) ) {
        foreach ( erf_editable_fields() as $field ) {
            if ( isset( $override[ $field ] ) && $override[ $field ] !== '' ) {
                $defaults[ $field ] = $override[ $field ];
            }
        }
    }
    return $defaults;
}

/* ---------------------------------------------------------------------------
 * Display filters (the "what shows" knobs) — live-editable in WP Admin.
 *
 * These are applied CLIENT-SIDE by enamel-reviews-api.js _buildPayload(), so a
 * change takes effect on the next page load — no re-fetch, no redeploy. They
 * are injected to the browser as window.ENAMEL_REVIEWS_SETTINGS.
 *
 *   minRating   1-5   Hide any review below N stars (curating, not lying).
 *   minLength   chars Skip terse "Great!" reviews so the grid has substance.
 *   maxReviews  1-4   Mini cards shown after the featured one. Capped at 4:
 *                     Google returns max 5 reviews per studio (1 featured +
 *                     4 mini), and the grid is designed for a clean 4-up row.
 *
 * maxFeatured is intentionally NOT exposed — it's fixed at 1 (the single
 * postcard hero is core to the design).
 * --------------------------------------------------------------------------- */
function erf_get_filter_defaults() {
    return [
        'minRating'  => 4,
        'minLength'  => 60,
        'maxReviews' => 4,
    ];
}

function erf_get_filters() {
    $defaults = erf_get_filter_defaults();
    $saved    = get_option( 'erf_filters', [] );
    if ( ! is_array( $saved ) ) {
        return $defaults;
    }
    return [
        'minRating'  => isset( $saved['minRating'] )  ? (int) $saved['minRating']  : $defaults['minRating'],
        'minLength'  => isset( $saved['minLength'] )  ? (int) $saved['minLength']  : $defaults['minLength'],
        'maxReviews' => isset( $saved['maxReviews'] ) ? (int) $saved['maxReviews'] : $defaults['maxReviews'],
    ];
}

/** Clamp filter values to safe ranges before saving. */
function erf_sanitize_filters( $input ) {
    $d = erf_get_filter_defaults();
    return [
        'minRating'  => min( 5, max( 1, isset( $input['minRating'] )  ? (int) $input['minRating']  : $d['minRating'] ) ),
        'minLength'  => min( 500, max( 0, isset( $input['minLength'] )  ? (int) $input['minLength']  : $d['minLength'] ) ),
        'maxReviews' => min( 4, max( 1, isset( $input['maxReviews'] ) ? (int) $input['maxReviews'] : $d['maxReviews'] ) ),
    ];
}
