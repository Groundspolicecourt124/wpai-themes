<?php
/**
 * Main template — the loop.
 *
 * On the front page this renders a full-width lead story followed by a
 * secondary story grid. Everywhere else it falls back to a standard list.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$ledger_is_lead_layout = ( is_front_page() && is_home() && ! is_paged() );
$ledger_lead_rendered  = false;

if ( $ledger_is_lead_layout && have_posts() ) :

	/* ---- Full-width LEAD STORY band (first post) ---- */
	the_post();
	$ledger_lead_rendered = true;
	?>
	<div class="site-wrap lead-band">
		<?php ledger_render_lead_story(); ?>
	</div>
	<?php
endif;
?>

<div class="site-wrap content-area">
	<div class="site-content">
		<?php
		if ( have_posts() ) :

			if ( $ledger_is_lead_layout ) :
				?>
				<h2 class="section-heading" data-reveal="rule"><?php esc_html_e( 'More Stories', 'ledger' ); ?></h2>
				<div class="story-grid" data-stagger>
					<?php
					while ( have_posts() ) :
						the_post();
						ledger_render_story_card();
					endwhile;
					?>
				</div>
				<?php
				the_posts_navigation( array(
					'prev_text' => esc_html__( 'Older stories', 'ledger' ),
					'next_text' => esc_html__( 'Newer stories', 'ledger' ),
				) );

			else :

				if ( is_home() && ! is_front_page() ) :
					?>
					<header class="page-header">
						<h1 class="page-title"><?php single_post_title(); ?></h1>
					</header>
					<?php
				elseif ( is_archive() ) :
					?>
					<header class="page-header">
						<?php
						the_archive_title( '<h1 class="page-title">', '</h1>' );
						the_archive_description( '<div class="page-header__desc">', '</div>' );
						?>
					</header>
					<?php
				elseif ( is_search() ) :
					?>
					<header class="page-header">
						<h1 class="page-title">
							<?php
							/* translators: %s: search query. */
							printf( esc_html__( 'Search: %s', 'ledger' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
							?>
						</h1>
					</header>
					<?php
				endif;

				echo '<div class="entry-list" data-stagger>';
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', get_post_type() );
				endwhile;
				echo '</div>';

				the_posts_navigation( array(
					'prev_text' => esc_html__( 'Older posts', 'ledger' ),
					'next_text' => esc_html__( 'Newer posts', 'ledger' ),
				) );

			endif;

		elseif ( ! $ledger_lead_rendered ) :
			?>
			<article class="entry">
				<header class="page-header">
					<h1 class="page-title"><?php esc_html_e( 'Nothing here yet', 'ledger' ); ?></h1>
				</header>
				<p><?php esc_html_e( 'It looks like nothing was found. Try a search?', 'ledger' ); ?></p>
				<?php get_search_form(); ?>
			</article>
			<?php
		endif;
		?>

<?php
get_sidebar();
get_footer();
