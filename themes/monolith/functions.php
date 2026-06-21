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
	wp_enqueue_style( 'monolith-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'monolith_assets' );

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
