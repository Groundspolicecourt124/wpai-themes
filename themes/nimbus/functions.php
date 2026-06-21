<?php
/**
 * Nimbus theme setup and assets.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'nimbus_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function nimbus_setup() {
		load_theme_textdomain( 'nimbus', get_template_directory() . '/languages' );

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
		add_theme_support( 'custom-background', array( 'default-color' => 'f6f7fb' ) );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'nimbus' ),
		) );
	}
}
add_action( 'after_setup_theme', 'nimbus_setup' );

/**
 * Set the content width in pixels.
 */
function nimbus_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'nimbus_content_width', 704 );
}
add_action( 'after_setup_theme', 'nimbus_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function nimbus_assets() {
	$version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'nimbus-style', get_stylesheet_uri(), array(), $version );

	// Self-contained motion system: scroll reveals, magnetic CTA, card tilt,
	// count-up stats, and the living gradient hero. Loaded in the footer with
	// defer; the script gates itself on prefers-reduced-motion internally.
	wp_enqueue_script(
		'nimbus-motion',
		get_template_directory_uri() . '/assets/js/motion.js',
		array(),
		$version,
		true
	);
	wp_script_add_data( 'nimbus-motion', 'defer', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'nimbus_assets' );

/**
 * Add a `defer` attribute to scripts that opted in via wp_script_add_data().
 *
 * @param string $tag    The full <script> tag.
 * @param string $handle The script handle.
 * @return string
 */
function nimbus_defer_scripts( $tag, $handle ) {
	if ( wp_scripts()->get_data( $handle, 'defer' ) && false === strpos( $tag, ' defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'nimbus_defer_scripts', 10, 2 );

/**
 * Print a tiny no-dependency snippet in <head> that flags JS as available.
 *
 * Adds a `js` class to <html> before paint so motion start-states only apply
 * when JS can finish them — no-JS users see all content immediately (no FOUC,
 * no hidden-and-stuck elements). This must run as early as possible, so it is
 * inlined in <head> rather than deferred to the footer.
 */
function nimbus_js_class_head() {
	echo "<script>document.documentElement.className += ' js';</script>\n";
}
add_action( 'wp_head', 'nimbus_js_class_head', 0 );

/**
 * Load the Customizer integration (live color & style controls).
 */
require_once get_template_directory() . '/inc/customizer.php';

/**
 * Register the sidebar widget area.
 */
function nimbus_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'nimbus' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'nimbus' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'nimbus_widgets_init' );

/**
 * Add helper classes to <body> so the layout can react to the sidebar.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function nimbus_body_classes( $classes ) {
	if ( is_active_sidebar( 'sidebar-1' ) && ! is_404() ) {
		$classes[] = 'has-sidebar';
	} else {
		$classes[] = 'no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'nimbus_body_classes' );

/**
 * Trim the auto-excerpt to a tidy length.
 *
 * @param int $length Default word length.
 * @return int
 */
function nimbus_excerpt_length( $length ) {
	return 28;
}
add_filter( 'excerpt_length', 'nimbus_excerpt_length' );

/**
 * Replace the [...] excerpt suffix with an ellipsis.
 *
 * @param string $more Default more string.
 * @return string
 */
function nimbus_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'nimbus_excerpt_more' );

/**
 * Add the feature modifier class to the first post card in a listing.
 * Toggled via the $nimbus_feature global around get_template_part().
 *
 * @param array $classes Existing post classes.
 * @return array
 */
function nimbus_feature_class( $classes ) {
	if ( ! empty( $GLOBALS['nimbus_feature'] ) ) {
		$classes[] = 'entry--feature';
	}
	return $classes;
}

/**
 * Print human-readable post meta (date + author).
 */
if ( ! function_exists( 'nimbus_posted_meta' ) ) {
	function nimbus_posted_meta() {
		printf(
			/* translators: 1: post date, 2: post author */
			esc_html__( '%1$s · by %2$s', 'nimbus' ),
			'<time datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>',
			'<span class="author">' . esc_html( get_the_author() ) . '</span>'
		);
	}
}

/**
 * Print the primary category as a linked chip, for listing covers.
 */
if ( ! function_exists( 'nimbus_primary_category' ) ) {
	function nimbus_primary_category() {
		$categories = get_the_category();
		if ( empty( $categories ) ) {
			return;
		}
		$category = $categories[0];
		printf(
			'<span class="entry__cat"><a class="cat-chip" href="%1$s">%2$s</a></span>',
			esc_url( get_category_link( $category->term_id ) ),
			esc_html( $category->name )
		);
	}
}

/**
 * Render a gradient placeholder cover when a post has no featured image.
 * Uses the first letter of the title as a mark.
 */
if ( ! function_exists( 'nimbus_placeholder_cover' ) ) {
	function nimbus_placeholder_cover() {
		$title = wp_strip_all_tags( get_the_title() );
		$mark  = $title ? mb_substr( $title, 0, 1 ) : 'N';
		echo '<span class="placeholder-mark" aria-hidden="true">' . esc_html( mb_strtoupper( $mark ) ) . '</span>';
	}
}
