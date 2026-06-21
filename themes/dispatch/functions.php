<?php
/**
 * Dispatch theme setup and assets.
 *
 * @package Dispatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DISPATCH_VERSION' ) ) {
	define( 'DISPATCH_VERSION', '1.0.0' );
}

// Customizer: live color & style controls.
require_once get_template_directory() . '/inc/customizer.php';

if ( ! function_exists( 'dispatch_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function dispatch_setup() {
		load_theme_textdomain( 'dispatch', get_template_directory() . '/languages' );

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
			'navigation-widgets',
		) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'custom-background', array(
			'default-color' => 'f4f5f7',
		) );

		// Native WPAI companion-plugin placement. Declaring this support tells the
		// free companion plugins (Reading Time Badge, Contents, Kindred) that this
		// theme fires `wpai_entry_top` / `wpai_entry_bottom` action hooks around the
		// single-post article body — outside the prose column — so their output can
		// render at full article width instead of being injected into the_content.
		add_theme_support( 'wpai-companions' );

		// Image size for the homepage hero lead cover.
		add_image_size( 'dispatch-lead', 1360, 765, true );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'dispatch' ),
			'social'  => esc_html__( 'Footer Menu', 'dispatch' ),
		) );
	}
}
add_action( 'after_setup_theme', 'dispatch_setup' );

/**
 * Set the content width in pixels.
 */
