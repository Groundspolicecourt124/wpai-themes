<?php
/**
 * 404 template.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="error-404">
	<p class="error-404__big" aria-hidden="true">404</p>
	<h1 class="error-404__title"><?php esc_html_e( 'This page slipped between the lines', 'sonnet' ); ?></h1>
	<p class="error-404__text"><?php esc_html_e( 'The page you were after has moved or never existed. Search for it, or pick up one of the recent essays below.', 'sonnet' ); ?></p>

	<?php get_search_form(); ?>

	<?php
	$sonnet_recent = new WP_Query( array(
		'posts_per_page'      => 4,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	if ( $sonnet_recent->have_posts() ) :
		?>
		<div class="error-404__recent">
			<h2 class="error-404__recent-title"><?php esc_html_e( 'Recent essays', 'sonnet' ); ?></h2>
			<ul class="error-404__list">
				<?php
				while ( $sonnet_recent->have_posts() ) :
					$sonnet_recent->the_post();
					?>
					<li>
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						<span class="error-404__date"><?php echo esc_html( get_the_date() ); ?></span>
					</li>
					<?php
				endwhile;
				?>
			</ul>
		</div>
		<?php
		wp_reset_postdata();
	endif;
	?>
</section>
<?php
get_footer();
