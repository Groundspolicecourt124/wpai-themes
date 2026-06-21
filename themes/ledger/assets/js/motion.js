/**
 * Ledger — motion system.
 *
 * A crisp, journalistic enhancement layer for the Ledger editorial theme:
 *
 *   1. Staggered scroll reveals  — story columns, articles, and section rules
 *      rise into place via IntersectionObserver (transform + opacity only).
 *   2. Drawing rule-lines        — the masthead hairlines and section-heading
 *      rules "draw" across when they enter the viewport (scaleX).
 *   3. "LATEST" ticker           — a seamless, pausable headline marquee.
 *   4. Animated dateline         — the edition line types/reveals on load.
 *   5. Reading-progress bar      — a thin red rule fills as you read an article.
 *
 * Self-contained: no libraries, no CDNs. Vanilla JS + CSS. Everything is a
 * progressive enhancement — markup ships visible, and the whole module is
 * skipped (revealing all content immediately) when the visitor prefers
 * reduced motion. Only transform and opacity are animated.
 *
 * @package Ledger
 */
(function () {
	'use strict';

	var root = document.documentElement;
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/**
	 * Reveal everything immediately: strip the motion hooks so nothing is left
	 * hidden. Used for reduced-motion visitors and as the IO fallback.
	 */
	function revealAll() {
		var hidden = document.querySelectorAll('[data-reveal]');
		for (var i = 0; i < hidden.length; i++) {
			hidden[i].removeAttribute('data-reveal');
		}
	}

	// Honour the OS-level "reduce motion" setting: show all content, wire up no
	// animation. The CSS @media block already neutralises transitions/marquee.
	if (reduce) {
		root.classList.add('l-motion-off');
		revealAll();
		return;
	}

	root.classList.add('l-motion-on');

	/* ----------------------------------------------------------------- *
	 * 1 + 2. Scroll reveals (staggered) and drawing rule-lines.
	 *
	 * Any element tagged with [data-reveal] starts in a hidden entrance
	 * state (defined in CSS) and flips to .is-revealed when it scrolls into
	 * view. Children inside a [data-stagger] container inherit an index via
	 * a CSS custom property so they cascade in one after another.
	 * ----------------------------------------------------------------- */
	function initReveals() {
		var targets = document.querySelectorAll('[data-reveal]');
		if (!targets.length) {
			return;
		}

		// Assign per-child stagger indices so the CSS can offset each one.
		var groups = document.querySelectorAll('[data-stagger]');
		for (var g = 0; g < groups.length; g++) {
			var kids = groups[g].querySelectorAll('[data-reveal]');
			for (var k = 0; k < kids.length; k++) {
				// Cap the cascade so a long archive never feels slow.
				kids[k].style.setProperty('--l-reveal-i', Math.min(k, 6));
			}
		}

		if (!('IntersectionObserver' in window)) {
			// No IO support: just show everything rather than leave it hidden.
			revealAll();
			return;
		}

		var io = new IntersectionObserver(function (entries, obs) {
			for (var i = 0; i < entries.length; i++) {
				var entry = entries[i];
				if (entry.isIntersecting) {
					entry.target.classList.add('is-revealed');
					// One-shot: never re-hide once it has been seen.
					obs.unobserve(entry.target);
				}
			}
		}, {
			root: null,
			// Trigger a touch before the element is fully on-screen, and don't
			// fire for content already scrolled past at the bottom.
			rootMargin: '0px 0px -12% 0px',
			threshold: 0.08
		});

		for (var t = 0; t < targets.length; t++) {
			io.observe(targets[t]);
		}
	}

	/* ----------------------------------------------------------------- *
	 * 3. "LATEST" ticker.
	 *
	 * The track is already duplicated in markup. We measure one group and
	 * drive a single translateX loop with requestAnimationFrame so the speed
	 * is pixel-consistent regardless of how many headlines exist. Pauses on
	 * hover/focus (so links are reachable) and when the tab is hidden.
	 * ----------------------------------------------------------------- */
	function initTicker() {
		var ticker = document.querySelector('.l-ticker');
		if (!ticker) {
			return;
		}
		var track = ticker.querySelector('.l-ticker__track');
		var group = ticker.querySelector('.l-ticker__group');
		if (!track || !group) {
			return;
		}

		var offset = 0;
		var speed = 40; // px per second — calm, newswire pace.
		var last = null;
		var paused = false;
		var rafId = null;
		var groupWidth = group.getBoundingClientRect().width;

		function step(now) {
			if (last === null) {
				last = now;
			}
			var dt = (now - last) / 1000;
			last = now;

			if (!paused && groupWidth > 0) {
				offset -= speed * dt;
				// Loop seamlessly: once a full group has passed, wrap back.
				if (-offset >= groupWidth) {
					offset += groupWidth;
				}
				track.style.transform = 'translate3d(' + offset.toFixed(2) + 'px,0,0)';
			}
			rafId = window.requestAnimationFrame(step);
		}

		function pause() { paused = true; }
		function resume() { last = null; paused = false; }

		ticker.addEventListener('mouseenter', pause);
		ticker.addEventListener('mouseleave', resume);
		ticker.addEventListener('focusin', pause);
		ticker.addEventListener('focusout', resume);

		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				pause();
			} else {
				resume();
			}
		});

		function remeasure() {
			groupWidth = group.getBoundingClientRect().width;
		}

		// Re-measure on resize (font reflow can change the group width).
		var resizeTimer = null;
		window.addEventListener('resize', function () {
			window.clearTimeout(resizeTimer);
			resizeTimer = window.setTimeout(remeasure, 200);
		});

		// Web-font swap can change the group width after first paint.
		window.addEventListener('load', remeasure);
		if (document.fonts && document.fonts.ready && typeof document.fonts.ready.then === 'function') {
			document.fonts.ready.then(remeasure);
		}

		ticker.classList.add('is-live');
		rafId = window.requestAnimationFrame(step);
	}

	/* ----------------------------------------------------------------- *
	 * 4. Animated dateline.
	 *
	 * The edition strip starts ink-dim and settles to full on load — a small
	 * "the press has started" cue. Handled purely by toggling a class the CSS
	 * transitions; no layout is touched.
	 * ----------------------------------------------------------------- */
	function initDateline() {
		var dateline = document.querySelector('.site-dateline');
		if (!dateline) {
			return;
		}
		// Next frame so the transition actually runs from the start state.
		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(function () {
				dateline.classList.add('is-set');
			});
		});
	}

	/* ----------------------------------------------------------------- *
	 * 5. Reading-progress bar (single posts only).
	 *
	 * A thin rule under the nav fills from 0 to 100% across the body of the
	 * article. Driven by scroll, throttled with requestAnimationFrame, and
	 * only ever writes transform: scaleX — no layout, no reflow.
	 * ----------------------------------------------------------------- */
	function initProgress() {
		var bar = document.querySelector('.l-progress__bar');
		var article = document.querySelector('[data-reading="article"] .entry-content');
		if (!bar || !article) {
			return;
		}

		var ticking = false;

		function update() {
			ticking = false;
			var rect = article.getBoundingClientRect();
			var viewport = window.innerHeight || document.documentElement.clientHeight;
			// Distance the article top must travel for the body to be fully read.
			var total = rect.height - viewport;
			var progress;

			if (total <= 0) {
				// Short article: fully read as soon as its top reaches the top.
				progress = rect.top <= 0 ? 1 : 0;
			} else {
				progress = (-rect.top) / total;
			}

			progress = Math.max(0, Math.min(1, progress));
			bar.style.transform = 'scaleX(' + progress + ')';
		}

		function onScroll() {
			if (!ticking) {
				ticking = true;
				window.requestAnimationFrame(update);
			}
		}

		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll, { passive: true });
		update();
	}

	/* --------------------------- bootstrap --------------------------- */
	function init() {
		// Dateline first so its on-load cue resolves even if a later
		// init throws; each module is independently wrapped so one
		// failure never leaves content hidden.
		try { initDateline(); } catch (e) {}
		try { initReveals(); } catch (e) { revealAll(); }
		try { initTicker(); } catch (e) {}
		try { initProgress(); } catch (e) {}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
