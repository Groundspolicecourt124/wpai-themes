<?php
/**
 * Post / page content partial.
 *
 * Renders in three modes:
 *   - lead      : the large featured story at the top of the blog home.
 *   - list      : the compact card used for every other archive post.
 *   - singular  : the full article on single posts and pages.
 *
 * @package Aurora
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aurora_is_lead     = (bool) get_query_var( 'aurora_is_lead' );
$aurora_is_singular = is_singular();
$aurora_mode        = $aurora_is_singular ? 'singular' : ( $aurora_is_lead ? 'lead' : 'list' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--' . $aurora_mode ); ?> data-aurora-reveal>

	<?php if ( 'lead' === $aurora_mode ) : ?>

		<?php aurora_featured_media( 'aurora-lead', true, true ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php aurora_category_pill(); ?>
				<?php aurora_posted_meta(); ?>
			</div>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Read the story', 'aurora' ); ?>
				<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
			</a>
		</div>

	<?php elseif ( 'list' === $aurora_mode ) : ?>

		<?php aurora_featured_media( 'medium_large', true ); ?>
		<div class="entry__body">
			<div class="entry-meta">
				<?php aurora_category_pill(); ?>
				<?php if ( 'post' === get_post_type() ) : ?>
					<?php aurora_posted_meta(); ?>
				<?php endif; ?>
			</div>
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>
			<a class="read-more" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Continue reading', 'aurora' ); ?>
				<span class="read-more__arrow" aria-hidden="true">&rarr;</span>
			</a>
		</div>

	<?php else : // singular ?>

		<header class="entry-header">
			<?php if ( 'post' === get_post_type() ) : ?>
				<div class="entry-meta">
					<?php aurora_category_pill(); ?>
					<?php aurora_posted_meta(); ?>
				</div>
			<?php endif; ?>

			<?php
			the_title(
				'<h1 class="entry-title" data-aurora-words><span class="entry-title__text">',
				'</span></h1>'
			);
			aurora_ink_underline();
			?>

			<?php if ( has_excerpt() && 'post' === get_post_type() ) : ?>
				<p class="entry-standfirst" data-aurora-reveal><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div data-aurora-reveal>
				<?php aurora_featured_media( 'large', false ); ?>
			</div>
		<?php endif; ?>

		<div class="entry-content">
			<?php
			the_content();
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'aurora' ) . ' ',
				'after'  => '</div>',
			) );
			?>
		</div>

		<?php
		$aurora_tags = get_the_tag_list( '<ul class="entry-tags"><li>', '</li><li>', '</li></ul>' );
		if ( $aurora_tags && 'post' === get_post_type() ) :
			?>
			<footer class="entry-footer">
				<span class="entry-tags__label"><?php esc_html_e( 'Filed under', 'aurora' ); ?></span>
				<?php echo wp_kses_post( $aurora_tags ); ?>
			</footer>
		<?php endif; ?>

	<?php endif; ?>

</article>
