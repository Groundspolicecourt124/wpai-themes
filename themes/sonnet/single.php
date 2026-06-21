<?php
/**
 * Single post template.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content', get_post_type() );

	the_post_navigation( array(
		'prev_text' => '← %title',
		'next_text' => '%title →',
	) );

	if ( comments_open() || get_comments_number() ) :
		comments_template();
	endif;

endwhile;

get_sidebar();
get_footer();
