<?php
/**
 * Enamel Reviews Feed — Daily cron fetch
 *
 * Calls Google Places Details once per Place ID (10 calls/day total),
 * merges results, and writes two types of JSON to /wp-content/uploads/enamel/:
 *
 *   enamel-reviews.json              — all studios combined (for generic/homepage widget)
 *   enamel-reviews-{slug}.json       — per-studio filtered (for location/service pages)
 *
 * The JSON shape matches what enamel-reviews-api.js expects from _loadFromStatic():
 *   { aggregate: { rating, total, studios, distribution }, reviews: [...] }
 *
 * NOTE: Google Places API returns a maximum of 5 reviews per Place ID.
 * Across 10 studios that gives up to 50 reviews in the combined feed.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'erf_daily_fetch', 'erf_do_fetch' );

function erf_do_fetch() {
    $api_key = get_option( 'erf_api_key', '' );

    if ( empty( $api_key ) || strpos( $api_key, '__' ) !== false ) {
        update_option( 'erf_last_fetch_status', 'error: API key not set' );
        update_option( 'erf_last_fetch_time', time() );
        return;
    }

    $locations   = erf_get_locations();
    $all_reviews = [];
    $rating_sum  = 0;
    $rating_count = 0;
    $per_location = [];

    foreach ( $locations as $slug => $config ) {
        if ( strpos( $config['place_id'], '__' ) !== false ) {
            continue; // skip unconfigured locations
        }

        $url = add_query_arg(
            [
                'place_id' => $config['place_id'],
                'fields'   => 'rating,user_ratings_total,reviews',
                'language' => 'en',
                'key'      => $api_key,
            ],
            'https://maps.googleapis.com/maps/api/place/details/json'
        );

        $response = wp_remote_get( $url, [ 'timeout' => 10 ] );

        if ( is_wp_error( $response ) ) {
            continue;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['result'] ) || $body['status'] !== 'OK' ) {
            continue;
        }

        $place      = $body['result'];
        $loc_rating = (float) ( $place['rating'] ?? 0 );
        $loc_total  = (int) ( $place['user_ratings_total'] ?? 0 );

        $rating_sum   += $loc_rating * $loc_total;
        $rating_count += $loc_total;

        $loc_reviews = [];

        foreach ( $place['reviews'] ?? [] as $r ) {
            $review = [
                'author_name'              => $r['author_name'] ?? '',
                'initials'                 => erf_initials( $r['author_name'] ?? '' ),
                'profile_photo_url'        => $r['profile_photo_url'] ?? null,
                'rating'                   => (int) ( $r['rating'] ?? 5 ),
                'time'                     => (int) ( $r['time'] ?? 0 ),
                'relative_time_description' => $r['relative_time_description'] ?? '',
                'location'                 => $config['label'],
                'location_slug'            => $slug,
                'featured'                 => false,
                'text'                     => $r['text'] ?? '',
            ];
            $all_reviews[] = $review;
            $loc_reviews[] = $review;
        }

        $per_location[ $slug ] = [
            'rating'   => $loc_rating,
            'total'    => $loc_total,
            'reviews'  => $loc_reviews,
        ];

        usleep( 200000 ); // 200ms between Places calls — stay well under quota
    }

    if ( empty( $all_reviews ) ) {
        update_option( 'erf_last_fetch_status', 'error: no reviews returned — check Place IDs and API key' );
        update_option( 'erf_last_fetch_time', time() );
        return;
    }

    $network_aggregate = [
        'rating'       => $rating_count > 0 ? round( $rating_sum / $rating_count, 1 ) : 4.9,
        'total'        => $rating_count,
        'studios'      => count( $locations ),
        'distribution' => [],
    ];

    // Write combined feed (all studios)
    erf_write_feed(
        ERF_FEED_DIR . 'enamel-reviews.json',
        [
            'aggregate' => $network_aggregate,
            'reviews'   => $all_reviews,
        ]
    );

    // Write per-location feeds
    foreach ( $per_location as $slug => $data ) {
        erf_write_feed(
            ERF_FEED_DIR . "enamel-reviews-{$slug}.json",
            [
                'aggregate' => [
                    'rating'       => $data['rating'],
                    'total'        => $data['total'],
                    'studios'      => count( $locations ), // keep network count for stat strip
                    'distribution' => [],
                ],
                'reviews' => $data['reviews'],
            ]
        );
    }

    update_option( 'erf_last_fetch_status', 'ok: ' . count( $all_reviews ) . ' reviews across ' . count( $per_location ) . ' studios' );
    update_option( 'erf_last_fetch_time', time() );
}

function erf_write_feed( $path, $payload ) {
    if ( ! file_exists( ERF_FEED_DIR ) ) {
        wp_mkdir_p( ERF_FEED_DIR );
    }
    file_put_contents( $path, wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
}

function erf_initials( $name ) {
    $parts = preg_split( '/\s+/', trim( $name ) );
    $init  = '';
    foreach ( array_slice( $parts, 0, 2 ) as $part ) {
        $init .= mb_strtoupper( mb_substr( $part, 0, 1 ) );
    }
    return $init ?: '?';
}
