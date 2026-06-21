<?php
/**
 * Main template — the loop.
 *
 * On the blog home / front page this renders a literary masthead, a featured
 * lead essay (the most recent post, shown large with its cover image and a
 * drop-cap excerpt), then a refined list of the remaining posts. Archive and
 * search views fall back to a clean, scannable list.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$sonnet_is_blog_index = ( is_home() && ! is_paged() );

if ( have_posts() ) :

	if ( $sonnet_is_blog_index ) :

		/* ---- Literary masthead (uses the site tagline as a lead-in) ---- */
		?>
		<header class="masthead">
			<p class="masthead__kicker"><?php echo esc_html( sonnet_masthead_kicker() ); ?></p>
			<?php
			$sonnet_tagline = get_bloginfo( 'description', 'display' );
			if ( $sonnet_tagline ) :
				?>
				<p class="masthead__lede"><?php echo esc_html( $sonnet_tagline ); ?></p>
			<?php endif; ?>
		</header>
		<?php

		/* ---- Featured lead essay: the first post, rendered large ---- */
		the_post();
		get_template_part( 'template-parts/content', 'lead' );

		/* ---- The refined list of everything after the lead ---- */
		if ( have_posts() ) :
			?>
			<div class="post-list">
				<h2 class="post-list__title"><?php esc_html_e( 'More reading', 'sonnet' ); ?></h2>
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'list' );
				endwhile;
				?>
			</div>
			<?php
		endif;

	else :

		/* ---- Archive / search / paged views: header + clean list ---- */
		if ( is_home() && ! is_front_page() ) :
			?>
			<header class="page-header">
				<p class="page-header__kicker"><?php esc_html_e( 'Journal', 'sonnet' ); ?></p>
				<h1 class="page-title"><?php single_post_title(); ?></h1>
			</header>
			<?php
		elseif ( is_archive() ) :
			?>
			<header class="page-header">
				<p class="page-header__kicker"><?php echo esc_html( sonnet_archive_kicker() ); ?></p>
				<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
				<?php the_archive_description( '<div class="page-header__desc">', '</div>' ); ?>
			</header>
			<?php
		elseif ( is_search() ) :
			?>
			<header class="page-header">
				<p class="page-header__kicker"><?php esc_html_e( 'Search', 'sonnet' ); ?></p>
				<h1 class="page-title">
					<?php
					printf(
						/* translators: %s: search query. */
						esc_html__( 'Results for “%s”', 'sonnet' ),
						'<span class="search-term">' . esc_html( get_search_query() ) . '</span>'
					);
					?>
				</h1>
			</header>
			<?php
		endif;
		?>
		<div class="post-list post-list--flush">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'list' );
			endwhile;
			?>
		</div>
		<?php
	endif;

	the_posts_navigation( array(
		'prev_text' => esc_html__( 'Older entries', 'sonnet' ),
		'next_text' => esc_html__( 'Newer entries', 'sonnet' ),
	) );

else :
	?>
	<section class="no-results">
		<p class="no-results__kicker"><?php esc_html_e( 'Nothing here', 'sonnet' ); ?></p>
		<h1 class="page-title"><?php esc_html_e( 'A blank page', 'sonnet' ); ?></h1>
		<p class="no-results__text"><?php esc_html_e( 'Nothing was found in this corner of the journal. Try a search instead?', 'sonnet' ); ?></p>
		<?php get_search_form(); ?>
	</section>
	<?php
endif;

get_sidebar();
get_footer();
