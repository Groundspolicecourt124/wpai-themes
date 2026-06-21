<?php
/**
 * Orbit theme setup and assets.
 *
 * @package Orbit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ORBIT_VERSION' ) ) {
	define( 'ORBIT_VERSION', '1.0.0' );
}

// Customizer: live color & style controls.
require_once get_template_directory() . '/inc/customizer.php';

if ( ! function_exists( 'orbit_setup' ) ) {
	/**
	 * Register theme supports and nav menus.
	 */
	function orbit_setup() {
		load_theme_textdomain( 'orbit', get_template_directory() . '/languages' );

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
			'default-color' => '070b16',
		) );

		// Native WPAI companion-plugin placement. Declaring this support tells the
		// free companion plugins (Reading Time Badge, Contents, Kindred) that this
		// theme fires `wpai_entry_top` / `wpai_entry_bottom` action hooks around the
		// single-post article body — outside the prose column — so their output can
		// render at full article width instead of being injected into the_content.
		add_theme_support( 'wpai-companions' );

		// Image size for the homepage lead story cover.
		add_image_size( 'orbit-lead', 1320, 760, true );

		register_nav_menus( array(
			'primary' => esc_html__( 'Primary Menu', 'orbit' ),
			'social'  => esc_html__( 'Footer Menu', 'orbit' ),
		) );
	}
}
add_action( 'after_setup_theme', 'orbit_setup' );

/**
 * Set the content width in pixels.
 */
