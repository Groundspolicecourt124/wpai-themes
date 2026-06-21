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
	?>
	<section class="nimbus-hero">
		<div class="nimbus-hero__inner">
			<p class="nimbus-hero__eyebrow"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
			<h1 class="nimbus-hero__title">
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
			<p class="nimbus-hero__sub">
				<?php esc_html_e( 'A bright, fast home for your product and your writing. Browse the latest below.', 'nimbus' ); ?>
			</p>
			<div class="nimbus-hero__cta">
				<a class="button" href="#latest"><?php esc_html_e( 'Read the latest', 'nimbus' ); ?></a>
				<?php get_search_form(); ?>
			</div>
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
		<p class="section-eyebrow" id="latest"><?php esc_html_e( 'Latest writing', 'nimbus' ); ?></p>
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
