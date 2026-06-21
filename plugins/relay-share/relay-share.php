<?php
/**
 * Plugin Name: Relay — Social Share
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Tasteful, privacy-first social share buttons after single-post content. Copy link (with a "Copied!" confirmation), X, Bluesky, LinkedIn, Mastodon, and Email — plain share URLs and inline SVG icons, no third-party tracking scripts. Theme-adaptive, accessible, zero configuration.
 * Category:   Engagement
 * Version:     1.0.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: relay-share
 *
 * @package RelayShare
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const RELAY_VERSION = '1.0.0';

/**
 * The ordered set of share networks Relay can render.
 *
 * Each entry is a key the renderer understands. The default order is deliberate:
 * the universal Copy and Email actions bookend a tidy run of social networks.
 * Tunable via the `relay_networks` filter (return a subset / reordered list).
 *
 * @return string[] Network keys, e.g. array( 'copy', 'x', 'bluesky', … ).
 */
function relay_networks() {
	$default = array( 'copy', 'x', 'bluesky', 'linkedin', 'mastodon', 'email' );

	$networks = apply_filters( 'relay_networks', $default );

	if ( ! is_array( $networks ) || empty( $networks ) ) {
		return $default;
	}

	// Keep only keys we actually know how to render, preserving caller order.
	$known = array( 'copy', 'x', 'bluesky', 'linkedin', 'mastodon', 'email' );

	$networks = array_values(
		array_filter(
			array_map( 'strval', $networks ),
			static function ( $key ) use ( $known ) {
				return in_array( $key, $known, true );
			}
		)
	);

	return empty( $networks ) ? $default : array_values( array_unique( $networks ) );
}

/**
 * Inline SVG icon for a network.
 *
 * Icons are hand-authored, single-path-ish glyphs drawn with `currentColor` so
 * they tint themselves from the surrounding text — no icon font, no external
 * request, no color hardcoding. Each is marked aria-hidden because the visible
 * label (or aria-label) already names the action for assistive technology.
 *
 * @param string $key Network key.
 * @return string Safe, trusted SVG markup (hand-authored constants).
 */
