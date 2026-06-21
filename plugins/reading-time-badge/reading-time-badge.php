<?php
/**
 * Plugin Name: Reading Time Badge
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Adds a tasteful "X min read" badge above the content of single posts. Zero configuration.
 * Version:     1.0.0
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

const RTB_WORDS_PER_MINUTE = 220;

/**
 * Estimate reading time in minutes for a block of content.
 *
 * @param string $content Post content (may contain HTML).
 * @return int Minutes, minimum 1.
 */
function rtb_estimate_minutes( $content ) {
	$text  = wp_strip_all_tags( (string) $content );
	$words = preg_split( '/\s+/', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
	$count = is_array( $words ) ? count( $words ) : 0;
	$wpm   = (int) apply_filters( 'rtb_words_per_minute', RTB_WORDS_PER_MINUTE );
	$wpm   = $wpm > 0 ? $wpm : RTB_WORDS_PER_MINUTE;

	return max( 1, (int) ceil( $count / $wpm ) );
}

/**
 * Prepend the reading-time badge to single post content.
 *
 * @param string $content The post content.
 * @return string
 */
function rtb_prepend_badge( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$minutes = rtb_estimate_minutes( $content );
	$label   = sprintf(
		/* translators: %d: number of minutes */
		_n( '%d min read', '%d min read', $minutes, 'reading-time-badge' ),
		$minutes
	);

	$badge = sprintf(
		'<p class="rtb-badge"><span class="rtb-badge__dot" aria-hidden="true"></span>%s</p>',
		esc_html( $label )
	);

	return $badge . $content;
}
add_filter( 'the_content', 'rtb_prepend_badge', 20 );

/**
 * Inline styles for the badge (no external file, no enqueue overhead).
 */
function rtb_styles() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	$css = '.rtb-badge{display:inline-flex;align-items:center;gap:.5em;margin:0 0 1.4em;'
		. 'font:600 .8rem/1.4 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;'
		. 'letter-spacing:.04em;text-transform:uppercase;color:#6b7280;}'
		. '.rtb-badge__dot{width:.5em;height:.5em;border-radius:50%;background:currentColor;opacity:.6;}';
	wp_register_style( 'rtb-inline', false );
	wp_enqueue_style( 'rtb-inline' );
	wp_add_inline_style( 'rtb-inline', $css );
}
add_action( 'wp_enqueue_scripts', 'rtb_styles' );
