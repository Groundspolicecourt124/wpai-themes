<?php
/**
 * List item — a refined, scannable entry used for the homepage "more reading"
 * list and for archive / search views. A compact cover thumbnail sits beside
 * the text; the layout degrades gracefully when a post has no featured image.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sonnet_has_thumb = has_post_thumbnail();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $sonnet_has_thumb ? 'post-item has-thumb' : 'post-item no-thumb' ); ?>>

	<?php if ( $sonnet_has_thumb ) : ?>
		<a class="post-item__thumb" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'sonnet-card' ); ?>
		</a>
	<?php endif; ?>

	<div class="post-item__body">
		<header class="post-item__header">
			<?php
			$sonnet_cat = sonnet_primary_category();
			if ( $sonnet_cat ) :
				?>
				<p class="entry-kicker"><span class="entry-kicker__cat"><?php echo esc_html( $sonnet_cat ); ?></span></p>
			<?php endif; ?>

			<?php the_title( '<h3 class="post-item__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' ); ?>

			<?php if ( 'post' === get_post_type() ) : ?>
				<p class="entry-meta"><?php sonnet_posted_meta(); ?></p>
			<?php endif; ?>
		</header>

		<div class="post-item__excerpt">
			<?php the_excerpt(); ?>
		</div>

		<a class="read-more read-more--quiet" href="<?php the_permalink(); ?>">
			<span class="read-more__text"><?php esc_html_e( 'Continue reading', 'sonnet' ); ?></span>
			<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
		</a>
	</div>
</article>
