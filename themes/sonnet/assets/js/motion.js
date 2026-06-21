/**
 * Sonnet — motion system.
 *
 * A slow, literary motion layer for the Sonnet theme: long graceful fade
 * reveals, a thin gold reading-progress line, a drop-cap that fades and scales
 * in, a gold shimmer that sweeps the eyebrows, a refined trailing caret, and the
 * signature constellation that drifts behind the page like ink on dark paper.
 *
 * Self-contained vanilla JS — no libraries, no CDNs. Loaded deferred in the
 * footer. Everything is progressive enhancement: the markup ships fully visible,
 * and this script only *adds* the hidden-then-reveal once it confirms motion is
 * welcome. Honours prefers-reduced-motion (reduce): if the user asks for less
 * motion we reveal everything immediately and draw nothing.
 *
 * Animates transform/opacity only. No layout thrash, no scroll-jacking, no CLS.
 *
 * @package Sonnet
 */
( function () {
	'use strict';

	var root = document.documentElement;
	var reduceQuery = window.matchMedia
		? window.matchMedia( '(prefers-reduced-motion: reduce)' )
		: { matches: false };

	/* Mark the document as motion-ready so the CSS can switch on the
	   hidden-then-reveal entrance states. Without this class (no JS, or the
	   branch below for reduced motion) the page renders in its final state. */
	root.classList.add( 'sonnet-motion-ready' );

	/* ------------------------------------------------------------------ *
	 * Reduced motion: bail out gracefully. Reveal everything, draw nothing.
	 * ------------------------------------------------------------------ */
	if ( reduceQuery.matches ) {
		root.classList.add( 'sonnet-reveal-all' );
		return;
	}

	/* A small helper so we never assume a feature exists. */
	var supportsIO = 'IntersectionObserver' in window;
	var rafqueued = false;

	/* ================================================================== *
	 * 1. Scroll reveals — IntersectionObserver with a tasteful stagger.
	 *
	 * Elements tagged [data-sonnet-reveal] start lowered + transparent (via
	 * CSS) and ease up into place as they enter the viewport. Siblings sharing
	 * a [data-sonnet-stagger] container reveal in sequence for a soft cascade.
	 * Each element is unobserved the moment it lands.
	 * ================================================================== */
	function collectTargets() {
		return Array.prototype.slice.call(
			document.querySelectorAll( '[data-sonnet-reveal]' )
		);
	}

	function reveal( el ) {
		el.classList.add( 'is-revealed' );
	}

	function initReveals() {
		var targets = collectTargets();
		if ( ! targets.length ) {
			return;
		}

		/* No IO support — just show everything and move on. */
		if ( ! supportsIO ) {
			targets.forEach( reveal );
			return;
		}

		var observer = new IntersectionObserver( function ( entries, obs ) {
			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) {
					return;
				}

				var el = entry.target;

				/* Stagger: offset this element by its index within a
				   [data-sonnet-stagger] group so a list cascades in. */
				var group = el.closest( '[data-sonnet-stagger]' );
				var delay = 0;
				if ( group ) {
					var siblings = Array.prototype.slice.call(
						group.querySelectorAll( '[data-sonnet-reveal]' )
					);
					var i = siblings.indexOf( el );
					if ( i > 0 ) {
						/* Cap the cascade so a long list never feels sluggish. */
						delay = Math.min( i, 6 ) * 90;
					}
				}

				if ( delay ) {
					el.style.transitionDelay = delay + 'ms';
				}

				reveal( el );
				obs.unobserve( el );
			} );
		}, {
			/* Reveal a touch before the element fully enters, and only once
			   it is meaningfully on screen — feels intentional, not twitchy. */
			rootMargin: '0px 0px -12% 0px',
			threshold: 0.15,
		} );

		targets.forEach( function ( el ) {
			observer.observe( el );
		} );

		/* Safety net: anything still hidden after load (e.g. tall above-the-fold
		   content that never triggers an intersection) gets revealed. */
		window.addEventListener( 'load', function () {
			setTimeout( function () {
				targets.forEach( function ( el ) {
					if ( ! el.classList.contains( 'is-revealed' ) ) {
						var r = el.getBoundingClientRect();
						if ( r.top < window.innerHeight ) {
							reveal( el );
							observer.unobserve( el );
						}
					}
				} );
			}, 200 );
		} );
	}

	/* ================================================================== *
	 * 2. Reading-progress line — thin gold bar at the top of the viewport.
	 *    Scroll-driven, scaled on the X axis (transform only), rAF-throttled.
	 * ================================================================== */
	function initProgress() {
		var track = document.querySelector( '.sonnet-progress' );
		var bar = track && track.querySelector( '.sonnet-progress__bar' );
		if ( ! track || ! bar ) {
			return;
		}

		function update() {
			rafqueued = false;
			var doc = document.documentElement;
			var scrollable = doc.scrollHeight - window.innerHeight;
			var ratio = scrollable > 0
				? Math.min( 1, Math.max( 0, window.scrollY / scrollable ) )
				: 0;
			bar.style.transform = 'scaleX(' + ratio + ')';
		}

		function onScroll() {
			if ( ! rafqueued ) {
				rafqueued = true;
				window.requestAnimationFrame( update );
			}
		}

		track.classList.add( 'is-active' );
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onScroll, { passive: true } );
		update();
	}

	/* ================================================================== *
	 * 3. Drop-cap reveal — fade + scale the first letter of the lead-in.
	 *    ::first-letter can't be animated, so we wrap it in a real span and
	 *    let CSS transition the wrapper once it scrolls into view.
	 * ================================================================== */
	function initDropCap() {
		var hosts = document.querySelectorAll(
			'.entry--single .entry-content > p:first-of-type, .lead-essay__excerpt.has-dropcap > p:first-of-type'
		);
		if ( ! hosts.length ) {
			return;
		}

		hosts.forEach( function ( p ) {
			/* Find the first text node with a visible character. */
			var node = p.firstChild;
			while ( node && node.nodeType === 3 && ! /\S/.test( node.nodeValue ) ) {
				node = node.nextSibling;
			}
			if ( ! node || node.nodeType !== 3 ) {
				return;
			}

			var text = node.nodeValue;
			var match = text.match( /^(\s*)(\S)/ );
			if ( ! match ) {
				return;
			}

			var lead = match[ 1 ];
			var letter = match[ 2 ];
			var rest = text.slice( match[ 0 ].length );

			var frag = document.createDocumentFragment();
			if ( lead ) {
				frag.appendChild( document.createTextNode( lead ) );
			}
			var cap = document.createElement( 'span' );
			cap.className = 'sonnet-dropcap';
			cap.textContent = letter;
			frag.appendChild( cap );
			frag.appendChild( document.createTextNode( rest ) );

			p.replaceChild( frag, node );
			p.classList.add( 'has-js-dropcap' );

			if ( supportsIO ) {
				var io = new IntersectionObserver( function ( entries, obs ) {
					entries.forEach( function ( entry ) {
						if ( entry.isIntersecting ) {
							cap.classList.add( 'is-revealed' );
							obs.unobserve( entry.target );
						}
					} );
				}, { threshold: 0.4 } );
				io.observe( cap );
			} else {
				cap.classList.add( 'is-revealed' );
			}
		} );
	}

	/* ================================================================== *
	 * 4. Refined caret — a soft gold ring that trails the pointer.
	 *    Fine pointers (mouse/trackpad) only; never shown on touch. Pure
	 *    transform translation, lerp-smoothed for a graceful lag.
	 * ================================================================== */
	function initCaret() {
		var caret = document.querySelector( '.sonnet-caret' );
		if ( ! caret || ! window.matchMedia ) {
			return;
		}
		/* Only for precise pointers that can hover — skip touch entirely. */
		if ( ! window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) {
			return;
		}

		var tx = window.innerWidth / 2;
		var ty = window.innerHeight / 2;
		var cx = tx;
		var cy = ty;
		var running = false;
		var visible = false;

		function tick() {
			/* Ease toward the target; a small factor = a slow, literary trail. */
			cx += ( tx - cx ) * 0.18;
			cy += ( ty - cy ) * 0.18;
			caret.style.transform =
				'translate3d(' + cx + 'px,' + cy + 'px,0) translate(-50%,-50%)';

			if ( Math.abs( tx - cx ) > 0.1 || Math.abs( ty - cy ) > 0.1 ) {
				window.requestAnimationFrame( tick );
			} else {
				running = false;
			}
		}

		function kick() {
			if ( ! running ) {
				running = true;
				window.requestAnimationFrame( tick );
			}
		}

		window.addEventListener( 'pointermove', function ( e ) {
			if ( e.pointerType && e.pointerType !== 'mouse' ) {
				return;
			}
			tx = e.clientX;
			ty = e.clientY;
			if ( ! visible ) {
				visible = true;
				caret.classList.add( 'is-visible' );
			}
			/* Grow the ring over interactive targets for a tactile cue. */
			var overLink = !! ( e.target && e.target.closest &&
				e.target.closest( 'a, button, input, textarea, select, [role="button"]' ) );
			caret.classList.toggle( 'is-active', overLink );
			kick();
		}, { passive: true } );

		window.addEventListener( 'pointerdown', function () {
			caret.classList.add( 'is-press' );
		}, { passive: true } );
		window.addEventListener( 'pointerup', function () {
			caret.classList.remove( 'is-press' );
		}, { passive: true } );

		document.addEventListener( 'mouseleave', function () {
			visible = false;
			caret.classList.remove( 'is-visible' );
		} );
	}

	/* ================================================================== *
	 * 5. Signature — the gold constellation.
	 *    A field of faint stars drifts slowly behind the page; near neighbours
	 *    are threaded with hairline gold lines, and a soft halo wakes around
	 *    the pointer. Capped DPR, count scaled to viewport, paused when the tab
	 *    is hidden. Decorative + aria-hidden; never touches layout.
	 * ================================================================== */
	function initConstellation() {
		var canvas = document.querySelector( '.sonnet-constellation' );
		if ( ! canvas || ! canvas.getContext ) {
			return;
		}

		var ctx = canvas.getContext( '2d' );
		var dpr = Math.min( window.devicePixelRatio || 1, 2 );
		var w = 0;
		var h = 0;
		var stars = [];
		var rafId = null;
		var pointer = { x: -9999, y: -9999, active: false };

		/* Read the theme's live accent so the stars match the (customizable)
		   gold. Falls back to the token default if the property is unreadable. */
		var accent = '203, 163, 93';
		try {
			var raw = getComputedStyle( root )
				.getPropertyValue( '--s-accent' ).trim();
			var rgb = hexToRgb( raw );
			if ( rgb ) {
				accent = rgb;
			}
		} catch ( e ) {} // eslint-disable-line no-empty

		function hexToRgb( hex ) {
			if ( ! hex || hex.charAt( 0 ) !== '#' ) {
				return null;
			}
			var c = hex.slice( 1 );
			if ( c.length === 3 ) {
				c = c[ 0 ] + c[ 0 ] + c[ 1 ] + c[ 1 ] + c[ 2 ] + c[ 2 ];
			}
			if ( c.length !== 6 ) {
				return null;
			}
			var n = parseInt( c, 16 );
			if ( isNaN( n ) ) {
				return null;
			}
			return ( ( n >> 16 ) & 255 ) + ',' + ( ( n >> 8 ) & 255 ) + ',' + ( n & 255 );
		}

		function makeStars() {
			/* Density scaled to area, but firmly capped for performance. */
			var area = w * h;
			var count = Math.min( 90, Math.max( 28, Math.round( area / 26000 ) ) );
			stars = [];
			for ( var i = 0; i < count; i++ ) {
				stars.push( {
					x: Math.random() * w,
					y: Math.random() * h,
					/* Very slow drift — cinematic, never busy. */
					vx: ( Math.random() - 0.5 ) * 0.12,
					vy: ( Math.random() - 0.5 ) * 0.12,
					r: Math.random() * 1.3 + 0.4,
					base: Math.random() * 0.35 + 0.15,
					tw: Math.random() * Math.PI * 2,
					tws: Math.random() * 0.015 + 0.004,
				} );
			}
		}

		function resize() {
			w = canvas.clientWidth = window.innerWidth;
			h = canvas.clientHeight = window.innerHeight;
			canvas.width = Math.round( w * dpr );
			canvas.height = Math.round( h * dpr );
			ctx.setTransform( dpr, 0, 0, dpr, 0, 0 );
			makeStars();
		}

		function draw() {
			ctx.clearRect( 0, 0, w, h );

			var i;
			var s;
			var linkDist = 130;
			var linkDist2 = linkDist * linkDist;

			/* Threads between near neighbours — drawn first, behind the stars. */
			for ( i = 0; i < stars.length; i++ ) {
				for ( var j = i + 1; j < stars.length; j++ ) {
					var dx = stars[ i ].x - stars[ j ].x;
					var dy = stars[ i ].y - stars[ j ].y;
					var d2 = dx * dx + dy * dy;
					if ( d2 < linkDist2 ) {
						var a = ( 1 - d2 / linkDist2 ) * 0.10;
						ctx.strokeStyle = 'rgba(' + accent + ',' + a.toFixed( 3 ) + ')';
						ctx.lineWidth = 0.6;
						ctx.beginPath();
						ctx.moveTo( stars[ i ].x, stars[ i ].y );
						ctx.lineTo( stars[ j ].x, stars[ j ].y );
						ctx.stroke();
					}
				}
			}

			/* Stars themselves, twinkling and drifting. */
			for ( i = 0; i < stars.length; i++ ) {
				s = stars[ i ];

				s.x += s.vx;
				s.y += s.vy;
				s.tw += s.tws;

				/* Wrap softly around the edges. */
				if ( s.x < -10 ) { s.x = w + 10; }
				if ( s.x > w + 10 ) { s.x = -10; }
				if ( s.y < -10 ) { s.y = h + 10; }
				if ( s.y > h + 10 ) { s.y = -10; }

				var twinkle = ( Math.sin( s.tw ) + 1 ) / 2;
				var alpha = s.base + twinkle * 0.4;

				/* A faint halo wakes near the pointer. */
				if ( pointer.active ) {
					var pdx = s.x - pointer.x;
					var pdy = s.y - pointer.y;
					var pd2 = pdx * pdx + pdy * pdy;
					if ( pd2 < 18000 ) {
						alpha += ( 1 - pd2 / 18000 ) * 0.5;
					}
				}
				alpha = Math.min( 1, alpha );

				ctx.beginPath();
				ctx.fillStyle = 'rgba(' + accent + ',' + alpha.toFixed( 3 ) + ')';
				ctx.arc( s.x, s.y, s.r, 0, Math.PI * 2 );
				ctx.fill();
			}

			rafId = window.requestAnimationFrame( draw );
		}

		function start() {
			if ( rafId === null ) {
				rafId = window.requestAnimationFrame( draw );
			}
		}
		function stop() {
			if ( rafId !== null ) {
				window.cancelAnimationFrame( rafId );
				rafId = null;
			}
		}

		var resizeTimer = null;
		window.addEventListener( 'resize', function () {
			window.clearTimeout( resizeTimer );
			resizeTimer = window.setTimeout( resize, 180 );
		}, { passive: true } );

		window.addEventListener( 'pointermove', function ( e ) {
			if ( e.pointerType && e.pointerType !== 'mouse' ) {
				return;
			}
			pointer.x = e.clientX;
			pointer.y = e.clientY;
			pointer.active = true;
		}, { passive: true } );
		window.addEventListener( 'pointerleave', function () {
			pointer.active = false;
		}, { passive: true } );

		/* Pause work entirely when the tab is backgrounded. */
		document.addEventListener( 'visibilitychange', function () {
			if ( document.hidden ) {
				stop();
			} else {
				start();
			}
		} );

		resize();
		canvas.classList.add( 'is-lit' );
		start();
	}

	/* ------------------------------------------------------------------ *
	 * Boot. Run once the DOM is parsed. (Deferred scripts already wait for
	 * the parser, but guard anyway in case of unusual load orders.)
	 * ------------------------------------------------------------------ */
	function boot() {
		initReveals();
		initProgress();
		initDropCap();
		initCaret();
		initConstellation();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
	} else {
		boot();
	}

	/* If the user flips on reduced motion mid-session, stop adding motion and
	   reveal anything still pending. (Older Safari uses addListener.) */
	function onReduceChange( e ) {
		if ( e.matches ) {
			root.classList.add( 'sonnet-reveal-all' );
		}
	}
	if ( reduceQuery.addEventListener ) {
		reduceQuery.addEventListener( 'change', onReduceChange );
	} else if ( reduceQuery.addListener ) {
		reduceQuery.addListener( onReduceChange );
	}
} )();
