<?php
/**
 * Enamel Reviews Feed — Admin (Settings → Enamel Reviews)
 *
 * Sections:
 *   1. Google API key (stored in wp_options as erf_api_key)
 *   2. Manual Fetch button
 *   3. Status (last fetch, next scheduled fetch)
 *   4. Feed file URLs (so you know what to point feedUrl at)
 *   5. Generic widget copy (network-wide booking URL, headline, lede, etc.)
 *   6. Per-location widget copy (10 collapsible sections, one per studio)
 *   7. Cloudways cron note
 *
 * Saves overrides to wp_options:
 *   - erf_api_key           (string)
 *   - erf_generic           (array of editable fields)
 *   - erf_locations         (array indexed by slug; each value is an array of editable fields)
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'erf_admin_menu' );
add_action( 'admin_init', 'erf_admin_init' );
add_action( 'admin_post_erf_fetch_now', 'erf_admin_fetch_now' );
add_action( 'admin_post_erf_save_copy', 'erf_admin_save_copy' );

function erf_admin_menu() {
    add_options_page(
        'Enamel Reviews',
        'Enamel Reviews',
        'manage_options',
        'enamel-reviews',
        'erf_admin_page'
    );
}

function erf_admin_init() {
    register_setting( 'erf_settings', 'erf_api_key', [
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ] );
    register_setting( 'erf_filters_group', 'erf_filters', [
        'sanitize_callback' => 'erf_sanitize_filters',
        'default'           => erf_get_filter_defaults(),
    ] );
}

function erf_admin_fetch_now() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
    check_admin_referer( 'erf_fetch_now' );
    erf_do_fetch();
    wp_safe_redirect( add_query_arg( [ 'page' => 'enamel-reviews', 'fetched' => '1' ], admin_url( 'options-general.php' ) ) );
    exit;
}

/**
 * Handles the "Save widget copy" submission — writes both 'erf_generic'
 * and 'erf_locations' options from POST data.
 */
function erf_admin_save_copy() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
    check_admin_referer( 'erf_save_copy' );

    $fields = erf_editable_fields();

    // Generic
    $generic = [];
    if ( ! empty( $_POST['erf_generic'] ) && is_array( $_POST['erf_generic'] ) ) {
        foreach ( $fields as $f ) {
            $val = isset( $_POST['erf_generic'][ $f ] ) ? wp_unslash( $_POST['erf_generic'][ $f ] ) : '';
            $generic[ $f ] = ( $f === 'lede' || $f === 'headline' ) ? wp_kses_post( $val ) : sanitize_text_field( $val );
        }
    }
    update_option( 'erf_generic', $generic );

    // Per-location
    $locations = [];
    if ( ! empty( $_POST['erf_locations'] ) && is_array( $_POST['erf_locations'] ) ) {
        foreach ( $_POST['erf_locations'] as $slug => $values ) {
            $slug = sanitize_key( $slug );
            $clean = [];
            foreach ( $fields as $f ) {
                $val = isset( $values[ $f ] ) ? wp_unslash( $values[ $f ] ) : '';
                $clean[ $f ] = ( $f === 'lede' || $f === 'headline' ) ? wp_kses_post( $val ) : sanitize_text_field( $val );
            }
            $locations[ $slug ] = $clean;
        }
    }
    update_option( 'erf_locations', $locations );

    wp_safe_redirect( add_query_arg( [ 'page' => 'enamel-reviews', 'saved' => '1' ], admin_url( 'options-general.php' ) ) . '#widget-copy' );
    exit;
}

