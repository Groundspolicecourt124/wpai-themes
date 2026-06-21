<?php
/**
 * Plugin Name: Lumen — Image Lightbox
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Turns content and gallery images into a smooth, accessible full-screen lightbox with keyboard navigation, a focus trap, prev/next, captions, and motion that respects prefers-reduced-motion. Self-contained, theme-adaptive, zero configuration.
 * Category:   Media & UX
 * Version:     1.0.0
 * Author:      WPAI Themes
 * Author URI:  https://github.com/lordbasilaiassistant-sudo/wpai-themes
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lumen-lightbox
 *
 * @package Lumen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin version, kept in sync with the header for cache-busting.
 */
const LUMEN_VERSION = '1.0.0';

/**
 * Whether the current request is a front-end singular view where the lightbox
 * should run.
 *
 * Centralizes the guard shared by the content filter and the asset enqueues so
 * they can never drift apart. Bails on the admin, feeds, embeds, and the REST
 * API — anywhere a click-to-zoom lightbox over rendered content makes no sense.
 *
 * @return bool
 */
function lumen_is_active() {
	$active = ! is_admin()
		&& ! is_feed()
		&& ! is_embed()
		&& ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		&& is_singular();

	/**
	 * Filter whether Lumen may load on this request.
	 *
	 * Returning false disables the markup tagging, the overlay, and the asset
	 * enqueue for the current view.
	 *
	 * @param bool $active Whether the lightbox is active for this view.
	 */
	return (bool) apply_filters( 'lumen_is_active', $active );
}

/**
 * Whether the lightbox is disabled for a specific post.
 *
 * Authors can switch the lightbox off per post with a `lumen_disable` custom
 * field set to a truthy value (1, true, yes, on). Filterable so a site can wire
 * the toggle to its own meta key or logic.
 *
 * @param int $post_id Post ID being rendered.
 * @return bool True if the lightbox should be suppressed for this post.
 */
