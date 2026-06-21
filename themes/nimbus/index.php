<?php
/**
 * Main template — the loop.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( ( is_home() || is_front_page() ) && ! is_paged() ) :
	$nimbus_tagline = get_bloginfo( 'description', 'display' );
	?>
	<section class="nimbus-hero">
		<div class="nimbus-hero__inner">
			<p class="nimbus-hero__eyebrow"><?php bloginfo( 'name' ); ?></p>
			<h1 class="nimbus-hero__title">
				<?php echo esc_html( $nimbus_tagline ? $nimbus_tagline : __( 'Build something people love.', 'nimbus' ) ); ?>
			</h1>
			<p class="nimbus-hero__sub">
				<?php esc_html_e( 'Fast, modern, and free. Ship your story with a theme that gets out of the way.', 'nimbus' ); ?>
			</p>
			<div class="nimbus-hero__cta">
				<a class="button" href="#latest"><?php esc_html_e( 'Read the latest', 'nimbus' ); ?></a>
				<?php get_search_form(); ?>
			</div>
		</div>
	</section>
	<?php
endif;

if ( have_posts() ) :

	if ( is_home() && ! is_front_page() ) :
		?>
		<header class="page-header" id="latest">
			<h1 class="entry-title"><?php single_post_title(); ?></h1>
		</header>
		<?php
	else :
		?>
		<span id="latest" class="screen-reader-text"><?php esc_html_e( 'Latest posts', 'nimbus' ); ?></span>
		<?php
	endif;

	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content', get_post_type() );
	endwhile;

	the_posts_navigation( array(
		'prev_text' => esc_html__( 'Older posts', 'nimbus' ),
		'next_text' => esc_html__( 'Newer posts', 'nimbus' ),
	) );

else :
	?>
	<article class="entry">
		<h2 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'nimbus' ); ?></h2>
		<p><?php esc_html_e( 'It looks like nothing was found. Try a search?', 'nimbus' ); ?></p>
		<?php get_search_form(); ?>
	</article>
	<?php
endif;

get_sidebar();
get_footer();
