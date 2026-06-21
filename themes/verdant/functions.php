<?php
/**
 * Verdant theme setup and assets.
 *
 * @package Verdant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customizer color & style controls with live preview.
 */
require_once get_template_directory() . '/inc/customizer.php';

if ( ! function_exists( 'verdant_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function verdant_setup() {
		load_theme_textdomain( 'verdant', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array(
			'height'      => 80,
			'width'       => 80,
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

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'verdant' ),
		) );
	}
}
add_action( 'after_setup_theme', 'verdant_setup' );

/**
 * Set the content width in pixels.
 */
function verdant_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'verdant_content_width', 720 );
}
add_action( 'after_setup_theme', 'verdant_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function verdant_assets() {
	wp_enqueue_style( 'verdant-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'verdant_assets' );

/**
 * Register the sidebar widget area.
 */
function verdant_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'verdant' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'verdant' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'verdant_widgets_init' );

/**
 * Print human-readable post meta (date + author).
 */
if ( ! function_exists( 'verdant_posted_meta' ) ) {
	function verdant_posted_meta() {
		printf(
			/* translators: 1: post date, 2: post author */
			esc_html__( '%1$s · by %2$s', 'verdant' ),
			'<time datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>',
			'<span class="author">' . esc_html( get_the_author() ) . '</span>'
		);
	}
}

/**
 * Output the post's primary category as a small pill "eyebrow".
 *
 * Prints nothing when the post has no categories, so it degrades gracefully.
 */
if ( ! function_exists( 'verdant_category_pill' ) ) {
	function verdant_category_pill() {
		if ( 'post' !== get_post_type() ) {
			return;
		}

		$categories = get_the_category();
		if ( empty( $categories ) ) {
			return;
		}

		$primary = $categories[0];

		printf(
			'<a class="entry-eyebrow" href="%1$s">%2$s</a>',
			esc_url( get_category_link( $primary->term_id ) ),
			esc_html( $primary->name )
		);
	}
}

/**
 * Decorative botanical mark used as a graceful fallback when a post has no
 * featured image. Inline SVG keeps it dependency-free and crisp at any size.
 */
if ( ! function_exists( 'verdant_leaf_mark' ) ) {
	function verdant_leaf_mark() {
		return '<svg class="leaf-mark" viewBox="0 0 64 64" width="64" height="64" aria-hidden="true" focusable="false" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M32 6C18 14 12 26 12 40c0 9 6 16 16 18 0-16 2-30 12-44-3 12-6 26-6 42 10-3 14-12 14-22C58 22 46 12 32 6Z" fill="currentColor" opacity="0.9"/><path d="M30 58c0-18 4-32 14-44" stroke="#fff" stroke-width="1.6" stroke-linecap="round" opacity="0.55"/></svg>';
	}
}

/**
 * Filter the excerpt "read more" ellipsis to a calm character.
 */
function verdant_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'verdant_excerpt_more' );

/**
 * A slightly longer, more inviting excerpt length.
 */
function verdant_excerpt_length() {
	return 32;
}
add_filter( 'excerpt_length', 'verdant_excerpt_length' );

/**
 * Add a body class on the blog/front page so the homepage hero can be styled
 * and the first post can be promoted to a lead "featured" card.
 */
function verdant_body_classes( $classes ) {
	if ( ( is_home() || is_front_page() ) && ! is_paged() ) {
		$classes[] = 'verdant-has-hero';
	}
	return $classes;
}
add_filter( 'body_class', 'verdant_body_classes' );
