<?php
/**
 * Main template — the loop.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$nimbus_show_hero = ( is_home() || is_front_page() ) && ! is_paged();

if ( $nimbus_show_hero ) :
	$nimbus_tagline = get_bloginfo( 'description', 'display' );

	// Hero stat figures. The published-post count is real; the two SaaS-flavored
	// figures are theme-supplied defaults a site owner can tune. They power the
	// count-up motion and are fully static/visible if JS is off.
	$nimbus_post_count = (int) wp_count_posts()->publish;
	$nimbus_stats      = array(
		array(
			'to'       => max( $nimbus_post_count, 1 ),
			'decimals' => 0,
			'suffix'   => '',
			'label'    => esc_html__( 'Stories published', 'nimbus' ),
		),
		array(
			'to'       => 99.9,
			'decimals' => 1,
			'suffix'   => '%',
			'label'    => esc_html__( 'Uptime, shipped daily', 'nimbus' ),
		),
		array(
			'to'       => 12000,
			'decimals' => 0,
			'suffix'   => '+',
			'label'    => esc_html__( 'Teams moving faster', 'nimbus' ),
		),
	);
	?>
	<section class="nimbus-hero">
		<div class="nimbus-hero__mesh" aria-hidden="true">
			<span class="nimbus-hero__blob nimbus-hero__blob--1"></span>
			<span class="nimbus-hero__blob nimbus-hero__blob--2"></span>
			<span class="nimbus-hero__blob nimbus-hero__blob--3"></span>
		</div>
		<div class="nimbus-hero__inner">
			<p class="nimbus-hero__eyebrow nm-reveal"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
			<h1 class="nimbus-hero__title nm-reveal">
				<?php
				if ( $nimbus_tagline ) {
					echo esc_html( $nimbus_tagline );
				} else {
					printf(
						/* translators: %s: highlighted phrase */
						esc_html__( 'Build something %s.', 'nimbus' ),
						'<span class="accent">' . esc_html__( 'people love', 'nimbus' ) . '</span>'
					);
				}
				?>
			</h1>
			<p class="nimbus-hero__sub nm-reveal">
				<?php esc_html_e( 'A bright, fast home for your product and your writing. Browse the latest below.', 'nimbus' ); ?>
			</p>
			<div class="nimbus-hero__cta nm-reveal">
				<a class="button nm-magnetic" href="#latest" data-nm-strength="0.4">
					<span class="nm-magnetic__label"><?php esc_html_e( 'Read the latest', 'nimbus' ); ?></span>
				</a>
				<?php get_search_form(); ?>
			</div>

			<dl class="nimbus-stats nm-reveal" data-nm-delay="120">
				<?php foreach ( $nimbus_stats as $nimbus_stat ) : ?>
					<div class="nimbus-stat">
						<dt class="nimbus-stat__value">
							<?php
								// Render the FINAL value server-side so no-JS / pre-paint users see
								// the real figure (not a stuck "0"). When motion runs, JS resets this
								// to 0 and counts up to data-count-to.
								$nimbus_stat_final = number_format_i18n( (float) $nimbus_stat['to'], (int) $nimbus_stat['decimals'] );
							?>
							<span
								class="nimbus-stat__num"
								data-count-to="<?php echo esc_attr( $nimbus_stat['to'] ); ?>"
								data-count-decimals="<?php echo esc_attr( $nimbus_stat['decimals'] ); ?>"
							><?php echo esc_html( $nimbus_stat_final ); ?></span><?php
							if ( '' !== $nimbus_stat['suffix'] ) {
								echo '<span class="nimbus-stat__suffix">' . esc_html( $nimbus_stat['suffix'] ) . '</span>';
							}
							?>
						</dt>
						<dd class="nimbus-stat__label"><?php echo esc_html( $nimbus_stat['label'] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>
	</section>
	<?php
endif;

if ( have_posts() ) :

	if ( is_home() && ! is_front_page() && ! $nimbus_show_hero ) :
		?>
		<header class="page-header">
			<h1 class="entry-title"><?php single_post_title(); ?></h1>
		</header>
		<?php
	elseif ( is_search() ) :
		?>
		<header class="page-header">
			<h1 class="entry-title">
				<?php
				printf(
					/* translators: %s: search query */
					esc_html__( 'Results for “%s”', 'nimbus' ),
					'<span class="accent">' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>
		<?php
	elseif ( is_archive() ) :
		?>
		<header class="page-header">
			<?php the_archive_title( '<h1 class="entry-title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
		</header>
		<?php
	endif;

	if ( ! is_archive() && ! is_search() ) :
		?>
		<p class="section-eyebrow nm-reveal" id="latest"><?php esc_html_e( 'Latest writing', 'nimbus' ); ?></p>
		<?php
	endif;
	?>

	<div class="post-grid">
		<?php
		$nimbus_i = 0;
		while ( have_posts() ) :
			the_post();
			// The first post on the first page becomes a full-width lead feature.
			add_filter( 'post_class', 'nimbus_feature_class' );
			$GLOBALS['nimbus_feature'] = ( 0 === $nimbus_i && ! is_paged() );
			get_template_part( 'template-parts/content', get_post_type() );
			remove_filter( 'post_class', 'nimbus_feature_class' );
			$nimbus_i++;
		endwhile;
		unset( $GLOBALS['nimbus_feature'] );
		?>
	</div>

	<?php
	the_posts_navigation( array(
		'prev_text' => esc_html__( 'Older posts', 'nimbus' ),
		'next_text' => esc_html__( 'Newer posts', 'nimbus' ),
	) );

else :
	?>
	<article class="entry">
		<div class="entry__pad">
			<h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'nimbus' ); ?></h2>
			<p><?php esc_html_e( 'It looks like nothing was found. Try a search?', 'nimbus' ); ?></p>
			<?php get_search_form(); ?>
		</div>
	</article>
	<?php
endif;

get_sidebar();
get_footer();
