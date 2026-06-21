<?php
/**
 * Main template — the loop.
 *
 * @package Monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :

	if ( is_home() && ! is_front_page() ) :
		?>
		<header class="page-header">
			<h1 class="entry-title"><?php single_post_title(); ?></h1>
		</header>
		<?php
	endif;

	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content', get_post_type() );
	endwhile;

	the_posts_navigation( array(
		'prev_text' => esc_html__( 'Older work', 'monolith' ),
		'next_text' => esc_html__( 'Newer work', 'monolith' ),
	) );

else :
	?>
	<article class="entry">
		<h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'monolith' ); ?></h2>
		<p><?php esc_html_e( 'No work has been published. Try a search?', 'monolith' ); ?></p>
		<?php get_search_form(); ?>
	</article>
	<?php
endif;

get_sidebar();
get_footer();
