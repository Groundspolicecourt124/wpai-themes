<?php
/**
 * Post / page content partial.
 *
 * Two presentations:
 *  - Index/archive: a brutalist "project card" (cover, category, title, byline, excerpt).
 *  - Singular: a full single post / page (kicker, large featured image, body, footer).
 *
 * @package Monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$monolith_is_page = is_page();

/* -------------------------------------------------------------------------
 * INDEX / ARCHIVE — project card
 * ---------------------------------------------------------------------- */
if ( ! is_singular() ) :

	$monolith_index = (int) get_query_var( 'monolith_index' );
	$monolith_cat   = '';
	if ( 'post' === get_post_type() ) {
		$monolith_cats = get_the_category();
		if ( ! empty( $monolith_cats ) ) {
			$monolith_cat = '<a href="' . esc_url( get_category_link( $monolith_cats[0]->term_id ) ) . '">' . esc_html( $monolith_cats[0]->name ) . '</a>';
		}
	}
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'project-card' ); ?>>

		<?php if ( has_post_thumbnail() ) : ?>
			<a class="project-card__cover" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php if ( $monolith_index ) : ?>
					<span class="project-card__index"><?php echo esc_html( sprintf( '%02d', $monolith_index ) ); ?></span>
				<?php endif; ?>
				<?php if ( $monolith_cat ) : ?>
					<span class="project-card__cat"><?php echo wp_kses_post( $monolith_cat ); ?></span>
				<?php endif; ?>
				<?php
				the_post_thumbnail(
					'large',
					array(
						'alt'     => the_title_attribute( array( 'echo' => false ) ),
						'loading' => 'lazy',
					)
				);
				?>
			</a>
		<?php else : ?>
			<a class="project-card__cover project-card__cover--empty" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php if ( $monolith_index ) : ?>
					<span class="project-card__index"><?php echo esc_html( sprintf( '%02d', $monolith_index ) ); ?></span>
				<?php endif; ?>
				<?php if ( $monolith_cat ) : ?>
					<span class="project-card__cat"><?php echo wp_kses_post( $monolith_cat ); ?></span>
				<?php endif; ?>
			</a>
		<?php endif; ?>

		<div class="project-card__body">
			<?php the_title( '<h2 class="project-card__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>

			<?php if ( 'post' === get_post_type() ) : ?>
				<p class="project-card__meta"><?php monolith_posted_meta(); ?></p>
			<?php endif; ?>

			<div class="project-card__excerpt"><?php the_excerpt(); ?></div>

			<div class="project-card__more">
				<a class="read-more" href="<?php the_permalink(); ?>">
					<?php
					/* translators: %s: post title (hidden, for screen readers). */
					printf( esc_html__( 'Read more %s', 'monolith' ), '<span class="screen-reader-text">' . esc_html( get_the_title() ) . '</span>' );
					?>
					<span aria-hidden="true">→</span>
				</a>
			</div>
		</div>
	</article>
	<?php
	return;
endif;

/* -------------------------------------------------------------------------
 * SINGULAR — single post or page
 * ---------------------------------------------------------------------- */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
	<header class="entry-header">
		<?php if ( $monolith_is_page ) : ?>
			<p class="page-kicker"><?php esc_html_e( 'Page', 'monolith' ); ?></p>
		<?php else : ?>
			<?php
			$monolith_cats = get_the_category();
			if ( ! empty( $monolith_cats ) ) :
				?>
				<p class="single-kicker"><?php echo esc_html( $monolith_cats[0]->name ); ?></p>
			<?php else : ?>
				<p class="single-kicker"><?php esc_html_e( 'Journal', 'monolith' ); ?></p>
			<?php endif; ?>
		<?php endif; ?>

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<?php if ( 'post' === get_post_type() ) : ?>
			<div class="entry-meta"><?php monolith_posted_meta(); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="featured-image">
			<?php
			the_post_thumbnail(
				'large',
				array( 'alt' => the_title_attribute( array( 'echo' => false ) ) )
			);
			?>
		</div>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages( array(
			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'monolith' ) . ' ',
			'after'  => '</div>',
		) );
		?>
	</div>

	<?php if ( ! $monolith_is_page ) : ?>
		<footer class="entry-footer">
			<?php
			$monolith_cat_list = get_the_category_list( ' ' );
			if ( $monolith_cat_list ) {
				echo '<p class="entry-cats">' . wp_kses_post( $monolith_cat_list ) . '</p>';
			}
			?>
		</footer>
	<?php endif; ?>
</article>
