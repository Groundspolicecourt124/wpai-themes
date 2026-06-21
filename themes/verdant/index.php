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

$verdant_show_hero = ( is_home() || is_front_page() ) && ! is_paged();
?>
<div class="primary-content">
<?php
if ( $verdant_show_hero ) {
	get_template_part( 'template-parts/hero' );
}

if ( have_posts() ) :

	if ( is_home() && ! is_front_page() && ! $verdant_show_hero ) :
		?>
		<header class="page-header">
			<h1 class="entry-title"><?php single_post_title(); ?></h1>
		</header>
		<?php
	endif;

	if ( $verdant_show_hero ) :
		?>
		<h2 class="section-label"><?php esc_html_e( 'Latest stories', 'verdant' ); ?></h2>
		<?php
	endif;

	$verdant_index = 0;
	?>
	<div class="post-list">
	<?php
	while ( have_posts() ) :
		the_post();

		// Promote the very first post on the homepage to a wide "lead" card.
		set_query_var( 'verdant_lead', ( $verdant_show_hero && 0 === $verdant_index ) );
		get_template_part( 'template-parts/content', get_post_type() );

		$verdant_index++;
	endwhile;
	?>
	</div>
	<?php

	the_posts_navigation( array(
		'prev_text' => esc_html__( 'Older posts', 'verdant' ),
		'next_text' => esc_html__( 'Newer posts', 'verdant' ),
	) );

else :
	?>
	<article class="entry entry--empty">
		<h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'verdant' ); ?></h2>
		<p><?php esc_html_e( 'It looks like nothing has grown here yet. Try a search?', 'verdant' ); ?></p>
		<?php get_search_form(); ?>
	</article>
	<?php
endif;
?>
</div><!-- .primary-content -->
<?php
get_sidebar();
get_footer();
