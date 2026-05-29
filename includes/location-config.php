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
            'place_id'    => '__PLACE_ID_SOUTH_LAMAR__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+south+lamar+reviews',
            'headline'    => 'South Lamar patients keep <em>telling on us.</em>',
            'lede'        => 'Our South Lamar patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at South Lamar',
        ],
        'east-austin' => [
            'label'       => 'East Austin',
            'place_id'    => '__PLACE_ID_EAST_AUSTIN__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+east+austin+reviews',
            'headline'    => 'East Austin patients keep <em>telling on us.</em>',
            'lede'        => 'Our East Austin patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at East Austin',
        ],
        'mueller' => [
            'label'       => 'Mueller',
            'place_id'    => '__PLACE_ID_MUELLER__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+mueller+reviews',
            'headline'    => 'Mueller patients keep <em>telling on us.</em>',
            'lede'        => 'Our Mueller patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Mueller',
        ],
        'the-domain' => [
            'label'       => 'The Domain',
            'place_id'    => '__PLACE_ID_THE_DOMAIN__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+the+domain+reviews',
            'headline'    => 'Domain patients keep <em>telling on us.</em>',
            'lede'        => 'Our Domain patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at The Domain',
        ],
        'tech-ridge' => [
            'label'       => 'Tech Ridge',
            'place_id'    => '__PLACE_ID_TECH_RIDGE__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+tech+ridge+reviews',
            'headline'    => 'Tech Ridge patients keep <em>telling on us.</em>',
            'lede'        => 'Our Tech Ridge patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Tech Ridge',
        ],
        'westlake' => [
            'label'       => 'Westlake',
            'place_id'    => '__PLACE_ID_WESTLAKE__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+westlake+reviews',
            'headline'    => 'Westlake patients keep <em>telling on us.</em>',
            'lede'        => 'Our Westlake patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Westlake',
        ],
        'cedar-park' => [
            'label'       => 'Cedar Park',
            'place_id'    => '__PLACE_ID_CEDAR_PARK__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+cedar+park+reviews',
            'headline'    => 'Cedar Park patients keep <em>telling on us.</em>',
            'lede'        => 'Our Cedar Park patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Cedar Park',
        ],
        'round-rock' => [
            'label'       => 'Round Rock',
            'place_id'    => '__PLACE_ID_ROUND_ROCK__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+round+rock+reviews',
            'headline'    => 'Round Rock patients keep <em>telling on us.</em>',
            'lede'        => 'Our Round Rock patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at Round Rock',
        ],
        'south-austin' => [
            'label'       => 'South Austin',
            'place_id'    => '__PLACE_ID_SOUTH_AUSTIN__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+south+austin+reviews',
            'headline'    => 'South Austin patients keep <em>telling on us.</em>',
            'lede'        => 'Our South Austin patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at South Austin',
        ],
        'mckinney' => [
            'label'       => 'McKinney',
            'place_id'    => '__PLACE_ID_MCKINNEY__',
            'booking_url' => 'https://enamel.subscribili.com/appointments',
            'google_url'  => 'https://www.google.com/search?q=enamel+dentistry+mckinney+reviews',
            'headline'    => 'McKinney patients keep <em>telling on us.</em>',
            'lede'        => 'Our McKinney patients keep saying about the same thing. We won&rsquo;t argue with them.',
            'button_text' => 'Book at McKinney',
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
        'lede'        => 'Across <strong data-role="agg-studios">10</strong> Enamel studios in Austin &amp; McKinney, patients keep saying about the same thing. We won&rsquo;t argue with them.',
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

    foreach ( $defaults as $slug => &$loc ) {
        if ( ! empty( $overrides[ $slug ] ) && is_array( $overrides[ $slug ] ) ) {
            foreach ( erf_editable_fields() as $field ) {
                if ( isset( $overrides[ $slug ][ $field ] ) && $overrides[ $slug ][ $field ] !== '' ) {
                    $loc[ $field ] = $overrides[ $slug ][ $field ];
                }
            }
        }
    }
    unset( $loc );
    return $defaults;
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
