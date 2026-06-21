<?php
/**
 * Header template.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'sonnet' ); ?></a>

<?php
// Signature element: a faint gold constellation drifts behind the page like ink
// settling on dark paper. Drawn by motion.js into this fixed, decorative canvas;
// it stays empty (and invisible) without JS or under reduced-motion, so it can
// never obscure content or shift layout. aria-hidden — purely atmospheric.
?>
<canvas class="sonnet-constellation" aria-hidden="true"></canvas>

<?php // Thin gold reading-progress line, pinned to the very top of the viewport. ?>
<div class="sonnet-progress" aria-hidden="true"><span class="sonnet-progress__bar"></span></div>

<?php // Refined custom caret: a soft gold ring that trails the pointer (fine pointers only). ?>
<div class="sonnet-caret" aria-hidden="true"></div>

<header class="site-header">
	<div class="site-wrap site-header__inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<div class="site-logo"><?php the_custom_logo(); ?></div>
			<?php endif; ?>

			<?php if ( is_front_page() && is_home() ) : ?>
				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<?php else : ?>
				<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
			<?php endif; ?>

			<?php
			$sonnet_desc = get_bloginfo( 'description', 'display' );
			if ( $sonnet_desc || is_customize_preview() ) :
				?>
				<p class="site-description"><?php echo esc_html( $sonnet_desc ); ?></p>
			<?php endif; ?>
		</div>

		<?php
		if ( has_nav_menu( 'primary' ) ) :
			?>
			<nav class="main-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'sonnet' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'main-nav__menu',
					'fallback_cb'    => false,
					'depth'          => 2,
				) );
				?>
			</nav>
			<?php
		endif;
		?>
	</div>
</header>

<main id="content" class="site-main" tabindex="-1">
	<div class="site-wrap">
