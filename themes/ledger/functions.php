<?php
/**
 * Ledger theme setup and assets.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ledger_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function ledger_setup() {
		load_theme_textdomain( 'ledger', get_template_directory() . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-logo', array(
			'height'      => 80,
			'width'       => 240,
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
			'primary' => esc_html__( 'Primary Menu', 'ledger' ),
		) );
	}
}
add_action( 'after_setup_theme', 'ledger_setup' );

/**
 * Set the content width in pixels.
 */
function ledger_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'ledger_content_width', 720 );
}
add_action( 'after_setup_theme', 'ledger_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function ledger_assets() {
	wp_enqueue_style( 'ledger-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ledger_assets' );

/**
 * Register the sidebar widget area.
 */
function ledger_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'ledger' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'ledger' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'ledger_widgets_init' );

/**
 * Print human-readable post meta (date + author) as a small-caps byline.
 */
if ( ! function_exists( 'ledger_posted_meta' ) ) {
	function ledger_posted_meta() {
		printf(
			/* translators: 1: post author, 2: post date, 3: separator */
			esc_html__( 'By %1$s%3$s%2$s', 'ledger' ),
			'<span class="author">' . esc_html( get_the_author() ) . '</span>',
			'<time datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>',
			'<span class="sep" aria-hidden="true">&middot;</span>'
		);
	}
}

/**
 * Print the primary category as a kicker above the headline.
 */
if ( ! function_exists( 'ledger_post_kicker' ) ) {
	function ledger_post_kicker() {
		$categories = get_the_category();

		if ( empty( $categories ) ) {
			return;
		}

		$category = $categories[0];

		printf(
			'<p class="entry-kicker"><a href="%1$s">%2$s</a></p>',
			esc_url( get_category_link( $category->term_id ) ),
			esc_html( $category->name )
		);
	}
}
