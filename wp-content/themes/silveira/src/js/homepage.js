/**
 * Homepage: navigation between three states.
 *
 *   collapsed  hero visible, map locked
 *   expanded   hero out, map unlocked, the footer strip peeks in
 *   footer     footer revealed
 *
 * The state lives in `html[data-home]` and the CSS reacts to it (see _main.scss and _map.scss).
 * While the transition runs, `html[data-home-busy="<origin state>"]` is set; it is released on the
 * content's `transitionend` or, if that never arrives, after SAFETY_MS. With reduced motion there
 * is no lock period.
 *
 * Without this script there is no `data-home` and the homepage is browsed with native scrolling.
 *
 * Events:
 *   listens   `silveira:slide-up`     (map.js, when a marker is clicked) → expanded
 *   emits     `silveira:home-change`  detail { state, settled } on each change and when it settles
 */

const SAFETY_MS = 1000;
const SWIPE_PX = 10;
const WHEEL_PX = 5;

const root = document.documentElement;
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

/** Is focus on a control where keys have another use (typing, panning the map, scrolling the list)? */
const isKeyExempt = (target) => {
	if (!(target instanceof Element)) return false;
	if (
		target.closest(
			'.leaflet-container, .c-map-panel, [contenteditable]:not([contenteditable="false"])',
		)
	) {
		return true;
	}
	if (target.matches('textarea, select')) return true;
	if (target.matches('input')) {
		return !['checkbox', 'radio', 'button', 'submit', 'reset'].includes(target.type);
	}
	return false;
};

/**
 * States in which an element can hold focus; if the current one is not in the list, switch to the
 * first. `null`: any (the header sits outside .l-site-content and is always visible).
 *
 * @param {Element} target
 * @returns {string[]|null}
 */
const focusStates = (target) => {
	if (target.closest('.c-footer__reveal')) return ['expanded', 'footer'];
	if (target.closest('.c-footer')) return ['footer'];
	if (target.closest('.c-filter-bar, .c-map')) return ['expanded'];
	if (target.closest('.c-hero')) return ['collapsed'];
	return null;
};

/**
 * States in which an element is visible. Same as focusStates, except the filters: they are also
 * visible with the hero, even though focusing them expands the map.
 *
 * @param {Element} target
 * @returns {string[]|null}
 */
const visibleStates = (target) =>
	target.closest('.c-filter-bar') ? ['collapsed', 'expanded'] : focusStates(target);

