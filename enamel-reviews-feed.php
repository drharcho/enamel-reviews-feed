<?php
/**
 * Plugin Name:       Enamel Reviews Feed
 * Plugin URI:        https://enameldentistry.com
 * Description:       Fetches Google Places reviews for all Enamel studios once daily and writes static JSON feeds consumed by the patient reviews Elementor widget. No client-side API key exposure.
 * Version:           1.1.2
 * Author:            Enamel Dentistry
 * License:           Proprietary
 * Text Domain:       enamel-reviews-feed
 * Update URI:        https://github.com/drharcho/enamel-reviews-feed
 *
 * ---------------------------------------------------------------------------
 * UPDATING THIS PLUGIN  (see RELEASING.md for the full flow)
 * ---------------------------------------------------------------------------
 * This plugin self-updates from its GitHub releases — same mechanism as the
 * Enamel Store Locator and Enamel Insurance Form plugins. No helper plugin
 * (e.g. Git Updater) is required. To ship a change:
 *   1. Edit code locally, commit, push to GitHub.
 *   2. Bump BOTH the "Version:" header above AND the ERF_VERSION constant
 *      below to the same new number (they MUST match — see erf_version_guard).
 *   3. Cut a release:  git tag v1.1.3 && git push --tags
 *                      gh release create v1.1.3 enamel-reviews-feed.zip
 *   4. Within ~12h (or immediately on Dashboard → Updates → Check Again) WP
 *      shows "Update Available" → click Update Now. See erf_check_for_update.
 * ---------------------------------------------------------------------------
 */

defined( 'ABSPATH' ) || exit;

define( 'ERF_VERSION',  '1.1.2' );
define( 'ERF_DIR',      plugin_dir_path( __FILE__ ) );
define( 'ERF_URL',      plugin_dir_url( __FILE__ ) );
define( 'ERF_FEED_DIR', WP_CONTENT_DIR . '/uploads/enamel/' );

require_once ERF_DIR . 'includes/location-config.php';
require_once ERF_DIR . 'includes/cron.php';
require_once ERF_DIR . 'includes/admin.php';

/* ------------------------------------------------------------------
 * Version-sync guard
 * ------------------------------------------------------------------
 * Git Updater compares the "Version:" plugin header against the latest
 * GitHub tag. If the header and the ERF_VERSION constant drift apart,
 * cache-busting and conditional logic break in confusing ways and the
 * update can silently no-op. This notice makes the mismatch loud.
 * ------------------------------------------------------------------ */
add_action( 'admin_notices', 'erf_version_guard' );
function erf_version_guard() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( ! function_exists( 'get_file_data' ) ) {
        return;
    }
    $header = get_file_data( __FILE__, [ 'Version' => 'Version' ] );
    $header_version = isset( $header['Version'] ) ? trim( $header['Version'] ) : '';

    if ( $header_version && $header_version !== ERF_VERSION ) {
        printf(
            '<div class="notice notice-error"><p><strong>Enamel Reviews Feed: version mismatch.</strong> '
            . 'Plugin header says <code>%s</code> but ERF_VERSION constant is <code>%s</code>. '
            . 'Set both to the same number (and match your GitHub tag) or updates may silently fail.</p></div>',
            esc_html( $header_version ),
            esc_html( ERF_VERSION )
        );
    }
}

/* ------------------------------------------------------------------
 * Activation / deactivation
 * ------------------------------------------------------------------ */

