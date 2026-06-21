<?php
/**
 * Post / page content partial.
 *
 * Renders a cover-image card on listings and a full article on single/page.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$nimbus_is_listing = ! is_singular();
$nimbus_is_post    = ( 'post' === get_post_type() );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>

	<?php if ( $nimbus_is_listing ) : ?>
		<?php /* Listing card: full-bleed cover with category chip, then padded body. */ ?>
		<div class="featured-image<?php echo has_post_thumbnail() ? '' : ' featured-image--placeholder'; ?>">
			<?php if ( $nimbus_is_post ) { nimbus_primary_category(); } ?>
			<a class="featured-image__link" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<?php
				if ( has_post_thumbnail() ) {
					// The lead/feature card sits above the fold, so load its cover
					// eagerly with a high fetch priority to avoid a blank flash.
					if ( ! empty( $GLOBALS['nimbus_feature'] ) ) {
						the_post_thumbnail( 'large', array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'async',
						) );
					} else {
						the_post_thumbnail( 'large' );
					}
				} else {
					nimbus_placeholder_cover();
				}
				?>
			</a>
		</div>

		<div class="entry__pad">
			<header class="entry-header">
				<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
				<?php if ( $nimbus_is_post ) : ?>
					<div class="entry-meta"><?php nimbus_posted_meta(); ?></div>
				<?php endif; ?>
			</header>

			<div class="entry-excerpt"><?php the_excerpt(); ?></div>

			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Read more', 'nimbus' ); ?>
				<span class="screen-reader-text"><?php echo esc_html( wp_strip_all_tags( get_the_title() ) ); ?></span>
			</a>
		</div>

	<?php else : ?>
		<?php /* Single post or page. */ ?>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="featured-image"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>

		<div class="entry-single-inner">
			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				<?php if ( $nimbus_is_post ) : ?>
					<div class="entry-meta"><?php nimbus_posted_meta(); ?></div>
				<?php endif; ?>
			</header>

			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages( array(
					'before' => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'nimbus' ) . '</span>',
					'after'  => '</div>',
				) );
				?>
			</div>

			<?php if ( $nimbus_is_post ) : ?>
				<?php $nimbus_cats = get_the_category_list( '' ); ?>
				<?php if ( $nimbus_cats ) : ?>
					<footer class="entry-footer">
						<p class="entry-cats">
							<span class="label"><?php esc_html_e( 'Filed under', 'nimbus' ); ?></span>
							<?php echo wp_kses_post( $nimbus_cats ); ?>
						</p>
					</footer>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</article>
