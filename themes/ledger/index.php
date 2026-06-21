<?php
/**
 * Main template — the loop.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :

	if ( is_home() && ! is_front_page() ) :
		?>
		<header class="page-header">
			<h1 class="page-title"><?php single_post_title(); ?></h1>
		</header>
		<?php
	elseif ( is_archive() ) :
		?>
		<header class="page-header">
			<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
		</header>
		<?php
	elseif ( is_search() ) :
		?>
		<header class="page-header">
			<h1 class="page-title">
				<?php
				/* translators: %s: search query. */
				printf( esc_html__( 'Search: %s', 'ledger' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
				?>
			</h1>
		</header>
		<?php
	endif;

	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content', get_post_type() );
	endwhile;

	the_posts_navigation( array(
		'prev_text' => esc_html__( 'Older posts', 'ledger' ),
		'next_text' => esc_html__( 'Newer posts', 'ledger' ),
	) );

else :
	?>
	<article class="entry">
		<h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'ledger' ); ?></h2>
		<p><?php esc_html_e( 'It looks like nothing was found. Try a search?', 'ledger' ); ?></p>
		<?php get_search_form(); ?>
	</article>
	<?php
endif;

get_sidebar();
get_footer();
