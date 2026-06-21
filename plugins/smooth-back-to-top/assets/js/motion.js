/**
 * Smooth Back to Top — motion layer.
 *
 * Progressive enhancement for the floating "return to top" control:
 *
 *   - The button ships visible-but-inert so it always works without JS.
 *   - On load we mark it `js-ready`, which hands visibility control to this file
 *     and starts it hidden, then reveals it once the user scrolls past a
 *     threshold (fade + scale in via CSS classes — only transform/opacity).
 *   - A circular SVG progress ring tracks how far down the page the visitor is.
 *     We drive it with a single CSS custom property (--sbtt-progress, 0→1) so the
 *     browser updates stroke-dashoffset off the main work we do; reads are
 *     coalesced through requestAnimationFrame so scrolling stays buttery.
 *   - Activating the button smoothly scrolls to the top, then moves focus there
 *     so keyboard and screen-reader users keep their place.
 *
 * prefers-reduced-motion is honored two ways: this script reads the live media
 * query (so toggling the OS setting needs no reload) and the stylesheet disables
 * transitions. When reduced motion is requested we reveal instantly and jump
 * rather than animate. No external libraries, no network, no layout shift.
 */
( function () {
	'use strict';

	var SHOW_AFTER = 300; // Pixels scrolled before the button reveals.

	var button = document.querySelector( '.sbtt-button' );
	if ( ! button ) {
		return;
	}

	// Read the reduced-motion preference live so flipping the OS setting takes
	// effect without a reload; degrade gracefully where matchMedia is absent.
	var reducedMotionQuery =
		typeof window.matchMedia === 'function'
			? window.matchMedia( '(prefers-reduced-motion: reduce)' )
			: { matches: false, addEventListener: null, addListener: null };

	function prefersReducedMotion() {
		return !! reducedMotionQuery.matches;
	}

	// Hand visibility control to JS. Until this class lands the button is shown by
	// CSS, so a JS failure leaves a fully working (if unanimated) control.
	button.classList.add( 'sbtt-js-ready' );

	var docEl = document.documentElement;
	var scrollTicking = false;

	function currentScroll() {
		return (
			window.pageYOffset ||
			( document.scrollingElement && document.scrollingElement.scrollTop ) ||
			docEl.scrollTop ||
			0
		);
	}

	function maxScroll() {
		// Total scrollable distance; guard against zero on short pages.
		var scrollHeight = Math.max(
			document.body ? document.body.scrollHeight : 0,
			docEl ? docEl.scrollHeight : 0
		);
		var viewport = window.innerHeight || docEl.clientHeight || 0;
		return Math.max( scrollHeight - viewport, 0 );
	}

	function update() {
		scrollTicking = false;

		var scrolled = currentScroll();
		var limit = maxScroll();
		var progress = limit > 0 ? Math.min( Math.max( scrolled / limit, 0 ), 1 ) : 0;

		// One custom property drives the ring; CSS turns it into stroke-dashoffset.
		button.style.setProperty( '--sbtt-progress', progress.toFixed( 4 ) );

		button.classList.toggle( 'sbtt-visible', scrolled > SHOW_AFTER );
	}

	function onScroll() {
		if ( ! scrollTicking ) {
			scrollTicking = true;
			window.requestAnimationFrame( update );
		}
	}

	function focusTop() {
		// Move focus to the top of the page so keyboard and screen-reader users
		// continue from the top rather than the bottom of the document.
		var target = document.querySelector( 'main' ) || document.body;
		if ( ! target || typeof target.focus !== 'function' ) {
			return;
		}

		var hadTabindex = target.hasAttribute( 'tabindex' );
		if ( ! hadTabindex ) {
			target.setAttribute( 'tabindex', '-1' );
		}

		target.focus( { preventScroll: true } );

		if ( ! hadTabindex ) {
			target.addEventListener( 'blur', function handler() {
				target.removeAttribute( 'tabindex' );
				target.removeEventListener( 'blur', handler );
			} );
		}
	}

	function scrollToTop() {
		var behavior = prefersReducedMotion() ? 'auto' : 'smooth';

		try {
			window.scrollTo( { top: 0, left: 0, behavior: behavior } );
		} catch ( err ) {
			// Older browsers without the options-object form of scrollTo().
			window.scrollTo( 0, 0 );
		}

		focusTop();
	}

	button.addEventListener( 'click', scrollToTop );
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll, { passive: true } );

	// React to the user toggling reduced motion at the OS level.
	if ( typeof reducedMotionQuery.addEventListener === 'function' ) {
		reducedMotionQuery.addEventListener( 'change', update );
	} else if ( typeof reducedMotionQuery.addListener === 'function' ) {
		// Safari < 14.
		reducedMotionQuery.addListener( update );
	}

	// Set the correct initial state (e.g. when the page loads already scrolled).
	update();
}() );
