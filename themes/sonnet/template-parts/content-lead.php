<?php
/**
 * Featured lead essay — the first post on the blog index, shown large with
 * its cover image, category eyebrow, byline, and a drop-cap excerpt.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'lead-essay' ); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<a class="lead-essay__cover" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php
			// The lead essay's cover is the hero image, above the fold on the blog
			// home, so load it eagerly with high priority to avoid a blank flash.
			// WordPress would otherwise add loading="lazy"; override it explicitly.
			the_post_thumbnail( 'large', array(
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
			) );
			?>
		</a>
	<?php endif; ?>

	<div class="lead-essay__body">
		<header class="lead-essay__header">
			<p class="entry-kicker">
				<span class="entry-kicker__label"><?php esc_html_e( 'Featured', 'sonnet' ); ?></span>
				<?php
				$sonnet_cat = sonnet_primary_category();
				if ( $sonnet_cat ) :
					?>
					<span class="entry-kicker__cat"><?php echo esc_html( $sonnet_cat ); ?></span>
				<?php endif; ?>
			</p>

			<?php the_title( '<h2 class="lead-essay__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>

			<p class="entry-meta"><?php sonnet_posted_meta(); ?></p>
		</header>

		<div class="lead-essay__excerpt has-dropcap">
			<?php the_excerpt(); ?>
		</div>

		<a class="read-more" href="<?php the_permalink(); ?>">
			<span class="read-more__text"><?php esc_html_e( 'Read the essay', 'sonnet' ); ?></span>
			<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
		</a>
	</div>
</article>
