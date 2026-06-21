<?php
/**
 * Post / page content partial.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
	<header class="entry-header">
		<?php
		if ( 'post' === get_post_type() ) {
			ledger_post_kicker();
		}

		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;

		if ( 'post' === get_post_type() ) :
			?>
			<div class="entry-meta"><?php ledger_posted_meta(); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( has_post_thumbnail() && ! is_singular() ) : ?>
		<a class="featured-image" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'large' ); ?>
		</a>
	<?php elseif ( has_post_thumbnail() ) : ?>
		<figure class="featured-image"><?php the_post_thumbnail( 'large' ); ?></figure>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		if ( is_singular() ) :
			the_content();
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'ledger' ),
				'after'  => '</div>',
			) );
		else :
			the_excerpt();
			?>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Continue reading →', 'ledger' ); ?>
			</a>
			<?php
		endif;
		?>
	</div>

	<?php if ( is_singular() && 'post' === get_post_type() ) : ?>
		<footer class="entry-footer">
			<?php
			$ledger_cats = get_the_category_list( ', ' );
			if ( $ledger_cats ) {
				echo '<p class="entry-cats">' . wp_kses_post( $ledger_cats ) . '</p>';
			}

			$ledger_tags = get_the_tag_list( '', ', ' );
			if ( $ledger_tags ) {
				echo '<p class="entry-cats">' . wp_kses_post( $ledger_tags ) . '</p>';
			}
			?>
		</footer>
	<?php endif; ?>
</article>
