<?php
/**
 * Main template — the loop.
 *
 * On the garden home, the newest note becomes a featured "seedling" and the
 * remaining notes fall into a tidy bed of cards. Other archives use the bed.
 *
 * @package Almanac
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$almanac_is_garden_home = ( is_home() && ! is_paged() );
?>

<?php
/*
 * On a static-front-page setup the dedicated Posts page renders no <h1>
 * (the header prints the site title as a <p>, and this template has no
 * archive/search header), so provide a screen-reader heading to keep every
 * primary view anchored by exactly one h1.
 */
if ( is_home() && ! is_front_page() ) :
	$almanac_blog_title = single_post_title( '', false );
	if ( ! $almanac_blog_title ) {
		$almanac_blog_title = __( 'Latest notes', 'almanac' );
	}
	?>
	<h1 class="screen-reader-text"><?php echo esc_html( $almanac_blog_title ); ?></h1>
	<?php
endif;
?>

<?php if ( $almanac_is_garden_home ) : ?>
	<section class="garden-intro" aria-label="<?php esc_attr_e( 'Welcome', 'almanac' ); ?>" data-alm-reveal>
		<p class="garden-intro__kicker"><?php esc_html_e( 'A digital garden', 'almanac' ); ?></p>
		<p class="garden-intro__line" data-alm-words>
			<span class="garden-intro__text"><?php
				$almanac_intro = get_bloginfo( 'description', 'display' );
				echo esc_html( $almanac_intro ? $almanac_intro : __( 'Notes I tend in the open — interlinked, evergreen, always growing.', 'almanac' ) );
			?></span>
			<?php almanac_sprout( 'intro' ); ?>
		</p>
		<p class="garden-intro__note">
			<?php
			printf(
				/* translators: %s: a link to the tags/archive. */
				esc_html__( 'Every note is stamped with when it was planted and last tended. Follow the %s to wander.', 'almanac' ),
				'<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'threads', 'almanac' ) . '</a>'
			);
			?>
		</p>
	</section>
<?php endif; ?>

<?php if ( have_posts() ) : ?>

	<?php if ( is_search() ) : ?>
		<header class="page-header" data-alm-reveal>
			<h1 class="page-header__title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Search: %s', 'almanac' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>
	<?php elseif ( is_archive() ) : ?>
		<header class="page-header" data-alm-reveal>
			<?php the_archive_title( '<h1 class="page-header__title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="page-header__desc">', '</div>' ); ?>
		</header>
	<?php endif; ?>

	<div class="layout">
		<div class="layout__main">
			<?php
			$almanac_index = 0;

			while ( have_posts() ) :
				the_post();

				// The newest note on the garden home becomes the featured seedling.
				$is_lead = ( $almanac_is_garden_home && 0 === $almanac_index );

				// A quiet "bed" label introduces the card list under the seedling.
				if ( $almanac_is_garden_home && 1 === $almanac_index ) :
					?>
					<div class="bed-label" data-alm-reveal>
						<h2 class="bed-label__title"><?php esc_html_e( 'More from the garden', 'almanac' ); ?></h2>
						<span class="bed-label__rule" aria-hidden="true"></span>
					</div>
					<?php
				endif;

				set_query_var( 'almanac_is_lead', $is_lead );
				get_template_part( 'template-parts/content', get_post_type() );

				$almanac_index++;
			endwhile;
			?>
		</div><!-- .layout__main -->

		<?php get_sidebar(); ?>
	</div><!-- .layout -->

	<?php
	the_posts_pagination( array(
		'mid_size'           => 1,
		'prev_text'          => esc_html__( 'Newer', 'almanac' ),
		'next_text'          => esc_html__( 'Older', 'almanac' ),
		'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'almanac' ) . ' </span>',
	) );
	?>

<?php else : ?>

	<div class="layout">
		<div class="layout__main">
			<article class="entry entry--empty">
				<h2 class="entry-title"><?php esc_html_e( 'Nothing planted here yet', 'almanac' ); ?></h2>
				<p class="entry-content"><?php esc_html_e( 'This patch of the garden is still bare. Try a search, or come back when something has grown.', 'almanac' ); ?></p>
				<?php get_search_form(); ?>
			</article>
		</div>
		<?php get_sidebar(); ?>
	</div>

<?php endif; ?>

<?php
get_footer();
