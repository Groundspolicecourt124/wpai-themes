<?php
/**
 * Plugin Name: Reading Time Badge
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Adds a tasteful "X min read" badge with a small clock glyph above the content of single posts. Theme-adaptive (light & dark), accessible, zero configuration.
 * Version:     1.1.0
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
const RTB_VERSION = '1.1.0';

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
	$icon = '<svg class="rtb-badge__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" '
		. 'stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" '
		. 'aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"></circle>'
		. '<polyline points="12 7 12 12 15.5 14"></polyline></svg>';

	return sprintf(
		'<p class="rtb-badge">%1$s<span class="rtb-badge__text">%2$s</span></p>',
		$icon, // Static, hand-authored markup — safe.
		esc_html( $label )
	);
}

/**
 * Prepend the reading-time badge to single post content.
 *
 * Guards on the main query in the loop for single posts only, so the badge is
 * never injected into excerpts, archives, feeds, REST responses, or secondary
 * queries.
 *
 * @param string $content The post content.
 * @return string
 */
function rtb_prepend_badge( $content ) {
	if ( is_admin() || is_feed() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$minutes = rtb_estimate_minutes( $content );

	return rtb_get_badge_html( $minutes ) . $content;
}
add_filter( 'the_content', 'rtb_prepend_badge', 20 );

/**
 * Register and enqueue the badge stylesheet.
 *
 * The CSS ships as a real, readable asset file (assets/reading-time-badge.css)
 * rather than an inline blob, and is only loaded on single posts where the badge
 * can appear. Versioned for cache-busting.
 *
 * @return void
 */
function rtb_enqueue_styles() {
	if ( is_admin() || is_feed() || ! is_singular( 'post' ) ) {
		return;
	}

	wp_enqueue_style(
		'reading-time-badge',
		plugins_url( 'assets/reading-time-badge.css', __FILE__ ),
		array(),
		RTB_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'rtb_enqueue_styles' );

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function rtb_load_textdomain() {
	load_plugin_textdomain( 'reading-time-badge', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'rtb_load_textdomain' );
