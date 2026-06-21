<?php
/**
 * Single post template.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content', get_post_type() );

	the_post_navigation( array(
		'prev_text'          => '<span class="screen-reader-text">' . esc_html__( 'Previous post:', 'nimbus' ) . '</span> ← %title',
		'next_text'          => '<span class="screen-reader-text">' . esc_html__( 'Next post:', 'nimbus' ) . '</span> %title →',
		'screen_reader_text' => esc_html__( 'Post navigation', 'nimbus' ),
		'aria_label'         => esc_html__( 'Posts', 'nimbus' ),
	) );

	if ( comments_open() || get_comments_number() ) :
		comments_template();
	endif;

endwhile;

get_sidebar();
get_footer();
