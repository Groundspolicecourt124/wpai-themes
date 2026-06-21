<?php
/**
 * Single page template.
 *
 * @package Aurora
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="layout">
	<div class="layout__main">
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'page' );

			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile;
		?>
	</div><!-- .layout__main -->

	<?php get_sidebar(); ?>
</div><!-- .layout -->
<?php
get_footer();
