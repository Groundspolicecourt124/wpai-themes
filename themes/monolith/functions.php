<?php
/**
 * Monolith theme setup and assets.
 *
 * @package Monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the Customizer (live color controls).
 */
require_once get_template_directory() . '/inc/customizer.php';

if ( ! function_exists( 'monolith_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function monolith_setup() {
		load_theme_textdomain( 'monolith', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array(
			'height'      => 88,
			'width'       => 88,
			'flex-height' => true,
			'flex-width'  => true,
		) );
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );

		add_theme_support( 'wp-block-styles' );

		// A slightly wider crop for project covers and featured images.
		add_image_size( 'monolith-cover', 1200, 750, true );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'monolith' ),
		) );
	}
}
add_action( 'after_setup_theme', 'monolith_setup' );

/**
 * Set the content width in pixels.
 */
function monolith_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'monolith_content_width', 704 );
}
add_action( 'after_setup_theme', 'monolith_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function monolith_assets() {
	$monolith_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'monolith-style', get_stylesheet_uri(), array(), $monolith_version );

	// Self-contained motion system — vanilla JS, footer-loaded. Deferred via
	// the script_loader_tag filter below for compatibility back to WP 5.0
	// (the wp_enqueue_script() $args/strategy form only exists in WP 6.3+).
	wp_enqueue_script(
		'monolith-motion',
		get_template_directory_uri() . '/assets/js/motion.js',
		array(),
		$monolith_version,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'monolith_assets' );

/**
 * Add `defer` to the motion script tag (compatible with all supported WP).
 *
 * @param string $monolith_tag    The full <script> tag.
 * @param string $monolith_handle The script handle.
 * @return string
 */
function monolith_defer_motion( $monolith_tag, $monolith_handle ) {
	if ( 'monolith-motion' === $monolith_handle && false === strpos( $monolith_tag, ' defer' ) ) {
		$monolith_tag = str_replace( ' src=', ' defer src=', $monolith_tag );
	}
	return $monolith_tag;
}
add_filter( 'script_loader_tag', 'monolith_defer_motion', 10, 2 );

/**
 * Add a `js` class to <html> before paint so no-JS visitors always see every
 * bit of content (the motion CSS only hides-then-reveals under `.js`). Printed
 * as early as possible in <head> via wp_head; no external request.
 */
function monolith_html_js_class() {
	echo "<script>document.documentElement.className+=' js';</script>\n";
}
add_action( 'wp_head', 'monolith_html_js_class', 0 );

/**
 * Render the signature brutalist marquee ticker.
 *
 * A horizontal scrolling strip of studio labels — the theme's signature move.
 * The phrase set is doubled in markup so the CSS animation can loop seamlessly,
 * and the duplicate is hidden from assistive tech. Filterable so child themes
 * can swap the phrases.
 *
 * @param string $monolith_class Optional extra class on the wrapper.
 */
if ( ! function_exists( 'monolith_marquee' ) ) {
	function monolith_marquee( $monolith_class = '' ) {
		$monolith_phrases = apply_filters(
			'monolith_marquee_phrases',
			array(
				esc_html__( 'Selected Work', 'monolith' ),
				esc_html__( 'Studio Journal', 'monolith' ),
				esc_html__( 'Brutalist by Design', 'monolith' ),
				esc_html__( 'Design & Engineering', 'monolith' ),
				esc_html__( 'No Rounded Corners', 'monolith' ),
			)
		);

		if ( empty( $monolith_phrases ) ) {
			return;
		}

		// Build one run of "PHRASE ///" items.
		$monolith_run = '';
		foreach ( $monolith_phrases as $monolith_phrase ) {
			$monolith_run .= '<span class="m-marquee__item">' . esc_html( $monolith_phrase ) . '</span>';
			$monolith_run .= '<span class="m-marquee__sep" aria-hidden="true">///</span>';
		}

		$monolith_wrap = 'm-marquee';
		if ( $monolith_class ) {
			$monolith_wrap .= ' ' . sanitize_html_class( $monolith_class );
		}

		printf(
			'<div class="%1$s" aria-hidden="true"><div class="m-marquee__track"><div class="m-marquee__run">%2$s</div><div class="m-marquee__run" aria-hidden="true">%2$s</div></div></div>',
			esc_attr( $monolith_wrap ),
			$monolith_run // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each phrase escaped above.
		);
	}
}

/**
 * Register the sidebar widget area.
 */
function monolith_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'monolith' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'monolith' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'monolith_widgets_init' );

/**
 * Print human-readable post meta (byline + date).
 */
if ( ! function_exists( 'monolith_posted_meta' ) ) {
	function monolith_posted_meta() {
		$monolith_author = '<span class="author">' . esc_html( get_the_author() ) . '</span>';
		$monolith_date   = '<time datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>';

		printf(
			/* translators: 1: post author, 2: separator, 3: post date */
			esc_html__( 'By %1$s%2$s%3$s', 'monolith' ),
			$monolith_author, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped above.
			'<span class="sep" aria-hidden="true">/</span>',
			$monolith_date // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped above.
		);
	}
}

/**
 * Tighten the auto-excerpt for the project grid.
 */
function monolith_excerpt_length( $length ) {
	return 28;
}
add_filter( 'excerpt_length', 'monolith_excerpt_length' );

/**
 * Replace the default [...] excerpt tail.
 */
function monolith_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'monolith_excerpt_more' );

/**
 * Give the body a helpful class when the front page is the blog index.
 */
function monolith_body_classes( $classes ) {
	if ( is_front_page() && is_home() ) {
		$classes[] = 'monolith-home';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'monolith-no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'monolith_body_classes' );
