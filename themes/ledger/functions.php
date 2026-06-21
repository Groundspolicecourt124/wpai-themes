<?php
/**
 * Ledger theme setup and assets.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customizer color & style controls with live preview.
 */
require_once get_template_directory() . '/inc/customizer.php';

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
		add_editor_style( 'style.css' );

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
 * Print human-readable post meta (author + date) as a small-caps byline.
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
 * Return the primary category for the current post, or false.
 *
 * @return WP_Term|false
 */
if ( ! function_exists( 'ledger_primary_category' ) ) {
	function ledger_primary_category() {
		$categories = get_the_category();

		if ( empty( $categories ) ) {
			return false;
		}

		return $categories[0];
	}
}

/**
 * Print the primary category as a kicker above the headline.
 *
 * @param string $class Extra class for the kicker element.
 */
if ( ! function_exists( 'ledger_post_kicker' ) ) {
	function ledger_post_kicker( $class = 'entry-kicker' ) {
		$category = ledger_primary_category();

		if ( ! $category ) {
			return;
		}

		printf(
			'<p class="%1$s"><a href="%2$s">%3$s</a></p>',
			esc_attr( $class ),
			esc_url( get_category_link( $category->term_id ) ),
			esc_html( $category->name )
		);
	}
}

/**
 * Trim the auto-excerpt to a tighter, magazine-friendly length.
 *
 * @param int $length Word count.
 * @return int
 */
function ledger_excerpt_length( $length ) {
	return 28;
}
add_filter( 'excerpt_length', 'ledger_excerpt_length', 999 );

/**
 * Replace the default [...] excerpt tail with a clean ellipsis.
 *
 * @param string $more Excerpt suffix.
 * @return string
 */
function ledger_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'ledger_excerpt_more' );

/**
 * Render the full-width lead story used at the top of the front page.
 *
 * Expects to be called inside the loop, after the_post().
 */
if ( ! function_exists( 'ledger_render_lead_story' ) ) {
	function ledger_render_lead_story() {
		$has_image = has_post_thumbnail();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'lead-story' . ( $has_image ? '' : ' lead-story--noimg' ) ); ?>>
			<?php if ( $has_image ) : ?>
				<figure class="lead-story__media">
					<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
						<?php
						// Lead story is above the fold: load eagerly and prioritize
						// so it paints immediately instead of lazy-loading.
						the_post_thumbnail( 'large', array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'async',
						) );
						?>
					</a>
				</figure>
			<?php endif; ?>

			<div class="lead-story__body">
				<?php ledger_post_kicker( 'lead-kicker' ); ?>
				<?php the_title( '<h2 class="lead-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
				<div class="entry-meta lead-meta"><?php ledger_posted_meta(); ?></div>
				<p class="lead-standfirst"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<a class="read-more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read the lead story', 'ledger' ); ?></a>
			</div>
		</article>
		<?php
	}
}

/**
 * Render a secondary story card used in the front-page grid.
 *
 * Expects to be called inside the loop, after the_post().
 */
if ( ! function_exists( 'ledger_render_story_card' ) ) {
	function ledger_render_story_card() {
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'story-card' ); ?>>
			<?php if ( has_post_thumbnail() ) : ?>
				<a class="story-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
					<?php the_post_thumbnail( 'medium_large' ); ?>
				</a>
			<?php endif; ?>
			<?php ledger_post_kicker(); ?>
			<?php the_title( '<h3 class="story-card__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' ); ?>
			<div class="entry-meta"><?php ledger_posted_meta(); ?></div>
			<p class="story-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<a class="read-more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Continue reading', 'ledger' ); ?></a>
		</article>
		<?php
	}
}