register_activation_hook( __FILE__, 'erf_activate' );
function erf_activate() {
    if ( ! file_exists( ERF_FEED_DIR ) ) {
        wp_mkdir_p( ERF_FEED_DIR );
    }

    // Schedule daily cron if not already scheduled.
    if ( ! wp_next_scheduled( 'erf_daily_fetch' ) ) {
        // 3:00 AM UTC — low-traffic window.
        $first_run = strtotime( 'tomorrow 03:00 UTC' );
        wp_schedule_event( $first_run, 'daily', 'erf_daily_fetch' );
    }

    // Queue an immediate one-shot fetch so the feeds exist before the first
    // daily cron fires.  Runs on the next page load that triggers WP-Cron.
    if ( ! wp_next_scheduled( 'erf_daily_fetch' ) ) {
        wp_schedule_single_event( time() + 5, 'erf_daily_fetch' );
    }

    // Protect the uploads/enamel/ directory from directory listing.
    $htaccess = ERF_FEED_DIR . '.htaccess';
    if ( ! file_exists( $htaccess ) ) {
        file_put_contents( $htaccess, "Options -Indexes\n" );
    }

    set_transient( 'erf_activated', true, 30 );
}

register_deactivation_hook( __FILE__, 'erf_deactivate' );
function erf_deactivate() {
    wp_clear_scheduled_hook( 'erf_daily_fetch' );
}

/* ------------------------------------------------------------------
 * Admin activation notice
 * ------------------------------------------------------------------ */

add_action( 'admin_notices', 'erf_activation_notice' );
function erf_activation_notice() {
    if ( ! get_transient( 'erf_activated' ) ) {
        return;
    }
    delete_transient( 'erf_activated' );
    ?>
    <div class="notice notice-info is-dismissible">
        <p>
            <strong>Enamel Reviews Feed activated.</strong>
            Go to <a href="<?php echo esc_url( admin_url( 'options-general.php?page=enamel-reviews' ) ); ?>">Settings &rarr; Enamel Reviews</a>,
            enter your Google API key, and click <em>Fetch Reviews Now</em> to populate the feeds before the first cron run.
        </p>
    </div>
    <?php
}

/* ------------------------------------------------------------------
 * Enqueue front-end assets (CSS + API JS) on all pages.
 * The Elementor widget HTML only contains the <section> + inline
 * binding script — these shared assets are loaded by the plugin.
 * ------------------------------------------------------------------ */

add_action( 'wp_enqueue_scripts', 'erf_enqueue' );
function erf_enqueue() {
    wp_enqueue_style(
        'enamel-reviews',
        ERF_URL . 'assets/enamel-reviews.css',
        [],
        ERF_VERSION
    );

    wp_enqueue_script(
        'enamel-reviews-api',
        ERF_URL . 'assets/enamel-reviews-api.js',
        [],
        ERF_VERSION,
        true // load in footer
    );

    // Inject window.ENAMEL_LOCATIONS so the universal widget can look up
    // per-location config (booking URL, headline, lede, feed URL) by
    // reading its own data-location attribute.  Printed BEFORE the API JS.
    wp_add_inline_script( 'enamel-reviews-api', erf_build_locations_js(), 'before' );
}

/**
 * Builds the inline JS that exposes the location registry to the browser.
 * Includes the generic config under key '' (empty string).
 */
function erf_build_locations_js() {
    $registry = [];

    // Generic (empty data-location)
    $g = erf_get_generic();
    $registry[''] = [
        'label'       => $g['label'],
        'bookingUrl'  => $g['booking_url'],
        'googleUrl'   => $g['google_url'],
        'headline'    => $g['headline'],
        'lede'        => $g['lede'],
        'buttonText'  => $g['button_text'],
        'feedUrl'     => content_url( 'uploads/enamel/enamel-reviews.json' ),
    ];

    // Per-location
    foreach ( erf_get_locations() as $slug => $loc ) {
        $registry[ $slug ] = [
            'label'       => $loc['label'],
            'bookingUrl'  => $loc['booking_url'],
            'googleUrl'   => $loc['google_url'],
            'headline'    => $loc['headline'],
            'lede'        => $loc['lede'],
            'buttonText'  => $loc['button_text'],
            'feedUrl'     => content_url( 'uploads/enamel/enamel-reviews-' . $slug . '.json' ),
        ];
    }

    // Display filters (minRating, minLength, maxReviews) — live-editable in
    // admin, applied client-side by _buildPayload on the next page load.
    $filters = erf_get_filters();

    return 'window.ENAMEL_LOCATIONS = ' . wp_json_encode( $registry ) . ';'
         . 'window.ENAMEL_REVIEWS_SETTINGS = ' . wp_json_encode( $filters ) . ';';
}