const initHomepage = () => {
	const siteContent = document.querySelector('.l-site-content');
	const hero = document.querySelector('.c-hero');
	const header = document.querySelector('.c-header');
	const filterBar = document.querySelector('.c-filter-bar');
	const mapWrapper = document.querySelector('.c-map');
	const footer = document.querySelector('.c-footer');
	const reveal = document.querySelector('.c-footer__reveal');
	if (!siteContent || !hero || !mapWrapper || !footer) return;

	// --- Measurements (design D5) ---
	const measure = () => {
		const set = (name, node) => {
			if (node) siteContent.style.setProperty(name, `${node.offsetHeight}px`);
		};
		set('--sil-header-height', header);
		set('--sil-hero-height', hero);
		set('--sil-filter-height', filterBar);
		set('--sil-footer-height', footer);
	};
	measure();
	if ('ResizeObserver' in window) {
		const observer = new ResizeObserver(measure);
		[header, hero, filterBar, footer].forEach((node) => node && observer.observe(node));
	} else {
		window.addEventListener('resize', measure);
	}

	// --- State machine (design D1) ---
	let state = 'collapsed';
	let busy = false;
	let safetyTimer = 0;

	const emit = (settled) =>
		window.dispatchEvent(new CustomEvent('silveira:home-change', { detail: { state, settled } }));

	const settle = () => {
		window.clearTimeout(safetyTimer);
		if (!busy) return;
		busy = false;
		delete root.dataset.homeBusy;
		emit(true);
	};

	/**
	 * Changes state.
	 *
	 * @param {string} next
	 * @param {{force?: boolean}} [options] force: even if a transition is in progress (focus wins).
	 */
	const go = (next, { force = false } = {}) => {
		if (next === state || (busy && !force)) return;
		const previous = state;
		state = next;
		root.dataset.home = next;
		if (reveal) reveal.setAttribute('aria-expanded', String(next === 'footer'));

		if (reducedMotion.matches) {
			settle();
			emit(true);
			return;
		}
		busy = true;
		root.dataset.homeBusy = previous; // CSS waits for the end only when leaving the hero

		window.clearTimeout(safetyTimer);
		safetyTimer = window.setTimeout(settle, SAFETY_MS);
		emit(false);
	};

	siteContent.addEventListener('transitionend', (e) => {
		if (e.target === siteContent && e.propertyName === 'transform') settle();
	});

	// Initial state: CSS only locks native scrolling when `data-home` exists (design D7).
	if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
	root.dataset.home = state;
	window.scrollTo(0, 0);
	// With overflow: hidden the document still scrolls when something off-screen gets focus; the
	// offset comes from the transform, so that scroll is cancelled.
	window.addEventListener('scroll', () => {
		if (window.scrollY !== 0) window.scrollTo(0, 0);
	});

	// --- Mouse and touch (previous behaviour, design D2) ---
	const onFilterOrHeader = (target) =>
		target instanceof Element && !!target.closest('.c-filter-bar, .c-header');

	document.querySelectorAll('.c-select').forEach((select) => {
		select.addEventListener('click', () => {
			if (state === 'collapsed') go('expanded');
		});
	});

	window.addEventListener('silveira:slide-up', () => {
		if (state === 'collapsed') go('expanded');
	});

	// Mouse only: on touch, a tap's emulated pointerenter would open it and the click close it again.
	footer.addEventListener('pointerenter', (e) => {
		if (e.pointerType === 'mouse' && state === 'expanded') go('footer');
	});
	footer.addEventListener('pointerleave', (e) => {
		// Only when leaving through the top.
		if (
			e.pointerType === 'mouse' &&
			state === 'footer' &&
			e.clientY <= footer.getBoundingClientRect().top + 5
		) {
			go('expanded');
		}
	});

	window.addEventListener(
		'wheel',
		(e) => {
			if (busy) return;
			if (state === 'footer') {
				if (e.deltaY < -WHEEL_PX) go('expanded');
			} else if (e.deltaY > WHEEL_PX && state === 'collapsed') {
				go('expanded');
			} else if (e.deltaY < -WHEEL_PX && state === 'expanded' && onFilterOrHeader(e.target)) {
				go('collapsed');
			}
		},
		{ passive: true },
	);

	let touchStartY = 0;
	window.addEventListener(
		'touchstart',
		(e) => {
			touchStartY = e.touches[0].clientY;
		},
		{ passive: true },
	);
	window.addEventListener(
		'touchmove',
		(e) => {
			if (busy) return;
			const diff = touchStartY - e.touches[0].clientY; // > 0: finger moves up (advance)
			if (state === 'footer') {
				if (diff < -SWIPE_PX) go('expanded');
			} else if (diff > SWIPE_PX && state === 'collapsed') {
				go('expanded');
			} else if (
				diff > SWIPE_PX &&
				state === 'expanded' &&
				e.target.closest?.('.c-footer__reveal')
			) {
				go('footer');
			} else if (diff < -SWIPE_PX && state === 'expanded' && onFilterOrHeader(e.target)) {
				go('collapsed');
			}
		},
		{ passive: true },
	);

	// --- Footer strip (design D4) ---
	if (reveal) {
		reveal.addEventListener('click', () => {
			go(state === 'footer' ? 'expanded' : 'footer', { force: true });
		});
	}

	// --- Keyboard (design D2) ---
	document.addEventListener('keydown', (e) => {
		if (e.defaultPrevented || e.altKey || e.ctrlKey || e.metaKey || isKeyExempt(e.target)) return;
		const onDocument = e.target === document.body || e.target === root;
		let next = null;

		switch (e.key) {
			case 'PageDown':
			case 'ArrowDown':
				if (state === 'collapsed') next = 'expanded';
				break;
			case ' ':
				// Space only from the document: on buttons and links it keeps its native function.
				if (onDocument && state === 'collapsed' && !e.shiftKey) next = 'expanded';
				break;
			case 'PageUp':
				if (state === 'footer') next = 'expanded';
				else if (state === 'expanded') next = 'collapsed';
				break;
			case 'Home':
				next = 'collapsed';
				break;
			case 'End':
				next = 'footer';
				break;
			default:
				return;
		}
		if (next && next !== state && !busy) {
			e.preventDefault();
			go(next);
			keepFocusVisible();
		}
	});

	/**
	 * After a keyboard change, focus cannot stay on something no longer visible (e.g. `End` with
	 * focus on a filter): in the footer it moves to the strip; otherwise it is released, and the
	 * browser keeps that point as the start of the next Tab.
	 */
	const keepFocusVisible = () => {
		const active = document.activeElement;
		if (!(active instanceof HTMLElement) || active === document.body) return;
		const states = visibleStates(active);
		if (!states || states.includes(state)) return;
		if (state === 'footer' && reveal) reveal.focus();
		else active.blur();
	};

	// --- Focus (design D3): the focused element always stays in view ---
	document.addEventListener('focusin', (e) => {
		const states = e.target instanceof Element ? focusStates(e.target) : null;
		if (states && !states.includes(state)) go(states[0], { force: true });
		if (window.scrollY !== 0) window.scrollTo(0, 0);
	});
};

document.addEventListener('DOMContentLoaded', initHomepage);
