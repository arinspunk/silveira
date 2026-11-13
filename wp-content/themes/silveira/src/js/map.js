/**
 * Interactive Map Logic
 */

/**
 * Creates a DOM element. String children are inserted as text (never as HTML);
 * null/false/undefined ones are skipped.
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
 * Returns the absolute URL only if it is http(s); otherwise null.
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
 * UI text, translated in PHP (silveira_map_i18n()); the fallback is the source text.
 *
 * @param {string} key
 * @param {string} fallback
 * @returns {string}
 */
const t = (key, fallback) => window.silveiraI18n?.[key] || fallback;

/**
 * Popup for a marker.
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
 * Side list item. Without a safe URL it is rendered as a <div> (no link).
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

/** Default tiles if PHP injects no configuration (see silveira_map_tiles()). */
const DEFAULT_TILES = {
	url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
	attribution:
		'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
	maxZoom: 19,
};

document.addEventListener('DOMContentLoaded', async () => {
	const mapContainer = document.getElementById('map');
	if (!mapContainer) return;

	// Leaflet only on pages with a map: Vite splits it into its own chunk (JS + CSS) inside assets/.
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

	// 2. Add Tiles (provider configurable from PHP via the silveira_map_tiles filter)
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
			// Identifies each project's marker (used by the smoke test).
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
	// State: checked values per group. Within a group they combine with OR; across groups, with AND.
	const filters = {
		modalidade: [],
		comarca: [],
		localidade: [],
	};
	// Point field compared against each group.
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

	/** Does the point pass a group's filter? (yes if nothing is checked). */
	const matches = (point, group) =>
		filters[group].length === 0 || filters[group].some((v) => point[pointField[group]].includes(v));

	/** Does the point pass every group, except those in `except`? */
	const matchesAll = (point, except = []) =>
		Object.keys(filters).every((group) => except.includes(group) || matches(point, group));

	// Groups that do NOT restrict each group's options. Comarcas do not depend on the localidade
	// (it refines within the comarca): choosing another comarca unchecks incompatible localidades.
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

	/** A localidade is only compatible if no comarca is checked or it belongs to one of them. */
	const localidadeFitsComarcas = (cb) =>
		filters.comarca.length === 0 ||
		filters.comarca.includes(cb.closest('.c-checkbox')?.dataset.parentComarca);

	/**
	 * Sole owner of which options are shown (`hidden` attribute): an option is offered if, combined
	 * with the other groups (see ignoredFor), it would yield results. Checked ones stay visible,
	 * except localidades from another comarca, which have already been unchecked in updateFilters().
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
	 * Accessible name of each filter button: its label and, if options are checked, how many
	 * ("Modalidade educativa, 2 selecionadas"). Without the visible placeholder text.
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

		// Checked localidades that no longer fit the chosen comarcas: unchecked in this same pass.
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

	// 5. List panel (design D6). When closed it is `inert`: no focus, no screen readers.
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

	// 6. Homepage state (homepage.js): returning to the hero closes the list; when each
	// transition ends, Leaflet recalculates its size.
	window.addEventListener('silveira:home-change', (e) => {
		if (e.detail.state === 'collapsed') setListOpen(false);
		if (e.detail.settled) map.invalidateSize();
	});
});
