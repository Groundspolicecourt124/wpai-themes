<?php
/**
 * Homepage welcome hero.
 *
 * A gentle, organic introduction shown at the top of the blog index. It leans
 * on the site tagline when one is set, with a warm fallback otherwise.
 *
 * @package Verdant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$verdant_tagline = get_bloginfo( 'description', 'display' );
?>
<section class="home-hero" aria-label="<?php esc_attr_e( 'Welcome', 'verdant' ); ?>">
	<div class="home-hero__inner">
		<p class="home-hero__eyebrow v-reveal">
			<span class="home-hero__leaf" aria-hidden="true">&#10087;</span>
			<?php esc_html_e( 'Welcome', 'verdant' ); ?>
		</p>

		<h1 class="home-hero__title v-reveal">
			<?php
			if ( $verdant_tagline ) {
				echo esc_html( $verdant_tagline );
			} else {
				/* translators: %s: site name */
				printf( esc_html__( 'A quiet, growing space from %s.', 'verdant' ), esc_html( get_bloginfo( 'name' ) ) );
			}
			?>
		</h1>

		<p class="home-hero__lead v-reveal">
			<?php esc_html_e( 'Stories, notes, and ideas — gathered slowly and shared with care. Settle in and read a while.', 'verdant' ); ?>
		</p>
	</div>
	<div class="home-hero__drift" aria-hidden="true">
		<?php echo verdant_botanical_drift( 5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG markup. ?>
	</div>
	<span class="home-hero__sprig" aria-hidden="true"></span>
</section>
