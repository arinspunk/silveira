# Solución: Integración de Material Symbols (Google Icons)

Para integrar los iconos de Google de forma profesional en Silveira, seguiremos el patrón de "Material Symbols Outlined" por ser la versión más versátil y moderna.

## Cambios Propuestos

### 1. WordPress (PHP)
- Crear `wp-content/themes/silveira/inc/assets.php` para centralizar la carga de fuentes externas.
- Encolar `Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200`.

### 2. SCSS (Design System)
- Crear `wp-content/themes/silveira/src/scss/4-elements/_icons.scss`.
- Definir una clase base `.o-icon` (Object Icon) para normalizar el renderizado (display, alineación, tamaño base).
- Configurar variables CSS para facilitar el cambio de grosor o relleno (Fill).

### 3. Kitchen Sink
- Añadir una sección con una cuadrícula de iconos comunes (search, calendar, location, arrow_forward).

## Justificación
El uso de Material Symbols como "Variable Font" permite un control granular del peso y estilo de los iconos con CSS, lo cual encaja perfectamente con un Design System de alto nivel.

## Plan de Ejecución
1. Crear `inc/assets.php`.
2. Registrar `inc/assets.php` en `functions.php`.
3. Crear `src/scss/4-elements/_icons.scss`.
4. Importar `_icons.scss` en `main.scss`.
5. Actualizar la Kitchen Sink.
