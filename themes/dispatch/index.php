<?php
/**
 * Main template — the loop.
 *
 * On the blog home, the newest post becomes a full hero lead beside a column
 * of secondary stories; the remaining posts fall into a two-up card river.
 * Other archives use the card-river form.
 *
 * @package Dispatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$dispatch_is_blog_home = ( is_home() && ! is_paged() );
?>

<?php if ( have_posts() ) : ?>

	<?php if ( is_search() ) : ?>
		<header class="page-header" data-dispatch-reveal>
			<h1 class="page-header__title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Search: %s', 'dispatch' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>
	<?php elseif ( is_archive() ) : ?>
		<header class="page-header" data-dispatch-reveal>
			<?php the_archive_title( '<h1 class="page-header__title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="page-header__desc">', '</div>' ); ?>
		</header>
	<?php endif; ?>

	<div class="layout">
		<div class="layout__main">
			<?php
			$dispatch_index = 0;

			if ( $dispatch_is_blog_home ) :
				// --- News grid: hero lead + a column of secondary stories ------
				$dispatch_secondary_count = 3;
				?>
				<section class="news-grid" aria-label="<?php esc_attr_e( 'Top stories', 'dispatch' ); ?>">
					<?php
					while ( have_posts() && $dispatch_index <= $dispatch_secondary_count ) :
						the_post();

						if ( 0 === $dispatch_index ) :
							set_query_var( 'dispatch_mode', 'lead' );
							get_template_part( 'template-parts/content', get_post_type() );
							echo '<div class="news-grid__secondary">';
						else :
							set_query_var( 'dispatch_mode', 'secondary' );
							get_template_part( 'template-parts/content', get_post_type() );
						endif;

						$dispatch_index++;
					endwhile;

					// Close the secondary column wrapper if it was opened. The
					// opening div is emitted right after the lead renders (when the
					// index becomes 1), so it must be closed whenever the lead ran —
					// even if no secondary stories followed (e.g. a one-post home).
					if ( $dispatch_index >= 1 ) {
						echo '</div><!-- .news-grid__secondary -->';
					}
					?>
				</section>

				<?php if ( have_posts() ) : ?>
					<div class="section-kicker">
						<span class="section-kicker__label"><?php esc_html_e( 'More stories', 'dispatch' ); ?></span>
						<span class="section-kicker__rule" aria-hidden="true"></span>
					</div>

					<div class="entry-river">
						<?php
						while ( have_posts() ) :
							the_post();
							set_query_var( 'dispatch_mode', 'list' );
							get_template_part( 'template-parts/content', get_post_type() );
						endwhile;
						?>
					</div><!-- .entry-river -->
				<?php endif; ?>

			<?php else : // Archives / paged: a clean two-up card river. ?>

				<div class="entry-river">
					<?php
					while ( have_posts() ) :
						the_post();
						set_query_var( 'dispatch_mode', 'list' );
						get_template_part( 'template-parts/content', get_post_type() );
					endwhile;
					?>
				</div><!-- .entry-river -->

			<?php endif; ?>
		</div><!-- .layout__main -->

		<?php get_sidebar(); ?>
	</div><!-- .layout -->

	<?php
	the_posts_pagination( array(
		'mid_size'           => 1,
		'prev_text'          => esc_html__( 'Newer', 'dispatch' ),
		'next_text'          => esc_html__( 'Older', 'dispatch' ),
		'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'dispatch' ) . ' </span>',
	) );
	?>

<?php else : ?>

	<div class="layout">
		<div class="layout__main">
			<article class="entry entry--empty">
				<h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'dispatch' ); ?></h2>
				<p class="entry-content"><?php esc_html_e( 'It looks like nothing was found in this spot. Perhaps a search will help?', 'dispatch' ); ?></p>
				<?php get_search_form(); ?>
			</article>
		</div>
		<?php get_sidebar(); ?>
	</div>

<?php endif; ?>

<?php
get_footer();
