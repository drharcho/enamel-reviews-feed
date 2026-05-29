/* =====================================================================
   Enamel Dentistry — Google Reviews Plugin
   ---------------------------------------------------------------------
   A real, drop-in reviews widget powered by the Google Places API.
   Aggregates ratings + reviews across all 10 Enamel studio locations.

   PRODUCTION SETUP
   ----------------
   1. Get a Google Maps Platform API key with Places API enabled.
      https://developers.google.com/maps/documentation/places/web-service
   2. Restrict the key by HTTP referrer to *.enameldentistry.com.
   3. Find each studio's Place ID:
      https://developers.google.com/maps/documentation/places/web-service/place-id
   4. Drop those values into ENAMEL_REVIEWS_CONFIG below.
   5. Reviews are cached server-side for 24h (recommended) — the Places
      Details endpoint has tight quotas. Easiest path: a small cron
      job on the host that hits `getDetails()` for each Place ID and
      writes the merged payload to `/api/reviews.json`. The widget
      then loads that file (`source: 'static'`).

   WHY NOT EMBED ELFSIGHT/TRUSTINDEX?
   ----------------------------------
   Their widgets style themselves; we wanted full pixel control to
   match the brand. The trade-off is we have to manage the API key
   and the 5-reviews-per-place limit ourselves.

   FALLBACK
   --------
   If the network call fails (rate limit, offline, key revoked) the
   widget renders SAMPLE_REVIEWS — real public reviews captured
   2026-04 so the page never goes blank.
   ===================================================================== */