function dispatch_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'dispatch_content_width', 760 );
}
add_action( 'after_setup_theme', 'dispatch_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function dispatch_assets() {
	wp_enqueue_style( 'dispatch-style', get_stylesheet_uri(), array(), DISPATCH_VERSION );

	wp_enqueue_script(
		'dispatch-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		DISPATCH_VERSION,
		true
	);

	// Motion system: staggered news-grid reveals, the live headline ticker
	// marquee, and scroll reveals. Deferred, footer-loaded, and fully gated
	// behind prefers-reduced-motion inside the script itself.
	wp_enqueue_script(
		'dispatch-motion',
		get_template_directory_uri() . '/assets/js/motion.js',
		array(),
		DISPATCH_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'dispatch_assets' );

/**
 * Add the `defer` attribute to Dispatch's footer scripts so they never block
 * paint. Uses the loader-tag filter for compatibility back to WP 5.0.
 *
 * @param string $tag    The full <script> tag.
 * @param string $handle The script's registered handle.
 * @return string
 */
function dispatch_defer_scripts( $tag, $handle ) {
	$deferred = array( 'dispatch-navigation', 'dispatch-motion' );

	if ( in_array( $handle, $deferred, true ) && false === strpos( $tag, ' defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'dispatch_defer_scripts', 10, 2 );

/**
 * Register the sidebar widget area.
 */
function dispatch_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'dispatch' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Widgets shown beside posts and pages.', 'dispatch' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'dispatch_widgets_init' );

/**
 * Use a clean hellip reading prompt instead of the default bracketed link.
 *
 * @param string $more The default "more" string.
 * @return string
 */
function dispatch_excerpt_more( $more ) {
	if ( is_admin() ) {
		return $more;
	}
	return '&hellip;';
}
add_filter( 'excerpt_more', 'dispatch_excerpt_more' );

/**
 * A slightly tighter excerpt suits the scannable news layout.
 *
 * @param int $length Default excerpt length in words.
 * @return int
 */
function dispatch_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return 28;
}
add_filter( 'excerpt_length', 'dispatch_excerpt_length' );

/**
 * Print human-readable post meta (byline + date).
 */
if ( ! function_exists( 'dispatch_posted_meta' ) ) {
	function dispatch_posted_meta() {
		$time = sprintf(
			'<time class="entry-meta__date" datetime="%1$s">%2$s</time>',
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);

		printf(
			/* translators: 1: post author, 2: post date */
			'<span class="entry-meta__byline">' . esc_html__( 'By %1$s', 'dispatch' ) . '</span> <span class="entry-meta__sep" aria-hidden="true">&middot;</span> %2$s',
			'<span class="author">' . esc_html( get_the_author() ) . '</span>',
			$time // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts above.
		);
	}
}

/**
 * Deterministic category color for the color-coding system.
 *
 * Every category maps to a stable hue derived from its term ID, so each
 * section reads in its own consistent color across the whole site. The first
 * category in the palette wheel hits the theme's red accent, the rest fan out
 * across a tasteful set of newsroom hues.
 *
 * @param int $term_id Category term ID.
 * @return string An hsl() color string.
 */
if ( ! function_exists( 'dispatch_cat_color' ) ) {
	function dispatch_cat_color( $term_id ) {
		$term_id = (int) $term_id;
		if ( $term_id <= 0 ) {
			return 'var(--d-accent)';
		}

		// A curated set of hues that all read clearly as section colors against
		// the light canvas and as solid pills with white text.
		$hues = array( 354, 210, 158, 28, 268, 190, 120, 330 );
		$hue  = $hues[ $term_id % count( $hues ) ];

		// Slightly vary saturation/lightness per term so adjacent IDs differ.
		$sat  = 62 + ( $term_id % 3 ) * 6; // 62–74%
		$lig  = 45 + ( $term_id % 2 ) * 3; // 45–48%

		return sprintf( 'hsl(%1$d %2$d%% %3$d%%)', $hue, $sat, $lig );
	}
}

/**
 * Print the post's primary category as a color-coded tag link. The element
 * carries an inline --d-cat custom property so its pill (and the cover rail /
 * placeholder tint) all share the category's color.
 *
 * @param bool $echo Whether to print (true) or return (false) the markup.
 * @return string
 */
if ( ! function_exists( 'dispatch_category_tag' ) ) {
	function dispatch_category_tag( $echo = true ) {
		if ( 'post' !== get_post_type() ) {
			return '';
		}

		$categories = get_the_category();
		if ( empty( $categories ) ) {
			return '';
		}

		$category = $categories[0];
		$markup   = sprintf(
			'<a class="entry-cat" style="--d-cat:%1$s" href="%2$s">%3$s</a>',
			esc_attr( dispatch_cat_color( $category->term_id ) ),
			esc_url( get_category_link( $category->term_id ) ),
			esc_html( $category->name )
		);

		if ( $echo ) {
			echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts above.
		}

		return $markup;
	}
}

/**
 * The inline --d-cat value for the current post, so a card / cover can be
 * tinted by its category even where the pill itself is not printed.
 *
 * @return string CSS custom-property declaration, or empty string.
 */
if ( ! function_exists( 'dispatch_cat_style' ) ) {
	function dispatch_cat_style() {
		if ( 'post' !== get_post_type() ) {
			return '';
		}
		$categories = get_the_category();
		if ( empty( $categories ) ) {
			return '';
		}
		return '--d-cat:' . dispatch_cat_color( $categories[0]->term_id ) . ';';
	}
}

/**
 * Output a featured image, or a graceful category-tinted placeholder when a
 * post has none, so the grid and single never break their rhythm.
 *
 * @param string $size  Image size handle.
 * @param bool   $link  Wrap the image in a permalink (used on archives).
 * @param bool   $eager Load the image eagerly with high fetch priority. Used
 *                      for the above-the-fold hero cover on the blog home so it
 *                      paints immediately instead of lazy-loading.
 */
if ( ! function_exists( 'dispatch_featured_media' ) ) {
	function dispatch_featured_media( $size = 'large', $link = false, $eager = false ) {
		$has_image = has_post_thumbnail();

		$classes  = 'entry-media';
		$classes .= $has_image ? ' has-image' : ' is-placeholder';

		$style = dispatch_cat_style();

		$inner = '';
		if ( $has_image ) {
			$load_eager = ( $eager || is_singular() );

			$img_attr = array(
				'class'    => 'entry-media__img',
				'loading'  => $load_eager ? 'eager' : 'lazy',
				'decoding' => 'async',
			);

			// The above-the-fold hero needs high fetch priority so it paints
			// first; everything else stays lazy with default priority.
			if ( $eager ) {
				$img_attr['fetchpriority'] = 'high';
			}

			$inner = get_the_post_thumbnail( null, $size, $img_attr );
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
 * Render the signature headline ticker — a live strip of the latest stories.
 *
 * Output is duplicated once so the marquee can loop seamlessly (motion.js
 * translates the track by -50%). Without JS or with reduced motion, the strip
 * is simply a static, scrollable list of links — fully functional and
 * accessible either way.
 *
 * Shown on the blog home only, just under the masthead.
 */
if ( ! function_exists( 'dispatch_ticker' ) ) {
	function dispatch_ticker() {
		$latest = get_posts( array(
			'numberposts'         => 6,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
		) );

		if ( empty( $latest ) ) {
			return;
		}

		$items = '';
		// Use a local variable (not $post) so we never clobber the global $post,
		// which is read by template tags later in the page after the header.
		foreach ( $latest as $latest_post ) {
			$items .= sprintf(
				'<a class="ticker__item" href="%1$s">%2$s</a>',
				esc_url( get_permalink( $latest_post ) ),
				esc_html( get_the_title( $latest_post ) )
			);
		}
		?>
		<div class="ticker" aria-label="<?php esc_attr_e( 'Latest headlines', 'dispatch' ); ?>">
			<div class="site-wrap ticker__inner">
				<span class="ticker__tag">
					<span aria-hidden="true"><?php esc_html_e( 'Latest', 'dispatch' ); ?></span>
				</span>
				<div class="ticker__viewport">
					<div class="ticker__track">
						<?php
						// First copy is the real, readable content.
						echo $items; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts above.
						// Second copy is decorative, only used to make the marquee loop.
						printf( '<span aria-hidden="true" data-dispatch-ticker-clone>%s</span>', $items ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- duplicate of escaped markup above.
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

/**
 * Add helpful context classes to <body>.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function dispatch_body_classes( $classes ) {
	if ( is_home() || is_front_page() ) {
		$classes[] = 'dispatch-home';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'dispatch-no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'dispatch_body_classes' );

require_once get_template_directory() . '/inc/companions.php'; // Recommended companion plugins (admin one-click install).
