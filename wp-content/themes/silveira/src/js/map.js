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
 * Texto de la interfaz, traducido en PHP (silveira_map_i18n()); el fallback es el texto fuente.
 *
 * @param {string} key
 * @param {string} fallback
 * @returns {string}
 */
const t = (key, fallback) => window.silveiraI18n?.[key] || fallback;

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
				el('h2', { className: 'c-map-card__title' }, [title]),
				el('span', { className: 'c-map-card__subhead' }, [point.lema || '']),
			]),
		]),
		url &&
			el('div', { className: 'c-map-card__actions' }, [
				el('a', { className: 'c-btn c-btn--secondary c-btn--l', attrs: { href: url } }, [
					t('viewProject', 'Ver projeto'),
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
					el('h2', { className: 'c-project-list-item__title' }, [point.title || '']),
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

/** Teselas por defecto si PHP no inyecta configuración (ver silveira_map_tiles()). */
const DEFAULT_TILES = {
	url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
	attribution:
		'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
	maxZoom: 19,
};

document.addEventListener('DOMContentLoaded', async () => {
	const mapContainer = document.getElementById('map');
	if (!mapContainer) return;

	// Leaflet solo en páginas con mapa: Vite lo separa en un chunk propio (JS + CSS) dentro de assets/.
	const [leaflet] = await Promise.all([import('leaflet'), import('leaflet/dist/leaflet.css')]);
	const L = leaflet.default ?? leaflet;

	// 1. Initialize Map
	// Centered in Galicia
	const map = L.map('map', {
		center: [42.5751, -8.1339],
		zoom: 8,
		zoomControl: false,
	});

	L.control.zoom({ position: 'bottomright' }).addTo(map);

	// 2. Add Tiles (proveedor configurable desde PHP con el filtro silveira_map_tiles)
	const tiles = { ...DEFAULT_TILES, ...(window.silveiraMapConfig?.tiles || {}) };
	L.tileLayer(tiles.url, {
		attribution: tiles.attribution,
		maxZoom: tiles.maxZoom,
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

		if (projectListContainer && filteredPoints.length === 0) {
			projectListContainer.append(
				el('li', { className: 'c-project-list__empty' }, [
					t('noResults', 'Nom há projetos para estes filtros.'),
				]),
			);
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
			// Identifica el marcador de cada proyecto (lo usa el smoke test).
			marker.getElement()?.setAttribute('data-project-id', String(point.id));

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
	// Estado: valores marcados por grupo. Dentro de un grupo se combinan con O; entre grupos, con Y.
	const filters = {
		modalidade: [],
		comarca: [],
		localidade: [],
	};
	// Campo del punto que se compara con cada grupo.
	const pointField = {
		modalidade: 'modalidades',
		comarca: 'territorios',
		localidade: 'territorios',
	};

	const groupOf = (cb) => {
		if (cb.dataset.filter === 'modalidade') return 'modalidade';
		if (cb.classList.contains('js-filter-comarca')) return 'comarca';
		if (cb.classList.contains('js-filter-localidade')) return 'localidade';
		return null;
	};

	const filterInputs = Array.from(document.querySelectorAll('.c-checkbox__input')).filter(groupOf);

	/** ¿Cumple el punto el filtro de un grupo? (sin nada marcado, sí). */
	const matches = (point, group) =>
		filters[group].length === 0 || filters[group].some((v) => point[pointField[group]].includes(v));

	/** ¿Cumple el punto todos los grupos, salvo los de `except`? */
	const matchesAll = (point, except = []) =>
		Object.keys(filters).every((group) => except.includes(group) || matches(point, group));

	// Grupos que NO restringen las opciones de cada grupo. Las comarcas no dependen de la localidade
	// (es un refinamiento dentro de la comarca): elegir otra comarca desmarca las localidades incompatibles.
	const ignoredFor = {
		modalidade: ['modalidade'],
		comarca: ['comarca', 'localidade'],
		localidade: ['localidade'],
	};

	const readFilters = () => {
		Object.keys(filters).forEach((group) => {
			filters[group] = filterInputs
				.filter((cb) => cb.checked && groupOf(cb) === group)
				.map((cb) => cb.value);
		});
	};

	/** Una localidade solo es compatible si no hay comarcas marcadas o pertenece a una de ellas. */
	const localidadeFitsComarcas = (cb) =>
		filters.comarca.length === 0 ||
		filters.comarca.includes(cb.closest('.c-checkbox')?.dataset.parentComarca);

	/**
	 * Única responsable de qué opciones se ven (atributo `hidden`): se ofrece una opción si,
	 * combinada con los demás grupos (ver ignoredFor), daría resultados. Las marcadas siguen visibles, salvo
	 * localidades de otra comarca, que ya se han desmarcado en updateFilters().
	 */
	const syncFilterOptions = () => {
		const available = {};
		Object.keys(filters).forEach((group) => {
			available[group] = new Set();
			points
				.filter((p) => matchesAll(p, ignoredFor[group]))
				.forEach((p) => p[pointField[group]].forEach((v) => available[group].add(v)));
		});

		filterInputs.forEach((cb) => {
			const group = groupOf(cb);
			const label = cb.closest('.c-checkbox');
			if (!label) return;
			let visible = cb.checked || available[group].has(cb.value);
			if (group === 'localidade' && !localidadeFitsComarcas(cb)) visible = false;
			label.hidden = !visible;
		});
	};

	/**
	 * Nombre accesible de cada botón de filtro: su etiqueta y, si hay opciones marcadas, cuántas
	 * ("Modalidade educativa, 2 selecionadas"). Sin el texto de ejemplo visible.
	 */
	const syncFilterStatus = () => {
		document.querySelectorAll('.c-select').forEach((select) => {
			const status = select.querySelector(`#${select.id}-status`);
			const label = select.querySelector('.c-select__label')?.textContent.trim() || '';
			if (!status) return;
			const count = select.querySelectorAll('.c-checkbox__input:checked').length;
			const template =
				count === 1
					? t('selectedSingular', '%d selecionada')
					: t('selectedPlural', '%d selecionadas');
			status.textContent = count ? `${label}, ${template.replace('%d', String(count))}` : label;
		});
	};

	const updateFilters = () => {
		readFilters();

		// Localidades marcadas que ya no encajan con las comarcas elegidas: se desmarcan en esta misma pasada.
		const incompatible = filterInputs.filter(
			(cb) => cb.checked && groupOf(cb) === 'localidade' && !localidadeFitsComarcas(cb),
		);
		if (incompatible.length) {
			incompatible.forEach((cb) => {
				cb.checked = false;
			});
			readFilters();
		}

		renderMarkers(points.filter((p) => matchesAll(p)));
		syncFilterOptions();
		syncFilterStatus();
	};

	filterInputs.forEach((cb) => {
		cb.addEventListener('change', updateFilters);
	});
	syncFilterOptions();
	syncFilterStatus();

	// 5. Panel de la lista (design D6). Cerrado es `inert`: ni foco ni lectores de pantalla.
	const listToggleBtn = document.getElementById('map-list-toggle');
	const listPanel = document.getElementById('map-project-list');

	const setListOpen = (open) => {
		if (!listToggleBtn || !listPanel) return;
		listPanel.classList.toggle('is-open', open);
		listPanel.inert = !open;
		listToggleBtn.classList.toggle('is-open', open);
		listToggleBtn.setAttribute('aria-expanded', String(open));
		const label = listToggleBtn.querySelector('.c-btn__label');
		if (label) {
			label.textContent = open ? t('hideList', 'Acochar a lista') : t('showList', 'Ver a lista');
		}
		listToggleBtn.querySelectorAll('[data-icon-when]').forEach((iconEl) => {
			iconEl.hidden = iconEl.dataset.iconWhen !== (open ? 'open' : 'closed');
		});
	};

	if (listToggleBtn && listPanel) {
		listToggleBtn.addEventListener('click', () => {
			setListOpen(!listPanel.classList.contains('is-open'));
		});
	}

	// 6. Estado de la portada (homepage.js): al volver al hero se cierra la lista; al terminar
	// cada transición, Leaflet recalcula su tamaño.
	window.addEventListener('silveira:home-change', (e) => {
		if (e.detail.state === 'collapsed') setListOpen(false);
		if (e.detail.settled) map.invalidateSize();
	});
});