/* ------------------------------------------------------------------
 * Optional: server-cron trigger via secret URL query param.
 * Add ?erf_cron_key=HASH to a Cloudways cron wget call.
 * ------------------------------------------------------------------ */

add_action( 'init', 'erf_maybe_handle_server_cron' );
function erf_maybe_handle_server_cron() {
    if ( ! isset( $_GET['erf_cron_key'] ) ) {
        return;
    }
    if ( ! hash_equals( wp_hash( 'erf_cron' ), sanitize_text_field( wp_unslash( $_GET['erf_cron_key'] ) ) ) ) {
        status_header( 403 );
        exit( 'Forbidden' );
    }
    erf_do_fetch();
    status_header( 200 );
    header( 'Content-Type: text/plain' );
    echo 'ok: ' . esc_html( get_option( 'erf_last_fetch_status', '' ) );
    exit;
}

/* ------------------------------------------------------------------
 * Self-updater — checks GitHub releases for new versions.
 * Same mechanism used by Enamel Store Locator & Enamel Insurance Form.
 * No helper plugin (Git Updater) required: WordPress shows the update
 * in Dashboard → Updates / Plugins, and Update Now pulls the release zip.
 * ------------------------------------------------------------------ */

add_filter( 'pre_set_site_transient_update_plugins', 'erf_check_for_update' );
function erf_check_for_update( $transient ) {
    if ( empty( $transient->checked ) ) {
        return $transient;
    }

    $plugin_slug = plugin_basename( __FILE__ );
    $api_url     = 'https://api.github.com/repos/drharcho/enamel-reviews-feed/releases/latest';

    $response = wp_remote_get( $api_url, [
        'headers' => [
            'Accept'     => 'application/vnd.github+json',
            'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ),
        ],
        'timeout' => 10,
    ] );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        return $transient;
    }

    $release = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $release['tag_name'] ) ) {
        return $transient;
    }

    $latest_version = ltrim( $release['tag_name'], 'v' );

    if ( version_compare( $latest_version, ERF_VERSION, '>' ) ) {
        // Prefer the uploaded .zip asset (correct folder structure); fall
        // back to GitHub's auto-generated zipball.
        $package = $release['zipball_url'];
        if ( ! empty( $release['assets'] ) ) {
            foreach ( $release['assets'] as $asset ) {
                if ( pathinfo( $asset['name'], PATHINFO_EXTENSION ) === 'zip' ) {
                    $package = $asset['browser_download_url'];
                    break;
                }
            }
        }

        $transient->response[ $plugin_slug ] = (object) [
            'slug'        => dirname( $plugin_slug ),
            'plugin'      => $plugin_slug,
            'new_version' => $latest_version,
            'url'         => 'https://github.com/drharcho/enamel-reviews-feed',
            'package'     => $package,
        ];
    }

    return $transient;
}

// Fix the folder name when WordPress installs an update from a GitHub zip
// (GitHub zips extract to a versioned folder; rename to the plugin slug).
add_filter( 'upgrader_source_selection', 'erf_fix_update_folder', 10, 4 );
function erf_fix_update_folder( $source, $remote_source, $upgrader, $extra ) {
    if ( ! isset( $extra['plugin'] ) || strpos( $extra['plugin'], 'enamel-reviews-feed' ) === false ) {
        return $source;
    }

    $correct = trailingslashit( $remote_source ) . 'enamel-reviews-feed/';

    if ( $source !== $correct ) {
        global $wp_filesystem;
        if ( $wp_filesystem->move( $source, $correct ) ) {
            return $correct;
        }
    }

    return $source;
}
