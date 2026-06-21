<?php
/**
 * Main template — the loop.
 *
 * @package Verdant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="primary-content">
<?php
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
		'prev_text' => esc_html__( 'Older posts', 'verdant' ),
		'next_text' => esc_html__( 'Newer posts', 'verdant' ),
	) );

else :
	?>
	<article class="entry">
		<h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'verdant' ); ?></h2>
		<p><?php esc_html_e( 'It looks like nothing was found. Try a search?', 'verdant' ); ?></p>
		<?php get_search_form(); ?>
	</article>
	<?php
endif;
?>
</div><!-- .primary-content -->
<?php
get_sidebar();
get_footer();