function orbit_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'orbit_content_width', 760 );
}
add_action( 'after_setup_theme', 'orbit_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function orbit_assets() {
	wp_enqueue_style( 'orbit-style', get_stylesheet_uri(), array(), ORBIT_VERSION );

	wp_enqueue_script(
		'orbit-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		ORBIT_VERSION,
		true
	);

	// Motion system: scroll reveals, staggered cards, the magnetic CTA, the
	// drifting starfield/orbit hero, the count-up metrics, and the reading
	// progress bar. Deferred, footer-loaded, and fully gated behind
	// prefers-reduced-motion inside the script itself.
	wp_enqueue_script(
		'orbit-motion',
		get_template_directory_uri() . '/assets/js/motion.js',
		array(),
		ORBIT_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'orbit_assets' );

/**
 * Add the `defer` attribute to Orbit's footer scripts so they never block
 * paint. Uses the loader-tag filter for compatibility back to WP 5.0.
 *
 * @param string $tag    The full <script> tag.
 * @param string $handle The script's registered handle.
 * @return string
 */
function orbit_defer_scripts( $tag, $handle ) {
	$deferred = array( 'orbit-navigation', 'orbit-motion' );

	if ( in_array( $handle, $deferred, true ) && false === strpos( $tag, ' defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'orbit_defer_scripts', 10, 2 );

/**
 * Register the sidebar widget area.
 */
function orbit_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'orbit' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Widgets shown beside posts and pages.', 'orbit' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'orbit_widgets_init' );

/**
 * Use a clean ellipsis reading prompt instead of the default bracketed link.
 *
 * @param string $more The default "more" string.
 * @return string
 */
function orbit_excerpt_more( $more ) {
	if ( is_admin() ) {
		return $more;
	}
	return '&hellip;';
}
add_filter( 'excerpt_more', 'orbit_excerpt_more' );

/**
 * A slightly shorter, punchier excerpt suits the product layout.
 *
 * @param int $length Default excerpt length in words.
 * @return int
 */
function orbit_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return 28;
}
add_filter( 'excerpt_length', 'orbit_excerpt_length' );

/**
 * Print human-readable post meta (byline + date).
 */
if ( ! function_exists( 'orbit_posted_meta' ) ) {
	function orbit_posted_meta() {
		$time = sprintf(
			'<time class="entry-meta__date" datetime="%1$s">%2$s</time>',
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);

		printf(
			/* translators: 1: post author, 2: post date */
			'<span class="entry-meta__byline">' . esc_html__( 'by %1$s', 'orbit' ) . '</span> <span class="entry-meta__sep" aria-hidden="true">&middot;</span> %2$s',
			'<span class="author">' . esc_html( get_the_author() ) . '</span>',
			$time // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped parts above.
		);
	}
}

/**
 * Print the post's primary category as a pill link. Falls back to nothing
 * when the post has no category (e.g. a custom post type).
 */
if ( ! function_exists( 'orbit_category_pill' ) ) {
	function orbit_category_pill() {
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
 * Print Orbit's brand mark — a small orbiting glyph (a planet on a ring).
 *
 * Decorative, so hidden from assistive tech. Used in the header beside the
 * wordmark and in the footer brand line.
 */
if ( ! function_exists( 'orbit_mark' ) ) {
	function orbit_mark() {
		echo '<span class="site-mark" aria-hidden="true">';
		echo '<svg viewBox="0 0 24 24" fill="none" focusable="false">';
		echo '<ellipse cx="12" cy="12" rx="10" ry="4.5" stroke="currentColor" stroke-width="1.6" transform="rotate(-28 12 12)"/>';
		echo '<circle cx="12" cy="12" r="3.4" fill="currentColor"/>';
		echo '<circle cx="20" cy="7.2" r="1.5" fill="currentColor"/>';
		echo '</svg>';
		echo '</span>';
	}
}

/**
 * Output a featured image, or a graceful gradient placeholder when a post has
 * none, so the index and single never break their rhythm.
 *
 * @param string $size  Image size handle.
 * @param bool   $link  Wrap the image in a permalink (used on archives).
 * @param bool   $eager Load the image eagerly with high fetch priority. Used
 *                      for the above-the-fold lead cover on the blog home.
 */
if ( ! function_exists( 'orbit_featured_media' ) ) {
	function orbit_featured_media( $size = 'large', $link = false, $eager = false ) {
		$has_image = has_post_thumbnail();

		// Deterministic hue from the post ID so placeholders feel intentional.
		// Bias toward the cyan/teal/blue/violet end of the wheel for the dark
		// theme's nebula placeholders.
		$hue   = 150 + ( (int) ( get_the_ID() * 37 ) % 130 );
		$style = sprintf(
			'--orbit-hue:%1$d;',
			$hue
		);

		$classes  = 'entry-media';
		$classes .= $has_image ? ' has-image' : ' is-placeholder';

		$inner = '';
		if ( $has_image ) {
			$load_eager = ( $eager || is_singular() );

			$img_attr = array(
				'class'    => 'entry-media__img',
				'loading'  => $load_eager ? 'eager' : 'lazy',
				'decoding' => 'async',
			);

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
 * Render the homepage hero: a starfield/orbit launch panel.
 *
 * Pulls its headline from the site tagline so it stays editable, but ships a
 * confident default if none is set. The drifting orbit rings and starfield are
 * inline SVG (no images, no network) and animate purely via CSS transforms,
 * disabled under prefers-reduced-motion.
 */
if ( ! function_exists( 'orbit_render_hero' ) ) {
	function orbit_render_hero() {
		$tagline = get_bloginfo( 'description', 'display' );
		$lead    = $tagline ? $tagline : __( 'The developer platform that turns ideas into shipping product — fast, observable, and built to scale from the first commit.', 'orbit' );
		?>
		<section class="hero" aria-labelledby="hero-title" data-orbit-reveal>
			<div class="hero__field" aria-hidden="true">
				<svg viewBox="0 0 600 400" preserveAspectRatio="xMidYMid slice" data-orbit-starfield></svg>
			</div>

			<div class="hero__orbits" aria-hidden="true">
				<svg viewBox="-150 -150 300 300">
					<g class="hero__orbit-group hero__orbit-group--1">
						<circle class="hero__orbit-ring" cx="0" cy="0" r="135" />
						<circle class="hero__planet" cx="135" cy="0" r="5" />
					</g>
					<g class="hero__orbit-group hero__orbit-group--2">
						<circle class="hero__orbit-ring" cx="0" cy="0" r="98" />
						<circle class="hero__planet hero__planet--b" cx="-98" cy="0" r="6" />
					</g>
					<g class="hero__orbit-group hero__orbit-group--3">
						<circle class="hero__orbit-ring" cx="0" cy="0" r="60" />
						<circle class="hero__planet" cx="0" cy="-60" r="4" />
					</g>
				</svg>
			</div>

			<div class="hero__inner">
				<p class="hero__badge">
					<span class="hero__badge-dot" aria-hidden="true"></span>
					<?php esc_html_e( 'v1.0 is live', 'orbit' ); ?>
				</p>
				<h1 class="hero__title" id="hero-title">
					<?php
					printf(
						/* translators: %s: site name. */
						esc_html__( 'Ship faster with %s', 'orbit' ),
						'<span class="hero__grad">' . esc_html( get_bloginfo( 'name' ) ) . '</span>'
					);
					?>
				</h1>
				<p class="hero__lead"><?php echo esc_html( $lead ); ?></p>
				<div class="hero__actions">
					<span class="cta-magnetic" data-orbit-magnetic>
						<a class="button" href="#latest"><?php esc_html_e( 'Read the changelog', 'orbit' ); ?></a>
					</span>
					<a class="button button--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Explore the docs', 'orbit' ); ?></a>
				</div>
			</div>
		</section>
		<?php
	}
}

/**
 * Render the count-up metrics strip beneath the hero.
 *
 * The numeric values live in data-attributes; motion.js counts each one up
 * from zero when it scrolls into view. Without JS (or with reduced motion) the
 * final number is rendered in the markup, so it is always correct and visible.
 */
if ( ! function_exists( 'orbit_render_metrics' ) ) {
	function orbit_render_metrics() {
		$metrics = array(
			array(
				'value'  => '99.99',
				'suffix' => '%',
				'label'  => __( 'Uptime SLA', 'orbit' ),
			),
			array(
				'value'  => '40',
				'suffix' => 'ms',
				'label'  => __( 'Median deploy', 'orbit' ),
			),
			array(
				'value'  => '12',
				'suffix' => 'k',
				'label'  => __( 'Teams shipping', 'orbit' ),
			),
			array(
				'value'  => '2.4',
				'suffix' => 'M',
				'label'  => __( 'Builds per week', 'orbit' ),
			),
		);
		?>
		<section class="metrics" aria-label="<?php esc_attr_e( 'Key metrics', 'orbit' ); ?>" data-orbit-reveal data-orbit-stagger>
			<?php foreach ( $metrics as $metric ) : ?>
				<div class="metric">
					<div class="metric__value">
						<span class="metric__num" data-orbit-count="<?php echo esc_attr( $metric['value'] ); ?>"><?php echo esc_html( $metric['value'] ); ?></span><span class="metric__suffix"><?php echo esc_html( $metric['suffix'] ); ?></span>
					</div>
					<p class="metric__label"><?php echo esc_html( $metric['label'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</section>
		<?php
	}
}

/**
 * Render the three-up feature grid that frames the product.
 */
if ( ! function_exists( 'orbit_render_features' ) ) {
	function orbit_render_features() {
		// Each feature carries a small inline SVG icon (no external assets).
		$features = array(
			array(
				'title' => __( 'Instant previews', 'orbit' ),
				'text'  => __( 'Every push spins up a live, shareable environment in seconds. Review real product, not a static mock.', 'orbit' ),
				'icon'  => '<path d="M3 5h18v12H3z" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linejoin="round"/><path d="M8 21h8M12 17v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
			),
			array(
				'title' => __( 'Observability built in', 'orbit' ),
				'text'  => __( 'Traces, logs, and metrics ship with the runtime. Find the slow span before your users do.', 'orbit' ),
				'icon'  => '<path d="M3 14l4-5 4 4 5-7 5 6" stroke="currentColor" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 19h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
			),
			array(
				'title' => __( 'Scales on autopilot', 'orbit' ),
				'text'  => __( 'From the first commit to ten million requests, the platform grows with you — no infra babysitting.', 'orbit' ),
				'icon'  => '<circle cx="12" cy="12" r="3" fill="currentColor"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
			),
		);
		?>
		<section class="orbit-features" aria-labelledby="features-title" data-orbit-reveal>
			<div class="section-head">
				<p class="section-head__kicker">// <?php esc_html_e( 'Why teams switch', 'orbit' ); ?></p>
				<h2 class="section-head__title" id="features-title"><?php esc_html_e( 'Everything you need to launch and keep shipping', 'orbit' ); ?></h2>
			</div>
			<div class="feature-grid" data-orbit-stagger>
				<?php foreach ( $features as $feature ) : ?>
					<div class="feature">
						<span class="feature__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" focusable="false"><?php echo $feature['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG markup defined above. ?></svg>
						</span>
						<h3 class="feature__title"><?php echo esc_html( $feature['title'] ); ?></h3>
						<p class="feature__text"><?php echo esc_html( $feature['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}

/**
 * Add helpful context classes to <body>.
 *
 * @param array $classes Existing body classes.
 * @return array
 */
function orbit_body_classes( $classes ) {
	if ( is_home() || is_front_page() ) {
		$classes[] = 'orbit-home';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'orbit-no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'orbit_body_classes' );

require_once get_template_directory() . '/inc/companions.php'; // Recommended companion plugins (admin one-click install).