function relay_icon( $key ) {
	$open  = '<svg class="relay__icon" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">';
	$close = '</svg>';

	$icons = array(
		// Link / chain glyph for "Copy link".
		'copy'     => '<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 13a5 5 0 0 0 7.07 0l3-3A5 5 0 0 0 12 3l-1.5 1.5"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 11a5 5 0 0 0-7.07 0l-3 3A5 5 0 0 0 12 21l1.5-1.5"/>',
		// Confirmation checkmark, swapped in by JS after a successful copy.
		'check'    => '<path fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/>',
		// X (formerly Twitter) wordmark glyph.
		'x'        => '<path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.657l-5.214-6.817-5.966 6.817H1.68l7.73-8.835L1.254 2.25h6.826l4.713 6.231 5.451-6.231Zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77Z"/>',
		// Bluesky butterfly.
		'bluesky'  => '<path fill="currentColor" d="M6.34 4.06C8.7 5.83 11.24 9.42 12 11.35c.76-1.93 3.3-5.52 5.66-7.29 1.7-1.28 4.46-2.27 4.46.88 0 .63-.36 5.29-.57 6.05-.74 2.62-3.41 3.29-5.79 2.89 4.16.71 5.22 3.06 2.94 5.41-4.34 4.46-6.24-1.12-6.72-2.55-.09-.26-.13-.38-.13-.27 0-.11-.04.01-.13.27-.49 1.43-2.38 7.01-6.72 2.55-2.29-2.35-1.22-4.7 2.94-5.41-2.38.4-5.06-.27-5.79-2.89C1.9 10.4 1.54 5.74 1.54 5.11c0-3.15 2.76-2.16 4.46-.88l.34.27Z"/>',
		// LinkedIn "in" mark.
		'linkedin' => '<path fill="currentColor" d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.07 2.07 0 1 1 0-4.13 2.07 2.07 0 0 1 0 4.13ZM7.12 20.45H3.56V9h3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.22.79 24 1.77 24h20.45c.98 0 1.78-.78 1.78-1.73V1.73C24 .77 23.2 0 22.22 0Z"/>',
		// Mastodon glyph (simplified).
		'mastodon' => '<path fill="currentColor" d="M21.33 5.36C21 2.94 18.88 1.03 16.37.66 15.94.6 14.34.36 10.63.36h-.03C6.89.36 6.1.6 5.66.66 3.22 1.02 1 2.74.46 5.19.2 6.4.17 7.74.22 8.97c.07 1.76.08 3.52.24 5.27.11 1.16.31 2.32.6 3.45.55 2.09 2.62 3.83 4.66 4.54 2.18.74 4.53.87 6.78.36.25-.06.49-.13.73-.21.54-.17 1.17-.36 1.63-.7a.05.05 0 0 0 .02-.04v-1.7a.05.05 0 0 0-.06-.05c-1.43.34-2.9.51-4.37.51-2.54 0-3.22-1.2-3.42-1.7a5.3 5.3 0 0 1-.3-1.35.04.04 0 0 1 .05-.05c1.4.34 2.85.51 4.3.51.35 0 .7 0 1.05-.01 1.46-.04 3-.12 4.44-.4l.1-.02c2.27-.44 4.43-1.81 4.65-5.27.01-.14.03-1.43.03-1.57 0-.48.15-3.4-.02-5.19ZM18.4 14.2h-2.43V8.27c0-1.25-.52-1.88-1.59-1.88-1.17 0-1.76.76-1.76 2.26v3.27h-2.42V8.65c0-1.5-.59-2.26-1.76-2.26-1.06 0-1.59.63-1.59 1.88v5.93H4.4V8.09c0-1.25.32-2.24.96-2.97.66-.73 1.52-1.1 2.6-1.1 1.25 0 2.19.48 2.81 1.43l.6 1.02.61-1.02c.62-.95 1.56-1.43 2.81-1.43 1.07 0 1.94.37 2.6 1.1.64.73.96 1.72.96 2.97v6.11Z"/>',
		// Envelope for Email.
		'email'    => '<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="m3.5 6.5 8.5 6 8.5-6"/>',
	);

	$path = isset( $icons[ $key ] ) ? $icons[ $key ] : '';

	return '' === $path ? '' : $open . $path . $close;
}

/**
 * The human-readable label for a network's action.
 *
 * @param string $key Network key.
 * @return string Translated label.
 */
function relay_label( $key ) {
	switch ( $key ) {
		case 'copy':
			return __( 'Copy link', 'relay-share' );
		case 'x':
			return __( 'Share on X', 'relay-share' );
		case 'bluesky':
			return __( 'Share on Bluesky', 'relay-share' );
		case 'linkedin':
			return __( 'Share on LinkedIn', 'relay-share' );
		case 'mastodon':
			return __( 'Share on Mastodon', 'relay-share' );
		case 'email':
			return __( 'Share by email', 'relay-share' );
		default:
			return '';
	}
}

/**
 * Build the share URL for a network from the post's URL and title.
 *
 * Every network here is reached with a plain, well-known web-intent URL — no
 * SDKs, no widgets, no tracking pixels. Mastodon has no single host, so its
 * button is JS-driven (it prompts for the visitor's instance); we return '' for
 * it here and the renderer flags it as a button instead of a link.
 *
 * Values are passed raw to add_query_arg(), which URL-encodes them itself; pre-
 * encoding here would double-encode (a space would become %2520, not %20).
 *
 * @param string $key   Network key.
 * @param string $url   The canonical post permalink.
 * @param string $title The post title.
 * @return string A share URL, or '' for actions handled in JS (copy, mastodon).
 */
