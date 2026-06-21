<?php
/**
 * Header template.
 *
 * @package Ledger
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
<a class="skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'ledger' ); ?></a>

<div class="site-dateline">
	<div class="site-wrap site-dateline__inner">
		<span class="site-dateline__edition">
			<?php
			printf(
				/* translators: 1: localized current date, 2: volume number */
				esc_html__( '%1$s · Vol. %2$s', 'ledger' ),
				esc_html( date_i18n( get_option( 'date_format' ) ) ),
				esc_html( date_i18n( 'Y' ) )
			);
			?>
		</span>
		<span class="site-dateline__motto"><?php esc_html_e( 'The Editorial Review', 'ledger' ); ?></span>
	</div>
</div>

<header class="site-header">
	<div class="site-wrap site-header__inner">
		<span class="site-header__rule site-header__rule--l" aria-hidden="true"></span>
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php endif; ?>
			<?php
			$ledger_tag = is_front_page() && is_home() ? 'h1' : 'p';
			printf(
				'<%1$s class="site-title"><a href="%2$s" rel="home">%3$s</a></%1$s>',
				esc_attr( $ledger_tag ),
				esc_url( home_url( '/' ) ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
			<?php
			$ledger_desc = get_bloginfo( 'description', 'display' );
			if ( $ledger_desc || is_customize_preview() ) :
				?>
				<p class="site-description"><?php echo esc_html( $ledger_desc ); ?></p>
			<?php endif; ?>
		</div>
		<span class="site-header__rule site-header__rule--r" aria-hidden="true"></span>
	</div>
</header>

<nav class="main-nav" aria-label="<?php esc_attr_e( 'Primary', 'ledger' ); ?>">
	<div class="site-wrap main-nav__inner">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'fallback_cb'    => false,
			'depth'          => 2,
		) );
		?>
	</div>
</nav>

<?php ledger_render_ticker(); ?>

<?php if ( is_singular( 'post' ) ) : ?>
	<div class="l-progress" aria-hidden="true"><span class="l-progress__bar"></span></div>
<?php endif; ?>

<main id="content" class="site-main">
