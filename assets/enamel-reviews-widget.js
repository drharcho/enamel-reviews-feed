/* =====================================================================
   Enamel Reviews — widget binder
   ---------------------------------------------------------------------
   Binds EVERY .ed-rv section on the page (however it got there — the
   [enamel_reviews] shortcode, an Elementor HTML widget, etc.): reads its
   data-location, pulls the matching config from window.ENAMEL_LOCATIONS,
   injects copy + CTAs, and renders the reviews from the per-location feed.
   Enqueued globally by the plugin, after enamel-reviews-api.js.
   ===================================================================== */
(function () {
  'use strict';

  function init() {
    // Wait until BOTH the API and the per-location config (printed by the
    // plugin immediately before the API script) are present.
    if (!window.EnamelReviews || !window.ENAMEL_LOCATIONS) return setTimeout(init, 50);

    var roots = document.querySelectorAll('.ed-rv:not([data-bound])');
    roots.forEach(function (root) {
      root.dataset.bound = '1';

      // 1) Pick config based on data-location, fall back to generic ('')
      var slug = root.getAttribute('data-location') || '';
      var cfg  = window.ENAMEL_LOCATIONS[slug] || window.ENAMEL_LOCATIONS[''] || null;
      if (!cfg) {
        console.warn('[EnamelReviews] no config for data-location="' + slug + '"');
        return;
      }

      // 2) Inject the location-specific copy & links
      var headlineEl = root.querySelector('[data-role="headline"]');
      var ledeEl     = root.querySelector('[data-role="lede"]');
      var bookBtn    = root.querySelector('[data-role="booking-link"]');
      var bookText   = root.querySelector('[data-role="booking-text"]');
      var googleBtn  = root.querySelector('[data-role="google-link"]');

      if (headlineEl) headlineEl.innerHTML = cfg.headline;
      if (ledeEl)     ledeEl.innerHTML     = cfg.lede;
      if (bookBtn)    bookBtn.setAttribute('href', cfg.bookingUrl);
      if (bookText)   bookText.textContent = cfg.buttonText;
      if (googleBtn)  googleBtn.setAttribute('href', cfg.googleUrl);

      // 3) Unique heading id to prevent aria-labelledby collisions when
      //    multiple widgets land on the same page.
      var uid = 'ed-rv-' + Math.random().toString(36).slice(2, 7);
      var h2  = root.querySelector('h2[id]');
      if (h2) { h2.id = uid; root.setAttribute('aria-labelledby', uid); }

      // 4) Read live-editable display filters (injected by the plugin).
      var s = window.ENAMEL_REVIEWS_SETTINGS || {};
      var minRating  = (typeof s.minRating  === 'number') ? s.minRating  : 4;
      var minLength  = (typeof s.minLength  === 'number') ? s.minLength  : 60;
      var maxReviews = (typeof s.maxReviews === 'number') ? s.maxReviews : 4;
      maxReviews = Math.min(4, Math.max(1, maxReviews));

      // 5) Fetch reviews from the right per-location JSON
      window.EnamelReviews.load({
        source:      'static',
        feedUrl:     cfg.feedUrl,
        minRating:   minRating,
        minLength:   minLength,
        maxReviews:  maxReviews,
        maxFeatured: 1
      }).then(function (payload) {
        bindPayload(root, payload, maxReviews);
        injectSchema(slug, cfg, payload);
      });
    });
  }

  function bindPayload(root, payload, maxReviews) {
    maxReviews = maxReviews || 4;
    var aggregate = payload.aggregate;
    var featured  = payload.featured;
    var reviews   = payload.reviews;
    var fmt       = window.EnamelReviews.fmt;
    var glogo     = window.EnamelReviews.googleLogoSVG;

    // Featured card
    var f = featured[0];
    if (f) {
      var card = root.querySelector('[data-role="featured"]');
      var starsEl = card.querySelector('[data-role="stars"]');
      starsEl.innerHTML = fmt.stars(f.rating);
      starsEl.setAttribute('aria-label', f.rating + ' out of 5 stars');
      card.querySelector('[data-role="quote"]').textContent    = fmt.truncate(f.text, 280);
      card.querySelector('[data-role="initials"]').textContent = f.initials;
      card.querySelector('[data-role="name"]').textContent     = f.author_name;
      card.querySelector('[data-role="location"]').textContent = f.location;
      card.querySelector('[data-role="time"]').textContent     = f.relative_time_description;
      card.querySelector('[data-role="glogo"]').innerHTML      = glogo(16);
    }

    // Aggregate (some targets optional — only present in generic lede)
    var set = function (sel, val) { var el = root.querySelector(sel); if (el) el.textContent = val; };
    set('[data-role="agg-total-rounded"]', roundDown(aggregate.total));
    set('[data-role="agg-rating"]',        aggregate.rating);
    set('[data-role="agg-studios"]',       aggregate.studios);
    var gl = root.querySelector('[data-role="glogo-sm"]'); if (gl) gl.innerHTML = glogo(14);

    // a11y: keep stat-strip star aria-label in sync with live rating
    var statStars = root.querySelector('.ed-rv__statline .ed-rv-stars');
    if (statStars) statStars.setAttribute('aria-label', aggregate.rating + ' out of 5 average rating');

    // Mini grid — render up to maxReviews cards; drive desktop column count
    // off the actual rendered count so the row always fills cleanly.
    var grid = root.querySelector('[data-role="grid"]');
    var shown = reviews.slice(0, maxReviews);
    grid.style.setProperty('--ed-cols', Math.min(4, Math.max(1, shown.length)));
    grid.innerHTML = shown.map(function (r) {
      return '<div class="ed-rv__mini" role="listitem">'
        + '<div class="ed-rv-stars" role="img" aria-label="' + r.rating + ' out of 5 stars">' + fmt.stars(r.rating) + '</div>'
        + '<p class="ed-rv__mini-text">' + esc(fmt.truncate(r.text, 140)) + '</p>'
        + '<div class="ed-rv__mini-foot">'
        +   '<span class="ed-rv__mini-name">' + esc(r.author_name) + '</span>'
        +   '<span class="ed-rv__mini-loc">'  + esc(r.location)    + '</span>'
        + '</div>'
        + '</div>';
    }).join('');
  }

  /* JSON-LD fallback for the Elementor HTML-widget embed path.
     The [enamel_reviews] shortcode prints AggregateRating schema server-side
     (PHP), which even non-JS crawlers can read. When the section arrived
     without it (pasted widget.html — no PHP ran), inject the same schema from
     the live feed payload so JS-rendering crawlers (Googlebot) still get it.
     Never emitted for the bundled sample fallback data (payload.live). */
  function injectSchema(slug, cfg, payload) {
    var key = slug || 'generic';
    if (!payload.live) return;
    if (document.querySelector('script[data-erf-schema="' + key + '"]')) return;
    var agg = payload.aggregate || {};
    if (!(agg.rating > 0) || !(agg.total > 0)) return;

    var schema = {
      '@context': 'https://schema.org',
      '@type': 'Dentist',
      name: 'Enamel Dentistry' + (slug ? ' ' + cfg.label : ''),
      url: location.href.split('#')[0],
      aggregateRating: {
        '@type': 'AggregateRating',
        ratingValue: agg.rating,
        ratingCount: agg.total,
        bestRating: 5,
        worstRating: 1
      },
      review: (payload.featured || []).concat(payload.reviews || []).map(function (r) {
        var item = {
          '@type': 'Review',
          author: { '@type': 'Person', name: r.author_name },
          reviewRating: { '@type': 'Rating', ratingValue: r.rating, bestRating: 5, worstRating: 1 },
          reviewBody: r.text
        };
        if (r.time) item.datePublished = new Date(r.time * 1000).toISOString().slice(0, 10);
        return item;
      })
    };

    var tag = document.createElement('script');
    tag.type = 'application/ld+json';
    tag.setAttribute('data-erf-schema', key);
    tag.textContent = JSON.stringify(schema);
    document.head.appendChild(tag);
  }

  function roundDown(n) {
    if (n >= 1000) return Math.floor(n / 1000) + ',000+';
    if (n >= 100)  return Math.floor(n / 100) * 100 + '+';
    return String(n);
  }
  function esc(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
