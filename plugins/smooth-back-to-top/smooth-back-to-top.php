<?php
/**
 * Plugin Name: Smooth Back to Top
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: A lightweight, accessible floating button that fades in on scroll and smoothly returns to the top of the page.
 * Version:     1.1.0
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
const SBTT_VERSION = '1.1.0';

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
 * inline blobs and the browser can cache them. The script is loaded in the
 * footer so it never blocks rendering.
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
		plugins_url( 'assets/sbtt.js', __FILE__ ),
		array(),
		SBTT_VERSION,
		true // In the footer.
	);
}
add_action( 'wp_enqueue_scripts', 'sbtt_enqueue_assets' );

/**
 * Output the button markup just before the closing body tag.
 *
 * The control is a native <button>, so it is keyboard-focusable and operable by
 * default. It carries a descriptive aria-label and matching title, and ships
 * with the `hidden` attribute so it never flashes before the script reveals it.
 * The chevron icon is decorative and hidden from assistive technology.
 *
 * @return void
 */
function sbtt_render_button() {
	if ( ! sbtt_is_active() ) {
		return;
	}

	$label = esc_attr__( 'Scroll back to top', 'smooth-back-to-top' );

	printf(
		'<button type="button" class="sbtt-button" aria-label="%1$s" title="%1$s" hidden>' .
		'<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">' .
		'<polyline points="6 14 12 8 18 14"></polyline></svg>' .
		'</button>',
		$label // Already escaped via esc_attr__() above.
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
