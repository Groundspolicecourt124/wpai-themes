<?php
/**
 * Post / page content partial.
 *
 * @package Verdant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$verdant_is_lead = (bool) get_query_var( 'verdant_lead' );
$verdant_classes = array( 'entry' );

if ( ! is_singular() ) {
	$verdant_classes[] = 'entry--card';
	$verdant_classes[] = 'v-reveal';
	$verdant_classes[] = $verdant_is_lead ? 'entry--lead' : 'entry--compact';
	$verdant_classes[] = has_post_thumbnail() ? 'has-thumb' : 'no-thumb';
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $verdant_classes ); ?>>

	<?php if ( ! is_singular() ) : ?>
		<a class="featured-image featured-image--mask" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php
			if ( has_post_thumbnail() ) {
				// The lead post's image sits above the fold, so load it eagerly
				// (with high fetch priority) to avoid a blank flash on first paint.
				// All other thumbnails keep WordPress's default lazy loading.
				$verdant_thumb_attr = $verdant_is_lead
					? array(
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'decoding'      => 'async',
					)
					: array();
				the_post_thumbnail( $verdant_is_lead ? 'large' : 'medium_large', $verdant_thumb_attr );
			} else {
				echo '<span class="featured-image__placeholder">' . verdant_leaf_mark() . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG.
			}
			?>
		</a>
	<?php endif; ?>

	<div class="entry__body">
		<header class="entry-header">
			<?php
			if ( is_singular( 'post' ) || ! is_singular() ) {
				verdant_category_pill();
			}

			if ( is_singular() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			endif;

			if ( 'post' === get_post_type() ) :
				?>
				<div class="entry-meta"><?php verdant_posted_meta(); ?></div>
			<?php endif; ?>
		</header>

		<?php if ( has_post_thumbnail() && is_singular() ) : ?>
			<div class="featured-image featured-image--single">
				<?php the_post_thumbnail( 'large' ); ?>
			</div>
		<?php endif; ?>

		<div class="entry-content">
			<?php
			if ( is_singular() ) :
				the_content();
				wp_link_pages( array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'verdant' ),
					'after'  => '</div>',
				) );
			else :
				the_excerpt();
				?>
				<a class="read-more" href="<?php the_permalink(); ?>">
					<?php esc_html_e( 'Continue reading', 'verdant' ); ?>
					<span class="read-more__arrow" aria-hidden="true">&#8594;</span>
				</a>
				<?php
			endif;
			?>
		</div>

		<?php if ( is_singular() ) : ?>
			<footer class="entry-footer">
				<?php
				$verdant_cats = get_the_category_list( ' ' );
				if ( $verdant_cats ) {
					echo '<p class="entry-cats">' . wp_kses_post( $verdant_cats ) . '</p>';
				}

				$verdant_tags = get_the_tag_list( '<ul class="entry-tags"><li>', '</li><li>', '</li></ul>' );
				if ( $verdant_tags ) {
					echo wp_kses_post( $verdant_tags );
				}
				?>
			</footer>
		<?php endif; ?>
	</div><!-- .entry__body -->
</article>
