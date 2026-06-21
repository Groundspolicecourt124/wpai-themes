<?php
/**
 * Plugin Name: Smooth Back to Top
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: A lightweight, accessible floating button that appears on scroll and smoothly returns to the top.
 * Version:     1.0.0
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
const SBTT_VERSION = '1.0.0';

/**
 * Register and enqueue the front-end script and inline styles.
 *
 * The JavaScript is shipped as a real file (assets/sbtt.js) and loaded in the
 * footer so it never blocks rendering. The CSS is attached as an inline style
 * on a registered handle to avoid an extra HTTP request.
 *
 * @return void
 */
function sbtt_enqueue_assets() {
	// Don't load in the admin, on feeds, or in embeds where it serves no purpose.
	if ( is_admin() || is_feed() || is_embed() ) {
		return;
	}

	wp_enqueue_script(
		'smooth-back-to-top',
		plugins_url( 'assets/sbtt.js', __FILE__ ),
		array(),
		SBTT_VERSION,
		true // In the footer.
	);

	// Register a style handle (no external file) to hang the inline CSS on.
	wp_register_style( 'smooth-back-to-top', false, array(), SBTT_VERSION );
	wp_enqueue_style( 'smooth-back-to-top' );
	wp_add_inline_style( 'smooth-back-to-top', sbtt_inline_css() );
}
add_action( 'wp_enqueue_scripts', 'sbtt_enqueue_assets' );

/**
 * Build the inline CSS for the button.
 *
 * Honors prefers-reduced-motion by disabling the fade transition for users who
 * have requested reduced motion at the OS level.
 *
 * @return string The CSS rules.
 */
function sbtt_inline_css() {
	$css = <<<CSS
.sbtt-button{
	position:fixed;
	right:1.25rem;
	bottom:1.25rem;
	z-index:2147483000;
	display:inline-flex;
	align-items:center;
	justify-content:center;
	width:3rem;
	height:3rem;
	margin:0;
	padding:0;
	border:0;
	border-radius:50%;
	background:#111827;
	color:#fff;
	cursor:pointer;
	opacity:0;
	visibility:hidden;
	transform:translateY(0.5rem);
	transition:opacity .25s ease,transform .25s ease,visibility .25s ease;
	box-shadow:0 4px 14px rgba(0,0,0,.25);
	-webkit-appearance:none;
	appearance:none;
}
.sbtt-button.sbtt-visible{
	opacity:1;
	visibility:visible;
	transform:translateY(0);
}
.sbtt-button:hover{
	background:#000;
}
.sbtt-button:focus-visible{
	outline:3px solid #2563eb;
	outline-offset:2px;
}
.sbtt-button svg{
	width:1.25rem;
	height:1.25rem;
	display:block;
	fill:none;
	stroke:currentColor;
	stroke-width:2.5;
	stroke-linecap:round;
	stroke-linejoin:round;
}
@media (prefers-reduced-motion: reduce){
	.sbtt-button{
		transition:opacity .01ms linear,visibility .01ms linear;
		transform:none;
	}
	.sbtt-button.sbtt-visible{
		transform:none;
	}
}
CSS;

	return $css;
}

/**
 * Output the button markup just before the closing body tag.
 *
 * The button carries an accessible label and is keyboard-focusable by virtue of
 * being a native <button> element. The chevron icon is decorative and hidden
 * from assistive technology.
 *
 * @return void
 */
function sbtt_render_button() {
	if ( is_admin() || is_feed() || is_embed() ) {
		return;
	}

	$label = esc_attr__( 'Scroll back to top', 'smooth-back-to-top' );

	printf(
		'<button type="button" class="sbtt-button" aria-label="%1$s" title="%1$s" hidden><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><polyline points="6 14 12 8 18 14"></polyline></svg></button>',
		esc_attr( $label )
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
