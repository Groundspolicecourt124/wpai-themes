<?php
/**
 * Single page template.
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
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content', 'page' );

	if ( comments_open() || get_comments_number() ) :
		comments_template();
	endif;

endwhile;
?>
</div><!-- .primary-content -->
<?php
get_sidebar();
get_footer();
