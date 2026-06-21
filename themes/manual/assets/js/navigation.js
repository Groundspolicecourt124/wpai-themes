/**
 * Manual — accessible navigation toggles.
 *
 * Progressive enhancement: both the primary menu and the docs rail are fully
 * visible without JS; this only adds collapse/expand behaviour on small screens.
 */
( function () {
	'use strict';

	/* ---- Primary header menu ---------------------------------------------- */
	( function initPrimaryNav() {
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

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && nav.classList.contains( 'is-open' ) ) {
				setOpen( false );
				toggle.focus();
			}
		} );

		menu.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( 'a' ) ) {
				setOpen( false );
			}
		} );
	}() );

	/* ---- Docs navigation rail (mobile disclosure) -------------------------- */
	( function initDocsNav() {
		var rail = document.querySelector( '.docs-nav' );
		if ( ! rail ) {
			return;
		}

		var toggle = rail.querySelector( '.docs-nav__toggle' );
		var panel = rail.querySelector( '.docs-nav__panel' );
		if ( ! toggle || ! panel ) {
			return;
		}

		rail.classList.add( 'has-js' );

		function setOpen( open ) {
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			rail.classList.toggle( 'is-open', open );
		}

		// Start collapsed on small screens, but open on the page the reader is on.
		var hasActive = !! panel.querySelector(
			'.current-menu-item, .current_page_item, .current-menu-ancestor'
		);
		setOpen( hasActive && window.innerWidth >= 960 );

		toggle.addEventListener( 'click', function () {
			var open = toggle.getAttribute( 'aria-expanded' ) === 'true';
			setOpen( ! open );
		} );

		// Close after following a link on small screens.
		panel.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( 'a' ) && window.innerWidth < 960 ) {
				setOpen( false );
			}
		} );
	}() );

	/* ---- "/" focuses search (the docs-hero keyboard hint) ------------------ */
	( function initSearchShortcut() {
		var field = document.querySelector( '.search-field' );
		if ( ! field ) {
			return;
		}

		document.addEventListener( 'keydown', function ( event ) {
			if ( '/' !== event.key || event.ctrlKey || event.metaKey || event.altKey ) {
				return;
			}

			// Don't hijack "/" while the user is already typing somewhere.
			var active = document.activeElement;
			if ( active ) {
				var tag = active.tagName;
				if (
					'INPUT' === tag ||
					'TEXTAREA' === tag ||
					'SELECT' === tag ||
					active.isContentEditable
				) {
					return;
				}
			}

			event.preventDefault();
			field.focus();
		} );
	}() );
}() );