function relay_share_url( $key, $url, $title ) {
	switch ( $key ) {
		case 'x':
			return add_query_arg(
				array(
					'text' => $title,
					'url'  => $url,
				),
				'https://twitter.com/intent/tweet'
			);

		case 'bluesky':
			// Bluesky's composer takes a single `text` param; include the URL.
			return add_query_arg(
				array( 'text' => $title . ' ' . $url ),
				'https://bsky.app/intent/compose'
			);

		case 'linkedin':
			return add_query_arg(
				array( 'url' => $url ),
				'https://www.linkedin.com/sharing/share-offsite/'
			);

		case 'email':
			return add_query_arg(
				array(
					'subject' => $title,
					/* translators: %s: the post URL, appended to the email body. */
					'body'    => sprintf( __( 'I thought you might like this: %s', 'relay-share' ), $url ),
				),
				'mailto:'
			);

		case 'copy':
		case 'mastodon':
		default:
			// Handled client-side (copy to clipboard / prompt for instance).
			return '';
	}
}

/**
 * Whether activating a network opens a sharer window vs. a same-tab action.
 *
 * Web-intent links open in a new tab; the mailto link and the JS-driven actions
 * (copy, mastodon) do not.
 *
 * @param string $key Network key.
 * @return bool
 */
function relay_opens_window( $key ) {
	return in_array( $key, array( 'x', 'bluesky', 'linkedin' ), true );
}

/**
 * Render a single share control.
 *
 * Networks with a real share URL render as an <a>; the Copy and Mastodon
 * actions, which run in the browser, render as a <button> so they are not
 * crawlable dead links and are correctly announced as actions. Every dynamic
 * value is escaped on output.
 *
 * @param string $key   Network key.
 * @param string $url   The canonical post permalink.
 * @param string $title The post title.
 * @return string Safe control HTML, or '' for an unknown key.
 */
function relay_render_control( $key, $url, $title ) {
	$label = relay_label( $key );
	$icon  = relay_icon( $key );

	if ( '' === $label || '' === $icon ) {
		return '';
	}

	$icon_label = sprintf(
		'%1$s<span class="relay__label">%2$s</span>',
		$icon, // Hand-authored, trusted SVG.
		esc_html( $label )
	);

	// Copy and Mastodon are client-side actions: render as real buttons.
	if ( 'copy' === $key || 'mastodon' === $key ) {
		// The copy button carries a live confirmation region. It starts empty so
		// that when JS writes "Copied!" into it the change is genuinely announced
		// by the aria-live region; a pre-filled, identical string is not reliably
		// re-announced by screen readers. The region is overlaid
		// (position: absolute), so leaving it empty causes no layout shift.
		$extra = '';
		if ( 'copy' === $key ) {
			$extra = '<span class="relay__status" role="status" aria-live="polite" data-relay-status></span>';
		}

		return sprintf(
			'<li class="relay__item relay__item--%1$s">' .
				'<button type="button" class="relay__btn" data-relay-action="%1$s" data-relay-url="%2$s" data-relay-title="%3$s" aria-label="%4$s">' .
					'%5$s%6$s' .
				'</button>' .
			'</li>',
			esc_attr( $key ),
			esc_url( $url ),
			esc_attr( $title ),
			esc_attr( $label ),
			$icon_label, // Built from escaped/trusted parts above.
			$extra       // Built from escaped/trusted parts above.
		);
	}

	$share_url = relay_share_url( $key, $url, $title );
	if ( '' === $share_url ) {
		return '';
	}

	$target = relay_opens_window( $key )
		? ' target="_blank" rel="noopener noreferrer"'
		: '';

	return sprintf(
		'<li class="relay__item relay__item--%1$s">' .
			'<a class="relay__btn" href="%2$s"%3$s aria-label="%4$s" data-relay-network="%1$s">' .
				'%5$s' .
			'</a>' .
		'</li>',
		esc_attr( $key ),
		esc_url( $share_url ),
		$target, // Static, trusted attribute string.
		esc_attr( $label ),
		$icon_label // Built from escaped/trusted parts above.
	);
}

