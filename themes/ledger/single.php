<?php
/**
 * Single post template.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="site-wrap content-area">
	<div class="site-content">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', get_post_type() );

			the_post_navigation( array(
				'prev_text' => '<span class="nav-subtitle screen-reader-text">' . esc_html__( 'Previous:', 'ledger' ) . '</span> &larr; %title',
				'next_text' => '<span class="nav-subtitle screen-reader-text">' . esc_html__( 'Next:', 'ledger' ) . '</span> %title &rarr;',
			) );

			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile;
		?>

<?php
get_sidebar();
get_footer();
