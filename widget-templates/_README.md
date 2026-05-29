# Enamel Reviews — Universal Widget

**One file. Paste it on every page. Change one attribute per page.**

## What this folder contains

| File | Purpose |
|---|---|
| `widget.html` | The single universal widget snippet. Paste this into any Elementor HTML widget. |

## How it works

The plugin (once activated) does three things on every front-end page load:

1. Enqueues the shared CSS (`enamel-reviews.css`).
2. Enqueues the API JS (`enamel-reviews-api.js`).
3. Injects a `window.ENAMEL_LOCATIONS` object containing per-studio config
   (booking URL, Google listing URL, headline, lede, button text, feed URL).

The widget HTML reads its own `data-location` attribute, looks up the matching
config from `window.ENAMEL_LOCATIONS`, fills in the headline/lede/CTAs, and
fetches the matching JSON feed.

## Per-page setup

Open the page in Elementor → add an HTML widget → paste `widget.html` → change
**one attribute** on the `<section>` element:

| Page | `data-location` value |
|---|---|
| Homepage / general pages | `""` (empty) |
| `/locations/south-lamar/` | `"south-lamar"` |
| `/locations/south-lamar/invisalign/` | `"south-lamar"` (same slug — service pages reuse the location slug) |
| `/locations/mueller/` | `"mueller"` |
| `/locations/the-domain/` | `"the-domain"` |
| `/locations/east-austin/` | `"east-austin"` |
| `/locations/tech-ridge/` | `"tech-ridge"` |
| `/locations/westlake/` | `"westlake"` |
| `/locations/cedar-park/` | `"cedar-park"` |
| `/locations/round-rock/` | `"round-rock"` |
| `/locations/south-austin/` | `"south-austin"` |
| `/locations/mckinney/` | `"mckinney"` |

That's it. The rest of the snippet is identical on every page.

## Editing per-location copy

Go to **WP Admin → Settings → Enamel Reviews → Widget Copy**. Each studio (and
the generic "All Studios" context) has its own collapsible block with five
fields:

- **Headline** — the big `<h2>` (limited HTML allowed for `<em>` accents).
- **Lede** — the body paragraph below the headline.
- **Book CTA href** — where the primary button points.
- **Book CTA button text** — what the primary button says.
- **Google listing href** — where the "Read all on Google" button points.

Save and refresh any page. No code changes, no Elementor edits, no risk of
pages drifting out of sync.

## Multiple widgets on the same page

Supported. Each `<section class="ed-rv">` gets its own `data-bound` flag and
its own unique `id` for `aria-labelledby`. You can show, e.g., a Mueller widget
above a South Lamar widget on a comparison page if you want.

## Production checklist

Before going live:

1. Add the real Google Place IDs in `includes/location-config.php` (replace
   the `__PLACE_ID_*__` placeholders).
2. Get a Google Maps Platform API key with **Places API** enabled.
3. Restrict the key to `*.enameldentistry.com` HTTP referrers.
4. WP Admin → Settings → Enamel Reviews → paste API key → **Save**.
5. Click **Fetch Reviews Now** to populate the JSON feeds before the first
   daily cron fires.
6. Visit the **Feed File URLs** table on the same admin page to verify each
   `.json` file now exists.
7. Paste `widget.html` into your homepage's Elementor HTML widget. Leave
   `data-location=""`. Save and check the front end.
8. Repeat for each location page, setting `data-location="<slug>"` per the
   table above.
