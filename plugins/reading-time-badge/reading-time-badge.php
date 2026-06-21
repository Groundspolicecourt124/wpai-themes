<?php
/**
 * Plugin Name: Reading Time Badge
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Adds a tasteful "X min read" badge with a small clock glyph above the content of single posts. Theme-adaptive (light & dark), accessible, zero configuration.
 * Category:   Content & Reading
 * Version:     1.4.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: reading-time-badge
 *
 * @package ReadingTimeBadge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const RTB_VERSION = '1.4.0';

/**
 * Default reading speed in words per minute.
 *
 * Tunable via the `rtb_words_per_minute` filter.
 */
const RTB_WORDS_PER_MINUTE = 220;

/**
 * Resolve the reading speed (words per minute), honoring the filter.
 *
 * Falls back to the default if a filter returns a non-positive value, so a
 * misbehaving filter can never cause a divide-by-zero.
 *
 * @return int Words per minute, always >= 1.
 */
function rtb_words_per_minute() {
	$wpm = (int) apply_filters( 'rtb_words_per_minute', RTB_WORDS_PER_MINUTE );

	return $wpm > 0 ? $wpm : RTB_WORDS_PER_MINUTE;
}

/**
 * Estimate reading time in minutes for a block of content.
 *
 * Strips shortcodes and HTML so markup, embeds, and tag soup do not inflate the
 * word count, then counts whitespace-delimited words.
 *
 * @param string $content Post content (may contain HTML and shortcodes).
 * @return int Minutes, minimum 1.
 */
