/**
 * Oracle — FAQ & Schema (behavior layer).
 *
 * Progressive enhancement for the [oracle_faq] accordion. Everything here is
 * optional polish: with this file absent, blocked, or erroring, every answer
 * stays fully expanded and readable (the panels keep their no-JS visible state).
 *
 * What it adds:
 *   - Collapse / expand of each answer panel, driven by its native <button>
 *     trigger. The button reflects state through aria-expanded so it is correct
 *     for assistive technology; the panel's `hidden` attribute is removed so the
 *     CSS grid-rows collapse can animate it open/closed.
 *   - Keyboard support beyond the native button activation: Up/Down/Home/End move
 *     focus between question triggers (the WAI-ARIA accordion pattern). Enter and
 *     Space already toggle the focused button natively.
 *
 * Constraints, kept in lockstep with the CSS:
 *   - Animate transform / opacity (the chevron) and a GPU-cheap grid-rows
 *     collapse only — no layout-thrashing properties, no cumulative layout shift.
 *   - prefers-reduced-motion is read live (no reload needed) and also enforced by
 *     the stylesheet. No globals, no dependencies, no network, no console noise.
 *
 * @package OracleFaq
 */
( function () {
	'use strict';

	var groups = document.querySelectorAll( '[data-oracle-faq]' );
	if ( ! groups.length ) {
		return;
	}

	// Live reduced-motion gate. The stylesheet already neutralizes the panel /
	// chevron transitions via @media (prefers-reduced-motion: reduce); this mirror
	// is read fresh on every toggle (no reload needed) so the script never relies
	// on motion playing and stays correct if the user flips the OS setting at
	// runtime. Guarded for very old browsers without matchMedia.
	function prefersReducedMotion() {
		return (
			typeof window.matchMedia === 'function' &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches
		);
	}

	/**
	 * Set an item's open/closed state, reflecting it on the trigger and panel.
	 *
	 * @param {Element} trigger The question <button>.
	 * @param {Element} panel   The answer panel region.
	 * @param {Element} item    The item wrapper.
	 * @param {boolean} open    Whether the item should be open.
	 */
	function setOpen( trigger, panel, item, open ) {
		trigger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

		// Honor reduced motion live: snap the panel open/closed (no transition)
		// when requested, otherwise let the stylesheet's transition play. We only
		// touch transition timing here — never any layout-thrashing property — so
		// motion is suppressed without changing what is shown.
		if ( prefersReducedMotion() ) {
			panel.style.transition = 'none';
		} else {
			panel.style.removeProperty( 'transition' );
		}

		item.classList.toggle( 'is-open', open );

		// Once the script controls the panel, the CSS owns visibility: the
		// grid-rows track collapses to 0fr (with overflow:hidden) when closed and
		// expands to 1fr when open, driven by the `is-open` class toggled above.
		// We deliberately do NOT re-add the `hidden` attribute on close: re-adding
		// it would (a) be overridden by the `.oracle-js .oracle-faq__panel`
		// display rule yet still sit in the accessibility tree, contradicting
		// aria-expanded, and (b) hard-hide the panel if the stylesheet ever fails
		// to load, breaking the "never stuck hidden" guarantee. Removing it here
		// keeps the closing transition playing and keeps content fail-open.
		panel.removeAttribute( 'hidden' );
	}

	Array.prototype.forEach.call( groups, function ( group ) {
		var items = Array.prototype.slice.call(
			group.querySelectorAll( '[data-oracle-item]' )
		);
		if ( ! items.length ) {
			return;
		}

		var triggers = [];

		items.forEach( function ( item ) {
			var trigger = item.querySelector( '[data-oracle-trigger]' );
			var panel = item.querySelector( '[data-oracle-panel]' );

			if ( ! trigger || ! panel ) {
				return;
			}

			triggers.push( trigger );

			// Take control: start collapsed. The CSS grid-rows collapse only
			// applies under .oracle-js, which is set before first paint, so the
			// panel begins closed without a flash. Removing `hidden` here lets the
			// CSS (not the attribute) own visibility from now on.
			panel.removeAttribute( 'hidden' );
			setOpen( trigger, panel, item, false );

			trigger.addEventListener( 'click', function () {
				var open = trigger.getAttribute( 'aria-expanded' ) === 'true';
				setOpen( trigger, panel, item, ! open );
			} );
		} );

		// WAI-ARIA accordion keyboard support: move focus between triggers.
		group.addEventListener( 'keydown', function ( event ) {
			var index = triggers.indexOf( event.target );
			if ( index === -1 ) {
				return; // The event came from inside a panel, not a trigger.
			}

			var next = null;

			switch ( event.key ) {
				case 'ArrowDown':
					next = triggers[ index + 1 ] || triggers[ 0 ];
					break;
				case 'ArrowUp':
					next = triggers[ index - 1 ] || triggers[ triggers.length - 1 ];
					break;
				case 'Home':
					next = triggers[ 0 ];
					break;
				case 'End':
					next = triggers[ triggers.length - 1 ];
					break;
				default:
					return; // Let every other key (incl. Enter/Space) behave natively.
			}

			if ( next ) {
				event.preventDefault();
				next.focus();
			}
		} );
	} );
}() );