/**
 * Build the full share bar markup for a post.
 *
 * Returns an empty string when there is nothing shareable (no permalink, or a
 * filter emptied the network list), so callers can safely echo the result.
 * Every dynamic value is escaped on output; the static SVGs and class hooks are
 * hand-authored.
 *
 * @param int $post_id The post to render share controls for.
 * @return string Safe section HTML, or '' when nothing to show.
 */
function relay_get_section_html( $post_id ) {
	$post_id = (int) $post_id;

	$url = get_permalink( $post_id );
	if ( ! $url ) {
		return '';
	}

	$title = get_the_title( $post_id );
	$title = '' !== $title ? $title : get_bloginfo( 'name' );

	$controls = '';
	foreach ( relay_networks() as $key ) {
		$controls .= relay_render_control( $key, $url, $title );
	}

	if ( '' === $controls ) {
		return '';
	}

	$heading    = apply_filters( 'relay_heading', __( 'Share this', 'relay-share' ) );
	$heading_id = 'relay-heading-' . $post_id;

	return sprintf(
		'<section class="relay" aria-labelledby="%1$s" data-relay>' .
			'<h2 class="relay__heading" id="%1$s">%2$s</h2>' .
			'<ul class="relay__list">%3$s</ul>' .
		'</section>',
		esc_attr( $heading_id ),
		esc_html( $heading ),
		$controls // Each control built and escaped above.
	);
}

/**
 * Echo the share bar. The manual placement helper for themes.
 *
 * Usage in a template: `if ( function_exists( 'relay_share' ) ) relay_share();`
 *
 * @param int|null $post_id Optional post ID; defaults to the current post.
 * @return void
 */
