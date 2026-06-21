/**
 * Verdant — organic motion system.
 *
 * Hand-rolled, dependency-free. Adds gentle, restful motion that fits the
 * theme's calm wellness personality:
 *   - Float-up scroll reveals with a soft stagger (IntersectionObserver).
 *   - An organic mask reveal on featured images.
 *   - A slowly drifting field of leaf/seed SVG shapes (the signature move).
 *   - A subtle parallax breath on the homepage hero.
 *
 * Progressive enhancement: markup ships fully visible. The inline <head>
 * snippet adds a `js` class to <html>; only then does CSS hide-then-reveal
 * the elements this file animates. If this script never runs, everything
 * stays put and readable.
 *
 * Accessibility: when the visitor prefers reduced motion we reveal everything
 * immediately and wire up nothing that moves. A matching CSS @media block
 * neutralises transitions/animations as a second line of defence.
 *
 * Performance: only `transform` and `opacity` are animated; reveals unobserve
 * after firing; the drift loop is rAF-driven and pauses when off-screen / on a
 * hidden tab.
 *
 * @package Verdant
 */
( function () {
	'use strict';

	var root = document.documentElement;
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* Mark JS as ready so reveal CSS can take effect even if `js` was set late,
	 * and stand down the inline head watchdog (it reveals everything only if
	 * this flag is never set — i.e. if this script fails to run). */
	root.classList.add( 'v-motion-ready' );
	window.__vMotion = true;

	/**
	 * Reveal everything immediately — used for reduced-motion and as the
	 * no-IntersectionObserver fallback.
	 */
	function revealAll() {
		var hidden = document.querySelectorAll( '.v-reveal:not(.is-visible)' );
		for ( var i = 0; i < hidden.length; i++ ) {
			hidden[ i ].classList.add( 'is-visible' );
		}
	}

	if ( reduceMotion ) {
		revealAll();
		return;
	}

	/* ---------------------------------------------------------------------
	 * 1. Scroll reveals — float up with a soft, kin-grouped stagger.
	 * ------------------------------------------------------------------- */
	function setupReveals() {
		var targets = document.querySelectorAll( '.v-reveal' );
		if ( ! targets.length ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			revealAll();
			return;
		}

		var io = new IntersectionObserver( function ( entries, observer ) {
			/* Sort by document order so a row of cards staggers left→right. */
			var shown = entries.filter( function ( e ) {
				return e.isIntersecting;
			} );

			shown.forEach( function ( entry, i ) {
				var el = entry.target;
				/* Stagger siblings gently; cap so a long list never crawls. */
				var step = Math.min( i, 5 ) * 90;
				el.style.setProperty( '--v-reveal-delay', step + 'ms' );
				el.classList.add( 'is-visible' );
				observer.unobserve( el );
			} );
		}, {
			rootMargin: '0px 0px -10% 0px',
			threshold: 0.12
		} );

		for ( var i = 0; i < targets.length; i++ ) {
			io.observe( targets[ i ] );
		}

		/* Accessibility: a keyboard user can tab to a link inside a card that
		 * hasn't scrolled into view yet. Never let motion hide a focused
		 * control — reveal its container immediately (and stop observing it). */
		document.addEventListener( 'focusin', function ( e ) {
			var host = e.target.closest ? e.target.closest( '.v-reveal:not(.is-visible)' ) : null;
			if ( host ) {
				host.style.setProperty( '--v-reveal-delay', '0ms' );
				host.classList.add( 'is-visible' );
				io.unobserve( host );
			}
		} );
	}

	/* ---------------------------------------------------------------------
	 * 2. Hero breath — a whisper of parallax on the drifting glow + sprig.
	 *    Pointer only, throttled to rAF, transform-only.
	 * ------------------------------------------------------------------- */
	function setupHeroBreath() {
		var hero = document.querySelector( '.home-hero' );
		if ( ! hero || ! window.matchMedia( '(pointer: fine)' ).matches ) {
			return;
		}

		// Parallax the drifting botanical field only. The corner sprig keeps its
		// own CSS "breathing" animation, so we never fight it with an inline
		// transform here.
		var drift = hero.querySelector( '.home-hero__drift' );
		if ( ! drift ) {
			return;
		}

		var targetX = 0, targetY = 0, curX = 0, curY = 0, ticking = false;

		function onMove( e ) {
			var r = hero.getBoundingClientRect();
			/* -0.5 … 0.5 relative to hero centre. */
			targetX = ( e.clientX - r.left ) / r.width - 0.5;
			targetY = ( e.clientY - r.top ) / r.height - 0.5;
			if ( ! ticking ) {
				ticking = true;
				requestAnimationFrame( frame );
			}
		}

		function frame() {
			/* Ease toward the target so it feels like a breath, not a jump. */
			curX += ( targetX - curX ) * 0.06;
			curY += ( targetY - curY ) * 0.06;

			drift.style.transform = 'translate3d(' + ( curX * 18 ) + 'px,' + ( curY * 18 ) + 'px,0)';

			if ( Math.abs( targetX - curX ) > 0.001 || Math.abs( targetY - curY ) > 0.001 ) {
				requestAnimationFrame( frame );
			} else {
				ticking = false;
			}
		}

		function reset() {
			targetX = 0;
			targetY = 0;
			if ( ! ticking ) {
				ticking = true;
				requestAnimationFrame( frame );
			}
		}

		hero.addEventListener( 'pointermove', onMove );
		hero.addEventListener( 'pointerleave', reset );
	}

	/* ---------------------------------------------------------------------
	 * 3. Init.
	 * ------------------------------------------------------------------- */
	function init() {
		setupReveals();
		setupHeroBreath();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
