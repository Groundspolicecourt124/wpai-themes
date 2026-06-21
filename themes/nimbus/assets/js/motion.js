/**
 * Nimbus — Motion system.
 *
 * A self-contained, dependency-free motion layer that gives the theme its
 * "funded startup" feel: a living gradient-mesh hero, a magnetic gradient CTA,
 * subtle 3D card tilt, springy staggered scroll entrances, count-up stats, and
 * a scroll progress bar.
 *
 * Principles:
 *   - Progressive enhancement. Markup ships visible; this file adds the
 *     hidden-then-reveal only when JS runs (the <html class="js"> hook in the
 *     <head> guarantees no-JS users see everything).
 *   - Accessibility first. If the user prefers reduced motion, we reveal
 *     everything immediately, set final stat values, and bind no animations.
 *   - Performance. We animate ONLY transform and opacity; reveals use
 *     IntersectionObserver and unobserve after firing; pointer effects are
 *     rAF-batched so they never thrash layout.
 *
 * @package Nimbus
 */
( function () {
	'use strict';

	var doc = document;
	var root = doc.documentElement;
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/**
	 * Reveal every motion-gated element immediately, with no transition.
	 * Used as the reduced-motion / fallback path so content is never hidden.
	 */
	function revealAll() {
		var nodes = doc.querySelectorAll( '.nm-reveal' );
		for ( var i = 0; i < nodes.length; i++ ) {
			nodes[ i ].classList.add( 'is-inview' );
		}
		var stats = doc.querySelectorAll( '.nimbus-stat__num' );
		for ( var j = 0; j < stats.length; j++ ) {
			var el = stats[ j ];
			var to = el.getAttribute( 'data-count-to' );
			if ( to ) {
				el.textContent = formatStat( parseFloat( to ), el );
			}
		}
	}

	/* ---------------------------------------------------------------------
	 * Scroll reveals — springy, staggered entrances via IntersectionObserver.
	 * ------------------------------------------------------------------- */
	function initReveals() {
		var targets = doc.querySelectorAll( '.nm-reveal' );
		if ( ! targets.length ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			revealAll();
			return;
		}

		var io = new IntersectionObserver( function ( entries, observer ) {
			for ( var i = 0; i < entries.length; i++ ) {
				var entry = entries[ i ];
				if ( ! entry.isIntersecting ) {
					continue;
				}
				var el = entry.target;

				// Stagger siblings that share a group so rows cascade in.
				var delay = parseInt( el.getAttribute( 'data-nm-delay' ), 10 );
				if ( isNaN( delay ) ) {
					delay = staggerWithin( el );
				}
				el.style.transitionDelay = delay + 'ms';
				el.classList.add( 'is-inview' );
				observer.unobserve( el );
			}
		}, {
			rootMargin: '0px 0px -10% 0px',
			threshold: 0.12
		} );

		for ( var i = 0; i < targets.length; i++ ) {
			io.observe( targets[ i ] );
		}
	}

	/**
	 * Compute a stagger delay (ms) from the element's index among its
	 * reveal-siblings, capped so a long list never feels slow.
	 *
	 * @param {Element} el The element being revealed.
	 * @return {number} Delay in milliseconds.
	 */
	function staggerWithin( el ) {
		var parent = el.parentNode;
		if ( ! parent ) {
			return 0;
		}
		var siblings = parent.children;
		var index = 0;
		for ( var i = 0; i < siblings.length; i++ ) {
			if ( siblings[ i ] === el ) {
				break;
			}
			if ( siblings[ i ].classList && siblings[ i ].classList.contains( 'nm-reveal' ) ) {
				index++;
			}
		}
		return Math.min( index, 6 ) * 70;
	}

	/* ---------------------------------------------------------------------
	 * Count-up stats — animates numbers from 0 to their target on reveal.
	 * ------------------------------------------------------------------- */
	function formatStat( value, el ) {
		var decimals = parseInt( el.getAttribute( 'data-count-decimals' ), 10 );
		if ( isNaN( decimals ) ) {
			decimals = 0;
		}
		var out = decimals > 0 ? value.toFixed( decimals ) : String( Math.round( value ) );
		// Thousands separators for big whole numbers (e.g. 12,000).
		if ( decimals === 0 && Math.abs( value ) >= 1000 ) {
			out = Math.round( value ).toLocaleString();
		}
		return out;
	}

	function countUp( el ) {
		var to = parseFloat( el.getAttribute( 'data-count-to' ) );
		if ( isNaN( to ) ) {
			return;
		}
		var duration = 1500;
		var start = null;

		// The server renders the final figure (so no-JS users see it). Now that
		// we're about to animate, reset to 0 first so the count-up is visible.
		el.textContent = formatStat( 0, el );

		function tick( now ) {
			if ( start === null ) {
				start = now;
			}
			var progress = Math.min( ( now - start ) / duration, 1 );
			// easeOutExpo for a brisk-then-settle feel.
			var eased = progress === 1 ? 1 : 1 - Math.pow( 2, -10 * progress );
			el.textContent = formatStat( to * eased, el );
			if ( progress < 1 ) {
				window.requestAnimationFrame( tick );
			} else {
				el.textContent = formatStat( to, el );
			}
		}

		window.requestAnimationFrame( tick );
	}

	function initCounters() {
		var nums = doc.querySelectorAll( '.nimbus-stat__num' );
		if ( ! nums.length ) {
			return;
		}
		if ( ! ( 'IntersectionObserver' in window ) ) {
			for ( var k = 0; k < nums.length; k++ ) {
				var node = nums[ k ];
				var t = node.getAttribute( 'data-count-to' );
				if ( t ) {
					node.textContent = formatStat( parseFloat( t ), node );
				}
			}
			return;
		}

		var io = new IntersectionObserver( function ( entries, observer ) {
			for ( var i = 0; i < entries.length; i++ ) {
				if ( entries[ i ].isIntersecting ) {
					countUp( entries[ i ].target );
					observer.unobserve( entries[ i ].target );
				}
			}
		}, { threshold: 0.6 } );

		for ( var j = 0; j < nums.length; j++ ) {
			io.observe( nums[ j ] );
		}
	}

	/* ---------------------------------------------------------------------
	 * Magnetic CTA — the button springs toward the pointer, then settles.
	 * Transform-only; rAF-batched; pointer-device only (no touch jank).
	 * ------------------------------------------------------------------- */
	function initMagnetic() {
		if ( ! window.matchMedia || ! window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) {
			return;
		}

		var magnets = doc.querySelectorAll( '.nm-magnetic' );

		Array.prototype.forEach.call( magnets, function ( magnet ) {
			var frame = null;
			var strength = parseFloat( magnet.getAttribute( 'data-nm-strength' ) ) || 0.32;

			function onMove( e ) {
				var rect = magnet.getBoundingClientRect();
				var relX = e.clientX - ( rect.left + rect.width / 2 );
				var relY = e.clientY - ( rect.top + rect.height / 2 );
				if ( frame ) {
					window.cancelAnimationFrame( frame );
				}
				frame = window.requestAnimationFrame( function () {
					magnet.style.transform = 'translate3d(' + ( relX * strength ).toFixed( 2 ) + 'px,' + ( relY * strength ).toFixed( 2 ) + 'px,0)';
					var label = magnet.querySelector( '.nm-magnetic__label' );
					if ( label ) {
						label.style.transform = 'translate3d(' + ( relX * strength * 0.45 ).toFixed( 2 ) + 'px,' + ( relY * strength * 0.45 ).toFixed( 2 ) + 'px,0)';
					}
				} );
			}

			function reset() {
				if ( frame ) {
					window.cancelAnimationFrame( frame );
				}
				magnet.style.transform = '';
				var label = magnet.querySelector( '.nm-magnetic__label' );
				if ( label ) {
					label.style.transform = '';
				}
			}

			magnet.addEventListener( 'pointermove', onMove );
			magnet.addEventListener( 'pointerleave', reset );
			magnet.addEventListener( 'blur', reset );
		} );
	}

	/* ---------------------------------------------------------------------
	 * 3D card tilt — a small, premium parallax tilt on hover.
	 * Transform-only; rAF-batched; pointer-device only.
	 * ------------------------------------------------------------------- */
	function initTilt() {
		if ( ! window.matchMedia || ! window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) {
			return;
		}

		var cards = doc.querySelectorAll( '.nm-tilt' );
		var MAX = 5; // degrees — kept small so it reads premium, not gimmicky.

		Array.prototype.forEach.call( cards, function ( card ) {
			var frame = null;

			function onMove( e ) {
				var rect = card.getBoundingClientRect();
				var px = ( e.clientX - rect.left ) / rect.width;
				var py = ( e.clientY - rect.top ) / rect.height;
				var rotX = ( 0.5 - py ) * ( MAX * 2 );
				var rotY = ( px - 0.5 ) * ( MAX * 2 );
				if ( frame ) {
					window.cancelAnimationFrame( frame );
				}
				frame = window.requestAnimationFrame( function () {
					card.style.transform = 'perspective(900px) rotateX(' + rotX.toFixed( 2 ) + 'deg) rotateY(' + rotY.toFixed( 2 ) + 'deg) translateZ(0)';
				} );
			}

			function reset() {
				if ( frame ) {
					window.cancelAnimationFrame( frame );
				}
				card.style.transform = '';
			}

			card.addEventListener( 'pointerenter', function () {
				card.classList.add( 'is-tilting' );
			} );
			card.addEventListener( 'pointermove', onMove );
			card.addEventListener( 'pointerleave', function () {
				card.classList.remove( 'is-tilting' );
				reset();
			} );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Scroll progress bar — pure transform (scaleX), no layout reads on scroll.
	 * ------------------------------------------------------------------- */
	function initProgress() {
		var bar = doc.querySelector( '.nm-progress__fill' );
		if ( ! bar ) {
			return;
		}
		var ticking = false;

		function update() {
			var scrollTop = window.pageYOffset || root.scrollTop;
			var height = root.scrollHeight - root.clientHeight;
			var ratio = height > 0 ? Math.min( scrollTop / height, 1 ) : 0;
			bar.style.transform = 'scaleX(' + ratio + ')';
			ticking = false;
		}

		function onScroll() {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		}

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onScroll, { passive: true } );
		update();
	}

	/* ---------------------------------------------------------------------
	 * Boot.
	 * ------------------------------------------------------------------- */
	function init() {
		// Tell CSS that motion is live (entrance start-states only apply here).
		root.classList.add( 'nm-motion' );

		if ( reduceMotion ) {
			// Respect the user: show everything, no animation, final values set.
			revealAll();
			return;
		}

		initReveals();
		initCounters();
		initMagnetic();
		initTilt();
		initProgress();
	}

	if ( doc.readyState === 'loading' ) {
		doc.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
