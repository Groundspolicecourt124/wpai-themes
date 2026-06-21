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
		<p class="home-hero__eyebrow">
			<span class="home-hero__leaf" aria-hidden="true">&#10087;</span>
			<?php esc_html_e( 'Welcome', 'verdant' ); ?>
		</p>

		<h1 class="home-hero__title">
			<?php
			if ( $verdant_tagline ) {
				echo esc_html( $verdant_tagline );
			} else {
				/* translators: %s: site name */
				printf( esc_html__( 'A quiet, growing space from %s.', 'verdant' ), esc_html( get_bloginfo( 'name' ) ) );
			}
			?>
		</h1>

		<p class="home-hero__lead">
			<?php esc_html_e( 'Stories, notes, and ideas — gathered slowly and shared with care. Settle in and read a while.', 'verdant' ); ?>
		</p>
	</div>
	<span class="home-hero__sprig" aria-hidden="true"></span>
</section>
