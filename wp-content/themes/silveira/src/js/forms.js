/**
 * Form and Input Logic (Selects, Checkboxes, etc.)
 */

export const initSelects = () => {
	const selects = Array.from(document.querySelectorAll('.c-select'));

	const parts = (select) => ({
		trigger: select.querySelector('.c-select__trigger'),
		panel: select.querySelector('.c-select__dropdown'),
		icon: select.querySelector('.c-select__action.o-icon'),
	});

	const setOpen = (select, open) => {
		const { trigger, panel, icon } = parts(select);
		if (!panel) return;
		panel.classList.toggle('c-select__dropdown--open', open);
		select.classList.toggle('c-select--active', open);
		if (trigger) trigger.setAttribute('aria-expanded', String(open));
		if (icon) icon.textContent = open ? 'expand_less' : 'expand_more';
	};

	const isOpen = (select) => !!parts(select).panel?.classList.contains('c-select__dropdown--open');

	const firstVisibleOption = (select) =>
		Array.from(select.querySelectorAll('.c-select__dropdown .c-checkbox__input')).find(
			(input) => !input.closest('.c-checkbox')?.hidden,
		);

	selects.forEach((select) => {
		const { trigger, panel } = parts(select);
		if (!trigger || !panel) return;

		// Enter y Espacio son nativos del botón y llegan aquí como click.
		trigger.addEventListener('click', (e) => {
			const open = !isOpen(select);
			selects.forEach((other) => {
				if (other !== select) setOpen(other, false);
			});
			setOpen(select, open);
			// Abierto con teclado (click sin puntero: detail === 0): el foco va a la primera opción visible.
			if (open && e.detail === 0) firstVisibleOption(select)?.focus();
		});

		select.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && isOpen(select)) {
				e.preventDefault();
				setOpen(select, false);
				trigger.focus();
			}
		});
	});

	// Clic fuera de un desplegable: se cierra. No se usa stopPropagation, para que el clic siga
	// llegando a los demás listeners (p. ej. el que expande el mapa en map.js).
	document.addEventListener('click', (e) => {
		selects.forEach((select) => {
			if (isOpen(select) && !select.contains(e.target)) setOpen(select, false);
		});
	});
};

document.addEventListener('DOMContentLoaded', () => {
	initSelects();
});
