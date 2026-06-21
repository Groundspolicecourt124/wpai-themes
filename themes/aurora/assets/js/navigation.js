/**
 * Aurora — accessible mobile navigation toggle.
 *
 * Progressive enhancement: the menu is fully visible without JS; this only
 * adds the collapse/expand behaviour on small screens.
 */
( function () {
	'use strict';

	var nav = document.querySelector( '.main-nav' );
	if ( ! nav ) {
		return;
	}

	var toggle = nav.querySelector( '.nav-toggle' );
	var menu = nav.querySelector( '.main-nav__list' );
	if ( ! toggle || ! menu ) {
		return;
	}

	nav.classList.add( 'has-js' );

	function setOpen( open ) {
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		nav.classList.toggle( 'is-open', open );
	}

	toggle.addEventListener( 'click', function () {
		var open = toggle.getAttribute( 'aria-expanded' ) === 'true';
		setOpen( ! open );
	} );

	// Close on Escape and return focus to the toggle.
	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key && nav.classList.contains( 'is-open' ) ) {
			setOpen( false );
			toggle.focus();
		}
	} );

	// Close when a menu link is followed (same-page anchors etc.).
	menu.addEventListener( 'click', function ( event ) {
		if ( event.target.closest( 'a' ) ) {
			setOpen( false );
		}
	} );
}() );