function relay_share( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( ! $post_id ) {
		return;
	}

	// Output is fully escaped inside relay_get_section_html().
	echo relay_get_section_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Shortcode handler: [relay_share].
 *
 * Lets users drop the share bar into post content or a shortcode block. Returns
 * markup (does not echo) as shortcodes must.
 *
 * @return string Section HTML for the current post, or '' off-context.
 */
function relay_shortcode() {
	$post_id = (int) get_the_ID();

	return $post_id ? relay_get_section_html( $post_id ) : '';
}
add_shortcode( 'relay_share', 'relay_shortcode' );

/**
 * Whether the current request is a single post where the bar can appear.
 *
 * Centralizes the guard shared by the content filter and the asset enqueue so
 * they can never drift apart. Filterable so themes can opt views in or out
 * (e.g. to enable pages, or disable auto-append when placing it manually).
 *
 * @return bool
 */
function relay_is_active() {
	$active = ! is_admin()
		&& ! is_feed()
		&& ! is_embed()
		&& ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		&& is_singular( 'post' );

	/**
	 * Filter whether Relay auto-appends and enqueues on this request.
	 *
	 * Returning false disables the automatic after-content bar and the asset
	 * enqueue — useful when placing the bar manually via the template tag or
	 * shortcode, or to extend it to pages / custom post types.
	 *
	 * @param bool $active Whether Relay is active for this view.
	 */
	return (bool) apply_filters( 'relay_is_active', $active );
}

/**
 * Whether the active theme opts in to native WPAI companion placement.
 *
 * When a theme declares `add_theme_support( 'wpai-companions' )` it fires
 * `wpai_entry_top` / `wpai_entry_bottom` action hooks around the article body,
 * outside the constrained `.entry-content` prose column. In that case Relay
 * renders on `wpai_entry_bottom` (full article width) instead of being appended
 * inside `the_content`, so the bar is never double-rendered.
 *
 * @return bool True when the theme supports the WPAI companions contract.
 */
function relay_theme_supports_companions() {
	return (bool) current_theme_supports( 'wpai-companions' );
}

/**
 * Append the share bar after single-post content.
 *
 * Guards on the main query in the loop for single posts only, so the bar is
 * never injected into excerpts, archives, feeds, REST responses, or secondary
 * queries. Returns the content untouched when there is nothing to show.
 *
 * When the active theme supports the WPAI companions contract this returns the
 * content unchanged: the bar is rendered on the `wpai_entry_bottom` hook instead
 * (see relay_render_entry_bottom), so it appears at full article width and is
 * never double-rendered.
 *
 * @param string $content The post content.
 * @return string
 */
function relay_append_section( $content ) {
	if ( ! relay_is_active() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	// WPAI-aware theme: it renders via the entry hook, not inside the content.
	if ( relay_theme_supports_companions() ) {
		return $content;
	}

	/**
	 * Filter whether to auto-append the bar to the content.
	 *
	 * Set false to keep the assets but place the bar manually with relay_share()
	 * or the [relay_share] shortcode.
	 *
	 * @param bool $auto Whether to append automatically.
	 */
	if ( ! apply_filters( 'relay_auto_append', true ) ) {
		return $content;
	}

	return $content . relay_get_section_html( (int) get_the_ID() );
}
add_filter( 'the_content', 'relay_append_section', 30 );

/**
 * Render the share bar on the theme's `wpai_entry_bottom` hook.
 *
 * Active only when the theme supports the WPAI companions contract; otherwise
 * placement stays in the_content (see relay_append_section), so there is never a
 * double render. The same activity and auto-append guards apply here, keeping
 * the two paths in lockstep.
 *
 * Priority 20 places the bar after other bottom companions (e.g. Kindred related
 * posts, which renders at the default priority). The hook fires outside the
 * .entry-content wrapper, so the bar can sit at full article width.
 *
 * @return void
 */
function relay_render_entry_bottom() {
	if ( ! relay_theme_supports_companions() || ! relay_is_active() || ! is_main_query() ) {
		return;
	}

	/** This filter is documented in relay_append_section(). */
	if ( ! apply_filters( 'relay_auto_append', true ) ) {
		return;
	}

	$post_id = (int) get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	// Output is fully escaped inside relay_get_section_html().
	echo relay_get_section_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wpai_entry_bottom', 'relay_render_entry_bottom', 20 );

/**
 * Register and enqueue the front-end stylesheet and behavior script.
 *
 * Both ship as real, versioned files in /assets (no inline blobs) and load only
 * on single posts where the bar can appear. The script is enqueued in the footer
 * and deferred (see relay_defer_script) so it never blocks rendering: the share
 * links work as plain anchors before it runs; copy/mastodon controls are wired
 * up the moment it does.
 *
 * The localized strings power the in-browser copy fallback and the Mastodon
 * instance prompt — all translatable, all on the page, no network calls.
 *
 * @return void
 */
function relay_enqueue_assets() {
	if ( ! relay_is_active() ) {
		return;
	}

	wp_enqueue_style(
		'relay-share',
		plugins_url( 'assets/relay-share.css', __FILE__ ),
		array(),
		RELAY_VERSION
	);

	wp_enqueue_script(
		'relay-share',
		plugins_url( 'assets/js/relay-share.js', __FILE__ ),
		array(),
		RELAY_VERSION,
		true // In the footer.
	);

	wp_localize_script(
		'relay-share',
		'relayShareI18n',
		array(
			'copied'          => __( 'Copied!', 'relay-share' ),
			'copyFailed'      => __( 'Press Ctrl+C to copy', 'relay-share' ),
			'mastodonPrompt'  => __( 'Enter your Mastodon instance (e.g. mastodon.social):', 'relay-share' ),
			'confirmDuration' => 2200,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'relay_enqueue_assets' );

/**
 * Add the `defer` attribute to the plugin's footer script tag.
 *
 * Keeps support back to WordPress 5.0 (the `strategy` enqueue argument arrived
 * in 6.3). The script is pure progressive enhancement, so deferring it is safe.
 *
 * @param string $tag    The full <script> tag for the enqueued handle.
 * @param string $handle The script's registered handle.
 * @return string The (possibly) modified script tag.
 */
function relay_defer_script( $tag, $handle ) {
	if ( 'relay-share' !== $handle || false !== strpos( $tag, ' defer' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'relay_defer_script', 10, 2 );

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function relay_load_textdomain() {
	load_plugin_textdomain( 'relay-share', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'relay_load_textdomain' );
