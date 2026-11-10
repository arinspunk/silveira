/**
 * Interactive Map Logic
 */

/**
 * Crea un elemento DOM. Los hijos string se insertan como texto (nunca como HTML);
 * los null/false/undefined se omiten.
 *
 * @param {string} tag
 * @param {{className?: string, attrs?: Record<string, string>}} [options]
 * @param {Array<Node|string|null|false|undefined>} [children]
 * @returns {HTMLElement}
 */
const el = (tag, { className, attrs } = {}, children = []) => {
	const node = document.createElement(tag);
	if (className) node.className = className;
	if (attrs) {
		Object.entries(attrs).forEach(([name, value]) => node.setAttribute(name, value));
	}
	children.forEach((child) => {
		if (child === null || child === undefined || child === false) return;
		node.append(typeof child === 'string' ? document.createTextNode(child) : child);
	});
	return node;
};

/**
 * Devuelve la URL absoluta solo si es http(s); si no, null.
 *
 * @param {unknown} value
 * @returns {string|null}
 */
const safeUrl = (value) => {
	if (typeof value !== 'string' || value === '') return null;
	try {
		const url = new URL(value, window.location.origin);
		return url.protocol === 'http:' || url.protocol === 'https:' ? url.href : null;
	} catch {
		return null;
	}
};

const icon = (className, name) => el('span', { className }, [name]);

/**
 * Popup de un marcador.
 *
 * @param {object} point
 * @returns {HTMLElement}
 */
const buildPopup = (point) => {
	const title = point.title || '';
	const image = safeUrl(point.image);
	const url = safeUrl(point.url);

	return el('article', { className: 'c-map-card' }, [
		image &&
			el('div', { className: 'c-map-card__media' }, [
				el('img', { attrs: { src: image, alt: title } }),
			]),
		el('div', { className: 'c-map-card__content' }, [
			el('div', { className: 'c-map-card__headline' }, [
				el('h3', { className: 'c-map-card__title' }, [title]),
				el('span', { className: 'c-map-card__subhead' }, [point.lema || '']),
			]),
		]),
		url &&
			el('div', { className: 'c-map-card__actions' }, [
				el('a', { className: 'c-btn c-btn--secondary c-btn--l', attrs: { href: url } }, [
					'Ver projeto',
				]),
			]),
	]);
};

/**
 * Elemento de la lista lateral. Sin URL segura se pinta como <div> (sin enlace).
 *
 * @param {object} point
 * @returns {HTMLLIElement}
 */
const buildListItem = (point) => {
	const url = safeUrl(point.url);

	return el('li', {}, [
		el(
			url ? 'a' : 'div',
			{ className: 'c-project-list-item', attrs: url ? { href: url } : undefined },
			[
				el('div', { className: 'c-project-list-item__leading' }, [
					icon('o-icon o-icon--lg o-icon--filled', 'location_on'),
				]),
				el('div', { className: 'c-project-list-item__content' }, [
					el('p', { className: 'c-project-list-item__overline' }, [point.overline || '']),
					el('h3', { className: 'c-project-list-item__title' }, [point.title || '']),
					point.lema && el('p', { className: 'c-project-list-item__lema' }, [point.lema]),
					el('p', { className: 'c-project-list-item__supporting' }, [point.excerpt || '']),
				]),
				el('div', { className: 'c-project-list-item__trailing' }, [
					icon('o-icon', 'arrow_forward'),
				]),
			],
		),
	]);
};

