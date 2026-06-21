<?php
/**
 * Post / page content partial.
 *
 * Used by single.php, page.php, and the standard archive/search loop.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ledger_is_post = ( 'post' === get_post_type() );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?><?php echo is_singular( 'post' ) ? ' data-reading="article"' : ''; ?><?php echo is_singular() ? '' : ' data-reveal="entry"'; ?>>
	<header class="entry-header">
		<?php
		if ( $ledger_is_post ) {
			ledger_post_kicker();
		}

		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;

		if ( $ledger_is_post ) :
			?>
			<div class="entry-meta"><?php ledger_posted_meta(); ?></div>
		<?php endif; ?>

		<?php if ( is_singular() && $ledger_is_post && has_excerpt() ) : ?>
			<p class="entry-standfirst"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( has_post_thumbnail() && is_singular() ) : ?>
		<figure class="featured-image entry-hero">
			<?php the_post_thumbnail( 'large' ); ?>
		</figure>
	<?php elseif ( has_post_thumbnail() ) : ?>
		<a class="featured-image" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'large' ); ?>
		</a>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		if ( is_singular() ) :
			the_content();
			wp_link_pages( array(
				'before' => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'ledger' ) . '</span>',
				'after'  => '</div>',
			) );
		else :
			the_excerpt();
			?>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Continue reading', 'ledger' ); ?>
			</a>
			<?php
		endif;
		?>
	</div>

	<?php if ( is_singular() && $ledger_is_post ) : ?>
		<footer class="entry-footer">
			<?php
			$ledger_cats = get_the_category_list( ', ' );
			if ( $ledger_cats ) {
				/* translators: %s: list of category links. */
				printf( '<p class="entry-cats">' . esc_html__( 'Filed under: %s', 'ledger' ) . '</p>', wp_kses_post( $ledger_cats ) );
			}

			$ledger_tags = get_the_tag_list( '', ', ' );
			if ( $ledger_tags ) {
				/* translators: %s: list of tag links. */
				printf( '<p class="entry-cats">' . esc_html__( 'Tagged: %s', 'ledger' ) . '</p>', wp_kses_post( $ledger_tags ) );
			}
			?>
		</footer>
	<?php endif; ?>
</article>
