/**
 * Monolith — motion system.
 *
 * A self-contained, dependency-free motion layer for the brutalist studio
 * theme. Everything degrades to fully-visible, fully-usable content:
 *
 *  - The <html> element already carries the `js` class (set by a tiny inline
 *    head snippet) so CSS only hides-then-reveals when JS is present.
 *  - If `prefers-reduced-motion: reduce` is set, we reveal everything
 *    immediately, skip every interaction, and exit. The CSS reduced-motion
 *    block also neutralises transitions/animations as a belt-and-braces.
 *  - Only `transform` and `opacity` are animated. No layout thrash, no
 *    scroll-jacking, no cumulative layout shift.
 *
 * @package Monolith
 */
( function () {
	'use strict';

	var root = document.documentElement;
	var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	/**
	 * Reveal everything immediately and mark the document "settled" so CSS
	 * drops every entrance/hidden state. Safe to call more than once.
	 */
	function revealAll() {
		root.classList.add( 'm-motion-ready' );
		var hidden = document.querySelectorAll( '.m-reveal' );
		for ( var i = 0; i < hidden.length; i++ ) {
			hidden[ i ].classList.add( 'is-in' );
		}
	}

	// Honour reduced-motion: show all content, wire up nothing animated.
	if ( reduce.matches ) {
		revealAll();
		return;
	}

	/* ---------------------------------------------------------------------
	 * 1. Scroll reveals — clip-path / wipe entrances with a tasteful stagger.
	 * ------------------------------------------------------------------- */
	function initReveals() {
		var targets = document.querySelectorAll( '.m-reveal' );
		if ( ! targets.length || ! ( 'IntersectionObserver' in window ) ) {
			revealAll();
			return;
		}

		root.classList.add( 'm-motion-ready' );

		var io = new IntersectionObserver(
			function ( entries, obs ) {
				for ( var i = 0; i < entries.length; i++ ) {
					var entry = entries[ i ];
					if ( ! entry.isIntersecting ) {
						continue;
					}
					var el = entry.target;

					// Stagger siblings inside a shared group for a confident,
					// cascading wipe-in (capped so long lists never crawl).
					var group = el.closest( '[data-m-stagger]' );
					var delay = 0;
					if ( group ) {
						var items = group.querySelectorAll( '.m-reveal' );
						var idx = Array.prototype.indexOf.call( items, el );
						if ( idx > -1 ) {
							delay = Math.min( idx, 8 ) * 70;
						}
					}
					el.style.transitionDelay = delay + 'ms';
					el.classList.add( 'is-in' );

					// One-shot: stop watching once revealed.
					obs.unobserve( el );
				}
			},
			{ root: null, rootMargin: '0px 0px -12% 0px', threshold: 0.08 }
		);

		for ( var i = 0; i < targets.length; i++ ) {
			io.observe( targets[ i ] );
		}
	}

	/* ---------------------------------------------------------------------
	 * 2. Index counters — monospace numbers that tick up to their value.
	 * ------------------------------------------------------------------- */
	function initCounters() {
		var counters = document.querySelectorAll( '[data-m-count]' );
		if ( ! counters.length || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		var io = new IntersectionObserver(
			function ( entries, obs ) {
				for ( var i = 0; i < entries.length; i++ ) {
					var entry = entries[ i ];
					if ( ! entry.isIntersecting ) {
						continue;
					}
					tick( entry.target );
					obs.unobserve( entry.target );
				}
			},
			{ root: null, rootMargin: '0px 0px -10% 0px', threshold: 0.5 }
		);

		for ( var i = 0; i < counters.length; i++ ) {
			io.observe( counters[ i ] );
		}
	}

	function tick( el ) {
		var target = parseInt( el.getAttribute( 'data-m-count' ), 10 );
		if ( isNaN( target ) ) {
			return;
		}
		var pad = el.textContent.replace( /[^0-9]/g, '' ).length || 2;
		var start = null;
		var dur = 520;

		function frame( now ) {
			if ( start === null ) {
				start = now;
			}
			var t = Math.min( ( now - start ) / dur, 1 );
			// easeOutCubic for a snappy settle.
			var eased = 1 - Math.pow( 1 - t, 3 );
			var val = Math.round( eased * target );
			el.textContent = String( val ).padStart( pad, '0' );
			if ( t < 1 ) {
				window.requestAnimationFrame( frame );
			}
		}
		window.requestAnimationFrame( frame );
	}

	/* ---------------------------------------------------------------------
	 * 3. Magnetic hovers — oversized links/buttons lean toward the cursor.
	 *    Pointer-precision only, so touch is never affected.
	 * ------------------------------------------------------------------- */
	function initMagnetic() {
		if ( ! window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) {
			return;
		}
		var magnets = document.querySelectorAll( '[data-m-magnetic]' );
		var strength = 0.32;
		var max = 14;

		for ( var i = 0; i < magnets.length; i++ ) {
			( function ( el ) {
				var raf = 0;
				var tx = 0;
				var ty = 0;

				function apply() {
					raf = 0;
					el.style.transform = 'translate3d(' + tx + 'px,' + ty + 'px,0)';
				}

				el.addEventListener( 'pointermove', function ( e ) {
					var r = el.getBoundingClientRect();
					var dx = e.clientX - ( r.left + r.width / 2 );
					var dy = e.clientY - ( r.top + r.height / 2 );
					tx = Math.max( -max, Math.min( max, dx * strength ) );
					ty = Math.max( -max, Math.min( max, dy * strength ) );
					if ( ! raf ) {
						raf = window.requestAnimationFrame( apply );
					}
				} );

				function reset() {
					tx = 0;
					ty = 0;
					if ( ! raf ) {
						raf = window.requestAnimationFrame( apply );
					}
				}
				el.addEventListener( 'pointerleave', reset );
				el.addEventListener( 'blur', reset );
			} )( magnets[ i ] );
		}
	}

	/* ---------------------------------------------------------------------
	 * 4. Scroll-progress bar — a thin electric rule across the very top.
	 * ------------------------------------------------------------------- */
	function initProgress() {
		var bar = document.querySelector( '.m-progress__fill' );
		if ( ! bar ) {
			return;
		}
		var raf = 0;

		function update() {
			raf = 0;
			var h = document.documentElement;
			var scrollable = h.scrollHeight - h.clientHeight;
			var p = scrollable > 0 ? h.scrollTop / scrollable : 0;
			bar.style.transform = 'scaleX(' + p + ')';
		}

		function onScroll() {
			if ( ! raf ) {
				raf = window.requestAnimationFrame( update );
			}
		}

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onScroll, { passive: true } );
		update();
	}

	/* ---------------------------------------------------------------------
	 * 5. Signature blocky cursor — a hard accent square that trails the
	 *    pointer and snaps/grows over interactive targets. Pointer-fine only;
	 *    keyboard focus and touch are untouched, and the native cursor stays
	 *    visible as a fallback (we never hide it on focusable controls).
	 * ------------------------------------------------------------------- */
	function initCursor() {
		if ( ! window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) {
			return;
		}

		var dot = document.createElement( 'div' );
		dot.className = 'm-cursor';
		dot.setAttribute( 'aria-hidden', 'true' );
		document.body.appendChild( dot );
		root.classList.add( 'm-has-cursor' );

		var x = window.innerWidth / 2;
		var y = window.innerHeight / 2;
		var cx = x;
		var cy = y;
		var running = false;

		function render() {
			// Lerp toward the pointer for a weighty, mechanical trail.
			cx += ( x - cx ) * 0.22;
			cy += ( y - cy ) * 0.22;
			dot.style.transform = 'translate3d(' + cx + 'px,' + cy + 'px,0) translate(-50%,-50%)';
			if ( Math.abs( x - cx ) > 0.1 || Math.abs( y - cy ) > 0.1 ) {
				window.requestAnimationFrame( render );
			} else {
				running = false;
			}
		}

		function kick() {
			if ( ! running ) {
				running = true;
				window.requestAnimationFrame( render );
			}
		}

		document.addEventListener(
			'pointermove',
			function ( e ) {
				if ( e.pointerType && e.pointerType !== 'mouse' ) {
					return;
				}
				x = e.clientX;
				y = e.clientY;
				dot.classList.add( 'is-on' );
				kick();
			},
			{ passive: true }
		);

		document.addEventListener( 'pointerdown', function () {
			dot.classList.add( 'is-down' );
		} );
		document.addEventListener( 'pointerup', function () {
			dot.classList.remove( 'is-down' );
		} );
		document.addEventListener( 'mouseleave', function () {
			dot.classList.remove( 'is-on' );
		} );

		// Grow + lock onto interactive targets.
		var hot = 'a, button, input[type=submit], .read-more, [data-m-magnetic], .project-card';
		document.addEventListener( 'pointerover', function ( e ) {
			if ( e.target.closest && e.target.closest( hot ) ) {
				dot.classList.add( 'is-hot' );
			}
		} );
		document.addEventListener( 'pointerout', function ( e ) {
			if ( e.target.closest && e.target.closest( hot ) ) {
				dot.classList.remove( 'is-hot' );
			}
		} );
	}

	/* --------------------------------------------------------------------- */
	function boot() {
		initReveals();
		initCounters();
		initMagnetic();
		initProgress();
		initCursor();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

	// If the user flips reduced-motion on after load, bail to a safe state.
	var onReduceChange = function ( e ) {
		if ( e.matches ) {
			revealAll();
		}
	};
	if ( reduce.addEventListener ) {
		reduce.addEventListener( 'change', onReduceChange );
	} else if ( reduce.addListener ) {
		reduce.addListener( onReduceChange );
	}
} )();
