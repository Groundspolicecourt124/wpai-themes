<?php
/**
 * Single post template.
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

			get_template_part( 'template-parts/content', get_post_type() );

			the_post_navigation( array(
				'prev_text' => '<span class="post-nav__label">' . esc_html__( 'Previous', 'aurora' ) . '</span><span class="post-nav__title">%title</span>',
				'next_text' => '<span class="post-nav__label">' . esc_html__( 'Next', 'aurora' ) . '</span><span class="post-nav__title">%title</span>',
			) );

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
