<?php
/**
 * Sonnet theme setup and assets.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Live color customization (Appearance → Customize → Colors & Style).
require_once get_template_directory() . '/inc/customizer.php';

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
			'height'      => 96,
			'width'       => 96,
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
		add_editor_style( 'style.css' );

		// A tasteful default image size for the list-card thumbnails (3:2).
		add_image_size( 'sonnet-card', 720, 480, true );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'sonnet' ),
		) );
	}
}
add_action( 'after_setup_theme', 'sonnet_setup' );

/**
 * Set the content width in pixels (matches the reading measure plus its gutters).
 */
function sonnet_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'sonnet_content_width', 660 );
}
add_action( 'after_setup_theme', 'sonnet_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function sonnet_assets() {
	$sonnet_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'sonnet-style', get_stylesheet_uri(), array(), $sonnet_version );

	// The motion system: scroll reveals, reading-progress line, drop-cap reveal,
	// gold shimmer sweep, refined caret, and the signature constellation canvas.
	// Hand-rolled vanilla JS — no libraries. Loaded in the footer, deferred, and
	// internally gated on prefers-reduced-motion. Content is fully visible if it
	// never runs (progressive enhancement via the `js` class on <html>).
	wp_enqueue_script(
		'sonnet-motion',
		get_template_directory_uri() . '/assets/js/motion.js',
		array(),
		$sonnet_version,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'sonnet_assets' );

/**
 * Print a tiny, render-blocking snippet in the <head> that flags the document
 * as JS-enabled before first paint. The motion CSS only hides-then-reveals
 * content when `html.js` is present, so no-JS users (and anyone whose script
 * fails) always see every element in its final, visible state — zero FOUC,
 * zero layout shift, no content trapped behind a transition that never fires.
 */
function sonnet_js_flag() {
	echo "<script>document.documentElement.className+=' js';</script>\n";
}
add_action( 'wp_head', 'sonnet_js_flag', 0 );

/**
 * Register the sidebar widget area.
 */
function sonnet_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'sonnet' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Widgets here appear below the writing, in the journal footer rail.', 'sonnet' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'sonnet_widgets_init' );

if ( ! function_exists( 'sonnet_posted_meta' ) ) {
	/**
	 * Print a literary byline: "By Mara Ellison · June 21, 2026".
	 */
	function sonnet_posted_meta() {
		printf(
			/* translators: 1: post author, 2: post date. */
			esc_html__( 'By %1$s %2$s', 'sonnet' ),
			'<span class="byline-author">' . esc_html( get_the_author() ) . '</span>',
			'<span class="byline-sep" aria-hidden="true">·</span> <time class="byline-date" datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>'
		);
	}
}

if ( ! function_exists( 'sonnet_primary_category' ) ) {
	/**
	 * Return the name of the post's first category, or an empty string.
	 *
	 * @return string Category name (unescaped — escape on output).
	 */
	function sonnet_primary_category() {
		if ( 'post' !== get_post_type() ) {
			return '';
		}

		$categories = get_the_category();
		if ( empty( $categories ) || is_wp_error( $categories ) ) {
			return '';
		}

		return $categories[0]->name;
	}
}

if ( ! function_exists( 'sonnet_masthead_kicker' ) ) {
	/**
	 * The small all-caps line that crowns the homepage masthead.
	 *
	 * @return string Unescaped label.
	 */
	function sonnet_masthead_kicker() {
		/* translators: shown above the homepage tagline. */
		return apply_filters( 'sonnet_masthead_kicker', __( 'The Journal', 'sonnet' ) );
	}
}

if ( ! function_exists( 'sonnet_archive_kicker' ) ) {
	/**
	 * A context-aware eyebrow for archive headers.
	 *
	 * @return string Unescaped label.
	 */
	function sonnet_archive_kicker() {
		if ( is_category() ) {
			return __( 'Category', 'sonnet' );
		}
		if ( is_tag() ) {
			return __( 'Tag', 'sonnet' );
		}
		if ( is_author() ) {
			return __( 'Author', 'sonnet' );
		}
		if ( is_date() ) {
			return __( 'Archive', 'sonnet' );
		}
		return __( 'Browse', 'sonnet' );
	}
}

/**
 * Drop WordPress's "Category:", "Tag:", etc. prefix from archive titles — the
 * matching context is already shown in the eyebrow kicker above the title.
 *
 * @param string $title Archive title markup.
 * @return string
 */
function sonnet_archive_title( $title ) {
	if ( is_category() || is_tag() || is_tax() ) {
		$title = single_term_title( '', false );
	} elseif ( is_author() ) {
		$title = get_the_author();
	} elseif ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );
	} elseif ( is_year() ) {
		$title = get_the_date( _x( 'Y', 'yearly archives date format', 'sonnet' ) );
	} elseif ( is_month() ) {
		$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'sonnet' ) );
	} elseif ( is_day() ) {
		$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'sonnet' ) );
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'sonnet_archive_title' );

/**
 * Trim the auto-excerpt to a tidy length for the reading list.
 *
 * @param int $length Default word count.
 * @return int
 */
function sonnet_excerpt_length( $length ) {
	return is_home() && ! is_paged() ? 44 : 30;
}
add_filter( 'excerpt_length', 'sonnet_excerpt_length', 999 );

/**
 * Replace the auto-excerpt ellipsis with a quiet typographic one.
 *
 * @param string $more Default "more" string.
 * @return string
 */
function sonnet_excerpt_more( $more ) {
	return '&#8202;&hellip;';
}
add_filter( 'excerpt_more', 'sonnet_excerpt_more' );

/**
 * Give the <body> a helpful class when the front page has a featured lead post,
 * so the masthead spacing can adapt.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function sonnet_body_classes( $classes ) {
	if ( is_home() && ! is_paged() ) {
		$classes[] = 'has-lead-essay';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'sonnet_body_classes' );

require_once get_template_directory() . '/inc/companions.php'; // Recommended companion plugins (admin one-click install).