document.addEventListener('DOMContentLoaded', () => {
	const mapContainer = document.getElementById('map');
	if (!mapContainer || typeof L === 'undefined') return;

	// 1. Initialize Map
	// Centered in Galicia
	const map = L.map('map', {
		center: [42.5751, -8.1339],
		zoom: 8,
		zoomControl: false,
	});

	L.control.zoom({ position: 'bottomright' }).addTo(map);

	// 2. Add Tiles
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; OpenStreetMap contributors',
	}).addTo(map);

	// 3. Markers Management
	let markers = [];
	const points = window.silveiraMapPoints || [];

	const projectListContainer = document.querySelector('.c-project-list');

	const renderMarkers = (filteredPoints) => {
		// Clear existing markers
		markers.forEach((m) => map.removeLayer(m));
		markers = [];

		// Clear list container
		if (projectListContainer) {
			projectListContainer.replaceChildren();
		}

		// Define custom icon using Material Symbol
		const customIcon = L.divIcon({
			html: '<span class="o-icon o-icon--lg o-icon--filled" style="color: var(--sil-violeta-500); filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">location_on</span>',
			className: 'c-map-marker',
			iconSize: [24, 24],
			iconAnchor: [12, 24],
			popupAnchor: [0, -24],
		});

		filteredPoints.forEach((point) => {
			const marker = L.marker([point.lat, point.lng], { icon: customIcon }).addTo(map);

			const popupContent = buildPopup(point);

			marker.bindPopup(popupContent);
			markers.push(marker);

			// Trigger expansion when clicking a marker
			marker.on('click', () => {
				window.dispatchEvent(new CustomEvent('silveira:slide-up'));
			});

			// Populate side list
			if (projectListContainer) {
				const li = buildListItem(point);
				projectListContainer.appendChild(li);
			}
		});
	};

	renderMarkers(points);

	// 4. Filtering Logic
	const filters = {
		modalidade: [],
		comarca: [],
		localidade: [],
	};

	const updateFilters = () => {
		// 1. Update state
		const checkboxes = document.querySelectorAll('.c-checkbox__input');
		filters.modalidade = [];
		filters.comarca = [];
		filters.localidade = [];

		checkboxes.forEach((cb) => {
			if (cb.checked) {
				if (cb.dataset.filter === 'modalidade') {
					filters.modalidade.push(cb.value);
				} else if (cb.classList.contains('js-filter-comarca')) {
					filters.comarca.push(cb.value);
				} else if (cb.classList.contains('js-filter-localidade')) {
					filters.localidade.push(cb.value);
				}
			}
		});

		// 2. Filter points
		const filteredPoints = points.filter((p) => {
			const matchModalidade =
				filters.modalidade.length === 0 ||
				filters.modalidade.some((m) => p.modalidades.includes(m));

			const matchComarca =
				filters.comarca.length === 0 || filters.comarca.some((t) => p.territorios.includes(t));

			const matchLocalidade =
				filters.localidade.length === 0 ||
				filters.localidade.some((t) => p.territorios.includes(t));

			return matchModalidade && matchComarca && matchLocalidade;
		});

		// 3. Update Markers
		renderMarkers(filteredPoints);

		// 4. Sync Filter Options UI
		syncFilterOptions();
	};

	const syncFilterOptions = () => {
		const checkboxes = document.querySelectorAll('.c-checkbox__input');

		// 1. Determine available options for each group
		// Modalidades are restricted by Comarca AND Localidade
		const availableM = new Set();
		points.forEach((p) => {
			const matchesOther =
				(filters.comarca.length === 0 || filters.comarca.some((t) => p.territorios.includes(t))) &&
				(filters.localidade.length === 0 ||
					filters.localidade.some((t) => p.territorios.includes(t)));
			if (matchesOther) {
				p.modalidades.forEach((m) => availableM.add(m));
			}
		});

		// Comarcas are restricted by Modalidade AND Localidade
		const availableC = new Set();
		points.forEach((p) => {
			const matchesOther =
				(filters.modalidade.length === 0 ||
					filters.modalidade.some((m) => p.modalidades.includes(m))) &&
				(filters.localidade.length === 0 ||
					filters.localidade.some((t) => p.territorios.includes(t)));
			if (matchesOther) {
				p.territorios.forEach((t) => availableC.add(t));
			}
		});

		// Localidades are restricted by Modalidade AND Comarca
		const availableL = new Set();
		points.forEach((p) => {
			const matchesOther =
				(filters.modalidade.length === 0 ||
					filters.modalidade.some((m) => p.modalidades.includes(m))) &&
				(filters.comarca.length === 0 || filters.comarca.some((t) => p.territorios.includes(t)));
			if (matchesOther) {
				p.territorios.forEach((t) => availableL.add(t));
			}
		});

		// 2. Update UI
		checkboxes.forEach((cb) => {
			const val = cb.value;
			const isM = cb.dataset.filter === 'modalidade';
			const isC = cb.classList.contains('js-filter-comarca');
			const isL = cb.classList.contains('js-filter-localidade');

			let isAvailable = false;
			if (isM) isAvailable = availableM.has(val);
			if (isC) isAvailable = availableC.has(val);
			if (isL) isAvailable = availableL.has(val);

			const label = cb.closest('.c-checkbox');
			if (label) {
				if (isAvailable || cb.checked) {
					label.style.display = 'flex';
				} else {
					label.style.display = 'none';
				}
			}
		});
	};

	document.querySelectorAll('.c-checkbox__input').forEach((cb) => {
		cb.addEventListener('change', updateFilters);
	});

	// 6. Map Scroll UX Optimization (Wheel & Touch Events)
	const mapWrapper = document.querySelector('.c-map');
	const hero = document.querySelector('.c-hero');
	const siteContent = document.querySelector('.l-site-content');

	const listToggleBtn = document.getElementById('map-list-toggle');
	const listPanel = document.getElementById('map-project-list');

	if (listToggleBtn && listPanel) {
		listToggleBtn.addEventListener('click', () => {
			const isOpen = listPanel.classList.toggle('is-open');
			listToggleBtn.classList.toggle('is-open', isOpen);

			if (isOpen) {
				listToggleBtn.innerHTML = `Acochar a lista <span class="c-btn__icon o-icon o-icon--xs">arrow_forward</span>`;
			} else {
				listToggleBtn.innerHTML = `<span class="c-btn__icon o-icon o-icon--xs">arrow_back</span> Ver a lista`;
			}
		});
	}

	let isMapLocked = true;
	let isFooterOpen = false;
	let isAnimating = false;

	if (mapWrapper && hero && siteContent) {
		const footer = document.querySelector('.c-footer');

		// Prevent scroll restoration on page reload
		if ('scrollRestoration' in history) {
			history.scrollRestoration = 'manual';
		}
		window.scrollTo(0, 0);

		// Ensure body doesn't scroll natively
		document.body.style.overflow = 'hidden';

		const slideUp = () => {
			if (!isMapLocked || isAnimating || isFooterOpen) return;
			isAnimating = true;
			siteContent.classList.add('is-scrolled');

			setTimeout(() => {
				mapWrapper.classList.remove('is-locked');
				if (listToggleBtn) {
					listToggleBtn.removeAttribute('style');
					listToggleBtn.classList.add('is-visible');
				}
				if (listPanel) {
					listPanel.removeAttribute('style');
				}
				isMapLocked = false;
				isAnimating = false;
				map.invalidateSize(); // Ensure leaflet recalculates bounds
			}, 800);
		};

		const slideDown = () => {
			if (isMapLocked || isAnimating || isFooterOpen) return;
			isAnimating = true;
			mapWrapper.classList.add('is-locked');
			siteContent.classList.remove('is-scrolled');

			if (listToggleBtn) {
				listToggleBtn.classList.remove('is-visible');
				listToggleBtn.classList.remove('is-open');
				listToggleBtn.innerHTML = `<span class="c-btn__icon o-icon o-icon--xs">arrow_back</span> Ver a lista`;
			}
			if (listPanel) listPanel.classList.remove('is-open');

			setTimeout(() => {
				isMapLocked = true;
				isAnimating = false;
			}, 800);
		};

		const openFooter = () => {
			if (isAnimating || isFooterOpen || isMapLocked) return;
			isAnimating = true;
			isFooterOpen = true;

			const footerHeight = footer.offsetHeight;
			siteContent.style.setProperty('--sil-footer-height', `${footerHeight}px`);

			siteContent.classList.add('is-footer-open');
			mapWrapper.classList.add('is-locked'); // Lock map while footer is open

			setTimeout(() => {
				isAnimating = false;
			}, 800);
		};

		const closeFooter = () => {
			if (isAnimating || !isFooterOpen) return;
			isAnimating = true;
			isFooterOpen = false;
			siteContent.classList.remove('is-footer-open');
			mapWrapper.classList.remove('is-locked'); // Unlock map

			setTimeout(() => {
				isAnimating = false;
				map.invalidateSize();
			}, 800);
		};

		if (footer) {
			footer.addEventListener('mouseenter', () => {
				if (!isMapLocked && !isFooterOpen) {
					openFooter();
				}
			});

			footer.addEventListener('mouseleave', (e) => {
				// If exiting from the top (clientY is near or above the footer's top boundary)
				if (isFooterOpen && e.clientY <= footer.getBoundingClientRect().top + 5) {
					closeFooter();
				}
			});
		}

		// Expand map when clicking on filters
		document.querySelectorAll('.c-select').forEach((select) => {
			select.addEventListener('click', () => {
				if (isMapLocked && !isAnimating) {
					slideUp();
				}
			});
		});

		// Expand map when clicking a marker (triggered from renderMarkers)
		window.addEventListener('silveira:slide-up', () => {
			if (isMapLocked && !isAnimating) {
				slideUp();
			}
		});

		// Track touch events for mobile swipe
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
				if (isAnimating) return;

				const touchEndY = e.touches[0].clientY;
				const diff = touchStartY - touchEndY;

				// If footer is open, swipe down (scroll up) closes it
				if (isFooterOpen) {
					if (diff < -10) {
						closeFooter();
					}
					return;
				}

				// Swipe Up (Scroll Down)
				if (diff > 10 && isMapLocked) {
					slideUp();
				}
				// Swipe Down (Scroll Up)
				else if (diff < -10 && !isMapLocked) {
					// Must be on filter bar or header
					if (e.target.closest('.c-filter-bar') || e.target.closest('.c-header')) {
						slideDown();
					}
				}
			},
			{ passive: false },
		);

		// Desktop Wheel
		window.addEventListener(
			'wheel',
			(e) => {
				if (isAnimating) return;

				// If footer is open, wheel up closes it
				if (isFooterOpen) {
					if (e.deltaY < -5) {
						closeFooter();
					}
					return;
				}

				// Scrolling down
				if (e.deltaY > 5 && isMapLocked) {
					slideUp();
				}
				// Scrolling up
				else if (e.deltaY < -5 && !isMapLocked) {
					if (e.target.closest('.c-filter-bar') || e.target.closest('.c-header')) {
						slideDown();
					}
				}
			},
			{ passive: true },
		);
	}
});