function erf_admin_page() {
    $last_time   = get_option( 'erf_last_fetch_time', 0 );
    $last_status = get_option( 'erf_last_fetch_status', 'never run' );
    $next_event  = wp_next_scheduled( 'erf_daily_fetch' );
    $locations   = erf_get_locations();
    $generic     = erf_get_generic();
    ?>
    <div class="wrap">
        <h1>Enamel Reviews Feed</h1>

        <?php if ( isset( $_GET['fetched'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>Fetch complete. Check status below.</p></div>
        <?php endif; ?>
        <?php if ( isset( $_GET['saved'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>Widget copy saved. Pages will reflect the new copy on next page load.</p></div>
        <?php endif; ?>

        <h2 class="title">1. API Key</h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'erf_settings' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="erf_api_key">Google Maps API Key</label></th>
                    <td>
                        <input
                            type="password"
                            id="erf_api_key"
                            name="erf_api_key"
                            value="<?php echo esc_attr( get_option( 'erf_api_key', '' ) ); ?>"
                            class="regular-text"
                            autocomplete="off"
                        >
                        <p class="description">
                            Restrict this key to <code>*.enameldentistry.com</code> referrers and enable <strong>Places API</strong> only.
                            <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">API Console &rarr;</a>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Save API Key' ); ?>
        </form>

        <hr>

        <h2 class="title">2. Manual Fetch</h2>
        <p>Run the Places fetch right now. (Normally runs once a day at 3am UTC.)</p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="erf_fetch_now">
            <?php wp_nonce_field( 'erf_fetch_now' ); ?>
            <?php submit_button( 'Fetch Reviews Now', 'secondary' ); ?>
        </form>

        <hr>

        <h2 class="title">3. Status</h2>
        <table class="widefat striped" style="max-width:680px;">
            <tbody>
                <tr><td><strong>Last fetch</strong></td><td><?php echo $last_time ? esc_html( date( 'Y-m-d H:i:s T', $last_time ) ) : '—'; ?></td></tr>
                <tr><td><strong>Last result</strong></td><td><?php echo esc_html( $last_status ); ?></td></tr>
                <tr><td><strong>Next scheduled fetch</strong></td><td><?php echo $next_event ? esc_html( date( 'Y-m-d H:i:s T', $next_event ) ) : '<em>not scheduled — deactivate &amp; reactivate the plugin</em>'; ?></td></tr>
            </tbody>
        </table>

        <hr id="widget-copy">

        <h2 class="title">4. Widget Copy</h2>
        <p>
            Edit the headline, lede, booking URL, and Google listing URL the widget shows for each context.
            The <strong>data-location</strong> attribute on the Elementor HTML widget chooses which set of copy gets used.
        </p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="erf_save_copy">
            <?php wp_nonce_field( 'erf_save_copy' ); ?>

            <details open style="margin: 14px 0; padding: 14px 18px; background: #fff; border: 1px solid #c3c4c7;">
                <summary style="font-weight:600;cursor:pointer;">
                    Generic / All Studios — used when <code>data-location=""</code> (homepage, network-wide pages)
                </summary>
                <?php erf_render_copy_fields( 'erf_generic', $generic ); ?>
            </details>

            <?php foreach ( $locations as $slug => $loc ) : ?>
                <details style="margin: 14px 0; padding: 14px 18px; background: #fff; border: 1px solid #c3c4c7;">
                    <summary style="font-weight:600;cursor:pointer;">
                        <?php echo esc_html( $loc['label'] ); ?> — used when <code>data-location="<?php echo esc_html( $slug ); ?>"</code>
                    </summary>
                    <?php erf_render_copy_fields( 'erf_locations[' . esc_attr( $slug ) . ']', $loc ); ?>
                </details>
            <?php endforeach; ?>

            <?php submit_button( 'Save All Widget Copy' ); ?>
        </form>

        <hr>

        <h2 class="title">5. Display Filters</h2>
        <p>Tune which reviews are eligible to show. Changes take effect on the next page load — no re-fetch needed.</p>
        <?php $filters = erf_get_filters(); ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'erf_filters_group' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="erf_minRating">Minimum star rating</label></th>
                    <td>
                        <input type="number" id="erf_minRating" name="erf_filters[minRating]" value="<?php echo esc_attr( $filters['minRating'] ); ?>" min="1" max="5" step="1" class="small-text">
                        <p class="description">Hide any review below this many stars. Default <code>4</code> — we curate, we don't lie.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="erf_minLength">Minimum review length</label></th>
                    <td>
                        <input type="number" id="erf_minLength" name="erf_filters[minLength]" value="<?php echo esc_attr( $filters['minLength'] ); ?>" min="0" max="500" step="5" class="small-text"> characters
                        <p class="description">Skip terse reviews like "Great!" so cards have substance. Default <code>60</code>.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="erf_maxReviews">Mini cards shown</label></th>
                    <td>
                        <input type="number" id="erf_maxReviews" name="erf_filters[maxReviews]" value="<?php echo esc_attr( $filters['maxReviews'] ); ?>" min="1" max="4" step="1" class="small-text">
                        <p class="description">
                            Number of smaller cards below the featured one (1–4). Capped at <strong>4</strong>:
                            Google returns at most 5 reviews per studio, and the grid is built for a clean 4-up row.
                            The featured postcard is always shown and isn't counted here.
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Save Filters' ); ?>
        </form>

        <hr>

        <h2 class="title">6. Feed File URLs</h2>
        <p>The plugin writes these JSON files for the widget to consume. They update once a day after the cron runs.</p>
        <table class="widefat striped" style="max-width:900px;">
            <thead><tr><th>Context</th><th>data-location value</th><th>Feed URL</th><th>File exists?</th></tr></thead>
            <tbody>
                <?php
                $rows = [ [ 'Generic / All studios', '<em>empty</em>', 'enamel-reviews.json' ] ];
                foreach ( $locations as $slug => $loc ) {
                    $rows[] = [ $loc['label'], $slug, 'enamel-reviews-' . $slug . '.json' ];
                }
                foreach ( $rows as $row ) {
                    list( $label, $slug_disp, $filename ) = $row;
                    $file_path = ERF_FEED_DIR . $filename;
                    $feed_url  = content_url( 'uploads/enamel/' . $filename );
                    $exists    = file_exists( $file_path );
                    echo '<tr>';
                    echo '<td>' . esc_html( $label ) . '</td>';
                    echo '<td><code>' . wp_kses_post( $slug_disp ) . '</code></td>';
                    echo '<td><code>' . esc_html( $feed_url ) . '</code></td>';
                    echo '<td>' . ( $exists ? '✅ yes' : '❌ no — run Fetch Now' ) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>

        <hr>

        <h2 class="title">7. Cloudways Cron Note</h2>
        <p>WP-Cron only fires when someone loads a page. For a guaranteed daily run, add this in <strong>Cloudways → Application → Cron Job Manager</strong>:</p>
        <pre style="background:#f0f0f1;padding:12px;max-width:780px;overflow:auto;">0 3 * * * wget -q -O /dev/null "<?php echo esc_url( site_url( '/?erf_cron_key=' . wp_hash( 'erf_cron' ) ) ); ?>" &gt;/dev/null 2&gt;&amp;1</pre>
        <p>Then add <code>define( 'DISABLE_WP_CRON', true );</code> to <code>wp-config.php</code>.</p>
    </div>
    <?php
}

/** Renders the 5-field copy editor for a single context (generic or a location). */
function erf_render_copy_fields( $name_prefix, $values ) {
    $headline    = isset( $values['headline'] )    ? $values['headline']    : '';
    $lede        = isset( $values['lede'] )        ? $values['lede']        : '';
    $booking     = isset( $values['booking_url'] ) ? $values['booking_url'] : '';
    $google      = isset( $values['google_url'] )  ? $values['google_url']  : '';
    $button      = isset( $values['button_text'] ) ? $values['button_text'] : '';
    ?>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><label>Headline</label></th>
            <td>
                <input type="text" name="<?php echo esc_attr( $name_prefix ); ?>[headline]" value="<?php echo esc_attr( $headline ); ?>" class="large-text">
                <p class="description">Limited HTML allowed (e.g. <code>&lt;em&gt;</code>). Becomes the <code>&lt;h2&gt;</code>.</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label>Lede</label></th>
            <td>
                <textarea name="<?php echo esc_attr( $name_prefix ); ?>[lede]" rows="2" class="large-text"><?php echo esc_textarea( $lede ); ?></textarea>
                <p class="description">The body paragraph below the headline.</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label>Book CTA href</label></th>
            <td><input type="url" name="<?php echo esc_attr( $name_prefix ); ?>[booking_url]" value="<?php echo esc_attr( $booking ); ?>" class="regular-text" placeholder="https://..."></td>
        </tr>
        <tr>
            <th scope="row"><label>Book CTA button text</label></th>
            <td><input type="text" name="<?php echo esc_attr( $name_prefix ); ?>[button_text]" value="<?php echo esc_attr( $button ); ?>" class="regular-text" placeholder="Book your visit"></td>
        </tr>
        <tr>
            <th scope="row"><label>Google listing href</label></th>
            <td>
                <input type="url" name="<?php echo esc_attr( $name_prefix ); ?>[google_url]" value="<?php echo esc_attr( $google ); ?>" class="regular-text" placeholder="https://...">
                <p class="description">The "Read all on Google" button. Use the direct Google Maps listing URL when possible.</p>
            </td>
        </tr>
    </table>
    <?php
}