function lumen_is_disabled_for_post( $post_id ) {
	$disabled = false;

	$flag = get_post_meta( $post_id, 'lumen_disable', true );

	if ( '' !== $flag ) {
		$disabled = in_array( strtolower( (string) $flag ), array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Filter whether the lightbox is disabled for this post.
	 *
	 * @param bool $disabled Whether the lightbox is suppressed.
	 * @param int  $post_id  The post ID.
	 */
	return (bool) apply_filters( 'lumen_is_disabled_for_post', $disabled, $post_id );
}

/**
 * Tag eligible images in rendered content so the front-end script can wire them.
 *
 * A single regex pass over the already-rendered content marks every qualifying
 * <img> with a `data-lumen` attribute and, where available, the full-size source
 * (`data-lumen-full`) and a caption (`data-lumen-caption`). The script reads only
 * these data attributes, so it never has to re-derive anything at runtime.
 *
 * What is intentionally skipped:
 *   - images already inside a link (the author already chose a click target),
 *   - images marked to opt out via `data-lumen-skip` or a `.lumen-skip` class,
 *   - tiny images (emoji, icons) detected by a small explicit width/height,
 *   - SVGs (no meaningful "full-size" zoom, often UI chrome).
 *
 * Captions are resolved per image in lumen_resolve_caption(): an enclosing
 * <figure>'s <figcaption> wins, falling back to the image's own alt text. The
 * full-size URL is upgraded from a WordPress size suffix (e.g. -1024x768) to the
 * original file when the markup carries a `wp-image-{id}` class.
 *
 * @param string $content The rendered post content.
 * @return string The content with eligible images tagged.
 */
function lumen_tag_images( $content ) {
	if ( false === strpos( $content, '<img' ) ) {
		return $content;
	}

	// Protect images that are already inside a link: the author has chosen a
	// click target, so the lightbox must leave them alone (see plugin docs/FAQ).
	// Each <a>…</a> block is swapped for an opaque placeholder before tagging and
	// restored verbatim afterwards, so no <img> within a link is ever matched.
	$anchors = array();
	if ( false !== stripos( $content, '<a' ) ) {
		$content = preg_replace_callback(
			'/<a\b[^>]*>.*?<\/a>/is',
			function ( $m ) use ( &$anchors ) {
				$token              = "\0lumen-a-" . count( $anchors ) . "\0";
				$anchors[ $token ] = $m[0];
				return $token;
			},
			$content
		);

		if ( null === $content ) {
			return ''; // preg failure handled by caller below.
		}
	}

	// Walk each <figure> (if any) so a <figcaption> can be attributed to the
	// image it wraps, then tag the figure's image. Standalone images are handled
	// in a second pass below.
	$content = preg_replace_callback(
		'/<figure\b[^>]*>.*?<\/figure>/is',
		'lumen_tag_figure',
		$content
	);

	if ( null === $content ) {
		return ''; // Should never happen; preg failure handled by caller below.
	}

	// Second pass: tag standalone images that were not inside a <figure>.
	$content = preg_replace_callback(
		'/<img\b[^>]*>/i',
		function ( $m ) {
			return lumen_tag_single_image( $m[0], '' );
		},
		$content
	);

	if ( null === $content ) {
		return '';
	}

	// Restore the untouched linked blocks.
	if ( $anchors ) {
		$content = strtr( $content, $anchors );
	}

	return $content;
}

/**
 * Tag the image inside a single matched <figure>, attributing its <figcaption>.
 *
 * @param array $m Regex match; $m[0] is the full <figure>…</figure> block.
 * @return string The figure block with its image tagged.
 */
function lumen_tag_figure( $m ) {
	$figure = $m[0];

	$caption = '';
	if ( preg_match( '/<figcaption\b[^>]*>(.*?)<\/figcaption>/is', $figure, $cap ) ) {
		$caption = trim( wp_strip_all_tags( $cap[1] ) );
	}

	// Tag the first <img> in the figure with the resolved caption.
	$tagged = preg_replace_callback(
		'/<img\b[^>]*>/i',
		function ( $img ) use ( $caption ) {
			return lumen_tag_single_image( $img[0], $caption );
		},
		$figure,
		1
	);

	return null === $tagged ? $figure : $tagged;
}

/**
 * Decide whether an <img> tag qualifies and, if so, return it with Lumen data.
 *
 * Already-tagged, opted-out, linked-context, SVG, and tiny images are returned
 * unchanged. Qualifying images get `data-lumen`, plus `data-lumen-full` and
 * `data-lumen-caption` when those can be resolved.
 *
 * @param string $tag           The full <img …> tag.
 * @param string $figcaption    Caption inherited from an enclosing <figure>, or ''.
 * @return string The original or augmented <img> tag.
 */
function lumen_tag_single_image( $tag, $figcaption ) {
	// Already processed (defensive against double filtering).
	if ( false !== stripos( $tag, 'data-lumen' ) ) {
		return $tag;
	}

	// Explicit opt-out.
	if (
		false !== stripos( $tag, 'data-lumen-skip' )
		|| preg_match( '/class=("|\')[^"\']*\blumen-skip\b[^"\']*\1/i', $tag )
	) {
		return $tag;
	}

	// Resolve the source; bail if there is none or it is an SVG.
	if ( ! preg_match( '/\ssrc=("|\')(.*?)\1/i', $tag, $src_match ) ) {
		return $tag;
	}
	$src = trim( $src_match[2] );
	if ( '' === $src || preg_match( '/\.svg(\?|#|$)/i', $src ) ) {
		return $tag;
	}

	// Skip clearly tiny images (icons, emoji, spacers).
	if ( preg_match( '/\swidth=("|\')(\d+)\1/i', $tag, $w ) && (int) $w[2] > 0 && (int) $w[2] < 100 ) {
		return $tag;
	}

	$full    = lumen_resolve_full_src( $tag, $src );
	$caption = lumen_resolve_caption( $tag, $figcaption );

	$attrs = ' data-lumen';
	if ( '' !== $full ) {
		$attrs .= ' data-lumen-full="' . esc_url( $full ) . '"';
	}
	if ( '' !== $caption ) {
		$attrs .= ' data-lumen-caption="' . esc_attr( $caption ) . '"';
	}

	// Insert the attributes just before the closing of the tag, preserving any
	// self-closing slash.
	if ( preg_match( '/\s*\/?>$/', $tag, $close, PREG_OFFSET_CAPTURE ) ) {
		$pos = $close[0][1];
		return substr( $tag, 0, $pos ) . $attrs . substr( $tag, $pos );
	}

	return $tag;
}

/**
 * Resolve the best full-size source URL for an image.
 *
 * Prefers an attachment's original file (looked up by the `wp-image-{id}` class
 * WordPress adds), then a srcset's largest candidate, then strips a WordPress
 * `-WIDTHxHEIGHT` size suffix from the displayed src as a last resort. Returns ''
 * when the best guess equals the displayed src (no upgrade available), so the
 * script can simply open the image's own src.
 *
 * @param string $tag The full <img> tag.
 * @param string $src The displayed src URL.
 * @return string A larger source URL, or '' when none beats the displayed src.
 */
function lumen_resolve_full_src( $tag, $src ) {
	// 1) Attachment original via the wp-image-{id} class.
	if ( preg_match( '/wp-image-(\d+)/', $tag, $id_match ) ) {
		$full = wp_get_attachment_image_url( (int) $id_match[1], 'full' );
		if ( $full && $full !== $src ) {
			return $full;
		}
	}

	// 2) Largest candidate in the srcset (highest width descriptor).
	if ( preg_match( '/\ssrcset=("|\')(.*?)\1/i', $tag, $ss ) ) {
		$best_url = '';
		$best_w   = 0;
		foreach ( explode( ',', $ss[2] ) as $candidate ) {
			$parts = preg_split( '/\s+/', trim( $candidate ) );
			if ( empty( $parts[0] ) ) {
				continue;
			}
			$cw = ( isset( $parts[1] ) && preg_match( '/^(\d+)w$/', $parts[1], $wm ) ) ? (int) $wm[1] : 0;
			if ( $cw >= $best_w ) {
				$best_w   = $cw;
				$best_url = $parts[0];
			}
		}
		if ( '' !== $best_url && $best_url !== $src ) {
			return $best_url;
		}
	}

	// 3) Strip a WordPress size suffix (e.g. photo-1024x768.jpg -> photo.jpg).
	$stripped = preg_replace( '/-\d+x\d+(\.[a-z0-9]+)(\?.*)?$/i', '$1$2', $src );
	if ( $stripped && $stripped !== $src ) {
		return $stripped;
	}

	return '';
}

/**
 * Resolve the caption for an image: an enclosing figcaption wins, then alt text.
 *
 * @param string $tag        The full <img> tag.
 * @param string $figcaption Caption from an enclosing <figure>, or ''.
 * @return string The caption text (may be ''), un-escaped (escaped by caller).
 */
function lumen_resolve_caption( $tag, $figcaption ) {
	if ( '' !== $figcaption ) {
		return $figcaption;
	}

	if ( preg_match( '/\salt=("|\')(.*?)\1/i', $tag, $alt ) ) {
		$caption = trim( wp_strip_all_tags( $alt[2] ) );
		if ( '' !== $caption ) {
			return $caption;
		}
	}

	return '';
}

/**
 * Filter rendered singular content to tag eligible images for the lightbox.
 *
 * Guards on the main query in the loop for singular views only, so images in
 * excerpts, archives, feeds, REST responses, and secondary queries are left
 * untouched. Returns the content unchanged on any regex failure (never blanks a
 * post).
 *
 * @param string $content The post content.
 * @return string
 */
function lumen_filter_content( $content ) {
	if ( ! lumen_is_active() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$post_id = get_the_ID();
	if ( ! $post_id || lumen_is_disabled_for_post( $post_id ) ) {
		return $content;
	}

	$tagged = lumen_tag_images( $content );

	// Defensive: if tagging produced nothing (e.g. catastrophic regex), keep the
	// original content so a post is never blanked.
	return '' === $tagged ? $content : $tagged;
}
// Priority 25 runs after core block/shortcode/wpautop filters (9–10) and after
// most galleries are rendered, so we tag real <img> tags rather than block
// comments.
add_filter( 'the_content', 'lumen_filter_content', 25 );

/**
 * Build the single, shared lightbox overlay markup.
 *
 * One dialog serves every image on the page; the script swaps the image, caption,
 * and counter as the visitor navigates. The dialog starts hidden (`hidden`
 * attribute) and inert to assistive tech until opened. All labels are translated
 * and every dynamic-looking value is a fixed, escaped literal — the only runtime
 * content (the image and caption) is injected by the script from the data
 * attributes tagged above.
 *
 * Icons are inline, hand-authored SVG (no icon font, no external request).
 *
 * @return string The overlay HTML.
 */
function lumen_overlay_html() {
	$close_label = esc_attr__( 'Close', 'lumen-lightbox' );
	$prev_label  = esc_attr__( 'Previous image', 'lumen-lightbox' );
	$next_label  = esc_attr__( 'Next image', 'lumen-lightbox' );
	$dialog_name = esc_attr__( 'Image viewer', 'lumen-lightbox' );

	$close_icon = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
	$prev_icon  = '<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="15 18 9 12 15 6"></polyline></svg>';
	$next_icon  = '<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="9 18 15 12 9 6"></polyline></svg>';

	return sprintf(
		'<div class="lumen-overlay" id="lumen-overlay" role="dialog" aria-modal="true" aria-label="%1$s" hidden data-lumen-overlay>' .
			'<div class="lumen-overlay__backdrop" data-lumen-close></div>' .
			'<div class="lumen-overlay__viewport">' .
				'<figure class="lumen-overlay__figure">' .
					'<img class="lumen-overlay__image" alt="" data-lumen-image />' .
					'<figcaption class="lumen-overlay__caption" data-lumen-caption hidden></figcaption>' .
				'</figure>' .
			'</div>' .
			'<button type="button" class="lumen-overlay__close" aria-label="%2$s" data-lumen-close>%5$s</button>' .
			'<button type="button" class="lumen-overlay__nav lumen-overlay__nav--prev" aria-label="%3$s" data-lumen-prev hidden>%6$s</button>' .
			'<button type="button" class="lumen-overlay__nav lumen-overlay__nav--next" aria-label="%4$s" data-lumen-next hidden>%7$s</button>' .
			'<p class="lumen-overlay__counter" data-lumen-counter aria-hidden="true" hidden></p>' .
			'<div class="lumen-overlay__live" aria-live="polite" data-lumen-live></div>' .
		'</div>',
		$dialog_name,
		$close_label,
		$prev_label,
		$next_label,
		$close_icon, // Static, hand-authored SVG.
		$prev_icon,
		$next_icon
	);
}

/**
 * Print the shared overlay markup in the footer when Lumen is active.
 *
 * Output is assembled from translated, escaped labels and fixed SVG literals in
 * lumen_overlay_html().
 *
 * @return void
 */
function lumen_print_overlay() {
	if ( ! lumen_is_active() ) {
		return;
	}

	$post_id = get_the_ID();
	if ( $post_id && lumen_is_disabled_for_post( $post_id ) ) {
		return;
	}

	echo lumen_overlay_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped, trusted parts in lumen_overlay_html().
}
add_action( 'wp_footer', 'lumen_print_overlay' );

/**
 * Register and enqueue the front-end stylesheet and behavior script.
 *
 * Both ship as real, versioned files in /assets (no inline blobs) and load only
 * on singular views where the lightbox can appear. The script is enqueued in the
 * footer and deferred (see lumen_defer_script) so it never blocks rendering: the
 * images remain ordinary images until it runs.
 *
 * @return void
 */
function lumen_enqueue_assets() {
	if ( ! lumen_is_active() ) {
		return;
	}

	$post_id = get_the_ID();
	if ( $post_id && lumen_is_disabled_for_post( $post_id ) ) {
		return;
	}

	wp_enqueue_style(
		'lumen-lightbox',
		plugins_url( 'assets/lumen-lightbox.css', __FILE__ ),
		array(),
		LUMEN_VERSION
	);

	wp_enqueue_script(
		'lumen-lightbox',
		plugins_url( 'assets/js/lumen-lightbox.js', __FILE__ ),
		array(),
		LUMEN_VERSION,
		true // In the footer.
	);

	// Pass translated, escaped strings the script reads when announcing state.
	wp_localize_script(
		'lumen-lightbox',
		'lumenL10n',
		array(
			/* translators: 1: current image number, 2: total images. */
			'counter' => __( 'Image %1$s of %2$s', 'lumen-lightbox' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'lumen_enqueue_assets' );

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
function lumen_defer_script( $tag, $handle ) {
	if ( 'lumen-lightbox' !== $handle || false !== strpos( $tag, ' defer' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'lumen_defer_script', 10, 2 );

/**
 * Load the plugin text domain for translations.
 *
 * @return void
 */
function lumen_load_textdomain() {
	load_plugin_textdomain( 'lumen-lightbox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'lumen_load_textdomain' );
