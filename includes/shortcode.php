<?php
/**
 * Enamel Reviews Feed — Shortcode
 *
 *   [enamel_reviews]                         → generic / all studios
 *   [enamel_reviews location="south-lamar"]  → a specific studio
 *
 * Drop it into an Elementor "Shortcode" widget, a Shortcoder block, a
 * Gutenberg shortcode block, or any content area. The plugin's enqueued
 * JS (enamel-reviews-widget.js) binds the rendered section and fills it
 * with the right per-location reviews. CSS/JS/config are already loaded
 * site-wide by the plugin, so the shortcode only needs to emit markup.
 */

defined( 'ABSPATH' ) || exit;

add_shortcode( 'enamel_reviews', 'erf_reviews_shortcode' );

function erf_reviews_shortcode( $atts ) {
    $atts = shortcode_atts(
        [ 'location' => '' ],
        $atts,
        'enamel_reviews'
    );

    // Sanitize to a slug; empty string = generic. Validate against known
    // locations so a typo falls back to generic rather than rendering blank.
    $slug = sanitize_key( $atts['location'] );
    if ( $slug !== '' && ! array_key_exists( $slug, erf_get_location_defaults() ) ) {
        $slug = '';
    }

    $loc    = esc_attr( $slug );
    $schema = erf_reviews_schema_jsonld( $slug );

    // The markup mirrors widget-templates/widget.html (binder fills the rest).
    return <<<HTML
<section class="ed-rv" data-location="{$loc}" aria-labelledby="ed-rv-title">
  <div class="ed-rv__wrap">

    <article class="ed-rv__featured" data-role="featured" aria-live="polite" aria-atomic="false" aria-label="Featured patient review">
      <span class="ed-rv__featured-pin">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M16 3l1.5 4.5L22 9l-4.5 1.5L16 15l-1.5-4.5L10 9l4.5-1.5L16 3zM7 12l1 3 3 1-3 1-1 3-1-3-3-1 3-1 1-3z"/>
        </svg>
        Featured patient
      </span>
      <div class="ed-rv-stars ed-rv-stars--lg" data-role="stars" role="img" aria-label="5 out of 5 stars">★★★★★</div>
      <p class="ed-rv__featured-quote" data-role="quote">Loading the latest review&hellip;</p>
      <div class="ed-rv__featured-foot">
        <div class="ed-rv-author">
          <div class="ed-rv-avatar" data-role="initials" aria-hidden="true">EA</div>
          <div>
            <div class="ed-rv-author-name" data-role="name">Enamel Patient</div>
            <div class="ed-rv-author-meta">
              <span data-role="location">Austin</span>
              <span aria-hidden="true">·</span>
              <span data-role="time">recent</span>
            </div>
          </div>
        </div>
        <span class="ed-rv-verified">
          <span data-role="glogo"></span>
          Verified · Google
        </span>
      </div>
    </article>

    <div class="ed-rv__side">
      <div class="ed-rv-eyebrow">
        <strong data-role="agg-total-rounded">4,000+</strong> Reviews
      </div>

      <h2 class="ed-rv__title" data-role="headline" id="ed-rv-title">&hellip;</h2>

      <div class="ed-rv__statline">
        <span class="ed-rv-stars ed-rv-stars--lg" role="img" aria-label="4.9 out of 5 average rating">★★★★★</span>
        <span class="ed-rv__statline-num"><strong data-role="agg-rating">4.9</strong> / 5 average</span>
        <span class="ed-rv__statline-sep" aria-hidden="true">·</span>
        <span class="ed-rv__statline-google">
          <span data-role="glogo-sm"></span>
          Verified on Google
        </span>
      </div>

      <p class="ed-rv__lede" data-role="lede">&hellip;</p>

      <div class="ed-rv__cta">
        <a class="ed-rv-btn ed-rv-btn--primary" data-role="booking-link" href="#">
          <span data-role="booking-text">Book your visit</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        <a class="ed-rv-btn ed-rv-btn--ghost" data-role="google-link" href="#" target="_blank" rel="noopener">
          Read all on Google
        </a>
      </div>
    </div>

    <div class="ed-rv__grid" data-role="grid" aria-live="polite" aria-atomic="false" role="list" aria-label="More patient reviews"></div>

  </div>
  {$schema}
</section>
HTML;
}

/**
 * Pasted-HTML embeds (an Elementor HTML widget holding widget-templates/
 * widget.html) never run the shortcode, so no PHP prints their schema — and
 * the site's delay-JS keeps the JS fallback from ever reaching crawlers.
 *
 * An output buffer scans the FINAL rendered HTML — the exact bytes a crawler
 * receives — for .ed-rv sections and injects the JSON-LD before </body>.
 * Works no matter which Elementor construct produced the section (HTML
 * widget, template, global widget). Nests inside WP Rocket's page-cache
 * buffer (started far earlier in advanced-cache.php), so the injected schema
 * is also what gets page-cached. Shortcode embeds are unaffected: their
 * block is already in the markup and erf_reviews_schema_jsonld() de-dupes
 * per slug, so the injector adds nothing for them.
 */
add_action( 'template_redirect', 'erf_schema_start_buffer', 2 );
function erf_schema_start_buffer() {
    if ( is_admin() || is_feed() || is_embed()
        || ( defined( 'DOING_AJAX' ) && DOING_AJAX )
        || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }
    ob_start( 'erf_schema_inject' );
}

