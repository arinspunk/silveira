/**
 * Interactive Map Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('map');
    if (!mapContainer || typeof L === 'undefined') return;

    // 1. Initialize Map
    // Centered in Galicia
    const map = L.map('map', {
        center: [42.5751, -8.1339],
        zoom: 8,
        zoomControl: false
    });

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // 2. Add Tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // 3. Markers Management
    let markers = [];
    const points = window.silveiraMapPoints || [];

    const projectListContainer = document.querySelector('.c-project-list');

    const renderMarkers = (filteredPoints) => {
        // Clear existing markers
        markers.forEach(m => map.removeLayer(m));
        markers = [];

        // Clear list container
        if (projectListContainer) {
            projectListContainer.innerHTML = '';
        }

        // Define custom icon using Material Symbol
        const customIcon = L.divIcon({
            html: '<span class="o-icon o-icon--lg o-icon--filled" style="color: var(--sil-violeta-500); filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">location_on</span>',
            className: 'c-map-marker',
            iconSize: [24, 24],
            iconAnchor: [12, 24],
            popupAnchor: [0, -24]
        });

        filteredPoints.forEach(point => {
            const marker = L.marker([point.lat, point.lng], { icon: customIcon }).addTo(map);
            
            const popupContent = `
                <div class="c-map-popup">
                    <h3 class="c-map-popup__title">${point.title}</h3>
                    ${point.lema ? `<p class="c-map-popup__lema">${point.lema}</p>` : ''}
                    <a href="${point.url}" class="c-map-popup__link">
                        Ver projeto <span class="o-icon">arrow_forward</span>
                    </a>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            markers.push(marker);

            // Populate side list
            if (projectListContainer) {
                const li = document.createElement('li');
                li.innerHTML = `
                    <a href="${point.url}" class="c-project-list-item">
                        <div class="c-project-list-item__leading">
                            <span class="o-icon o-icon--filled">location_on</span>
                        </div>
                        <div class="c-project-list-item__content">
                            <p class="c-project-list-item__overline">${point.overline || ''}</p>
                            <h3 class="c-project-list-item__title">${point.title}</h3>
                            ${point.lema ? `<p class="c-project-list-item__lema">${point.lema}</p>` : ''}
                            <p class="c-project-list-item__supporting">${point.excerpt || ''}</p>
                        </div>
                        <div class="c-project-list-item__trailing">
                            <span class="o-icon">arrow_forward</span>
                        </div>
                    </a>
                `;
                projectListContainer.appendChild(li);
            }
        });
    };

    renderMarkers(points);

    // 4. Filtering Logic
    const filters = {
        modalidade: [],
        territorio: []
    };

    const updateFilters = () => {
        // Get all currently active checkboxes
        const checkboxes = document.querySelectorAll('.c-checkbox__input');
        filters.modalidade = [];
        filters.territorio = [];

        checkboxes.forEach(cb => {
            if (cb.checked) {
                const type = cb.dataset.filter;
                filters[type].push(cb.value);
            }
        });

        const filteredPoints = points.filter(p => {
            const matchModalidade = filters.modalidade.length === 0 || 
                                   filters.modalidade.some(m => p.modalidades.includes(m));
            
            const matchTerritorio = filters.territorio.length === 0 || 
                                   filters.territorio.some(t => p.territorios.includes(t));

            return matchModalidade && matchTerritorio;
        });

        renderMarkers(filteredPoints);
    };

    document.querySelectorAll('.c-checkbox__input').forEach(cb => {
        cb.addEventListener('change', updateFilters);
    });

    // 6. Map Scroll UX Optimization (Wheel & Touch Events)
    const mapWrapper = document.querySelector('.c-map');
    const hero = document.querySelector('.c-hero');
    const mainWrapper = document.querySelector('.l-main--map');
    
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
    let isAnimating = false;

    if (mapWrapper && hero && mainWrapper) {
        
        // Prevent scroll restoration on page reload
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        window.scrollTo(0, 0);

        // Ensure body doesn't scroll natively
        document.body.style.overflow = 'hidden';

        const slideUp = () => {
            if (!isMapLocked || isAnimating) return;
            isAnimating = true;
            mainWrapper.classList.add('is-scrolled');
            
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
            if (isMapLocked || isAnimating) return;
            isAnimating = true;
            mapWrapper.classList.add('is-locked');
            mainWrapper.classList.remove('is-scrolled');
            
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

        // Track touch events for mobile swipe
        let touchStartY = 0;
        
        window.addEventListener('touchstart', (e) => {
            touchStartY = e.touches[0].clientY;
        }, { passive: true });

        window.addEventListener('touchmove', (e) => {
            if (isAnimating) return;
            
            const touchEndY = e.touches[0].clientY;
            const diff = touchStartY - touchEndY;
            
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
        }, { passive: false });

        // Desktop Wheel
        window.addEventListener('wheel', (e) => {
            if (isAnimating) return;

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
        }, { passive: true });
    }



});
