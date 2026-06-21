<?php
/**
 * Main template — the loop.
 *
 * On the blog home, the newest post becomes a full-width lead story and the
 * remaining posts fall into a tidy list. Other archives use the list form.
 *
 * @package Aurora
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$aurora_is_blog_home = ( is_home() && ! is_paged() );
?>

<?php if ( $aurora_is_blog_home && get_bloginfo( 'description', 'display' ) ) : ?>
	<section class="lead-essay" aria-label="<?php esc_attr_e( 'Introduction', 'aurora' ); ?>">
		<p class="lead-essay__kicker"><?php esc_html_e( 'The journal', 'aurora' ); ?></p>
		<p class="lead-essay__line"><?php echo esc_html( get_bloginfo( 'description', 'display' ) ); ?></p>
	</section>
<?php endif; ?>

<?php if ( have_posts() ) : ?>

	<?php if ( is_search() ) : ?>
		<header class="page-header">
			<h1 class="page-header__title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Search: %s', 'aurora' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>
	<?php elseif ( is_archive() ) : ?>
		<header class="page-header">
			<?php
			the_archive_title( '<h1 class="page-header__title">', '</h1>' );
			the_archive_description( '<div class="page-header__desc">', '</div>' );
			?>
		</header>
	<?php endif; ?>

	<div class="layout">
		<div class="layout__main">
			<?php
			$aurora_index = 0;

			while ( have_posts() ) :
				the_post();

				// The newest post on the blog home becomes the featured lead story.
				$is_lead = ( $aurora_is_blog_home && 0 === $aurora_index );

				set_query_var( 'aurora_is_lead', $is_lead );
				get_template_part( 'template-parts/content', get_post_type() );

				$aurora_index++;
			endwhile;
			?>
		</div><!-- .layout__main -->

		<?php get_sidebar(); ?>
	</div><!-- .layout -->

	<?php
	the_posts_pagination( array(
		'mid_size'           => 1,
		'prev_text'          => esc_html__( 'Newer', 'aurora' ),
		'next_text'          => esc_html__( 'Older', 'aurora' ),
		'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'aurora' ) . ' </span>',
	) );
	?>

<?php else : ?>

	<div class="layout">
		<div class="layout__main">
			<article class="entry entry--empty">
				<h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'aurora' ); ?></h2>
				<p class="entry-content"><?php esc_html_e( 'It looks like nothing was found in this spot. Perhaps a search will help?', 'aurora' ); ?></p>
				<?php get_search_form(); ?>
			</article>
		</div>
		<?php get_sidebar(); ?>
	</div>

<?php endif; ?>

<?php
get_footer();