function erf_schema_inject( $html ) {
    if ( strpos( $html, 'class="ed-rv"' ) === false || strpos( $html, '</body>' ) === false ) {
        return $html;
    }
    if ( ! preg_match_all( '/<section[^>]*class="ed-rv"[^>]*>/', $html, $tags ) ) {
        return $html;
    }

    $known = erf_get_location_defaults();
    $seen  = [];
    $out   = '';
    foreach ( $tags[0] as $tag ) {
        $slug = preg_match( '/data-location="([^"]*)"/', $tag, $m ) ? sanitize_key( $m[1] ) : '';
        if ( $slug !== '' && ! array_key_exists( $slug, $known ) ) {
            $slug = ''; // unknown slug renders as generic, mirror that
        }
        if ( isset( $seen[ $slug ] ) ) {
            continue;
        }
        $seen[ $slug ] = true;
        $out          .= erf_reviews_schema_jsonld( $slug );
    }
    if ( $out === '' ) {
        return $html;
    }

    $pos = strrpos( $html, '</body>' );
    return substr( $html, 0, $pos ) . $out . substr( $html, $pos );
}

/**
 * Server-rendered JSON-LD (Dentist → AggregateRating + Review list) matching
 * the reviews the widget displays. Emitted inline with the shortcode markup
 * so crawlers that do not execute JavaScript (most AI/LLM crawlers) still see
 * the rating data the widget only renders visually via JS.
 *
 * Built exclusively from the static feed JSON the cron fetched from Google —
 * never from the bundled sample data. If the feed doesn't exist yet (fresh
 * install, fetch not run) no schema is emitted at all: an empty block is
 * better than fabricated numbers.
 */
function erf_reviews_schema_jsonld( $slug ) {
    // One schema block per location per page (the widget can appear twice).
    static $emitted = [];
    if ( isset( $emitted[ $slug ] ) ) {
        return '';
    }

    $feed_file = $slug === ''
        ? ERF_FEED_DIR . 'enamel-reviews.json'
        : ERF_FEED_DIR . 'enamel-reviews-' . $slug . '.json';

    if ( ! is_readable( $feed_file ) ) {
        return '';
    }

    $data   = json_decode( (string) file_get_contents( $feed_file ), true );
    $rating = isset( $data['aggregate']['rating'] ) ? (float) $data['aggregate']['rating'] : 0;
    $total  = isset( $data['aggregate']['total'] )  ? (int) $data['aggregate']['total']    : 0;
    if ( $rating <= 0 || $total < 1 ) {
        return '';
    }

    $page_url = get_permalink() ? get_permalink() : home_url( '/' );

    if ( $slug === '' ) {
        $name = 'Enamel Dentistry';
    } else {
        $locations = erf_get_locations();
        $name      = 'Enamel Dentistry ' . $locations[ $slug ]['label'];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Dentist',
        'name'     => $name,
        'aggregateRating' => [
            '@type'       => 'AggregateRating',
            'ratingValue' => $rating,
            // ratingCount (not reviewCount): Google's user_ratings_total
            // includes star-only ratings without review text.
            'ratingCount' => $total,
            'bestRating'  => 5,
            'worstRating' => 1,
        ],
    ];

    if ( $slug !== '' ) {
        // Location pages carry a hand-built Dentist entity with
        // @id "<permalink>#dentist" (phone, geo, hours, insurers). Using the
        // same @id makes parsers MERGE our rating + reviews into that entity
        // instead of creating a duplicate LocalBusiness. On pages without it,
        // this node stands alone as the entity. Address/sameAs/url are left
        // to the hand-built block to avoid conflicting merged values.
        $schema['@id'] = $page_url . '#dentist';
    } else {
        $schema['url'] = $page_url;
    }

    // Mirror the widget's display filters so the marked-up reviews are the
    // same ones a visitor sees on the page (a schema.org/Google requirement).
    $filters = erf_get_filters();
    $reviews = array_values( array_filter(
        isset( $data['reviews'] ) ? $data['reviews'] : [],
        function ( $r ) use ( $filters ) {
            return (int) ( $r['rating'] ?? 0 ) >= $filters['minRating']
                && mb_strlen( $r['text'] ?? '' ) >= $filters['minLength'];
        }
    ) );
    usort( $reviews, function ( $a, $b ) {
        return ( $b['time'] ?? 0 ) <=> ( $a['time'] ?? 0 );
    } );
    $reviews = array_slice( $reviews, 0, 1 + $filters['maxReviews'] ); // featured + grid

    foreach ( $reviews as $r ) {
        $item = [
            '@type'  => 'Review',
            'author' => [ '@type' => 'Person', 'name' => $r['author_name'] ?? 'Enamel Patient' ],
            'reviewRating' => [
                '@type'       => 'Rating',
                'ratingValue' => (int) ( $r['rating'] ?? 5 ),
                'bestRating'  => 5,
                'worstRating' => 1,
            ],
            'reviewBody' => $r['text'] ?? '',
        ];
        if ( ! empty( $r['time'] ) ) {
            $item['datePublished'] = gmdate( 'Y-m-d', (int) $r['time'] );
        }
        $schema['review'][] = $item;
    }

    $emitted[ $slug ] = true;

    // wp_json_encode escapes "/" so a "</script>" in a review body cannot
    // break out of the tag. data-erf-schema also tells the widget JS not to
    // inject its duplicate fallback schema for this location.
    return '<script type="application/ld+json" data-erf-schema="' . esc_attr( $slug === '' ? 'generic' : $slug ) . '">'
        . wp_json_encode( $schema )
        . '</script>';
}
