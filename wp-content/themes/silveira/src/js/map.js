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
                <article class="c-map-card">
                    ${point.image ? `
                    <div class="c-map-card__media">
                        <img src="${point.image}" alt="${point.title}">
                    </div>
                    ` : ''}
                    <div class="c-map-card__content">
                        <div class="c-map-card__headline">
                            <h3 class="c-map-card__title">${point.title}</h3>
                            <span class="c-map-card__subhead">${point.lema || ''}</span>
                        </div>
                    </div>
                    <div class="c-map-card__actions">
                        <a href="${point.url}" class="c-btn c-btn--secondary c-btn--l">Ver projeto</a>
                    </div>
                </article>
            `;
            
            marker.bindPopup(popupContent);
            markers.push(marker);

            // Trigger expansion when clicking a marker
            marker.on('click', () => {
                window.dispatchEvent(new CustomEvent('silveira:slide-up'));
            });

            // Populate side list
            if (projectListContainer) {
                const li = document.createElement('li');
                li.innerHTML = `
                    <a href="${point.url}" class="c-project-list-item">
                        <div class="c-project-list-item__leading">
                            <span class="o-icon o-icon--lg o-icon--filled">location_on</span>
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
        comarca: [],
        localidade: []
    };

    const updateFilters = () => {
        // 1. Update state
        const checkboxes = document.querySelectorAll('.c-checkbox__input');
        filters.modalidade = [];
        filters.comarca = [];
        filters.localidade = [];

        checkboxes.forEach(cb => {
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
        const filteredPoints = points.filter(p => {
            const matchModalidade = filters.modalidade.length === 0 || 
                                   filters.modalidade.some(m => p.modalidades.includes(m));
            
            const matchComarca = filters.comarca.length === 0 || 
                                filters.comarca.some(t => p.territorios.includes(t));

            const matchLocalidade = filters.localidade.length === 0 || 
                                   filters.localidade.some(t => p.territorios.includes(t));

            return matchModalidade && matchComarca && matchLocalidade;
        });

        // 3. Update Markers
        renderMarkers(filteredPoints);

        // 4. Sync Filter Options UI
        syncFilterOptions();
    };

    const syncFilterOptions = () => {
        const checkboxes = document.querySelectorAll('.c-checkbox__input');

        // 1. Get points matching each group independently
        const matchesM = points.filter(p => filters.modalidade.length === 0 || filters.modalidade.some(m => p.modalidades.includes(m)));
        const matchesC = points.filter(p => filters.comarca.length === 0 || filters.comarca.some(t => p.territorios.includes(t)));
        const matchesL = points.filter(p => filters.localidade.length === 0 || filters.localidade.some(t => p.territorios.includes(t)));

        // 2. Determine available options for each group
        // Modalidades are restricted by Comarca AND Localidade
        const availableM = new Set();
        points.forEach(p => {
            const matchesOther = (filters.comarca.length === 0 || filters.comarca.some(t => p.territorios.includes(t))) &&
                                (filters.localidade.length === 0 || filters.localidade.some(t => p.territorios.includes(t)));
            if (matchesOther) {
                p.modalidades.forEach(m => availableM.add(m));
            }
        });

        // Comarcas are restricted by Modalidade AND Localidade
        const availableC = new Set();
        points.forEach(p => {
            const matchesOther = (filters.modalidade.length === 0 || filters.modalidade.some(m => p.modalidades.includes(m))) &&
                                (filters.localidade.length === 0 || filters.localidade.some(t => p.territorios.includes(t)));
            if (matchesOther) {
                p.territorios.forEach(t => availableC.add(t));
            }
        });

        // Localidades are restricted by Modalidade AND Comarca
        const availableL = new Set();
        points.forEach(p => {
            const matchesOther = (filters.modalidade.length === 0 || filters.modalidade.some(m => p.modalidades.includes(m))) &&
                                (filters.comarca.length === 0 || filters.comarca.some(t => p.territorios.includes(t)));
            if (matchesOther) {
                p.territorios.forEach(t => availableL.add(t));
            }
        });

        // 3. Update UI
        checkboxes.forEach(cb => {
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

    document.querySelectorAll('.c-checkbox__input').forEach(cb => {
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
        document.querySelectorAll('.c-select').forEach(select => {
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
        
        window.addEventListener('touchstart', (e) => {
            touchStartY = e.touches[0].clientY;
        }, { passive: true });

        window.addEventListener('touchmove', (e) => {
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
        }, { passive: false });

        // Desktop Wheel
        window.addEventListener('wheel', (e) => {
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
        }, { passive: true });
    }



});
