<?php
/**
 * Sonnet theme setup and assets.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'sonnet_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function sonnet_setup() {
		load_theme_textdomain( 'sonnet', get_template_directory() . '/languages' );

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
			'primary' => esc_html__( 'Primary Menu', 'sonnet' ),
		) );
	}
}
add_action( 'after_setup_theme', 'sonnet_setup' );

/**
 * Set the content width in pixels.
 */
function sonnet_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'sonnet_content_width', 608 );
}
add_action( 'after_setup_theme', 'sonnet_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function sonnet_assets() {
	wp_enqueue_style( 'sonnet-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'sonnet_assets' );

/**
 * Register the sidebar widget area.
 */
function sonnet_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'sonnet' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'sonnet' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'sonnet_widgets_init' );

/**
 * Print human-readable post meta (date + author).
 */
if ( ! function_exists( 'sonnet_posted_meta' ) ) {
	function sonnet_posted_meta() {
		printf(
			/* translators: 1: post date, 2: post author */
			esc_html__( '%1$s — by %2$s', 'sonnet' ),
			'<time datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>',
			'<span class="author">' . esc_html( get_the_author() ) . '</span>'
		);
	}
}
