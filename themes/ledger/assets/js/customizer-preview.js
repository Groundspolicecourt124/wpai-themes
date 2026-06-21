/**
 * Ledger Customizer live preview.
 *
 * Binds each color setting to its CSS custom property so the whole theme
 * updates instantly in the Customizer preview, and updates the site title and
 * tagline text live.
 *
 * @package Ledger
 */
( function ( $ ) {
	'use strict';

	var root = document.documentElement;

	/**
	 * Bind a Customizer color setting to a CSS custom property on :root.
	 *
	 * @param {string} settingId Customizer setting id.
	 * @param {string} cssVar    CSS custom property name (including leading --).
	 */
	function bindColor( settingId, cssVar ) {
		wp.customize( settingId, function ( value ) {
			value.bind( function ( newValue ) {
				if ( newValue ) {
					root.style.setProperty( cssVar, newValue );
				} else {
					root.style.removeProperty( cssVar );
				}
			} );
		} );
	}

	// Colors -> CSS variables. Derived accent shades follow --l-accent via
	// color-mix() in style.css, so only the base var needs to be set here.
	bindColor( 'ledger_accent', '--l-accent' );
	bindColor( 'ledger_bg', '--l-paper' );
	bindColor( 'ledger_surface', '--l-surface' );

	// Site title text.
	wp.customize( 'blogname', function ( value ) {
		value.bind( function ( newValue ) {
			$( '.site-title a' ).text( newValue );
		} );
	} );

	// Site tagline text.
	wp.customize( 'blogdescription', function ( value ) {
		value.bind( function ( newValue ) {
			$( '.site-description' ).text( newValue );
		} );
	} );
} )( jQuery );
