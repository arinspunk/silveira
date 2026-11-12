/**
 * Portada: navegación entre tres estados.
 *
 *   collapsed  hero visible, mapa bloqueado
 *   expanded   hero fuera, mapa desbloqueado, asoma la franja del footer
 *   footer     footer revelado
 *
 * El estado vive en `html[data-home]` y el CSS reacciona a él (ver _main.scss y _map.scss). Mientras dura
 * la transición se marca `html[data-home-busy="<estado de origen>"]`; se libera con el `transitionend` del contenido o, si este
 * no llega, tras SAFETY_MS. Con movimiento reducido no hay periodo de bloqueo.
 *
 * Sin este script no hay `data-home` y la portada se recorre con el scroll nativo.
 *
 * Eventos:
 *   escucha   `silveira:slide-up`     (map.js, al pulsar un marcador) → expanded
 *   emite     `silveira:home-change`  detail { state, settled } en cada cambio y al terminar la transición
 */

const SAFETY_MS = 1000;
const SWIPE_PX = 10;
const WHEEL_PX = 5;

const root = document.documentElement;
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

/** ¿El foco está en un control donde las teclas tienen otro uso (escribir, mover el mapa, desplazar la lista)? */
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
 * Estados en los que el foco puede estar en un elemento; si el actual no está en la lista, se pasa al
 * primero. `null`: cualquiera (la cabecera queda fuera de .l-site-content y siempre se ve).
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
 * Estados en los que un elemento se ve. Como focusStates, salvo los filtros: se ven también con el hero,
 * aunque al entrar en ellos con el foco el mapa se expanda.
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

	// --- Medidas (design D5) ---
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

	// --- Máquina de estados (design D1) ---
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
	 * Cambia de estado.
	 *
	 * @param {string} next
	 * @param {{force?: boolean}} [options] force: aunque haya una transición en curso (el foco manda).
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
		root.dataset.homeBusy = previous; // el CSS espera al final solo al salir del hero

		window.clearTimeout(safetyTimer);
		safetyTimer = window.setTimeout(settle, SAFETY_MS);
		emit(false);
	};

	siteContent.addEventListener('transitionend', (e) => {
		if (e.target === siteContent && e.propertyName === 'transform') settle();
	});

	// Estado inicial: el CSS solo bloquea el scroll nativo cuando existe `data-home` (design D7).
	if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
	root.dataset.home = state;
	window.scrollTo(0, 0);
	// Con overflow: hidden el documento aún se desplaza al enfocar algo fuera de pantalla; el
	// desplazamiento lo pone el transform, así que se anula ese scroll.
	window.addEventListener('scroll', () => {
		if (window.scrollY !== 0) window.scrollTo(0, 0);
	});

	// --- Ratón y táctil (comportamiento previo, design D2) ---
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

	// Solo ratón: en táctil el pointerenter emulado de un toque abriría y el click volvería a cerrar.
	footer.addEventListener('pointerenter', (e) => {
		if (e.pointerType === 'mouse' && state === 'expanded') go('footer');
	});
	footer.addEventListener('pointerleave', (e) => {
		// Solo si sale por arriba.
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
			const diff = touchStartY - e.touches[0].clientY; // > 0: el dedo sube (avanzar)
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

	// --- Franja del footer (design D4) ---
	if (reveal) {
		reveal.addEventListener('click', () => {
			go(state === 'footer' ? 'expanded' : 'footer', { force: true });
		});
	}

	// --- Teclado (design D2) ---
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
				// Espacio solo desde el documento: en botones y enlaces tiene su función nativa.
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
	 * Tras un cambio por teclado, el foco no puede quedarse en algo que ya no se ve (p. ej. `End` con el foco
	 * en un filtro): en el footer pasa a su franja; en otro caso se suelta, y el navegador mantiene ese punto
	 * como inicio del siguiente Tab.
	 */
	const keepFocusVisible = () => {
		const active = document.activeElement;
		if (!(active instanceof HTMLElement) || active === document.body) return;
		const states = visibleStates(active);
		if (!states || states.includes(state)) return;
		if (state === 'footer' && reveal) reveal.focus();
		else active.blur();
	};

	// --- Foco (design D3): el elemento enfocado siempre queda a la vista ---
	document.addEventListener('focusin', (e) => {
		const states = e.target instanceof Element ? focusStates(e.target) : null;
		if (states && !states.includes(state)) go(states[0], { force: true });
		if (window.scrollY !== 0) window.scrollTo(0, 0);
	});
};

document.addEventListener('DOMContentLoaded', initHomepage);
