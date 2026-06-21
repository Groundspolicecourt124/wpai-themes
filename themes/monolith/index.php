<?php
/**
 * Main template — the loop.
 *
 * Renders an oversized statement masthead on the front page, then the
 * brutalist project grid of posts.
 *
 * @package Monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :

	if ( is_front_page() && is_home() ) :

		// Front page: bold oversized statement masthead.
		$monolith_tagline = get_bloginfo( 'description', 'display' );
		?>
		<section class="home-masthead" aria-label="<?php esc_attr_e( 'Introduction', 'monolith' ); ?>">
			<p class="home-masthead__kicker"><?php esc_html_e( 'Selected Work', 'monolith' ); ?></p>
			<h1 class="home-masthead__title">
				<?php
				if ( $monolith_tagline ) {
					echo wp_kses_post( $monolith_tagline );
				} else {
					echo esc_html( get_bloginfo( 'name' ) );
				}
				?><span class="accent">.</span>
			</h1>
			<?php if ( $monolith_tagline ) : ?>
				<p class="home-masthead__lead">
					<?php
					printf(
						/* translators: %s: site name */
						esc_html__( 'The studio journal of %s — design, engineering, and the craft behind the work.', 'monolith' ),
						esc_html( get_bloginfo( 'name' ) )
					);
					?>
				</p>
			<?php endif; ?>
			<p class="home-masthead__rule">
				<?php esc_html_e( 'Studio Journal', 'monolith' ); ?> <span>/</span>
				<?php esc_html_e( 'Brutalist by design', 'monolith' ); ?> <span>/</span>
				<time datetime="<?php echo esc_attr( gmdate( 'Y' ) ); ?>"><?php echo esc_html( gmdate( 'Y' ) ); ?></time>
			</p>
		</section>

		<p class="index-label"><?php esc_html_e( 'Latest', 'monolith' ); ?></p>
		<?php

	elseif ( is_home() && ! is_front_page() ) :
		?>
		<header class="page-header">
			<p class="page-kicker"><?php esc_html_e( 'Journal', 'monolith' ); ?></p>
			<h1 class="entry-title"><?php single_post_title(); ?></h1>
		</header>
		<?php
	endif;
	?>

	<div class="project-grid">
		<?php
		$monolith_i = 0;
		while ( have_posts() ) :
			the_post();
			$monolith_i++;
			set_query_var( 'monolith_index', $monolith_i );
			get_template_part( 'template-parts/content', get_post_type() );
		endwhile;
		?>
	</div>

	<?php
	the_posts_pagination( array(
		'mid_size'  => 1,
		'prev_text' => esc_html__( '← Older', 'monolith' ),
		'next_text' => esc_html__( 'Newer →', 'monolith' ),
		'screen_reader_text' => esc_html__( 'Posts navigation', 'monolith' ),
	) );

else :
	?>
	<header class="page-header">
		<p class="page-kicker"><?php esc_html_e( 'Empty grid', 'monolith' ); ?></p>
		<h1 class="entry-title"><?php esc_html_e( 'Nothing here yet', 'monolith' ); ?></h1>
	</header>
	<p><?php esc_html_e( 'No work has been published. Try a search?', 'monolith' ); ?></p>
	<?php get_search_form(); ?>
	<?php
endif;

get_sidebar();
get_footer();
