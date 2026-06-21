<?php
/**
 * Plugin Name: Smooth Back to Top
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: A delightful, accessible floating button wrapped in a live scroll-progress ring that fades in on scroll and smoothly returns to the top of the page.
 * Version:     1.3.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smooth-back-to-top
 *
 * @package SmoothBackToTop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const SBTT_VERSION = '1.3.0';

/**
 * Whether the button should load for the current request.
 *
 * Skipped in the admin, on feeds, and inside embeds, where a floating
 * scroll-to-top control serves no purpose. Filterable so themes can opt
 * specific views in or out.
 *
 * @return bool True if the button should be enqueued and rendered.
 */
function sbtt_is_active() {
	$active = ! ( is_admin() || is_feed() || is_embed() );

	/**
	 * Filter whether the Smooth Back to Top button loads on this request.
	 *
	 * @param bool $active Whether the button is active for the current view.
	 */
	return (bool) apply_filters( 'sbtt_is_active', $active );
}

/**
 * Register and enqueue the front-end stylesheet and script.
 *
 * Both the CSS and JS ship as real, versioned files in /assets so there are no
 * inline blobs and the browser can cache them. The motion script is loaded in
 * the footer and deferred (see sbtt_defer_script) so it never blocks rendering.
 *
 * @return void
 */
function sbtt_enqueue_assets() {
	if ( ! sbtt_is_active() ) {
		return;
	}

	wp_enqueue_style(
		'smooth-back-to-top',
		plugins_url( 'assets/sbtt.css', __FILE__ ),
		array(),
		SBTT_VERSION
	);

	wp_enqueue_script(
		'smooth-back-to-top',
		plugins_url( 'assets/js/motion.js', __FILE__ ),
		array(),
		SBTT_VERSION,
		true // In the footer.
	);
}
add_action( 'wp_enqueue_scripts', 'sbtt_enqueue_assets' );

/**
 * Add the `defer` attribute to the plugin's footer script tag.
 *
 * Keeps support back to WordPress 5.0 (the `strategy` enqueue argument is only
 * available from 6.3). The script enhances progressively, so deferring it is
 * safe: the button is fully usable before the script runs.
 *
 * @param string $tag    The full <script> tag for the enqueued handle.
 * @param string $handle The script's registered handle.
 * @return string The (possibly) modified script tag.
 */
function sbtt_defer_script( $tag, $handle ) {
	if ( 'smooth-back-to-top' !== $handle || false !== strpos( $tag, ' defer' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'sbtt_defer_script', 10, 2 );

/**
 * Output the button markup just before the closing body tag.
 *
 * The control is a native <button>, so it is keyboard-focusable and operable by
 * default. It carries a descriptive aria-label and matching title. It renders
 * visible (progressive enhancement): without JS it is a working scroll-to-top
 * control, and motion.js takes over visibility once it loads.
 *
 * The icon stacks two decorative SVGs: a circular scroll-progress ring (a track
 * plus a progress circle whose stroke-dashoffset JS drives) and an up-arrow
 * glyph centered inside it. Both are hidden from assistive technology.
 *
 * @return void
 */
function sbtt_render_button() {
	if ( ! sbtt_is_active() ) {
		return;
	}

	$label = esc_attr__( 'Scroll back to top', 'smooth-back-to-top' );

	$icon =
		'<span class="sbtt-icon" aria-hidden="true">' .
			'<svg class="sbtt-ring" viewBox="0 0 44 44" width="44" height="44" focusable="false" aria-hidden="true">' .
				'<circle class="sbtt-ring-track" cx="22" cy="22" r="20"></circle>' .
				'<circle class="sbtt-ring-progress" cx="22" cy="22" r="20"></circle>' .
			'</svg>' .
			'<span class="sbtt-glyph">' .
				'<svg class="sbtt-arrow" viewBox="0 0 24 24" width="18" height="18" focusable="false" aria-hidden="true">' .
					'<polyline points="6 14 12 8 18 14"></polyline>' .
					'<line x1="12" y1="8" x2="12" y2="17"></line>' .
				'</svg>' .
			'</span>' .
		'</span>';

	printf(
		'<button type="button" class="sbtt-button" aria-label="%1$s" title="%1$s">%2$s</button>',
		$label, // Already escaped via esc_attr__() above.
		$icon   // Static, trusted markup assembled above.
	);
}
add_action( 'wp_footer', 'sbtt_render_button', 100 );

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function sbtt_load_textdomain() {
	load_plugin_textdomain( 'smooth-back-to-top', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'sbtt_load_textdomain' );
