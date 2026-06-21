<?php
/**
 * Post / page content partial.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
	<header class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;

		if ( 'post' === get_post_type() ) :
			?>
			<div class="entry-meta"><?php sonnet_posted_meta(); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( has_post_thumbnail() && ! is_singular() ) : ?>
		<a class="featured-image" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'large' ); ?>
		</a>
	<?php elseif ( has_post_thumbnail() ) : ?>
		<div class="featured-image"><?php the_post_thumbnail( 'large' ); ?></div>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		if ( is_singular() ) :
			the_content();
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'sonnet' ),
				'after'  => '</div>',
			) );
		else :
			the_excerpt();
			?>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Continue reading →', 'sonnet' ); ?>
			</a>
			<?php
		endif;
		?>
	</div>

	<?php if ( is_singular() ) : ?>
		<footer class="entry-footer">
			<?php
			$sonnet_cats = get_the_category_list( ', ' );
			if ( $sonnet_cats ) {
				echo '<p class="entry-cats">' . wp_kses_post( $sonnet_cats ) . '</p>';
			}
			?>
		</footer>
	<?php endif; ?>
</article>
