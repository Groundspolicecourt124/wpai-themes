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