function rtb_estimate_minutes( $content ) {
	$text  = wp_strip_all_tags( strip_shortcodes( (string) $content ) );
	$words = preg_split( '/\s+/', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
	$count = is_array( $words ) ? count( $words ) : 0;
	$wpm   = rtb_words_per_minute();

	$minutes = (int) max( 1, (int) ceil( $count / $wpm ) );

	/**
	 * Filter the final, computed reading time in whole minutes.
	 *
	 * @param int    $minutes Estimated minutes (>= 1).
	 * @param int    $count   Number of words counted.
	 * @param string $content The raw post content that was measured.
	 */
	return (int) max( 1, (int) apply_filters( 'rtb_estimate_minutes', $minutes, $count, $content ) );
}

/**
 * Build the badge markup for a given reading time.
 *
 * The clock glyph is an inline, decorative SVG (aria-hidden) so it never adds an
 * HTTP request and is invisible to assistive technology. Every dynamic value is
 * escaped on output.
 *
 * @param int $minutes Estimated reading time in minutes.
 * @return string Safe HTML for the badge.
 */
function rtb_get_badge_html( $minutes ) {
	$minutes = (int) max( 1, $minutes );

	$label = sprintf(
		/* translators: %d: estimated reading time in minutes. */
		_n( '%d min read', '%d min read', $minutes, 'reading-time-badge' ),
		$minutes
	);

	// Decorative clock icon. Uses currentColor so it inherits the badge color
	// on both light and dark themes. Marked aria-hidden / focusable="false".
	//
	// The two clock hands live in their own <g> so the CSS reveal can spin them
	// once (a single gentle sweep) around the dial center (12,12) without
	// touching the face. transform-box: fill-box keeps the rotation centered.
	$icon = '<svg class="rtb-badge__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" '
		. 'stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" '
		. 'aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"></circle>'
		. '<g class="rtb-badge__hands"><polyline points="12 7 12 12 15.5 14"></polyline></g></svg>';

	// data-rtb-badge is the JS hook; the badge is fully visible without it, and
	// only the enqueued motion script (gated on prefers-reduced-motion) adds the
	// hidden-then-reveal behavior.
	return sprintf(
		'<p class="rtb-badge" data-rtb-badge>%1$s<span class="rtb-badge__text">%2$s</span></p>',
		$icon, // Static, hand-authored markup — safe.
		esc_html( $label )
	);
}

/**
 * Whether the active theme opts in to native WPAI companion placement.
 *
 * When a theme declares `add_theme_support( 'wpai-companions' )`, it promises to
 * fire `wpai_entry_top` / `wpai_entry_bottom` action hooks around the article
 * body on single posts. In that mode the badge renders on `wpai_entry_top`
 * (full article width, outside the prose column) instead of being prepended via
 * `the_content`. Without theme support we keep the classic `the_content`
 * prepend so the plugin still works on any theme.
 *
 * @return bool True when the theme supports native companion hooks.
 */
function rtb_theme_supports_companions() {
	return (bool) current_theme_supports( 'wpai-companions' );
}

/**
 * Prepend the reading-time badge to single post content.
 *
 * Guards on the main query in the loop for single posts only, so the badge is
 * never injected into excerpts, archives, feeds, REST responses, or secondary
 * queries.
 *
 * When the active theme supports `wpai-companions`, this becomes a no-op: the
 * badge is rendered on the `wpai_entry_top` action hook instead (see
 * rtb_render_badge_on_entry_top) so it never double-renders.
 *
 * @param string $content The post content.
 * @return string
 */
function rtb_prepend_badge( $content ) {
	if ( is_admin() || is_feed() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	// Native companion placement owns the badge in supporting themes; do not
	// also prepend here, or the badge would render twice.
	if ( rtb_theme_supports_companions() ) {
		return $content;
	}

	$minutes = rtb_estimate_minutes( $content );

	return rtb_get_badge_html( $minutes ) . $content;
}
add_filter( 'the_content', 'rtb_prepend_badge', 20 );

/**
 * Render the reading-time badge on the theme's `wpai_entry_top` hook.
 *
 * Only active when the theme supports `wpai-companions`. The hook fires right
 * after the entry header and immediately before `the_content()`, outside the
 * `.entry-content` wrapper, so the badge can sit at full article width. The
 * reading time is computed from the current post's content because, unlike the
 * `the_content` filter, this hook does not receive the content as an argument.
 *
 * Guards match the `the_content` path (single posts, main query, in the loop)
 * so the badge appears in exactly the same contexts as the classic placement.
 *
 * @return void
 */
function rtb_render_badge_on_entry_top() {
	if ( is_admin() || is_feed() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return;
	}

	if ( ! rtb_theme_supports_companions() ) {
		return;
	}

	$post = get_post();

	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$minutes = rtb_estimate_minutes( $post->post_content );

	// rtb_get_badge_html() escapes every dynamic value internally.
	echo rtb_get_badge_html( $minutes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wpai_entry_top', 'rtb_render_badge_on_entry_top', 5 );

/**
 * Whether the current request is a single post where the badge can appear.
 *
 * Centralizes the guard shared by the asset enqueues and the no-JS head hook so
 * they can never drift out of sync.
 *
 * @return bool
 */
function rtb_is_badge_context() {
	return ! is_admin() && ! is_feed() && is_singular( 'post' );
}

/**
 * Register and enqueue the badge stylesheet and motion script.
 *
 * Both ship as real, readable asset files (assets/reading-time-badge.css and
 * assets/js/motion.js) rather than inline blobs, and load only on single posts
 * where the badge can appear. Versioned for cache-busting.
 *
 * The motion script is enqueued in the footer and made non-blocking (defer):
 * it is pure progressive enhancement. Without it the badge is fully visible;
 * with it the badge fades/slides in on scroll and the clock hands sweep once.
 *
 * @return void
 */
function rtb_enqueue_assets() {
	if ( ! rtb_is_badge_context() ) {
		return;
	}

	wp_enqueue_style(
		'reading-time-badge',
		plugins_url( 'assets/reading-time-badge.css', __FILE__ ),
		array(),
		RTB_VERSION
	);

	wp_enqueue_script(
		'reading-time-badge-motion',
		plugins_url( 'assets/js/motion.js', __FILE__ ),
		array(),
		RTB_VERSION,
		true // Load in the footer.
	);
}
add_action( 'wp_enqueue_scripts', 'rtb_enqueue_assets' );

/**
 * Add a `defer` attribute to the motion script tag.
 *
 * Keeps the enhancement non-render-blocking. Hooks `script_loader_tag` and is a
 * no-op for every handle except this plugin's motion script.
 *
 * @param string $tag    The full <script> tag.
 * @param string $handle The script's registered handle.
 * @return string
 */
function rtb_defer_motion_script( $tag, $handle ) {
	if ( 'reading-time-badge-motion' !== $handle ) {
		return $tag;
	}

	if ( false === strpos( $tag, ' defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'rtb_defer_motion_script', 10, 2 );

/**
 * Flip the document to its "JS available" state as early as possible.
 *
 * Prints a tiny, self-contained snippet in the <head> that swaps the `no-js`
 * class on <html> for `js`. This is the only inline script; it contains no
 * animation logic (all of that lives in the enqueued motion.js). Doing it in
 * <head> means the CSS that hides the badge before reveal only ever applies
 * when JS is present, so a no-JS visitor — or one whose script fails — always
 * sees the badge fully rendered (true progressive enhancement, zero flash).
 *
 * The output is a fixed string literal, so there is nothing dynamic to escape.
 *
 * @return void
 */
function rtb_print_js_class_hook() {
	if ( ! rtb_is_badge_context() ) {
		return;
	}

	echo "<script>document.documentElement.classList.remove('rtb-no-js');document.documentElement.classList.add('rtb-js');</script>\n";
}
add_action( 'wp_head', 'rtb_print_js_class_hook', 1 );

/**
 * Seed the <html> element with a `rtb-no-js` class.
 *
 * The reveal CSS is scoped to `.rtb-js`, so this default guarantees no-JS
 * visitors keep the badge visible. The early head snippet promotes it to
 * `rtb-js` the instant scripting is confirmed available.
 *
 * NOTE: the `language_attributes` filter receives a space-separated list of
 * HTML *attributes* (e.g. `lang="en-US" dir="rtl"`), not a class list, so we
 * must append a complete `class="rtb-no-js"` attribute — appending a bare token
 * would inject an invalid attribute into the `<html>` tag and never become a
 * class. WordPress core does not emit a `class` attribute here, so this does
 * not duplicate one.
 *
 * @param string $attributes Space-separated list of `<html>` attributes from
 *                           `language_attributes`.
 * @return string
 */
function rtb_html_no_js_class( $attributes ) {
	if ( ! rtb_is_badge_context() ) {
		return $attributes;
	}

	$attributes = trim( $attributes );
	$class_attr = 'class="rtb-no-js"';

	return '' === $attributes ? $class_attr : $attributes . ' ' . $class_attr;
}
add_filter( 'language_attributes', 'rtb_html_no_js_class' );

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function rtb_load_textdomain() {
	load_plugin_textdomain( 'reading-time-badge', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'rtb_load_textdomain' );
