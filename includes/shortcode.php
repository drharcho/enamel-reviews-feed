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

    $loc = esc_attr( $slug );

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
</section>
HTML;
}
