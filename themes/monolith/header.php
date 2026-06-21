<?php
/**
 * Header template.
 *
 * @package Monolith
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
<a class="skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'monolith' ); ?></a>

<div class="m-progress" aria-hidden="true"><span class="m-progress__fill"></span></div>

<header class="site-header">
	<div class="site-wrap site-header__inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php endif; ?>
			<div>
				<p class="site-title">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" data-m-magnetic><?php bloginfo( 'name' ); ?></a>
				</p>
				<?php
				$monolith_desc = get_bloginfo( 'description', 'display' );
				if ( $monolith_desc || is_customize_preview() ) :
					?>
					<p class="site-description"><?php echo esc_html( $monolith_desc ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<nav class="main-nav" aria-label="<?php esc_attr_e( 'Primary', 'monolith' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'fallback_cb'    => false,
				'depth'          => 2,
			) );
			?>
		</nav>
	</div>
</header>

<main id="content" class="site-main">
	<div class="site-wrap content-grid">
		<div class="content-main">
