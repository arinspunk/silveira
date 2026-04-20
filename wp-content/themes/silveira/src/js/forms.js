/**
 * Form and Input Logic (Selects, Checkboxes, etc.)
 */

export const initSelects = () => {
    const selects = document.querySelectorAll('.c-select');
    
    const updateSelectIcons = (s) => {
        const dropdown = s.querySelector('.c-select__dropdown');
        const icon = s.querySelector('.c-select__action.o-icon');
        if (!icon) return;
        
        if (dropdown.classList.contains('c-select__dropdown--open')) {
            icon.textContent = 'expand_less';
            s.classList.add('c-select--active');
        } else {
            icon.textContent = 'expand_more';
            s.classList.remove('c-select--active');
        }
    };

    selects.forEach(select => {
        const toggle = (e) => {
            e.stopPropagation();
            const dropdown = select.querySelector('.c-select__dropdown');
            const isOpen = dropdown.classList.contains('c-select__dropdown--open');

            // Close others
            selects.forEach(s => {
                if (s !== select) {
                    const otherDropdown = s.querySelector('.c-select__dropdown');
                    if (otherDropdown) {
                        otherDropdown.classList.remove('c-select__dropdown--open');
                        updateSelectIcons(s);
                    }
                }
            });

            dropdown.classList.toggle('c-select__dropdown--open', !isOpen);
            updateSelectIcons(select);
        };

        select.addEventListener('click', toggle);
    });

    // Close dropdowns on outside click
    document.addEventListener('click', () => {
        selects.forEach(s => {
            const d = s.querySelector('.c-select__dropdown');
            if (d && d.classList.contains('c-select__dropdown--open')) {
                d.classList.remove('c-select__dropdown--open');
                updateSelectIcons(s);
            }
        });
    });

    // Prevent closing when clicking inside dropdown
    document.querySelectorAll('.c-select__dropdown').forEach(d => {
        d.addEventListener('click', (e) => e.stopPropagation());
    });
};

/**
 * Dependent Filter Logic (Comarcas -> Localidades)
 */
export const initDependentFilters = () => {
    const comarcaCheckboxes = document.querySelectorAll('.js-filter-comarca');
    const localidadeCheckboxes = document.querySelectorAll('.js-filter-localidade');
    
    if (!comarcaCheckboxes.length || !localidadeCheckboxes.length) return;

    const updateVisibility = () => {
        const selectedComarcas = Array.from(comarcaCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        localidadeCheckboxes.forEach(cb => {
            const parentLabel = cb.closest('.c-checkbox');
            if (!parentLabel) return;

            const parentComarca = parentLabel.dataset.parentComarca;
            
            if (selectedComarcas.length === 0 || selectedComarcas.includes(parentComarca)) {
                parentLabel.style.display = 'flex';
            } else {
                parentLabel.style.display = 'none';
                if (cb.checked) {
                    cb.checked = false;
                    // Trigger a change event so other listeners (like map.js) know it was unchecked
                    cb.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });
    };

    comarcaCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateVisibility);
    });

    // Run once on load to ensure correct state
    updateVisibility();
};

document.addEventListener('DOMContentLoaded', () => {
    initSelects();
    initDependentFilters();
});
