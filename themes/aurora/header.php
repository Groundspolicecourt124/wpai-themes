<?php
/**
 * Header template.
 *
 * @package Aurora
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
	<script>
		/* Flag that JS is on so the no-JS fallback shows all content. motion.js
		   adds the `aurora-motion` class (which arms the reveal start-states)
		   only after confirming support and motion preferences — so if scripts
		   fail, nothing is ever left hidden. */
		document.documentElement.className += ' js';
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'aurora' ); ?></a>

<?php if ( is_single() ) : ?>
	<div class="reading-progress" aria-hidden="true" hidden>
		<span class="reading-progress__bar"></span>
	</div>
<?php endif; ?>

<header class="site-header">
	<div class="site-wrap site-header__inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<div class="site-logo"><?php the_custom_logo(); ?></div>
			<?php endif; ?>
			<div class="site-branding__text">
				<?php
				$aurora_title_tag = ( is_front_page() && is_home() ) ? 'h1' : 'p';
				printf(
					'<%1$s class="site-title"><a href="%2$s" rel="home">%3$s</a></%1$s>',
					esc_attr( $aurora_title_tag ),
					esc_url( home_url( '/' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
				<?php
				$aurora_desc = get_bloginfo( 'description', 'display' );
				if ( $aurora_desc || is_customize_preview() ) :
					?>
					<p class="site-description"><?php echo esc_html( $aurora_desc ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav class="main-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'aurora' ); ?>">
				<button class="nav-toggle" aria-expanded="false" aria-controls="primary-menu">
					<span class="nav-toggle__bar" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'aurora' ); ?></span>
				</button>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu',
					'menu_class'     => 'main-nav__list',
					'container'      => false,
					'fallback_cb'    => false,
					'depth'          => 2,
				) );
				?>
			</nav>
		<?php endif; ?>
	</div>
</header>

<main id="content" class="site-main">
	<div class="site-wrap">
