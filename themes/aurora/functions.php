<?php
/**
 * Aurora theme setup and assets.
 *
 * @package Aurora
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'AURORA_VERSION' ) ) {
	define( 'AURORA_VERSION', '1.2.0' );
}

// Customizer: live color & style controls.
require_once get_template_directory() . '/inc/customizer.php';

if ( ! function_exists( 'aurora_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function aurora_setup() {
		load_theme_textdomain( 'aurora', get_template_directory() . '/languages' );

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
			'navigation-widgets',
		) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'custom-background', array(
			'default-color' => 'f7f4ee',
		) );

		// Image size for the homepage lead story cover.
		add_image_size( 'aurora-lead', 1320, 760, true );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'aurora' ),
			'social'  => esc_html__( 'Footer Menu', 'aurora' ),
		) );
	}
}
add_action( 'after_setup_theme', 'aurora_setup' );

/**
 * Set the content width in pixels.
 */
function aurora_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'aurora_content_width', 720 );
}
add_action( 'after_setup_theme', 'aurora_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function aurora_assets() {
	wp_enqueue_style( 'aurora-style', get_stylesheet_uri(), array(), AURORA_VERSION );

	wp_enqueue_script(
		'aurora-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		AURORA_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'aurora_assets' );

/**
 * Register the sidebar widget area.
 */
function aurora_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'aurora' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Widgets shown beside posts and pages.', 'aurora' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'aurora_widgets_init' );

/**
 * Use a refined em-dash reading prompt instead of the default bracketed link.
 *
 * @param string $more The default "more" string.
 * @return string
 */
function aurora_excerpt_more( $more ) {
	if ( is_admin() ) {
		return $more;
	}
	return '&hellip;';
}
add_filter( 'excerpt_more', 'aurora_excerpt_more' );

/**
 * A slightly longer excerpt suits the editorial layout.
 *
 * @param int $length Default excerpt length in words.
 * @return int
 */
function aurora_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return 34;
}
add_filter( 'excerpt_length', 'aurora_excerpt_length' );

/**
 * Print human-readable post meta (byline + date).
 */
if ( ! function_exists( 'aurora_posted_meta' ) ) {
	function aurora_posted_meta() {
		$time = sprintf(
			'<time class="entry-meta__date" datetime="%1$s">%2$s</time>',
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);

		printf(
			/* translators: 1: post author, 2: post date */
			'<span class="entry-meta__byline">' . esc_html__( 'by %1$s', 'aurora' ) . '</span> <span class="entry-meta__sep" aria-hidden="true">&middot;</span> %2$s',
			'<span class="author">' . esc_html( get_the_author() ) . '</span>',
			$time // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts above.
		);
	}
}

/**
 * Print the post's primary category as a pill link. Falls back to nothing
 * when the post has no category (e.g. a custom post type).
 */
if ( ! function_exists( 'aurora_category_pill' ) ) {
	function aurora_category_pill() {
		if ( 'post' !== get_post_type() ) {
			return;
		}

		$categories = get_the_category();
		if ( empty( $categories ) ) {
			return;
		}

		$category = $categories[0];
		printf(
			'<a class="entry-cat" href="%1$s">%2$s</a>',
			esc_url( get_category_link( $category->term_id ) ),
			esc_html( $category->name )
		);
	}
}

/**
 * Output a featured image, or a graceful gradient placeholder when a post has
 * none, so the index and single never break their rhythm.
 *
 * @param string $size  Image size handle.
 * @param bool   $link  Wrap the image in a permalink (used on archives).
 */
if ( ! function_exists( 'aurora_featured_media' ) ) {
	function aurora_featured_media( $size = 'large', $link = false ) {
		$has_image = has_post_thumbnail();

		// Deterministic hue from the post ID so placeholders feel intentional.
		$hue   = (int) ( get_the_ID() * 47 ) % 360;
		$style = sprintf(
			'--aurora-hue:%1$d;',
			$hue
		);

		$classes  = 'entry-media';
		$classes .= $has_image ? ' has-image' : ' is-placeholder';

		$inner = '';
		if ( $has_image ) {
			$inner = get_the_post_thumbnail(
				null,
				$size,
				array(
					'class'    => 'entry-media__img',
					'loading'  => is_singular() ? 'eager' : 'lazy',
					'decoding' => 'async',
				)
			);
		} else {
			$title   = wp_strip_all_tags( get_the_title() );
			$initial = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 );
			$inner   = '<span class="entry-media__glyph" aria-hidden="true">';
			$inner  .= esc_html( $initial );
			$inner  .= '</span>';
		}

		if ( $link && ! is_singular() ) {
			printf(
				'<a class="%1$s" style="%2$s" href="%3$s" tabindex="-1" aria-hidden="true">%4$s</a>',
				esc_attr( $classes ),
				esc_attr( $style ),
				esc_url( get_permalink() ),
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe markup assembled above.
			);
		} else {
			printf(
				'<div class="%1$s" style="%2$s">%3$s</div>',
				esc_attr( $classes ),
				esc_attr( $style ),
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe markup assembled above.
			);
		}
	}
}

/**
 * Add helpful context classes to <body>.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function aurora_body_classes( $classes ) {
	if ( is_home() || is_front_page() ) {
		$classes[] = 'aurora-blog';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'aurora-no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'aurora_body_classes' );
