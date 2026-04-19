# Análisis: Integración de Google Icon Library (Material Symbols)

## Objetivo
Explorar y proponer la mejor forma de integrar la librería de iconos de Google (Material Symbols) en el tema Silveira, asegurando compatibilidad con el sistema de diseño BEM y el flujo de trabajo de Vite.

## Alternativas de Implementación

### 1. Google Fonts API (CDN)
- **Ventajas:** Implementación inmediata, cache compartido en navegadores.
- **Desventajas:** Dependencia externa, posible impacto en LCP si no se pre-conecta, cumplimiento de RGPD (Google Analytics de fuentes).

### 2. Autohospedaje (Self-hosting via Vite)
- **Ventajas:** Control total del asset, cumplimiento estricto de RGPD, funciona sin conexión.
- **Desventajas:** Mayor tamaño del bundle inicial si no se optimiza, requiere descarga manual de archivos de fuente.

## Propuesta de Implementación

Utilizaremos la **Google Fonts API** como punto de partida por su simplicidad, pero configurada correctamente mediante `wp_enqueue_style` para que WordPress gestione la prioridad.

### Arquitectura de Archivos
- `inc/assets.php`: Nuevo archivo para registrar fuentes y assets externos.
- `src/scss/4-elements/_icons.scss`: Estilos base para la integración de iconos (tamaños, alineación vertical).

### Pasos Técnicos
1. Crear `inc/assets.php` con una función `silveira_enqueue_google_fonts`.
2. Incluir `Material Symbols Outlined` (la versión moderna y variable) en el enqueue.
3. Registrar el nuevo archivo en `functions.php`.
4. Crear un elemento SCSS base para normalizar el uso de iconos en el tema.
5. Añadir ejemplos de uso en la `template-kitchen-sink.php`.

## Próximos Pasos
1. Validar la propuesta con el usuario.
2. Proceder a la creación de archivos.
