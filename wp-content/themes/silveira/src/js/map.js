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

    const renderMarkers = (filteredPoints) => {
        // Clear existing markers
        markers.forEach(m => map.removeLayer(m));
        markers = [];

        filteredPoints.forEach(point => {
            const marker = L.marker([point.lat, point.lng]).addTo(map);
            
            const popupContent = `
                <div class="c-map-popup">
                    <h3 class="c-map-popup__title">${point.title}</h3>
                    <a href="${point.url}" class="c-map-popup__link">
                        Ver projeto <span class="o-icon">arrow_forward</span>
                    </a>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            markers.push(marker);
        });
    };

    renderMarkers(points);

    // 4. Filtering Logic
    const filters = {
        modalidade: [],
        territorio: []
    };

    const updateFilters = () => {
        // Get all active checkboxes
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

    // 5. UI Helpers: Dropdowns
    const selects = document.querySelectorAll('.c-select');
    
    selects.forEach(select => {
        const action = select.querySelector('.c-select__action');
        const value = select.querySelector('.c-select__value');
        
        const toggle = (e) => {
            e.stopPropagation();
            // Close others
            selects.forEach(s => {
                if (s !== select) s.querySelector('.c-select__dropdown').classList.remove('c-select__dropdown--open');
            });
            select.querySelector('.c-select__dropdown').classList.toggle('c-select__dropdown--open');
        };

        select.addEventListener('click', toggle);
    });

    // Close dropdowns on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.c-select__dropdown').forEach(d => {
            d.classList.remove('c-select__dropdown--open');
        });
    });

    // Prevent closing when clicking inside dropdown
    document.querySelectorAll('.c-select__dropdown').forEach(d => {
        d.addEventListener('click', (e) => e.stopPropagation());
    });

});
