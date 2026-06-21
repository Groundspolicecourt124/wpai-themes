<?php
/**
 * Single page template.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content', 'page' );

	if ( comments_open() || get_comments_number() ) :
		comments_template();
	endif;

endwhile;

get_sidebar();
get_footer();