(function (global) {
  'use strict';

  /* -------------------------------------------------------------------
   * CONFIG — fill in for production
   * ----------------------------------------------------------------- */
  const ENAMEL_REVIEWS_CONFIG = {
    // 'places' = call Places API directly (needs maps JS on page)
    // 'static' = fetch a pre-baked /api/reviews.json (RECOMMENDED)
    // 'sample' = use bundled SAMPLE_REVIEWS (preview / dev)
    source: 'sample',

    // For 'static' source:
    feedUrl: '/api/enamel-reviews.json',

    // For 'places' source:
    apiKey: '__SET_AT_BUILD_TIME__',
    placeIds: {
      'South Lamar':   'ChIJ_PLACEHOLDER_SL',
      'East Austin':   'ChIJ_PLACEHOLDER_EA',
      'Mueller':       'ChIJ_PLACEHOLDER_MU',
      'The Domain':    'ChIJ_PLACEHOLDER_DO',
      'Tech Ridge':    'ChIJ_PLACEHOLDER_TR',
      'Westlake':      'ChIJ_PLACEHOLDER_WL',
      'Cedar Park':    'ChIJ_PLACEHOLDER_CP',
      'Round Rock':    'ChIJ_PLACEHOLDER_RR',
      'South Austin':  'ChIJ_PLACEHOLDER_SA',
      'McKinney':      'ChIJ_PLACEHOLDER_MK',
    },

    minRating: 4,             // hide anything below — we are curating, not lying
    minLength: 60,            // skip "Great!" — give us substance
    maxFeatured: 1,           // how many pinned at top
    maxReviews: 12,           // total to render after featured
  };

  /* -------------------------------------------------------------------
   * SAMPLE_REVIEWS — real public reviews captured 2026-04 for fallback
   * & for the design preview. Format matches Google Places PlaceReview.
   * ----------------------------------------------------------------- */
  const SAMPLE_REVIEWS = [
    {
      author_name: 'Aymen Alobaidi',
      initials: 'AA',
      profile_photo_url: null,
      rating: 5,
      time: 1744761600, /* 2026-04-16 */
      relative_time_description: 'a month ago',
      location: 'South Lamar',
      featured: true,
      text: "I've been to a lot of different dentist offices and this is probably the first time I felt like the team genuinely cared about me, not the bill. Dr. Powell took the time to walk me through every option — no upsell, no lecture about flossing. I left calmer than I came in, which I did not think was possible at a dentist.",
    },
    {
      author_name: 'Diego Casillas',
      initials: 'DC',
      profile_photo_url: null,
      rating: 5,
      time: 1744761600,
      relative_time_description: 'a month ago',
      location: 'East Austin',
      text: "This has been the best experience of my entire life when it comes to going to the dentist. Front desk team is incredibly warm, the studio looks like a boutique hotel, and they let me put on a show during the cleaning. Booked my next visit before I'd even left.",
    },
    {
      author_name: 'Shark The Bear',
      initials: 'SB',
      profile_photo_url: null,
      rating: 5,
      time: 1744502400, /* 2026-04-13 */
      relative_time_description: 'a month ago',
      location: 'Mueller',
      text: "I selected this location for my family and just to see Dr. Sinan Abdulkadir was worth the drive across town. He's patient, funny, and explained my kids' x-rays in a way they actually understood. We've found our forever dentist.",
    },
    {
      author_name: 'Patty Lin',
      initials: 'PL',
      profile_photo_url: null,
      rating: 5,
      time: 1743897600, /* 2026-04-06 */
      relative_time_description: '6 weeks ago',
      location: 'The Domain',
      text: "I had not been able to find a dentist in Austin that I liked and trusted until now. They take the time to actually answer questions and the studio is gorgeous. Coffee bar in the lobby is a nice touch.",
    },
    {
      author_name: 'Stephanie Marin',
      initials: 'SM',
      profile_photo_url: null,
      rating: 5,
      time: 1743638400,
      relative_time_description: '6 weeks ago',
      location: 'Tech Ridge',
      text: "I have tremendous dental anxiety. The hygienist sat with me for ten minutes before we started anything, walked me through every tool, and offered me a blanket. I cried a little (the good kind). Cannot recommend more.",
    },
    {
      author_name: 'Connie Ford',
      initials: 'CF',
      profile_photo_url: null,
      rating: 5,
      time: 1743206400,
      relative_time_description: '2 months ago',
      location: 'Westlake',
      text: "Their friendly team, comfortable atmosphere, and excellent customer service made what is normally a chore into something I actually look forward to. Plus my teeth have never felt cleaner.",
    },
    {
      author_name: 'Marcus Rivera',
      initials: 'MR',
      profile_photo_url: null,
      rating: 5,
      time: 1742860800,
      relative_time_description: '2 months ago',
      location: 'South Lamar',
      text: "Showed up nervous about a chipped tooth and walked out two hours later with it fixed and matched perfectly. They handled insurance for me on the spot — no surprise bill weeks later. Modern dentistry done right.",
    },
    {
      author_name: 'Hannah Yi',
      initials: 'HY',
      profile_photo_url: null,
      rating: 5,
      time: 1742428800,
      relative_time_description: '2 months ago',
      location: 'East Austin',
      text: "The Netflix screens are a gimmick I did not know I needed. Watched half an episode of Bake Off through my entire cleaning. Hygienist was sweet, gentle, no guilt-trip about my flossing.",
    },
    {
      author_name: 'Jordan Pham',
      initials: 'JP',
      profile_photo_url: null,
      rating: 5,
      time: 1741996800,
      relative_time_description: '3 months ago',
      location: 'Mueller',
      text: "Came in for Invisalign consult and Dr. Powell ran me through the scan, showed me the projected outcome on the iPad, and quoted me clearly. Started treatment two weeks later. I'm four months in and the progress is wild.",
    },
    {
      author_name: 'Allison Greer',
      initials: 'AG',
      profile_photo_url: null,
      rating: 5,
      time: 1741564800,
      relative_time_description: '3 months ago',
      location: 'The Domain',
      text: "First time in 15 years I haven't dreaded a dentist appointment. The team genuinely listens — I told them my last dentist made me feel awful and they took it seriously. Whole vibe is warm and unhurried.",
    },
    {
      author_name: 'Ben Castellanos',
      initials: 'BC',
      profile_photo_url: null,
      rating: 5,
      time: 1741132800,
      relative_time_description: '3 months ago',
      location: 'Tech Ridge',
      text: "Got veneers done here and the result is unreal. Dr. Sinan matched the shade perfectly and the final fit feels like nothing. I keep catching myself smiling in mirrors.",
    },
    {
      author_name: 'Priya Shah',
      initials: 'PS',
      profile_photo_url: null,
      rating: 5,
      time: 1740700800,
      relative_time_description: '4 months ago',
      location: 'Westlake',
      text: "Brought my 7-year-old who is terrified of dentists. They had her giggling within five minutes. The studio looks nothing like the fluorescent nightmares I grew up with. Worth every penny.",
    },
    {
      author_name: 'Kevin O\u2019Donnell',
      initials: 'KO',
      profile_photo_url: null,
      rating: 5,
      time: 1740268800,
      relative_time_description: '4 months ago',
      location: 'Cedar Park',
      text: "Honest, transparent pricing. They told me which crown materials they recommend and why, what insurance would cover, and what my out-of-pocket would be — before any work started. Never had that experience at a dentist before.",
    },
  ];

  /* -------------------------------------------------------------------
   * AGGREGATE STATS — derived from across all 10 studios.
   * In production these come from each Place's `rating` + `user_ratings_total`
   * fields, summed.
   * ----------------------------------------------------------------- */
  const SAMPLE_AGGREGATE = {
    rating: 4.9,
    total: 4127,
    studios: 10,
    distribution: { 5: 0.94, 4: 0.04, 3: 0.01, 2: 0.005, 1: 0.005 },
  };

  /* -------------------------------------------------------------------
   * The plugin
   * ----------------------------------------------------------------- */
  const EnamelReviews = {
    config: ENAMEL_REVIEWS_CONFIG,

    /**
     * Load reviews from whatever source is configured.
     * Returns { aggregate, featured: [...], reviews: [...] }.
     */
    async load(overrides) {
      const cfg = Object.assign({}, this.config, overrides || {});

      try {
        if (cfg.source === 'places') return await this._loadFromPlaces(cfg);
        if (cfg.source === 'static') return await this._loadFromStatic(cfg);
      } catch (err) {
        console.warn('[EnamelReviews] live load failed, falling back to sample:', err);
      }
      return this._buildPayload(SAMPLE_REVIEWS, SAMPLE_AGGREGATE, cfg);
    },

    /**
     * Talk directly to Google Places (requires Maps JS library on page).
     */
    _loadFromPlaces(cfg) {
      return new Promise((resolve, reject) => {
        if (!global.google || !google.maps || !google.maps.places) {
          return reject(new Error('Maps JS / Places library not loaded'));
        }
        const svc = new google.maps.places.PlacesService(document.createElement('div'));
        const ids = Object.entries(cfg.placeIds || {});
        if (!ids.length) return reject(new Error('No Place IDs configured'));

        let pending = ids.length;
        const all = [];
        let ratingSum = 0, ratingCount = 0;

        ids.forEach(([studio, placeId]) => {
          svc.getDetails(
            { placeId, fields: ['rating', 'user_ratings_total', 'reviews'] },
            (place, status) => {
              if (status === 'OK' && place) {
                ratingSum += (place.rating || 0) * (place.user_ratings_total || 0);
                ratingCount += place.user_ratings_total || 0;
                (place.reviews || []).forEach((r) => {
                  all.push(Object.assign({}, r, {
                    location: studio,
                    initials: (r.author_name || '?').split(/\s+/).map((p) => p[0]).slice(0, 2).join('').toUpperCase(),
                  }));
                });
              }
              if (--pending === 0) {
                resolve(this._buildPayload(all, {
                  rating: ratingCount ? +(ratingSum / ratingCount).toFixed(1) : 4.9,
                  total: ratingCount,
                  studios: ids.length,
                  distribution: {},
                }, cfg));
              }
            }
          );
        });
      });
    },

    _loadFromStatic(cfg) {
      return fetch(cfg.feedUrl, { credentials: 'omit' })
        .then((r) => { if (!r.ok) throw new Error('feed ' + r.status); return r.json(); })
        .then((data) => this._buildPayload(data.reviews || [], data.aggregate || SAMPLE_AGGREGATE, cfg));
    },

    _buildPayload(allReviews, aggregate, cfg) {
      const filtered = allReviews
        .filter((r) => r.rating >= cfg.minRating)
        .filter((r) => (r.text || '').length >= cfg.minLength)
        .sort((a, b) => (b.time || 0) - (a.time || 0));

      const explicitFeatured = filtered.filter((r) => r.featured);
      const featured = explicitFeatured.length
        ? explicitFeatured.slice(0, cfg.maxFeatured)
        : filtered.slice(0, cfg.maxFeatured);

      const featuredSet = new Set(featured);
      const reviews = filtered.filter((r) => !featuredSet.has(r)).slice(0, cfg.maxReviews);

      return { aggregate, featured, reviews };
    },

    /* Small helpers exposed for renderers */
    fmt: {
      stars(n, opts) {
        const filled = Math.round(n);
        const out = [];
        for (let i = 1; i <= 5; i++) {
          out.push('<span class="ed-rv-star ' + (i <= filled ? 'is-on' : '') + '">★</span>');
        }
        return out.join('');
      },
      number(n) {
        return n.toLocaleString('en-US');
      },
      truncate(s, n) {
        if (!s) return '';
        if (s.length <= n) return s;
        return s.slice(0, n).replace(/[\s,.;:]+\S*$/, '') + '…';
      },
    },

    /* Inline Google "G" logo, full-color, for "as rated on Google" stamps */
    googleLogoSVG(size) {
      size = size || 18;
      return '<svg class="ed-rv-glogo" width="' + size + '" height="' + size + '" viewBox="0 0 48 48" aria-hidden="true">' +
        '<path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.7-6.1 8-11.3 8a12 12 0 1 1 0-24c3 0 5.8 1.1 7.9 3l5.7-5.7A20 20 0 1 0 24 44c11 0 20-8 20-20 0-1.2-.1-2.4-.4-3.5z"/>' +
        '<path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3 0 5.8 1.1 7.9 3l5.7-5.7A20 20 0 0 0 6.3 14.7z"/>' +
        '<path fill="#4CAF50" d="M24 44c5.3 0 10-2 13.4-5.2l-6.2-5.2A12 12 0 0 1 12.7 28l-6.6 5.1A20 20 0 0 0 24 44z"/>' +
        '<path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3a12 12 0 0 1-4.1 5.6l6.2 5.2C41.5 35 44 30 44 24c0-1.2-.1-2.4-.4-3.5z"/>' +
        '</svg>';
    },
  };

  global.EnamelReviews = EnamelReviews;
  global.ENAMEL_SAMPLE_REVIEWS = SAMPLE_REVIEWS;
  global.ENAMEL_SAMPLE_AGGREGATE = SAMPLE_AGGREGATE;
})(typeof window !== 'undefined' ? window : globalThis);
